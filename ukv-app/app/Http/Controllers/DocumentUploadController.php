<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentUploadedBy;
use App\Models\Order;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
