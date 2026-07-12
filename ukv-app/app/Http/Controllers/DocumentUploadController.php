<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentUploadedBy;
use App\Http\Requests\DocumentDetailRequest;
use App\Mail\DocumentsUploaded;
use App\Models\Order;
use App\Services\DocumentService;
use App\Services\RequirementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Authenticated-by-reference customer document upload, tied to ONE order.
 *
 * Ported from ukv-doc-upload.php (Gap #68). The customer reaches this AFTER payment via their
 * order reference + email. We re-implement the WP non-enumerating auth: the order reference AND
 * the email must BOTH match the same order. A miss returns a GENERIC error — wrong-email and
 * nonexistent-ref are indistinguishable, the email is never echoed, and no other order's data
 * is ever revealed.
 *
 * Wiring (NOT done here — see reply to caller):
 *   POST /documents/upload -> store()   (apply `throttle:` to rate-limit auth probing)
 *
 * The upload itself is delegated to DocumentService, which validates type/size, stores the file
 * on the private disk, writes the documents row, and logs the upload OrderEvent.
 */
class DocumentUploadController extends Controller
{
    public function __construct(private readonly DocumentService $documents) {}

    /**
     * Render the public /documents page.
     *
     * If the request carries a matching ref+email (e.g. after submitting the detail form, or via a
     * link from the tracker/confirmation email), we load that order so the detail form can pre-fill
     * and so the personalised checklist can be computed. With no/invalid credentials the page still
     * renders — just without an order, so the checklist falls back to empty and the detail fields
     * stay blank. The lookup reuses the same non-enumerating ref+email match as the upload endpoint.
     */
    public function page(Request $request, RequirementService $requirements): View
    {
        $order = $this->authenticate(
            (string) $request->query('ref', $request->old('ref', '')),
            (string) $request->query('email', $request->old('email', '')),
        );

        // Personalised checklist partial contract: empty list when no order is identified yet.
        $docChecklist = $order !== null ? $requirements->for($order) : [];

        return view('public.documents', [
            'order' => $order,
            'docChecklist' => $docChecklist,
        ]);
    }

    /**
     * Persist the post-pay document-detail fields against the customer's order.
     *
     * Same ref+email auth as upload: a miss returns the generic non-enumerating message and never
     * reveals whether the ref or the email was wrong. On success we redirect back to the page with
     * the credentials so it re-renders pre-filled with a refreshed personalised checklist.
     */
    public function detail(DocumentDetailRequest $request): RedirectResponse
    {
        $order = $this->authenticate($request->input('ref'), $request->input('email'));

        if ($order === null) {
            return back()
                ->withInput($request->only(['ref', 'email']))
                ->with('error', "We couldn't find an application matching those details. "
                    .'Please check your reference and email, or contact us.');
        }

        $order->fill($request->detailAttributes())->save();

        return redirect()
            ->route('documents', ['ref' => $order->order_ref, 'email' => $order->email])
            ->with('status', "Thanks — we've saved your application details. Your document checklist below is now tailored to your case.");
    }

    /**
     * Validate the ref+email match, then store each uploaded file.
     *
     * Accepts a single `file` or multiple `files[]`. Returns JSON listing accepted files and
     * any per-file rejections. Auth failures and validation failures both return generic copy.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ref' => ['required', 'string', 'max:32'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'file' => ['sometimes', 'file'],
            'files' => ['sometimes', 'array'],
            'files.*' => ['file'],
        ]);

        $order = $this->authenticate($validated['ref'], $validated['email']);

        // Non-enumerating miss: identical generic message for wrong email vs nonexistent ref.
        if ($order === null) {
            return response()->json([
                'ok' => false,
                'message' => "We couldn't find an application matching those details. "
                    .'Please check your reference and email, or contact us.',
            ], 404);
        }

        // Gather uploads from either `file` or `files[]`.
        $files = [];
        if ($request->hasFile('file')) {
            $files[] = $request->file('file');
        }
        if ($request->hasFile('files')) {
            foreach ((array) $request->file('files') as $f) {
                $files[] = $f;
            }
        }

        if ($files === []) {
            return response()->json([
                'ok' => false,
                'message' => 'Please choose at least one file to upload.',
            ], 422);
        }

        $accepted = [];
        $rejected = [];

        foreach ($files as $file) {
            try {
                $document = $this->documents->store($order, $file, DocumentUploadedBy::Customer);
                $accepted[] = [
                    'id' => $document->getKey(),
                    'name' => $document->original_name,
                    'mime' => $document->mime->value,
                    'size_bytes' => $document->size_bytes,
                ];
            } catch (\InvalidArgumentException $e) {
                $rejected[] = [
                    'name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        // --- Owner ping: a customer uploaded documents, so the team can act without watching
        // the admin panel. Only when at least one file was accepted. Inline send + try/catch —
        // a mail hiccup must never fail the upload response. ---
        if ($accepted !== []) {
            $recipient = config('ukv.owner_email') ?: config('mail.from.address');
            if (! empty($recipient)) {
                try {
                    Mail::to($recipient)->send(new DocumentsUploaded(
                        $order,
                        array_map(static fn ($a) => (string) ($a['name'] ?? ''), $accepted),
                    ));
                    Log::info('Documents-uploaded emailed', ['to' => $recipient, 'order' => $order->order_ref, 'count' => count($accepted)]);
                } catch (\Throwable $e) {
                    Log::error('Documents-uploaded email failed', ['order' => $order->order_ref, 'error' => $e->getMessage()]);
                }
            }
        }

        return response()->json([
            'ok' => $accepted !== [],
            'accepted' => $accepted,
            'rejected' => $rejected,
        ], $accepted !== [] ? 201 : 422);
    }

    /**
     * Non-enumerating auth: the order reference AND email must match the SAME order.
     * Exact-match on the (normalised) reference only — no LIKE/partial probing. Email compared
     * case-insensitively (WP strtolower parity). Returns the Order on a match, null otherwise.
     */
    private function authenticate(string $ref, string $email): ?Order
    {
        $ref = strtoupper(trim($ref));
        $email = strtolower(trim($email));

        if ($ref === '' || $email === '') {
            return null;
        }

        $order = Order::query()->where('order_ref', $ref)->first();

        if ($order === null) {
            return null;
        }

        if (strtolower(trim((string) $order->email)) !== $email) {
            return null;
        }

        return $order;
    }
}
