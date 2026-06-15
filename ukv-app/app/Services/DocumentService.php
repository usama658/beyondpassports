<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentMime;
use App\Enums\DocumentUploadedBy;
use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Jobs\GenerateDocReview;
use App\Models\Document;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Customer-document storage + listing + deletion for an order.
 *
 * Ported from the WP mu-plugin ukv-doc-upload.php (Gap #68). The WP version:
 *   - accepts a STRICT allow-list of types: jpg/jpeg -> image/jpeg, png -> image/png,
 *     pdf -> application/pdf, heic -> image/heic (UKV_DOC_UPLOAD_ALLOWED);
 *   - caps each file at 10 MB (UKV_DOC_UPLOAD_MAX_BYTES = 10485760);
 *   - checks BOTH the extension AND the real bytes agree (wp_check_filetype_and_ext);
 *   - stores the file PRIVATELY, parented to exactly one order;
 *   - appends the attachment to the order's document list;
 *   - logs a journey note on channel "upload", agent "customer".
 *
 * Here the file lands on Laravel's private disk, a `documents` row is written, and an
 * OrderEvent (channel=upload, type=note, agent=customer) records it — same contract.
 *
 * Authentication (ref + email non-enumerating match) is the controller's job; this service
 * trusts that the caller has already resolved the correct Order.
 */
final class DocumentService
{
    /** Files are stored on this Laravel disk. Must be a PRIVATE (non-public) disk. */
    public const DISK = 'local';

    /** Per-order storage prefix. The full path is {DIR}/{order_id}/{name}. */
    public const DIR = 'order-documents';

    /** Max bytes per uploaded file — 10 MB, matching UKV_DOC_UPLOAD_MAX_BYTES. */
    public const MAX_BYTES = 10_485_760;

    /**
     * Allowed extension => canonical MIME. The ONLY types accepted.
     * Mirrors UKV_DOC_UPLOAD_ALLOWED (note jpg AND jpeg both map to image/jpeg).
     *
     * @var array<string, string>
     */
    public const ALLOWED = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
        'heic' => 'image/heic',
    ];

    public function __construct(private readonly OrderService $orders) {}

    /**
     * Validate + store ONE uploaded file against an order, write the documents row, and log
     * an OrderEvent. Returns the persisted Document.
     *
     * @param  Order  $order  the already-authenticated target order
     * @param  UploadedFile  $file  the incoming upload
     * @param  DocumentUploadedBy  $uploadedBy  who uploaded it (default: customer)
     *
     * @throws \InvalidArgumentException on any validation failure (bad type, mismatch, too large, empty).
     */
    public function store(
        Order $order,
        UploadedFile $file,
        DocumentUploadedBy $uploadedBy = DocumentUploadedBy::Customer,
    ): Document {
        $mime = $this->validate($file);

        $original = $file->getClientOriginalName();
        $size = (int) $file->getSize();

        // Store privately under a per-order directory. Laravel hashes the stored filename so
        // two uploads of the same client name never collide; the human name is kept in the row.
        $path = $file->store(self::DIR.'/'.$order->getKey(), self::DISK);

        if ($path === false || $path === '') {
            throw new \RuntimeException('Could not store the uploaded file.');
        }

        $document = DB::transaction(function () use ($order, $path, $original, $mime, $size, $uploadedBy): Document {
            $document = $order->documents()->create([
                'disk' => self::DISK,
                'path' => $path,
                'original_name' => $original,
                'mime' => $mime->value,
                'size_bytes' => $size,
                'uploaded_by' => $uploadedBy->value,
            ]);

            // Journey note — channel "upload", agent "customer" (WP parity).
            $this->orders->recordEvent(
                $order,
                EventType::Note,
                'Document uploaded: '.$original,
                channel: EventChannel::Upload,
                agent: $uploadedBy->value,
                meta: [
                    'document_id' => $document->getKey(),
                    'original_name' => $original,
                    'mime' => $mime->value,
                    'size_bytes' => $size,
                ],
            );

            return $document;
        });

        // Opt-in, safe-by-default: queue an ADVISORY vision review of the image (Phase-2 #99).
        // Only for raster images we can actually send to the vision endpoint, and only when an
        // Anthropic key is configured — so this is a guaranteed no-op pre-launch. The job itself
        // also guards again (key + image + file present) and never changes order state.
        $this->maybeDispatchDocReview($document, $mime);

        return $document;
    }

    /**
     * MIME types eligible for the advisory vision review. PDFs/HEIC are excluded — the vision
     * endpoint takes plain raster image blocks, and limiting the set also limits the leak surface.
     *
     * @var array<int, string>
     */
    private const VISION_REVIEWABLE = [
        DocumentMime::Jpeg->value,
        DocumentMime::Png->value,
    ];

    /**
     * Queue an advisory vision review iff: the file is a reviewable image AND an Anthropic key is
     * configured. Both guards keep this opt-in and safe pre-launch; the job re-checks regardless.
     */
    private function maybeDispatchDocReview(Document $document, DocumentMime $mime): void
    {
        if (! in_array($mime->value, self::VISION_REVIEWABLE, true)) {
            return;
        }

        if (trim((string) config('services.anthropic.key', '')) === '') {
            return;
        }

        GenerateDocReview::dispatch($document);
    }

    /**
     * All non-purged documents for an order, newest first.
     *
     * @return Collection<int, Document>
     */
    public function listForOrder(Order $order): Collection
    {
        return $order->documents()
            ->whereNull('purged_at')
            ->latest()
            ->get();
    }

    /**
     * Delete a single document: remove the stored file then the row. Logs an OrderEvent.
     * Safe to call on a document whose file is already gone.
     */
    public function delete(Document $document, string $reason = 'Document deleted'): void
    {
        $order = $document->order;

        DB::transaction(function () use ($document, $order, $reason): void {
            if ($document->disk && $document->path && Storage::disk($document->disk)->exists($document->path)) {
                Storage::disk($document->disk)->delete($document->path);
            }

            $name = $document->original_name;
            $id = $document->getKey();
            $document->delete();

            if ($order !== null) {
                $this->orders->recordEvent(
                    $order,
                    EventType::Note,
                    $reason.': '.($name ?? "#{$id}"),
                    channel: EventChannel::Internal,
                    agent: 'system',
                    meta: ['document_id' => $id, 'original_name' => $name],
                );
            }
        });
    }

    /**
     * Strictly validate the upload against the allow-list + size cap, confirming the declared
     * extension and the sniffed MIME agree (WP's wp_check_filetype_and_ext contract).
     *
     * @throws \InvalidArgumentException
     */
    private function validate(UploadedFile $file): DocumentMime
    {
        if (! $file->isValid()) {
            throw new \InvalidArgumentException('The file failed to upload. Please try again.');
        }

        $size = (int) $file->getSize();
        if ($size <= 0) {
            throw new \InvalidArgumentException('The file is empty.');
        }
        if ($size > self::MAX_BYTES) {
            throw new \InvalidArgumentException('That file is too large. The maximum is 10 MB.');
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if ($ext === '' || ! isset(self::ALLOWED[$ext])) {
            throw new \InvalidArgumentException('That file type is not allowed. Please upload a JPG, PNG, PDF or HEIC.');
        }

        $expected = self::ALLOWED[$ext];

        // Sniff the real bytes. getMimeType() reads the file content (finfo), not the client
        // header, so a renamed file is caught. HEIC is frequently mis-sniffed by finfo builds
        // (often application/octet-stream); accept it on extension alone, matching the WP
        // fallback for heic/pdf when finfo gives no usable type.
        $sniffed = (string) $file->getMimeType();

        if ($ext === 'heic') {
            return DocumentMime::Heic;
        }

        // jpg/jpeg/png/pdf: the sniffed type must match the canonical type for the extension.
        // PDFs may sniff as application/pdf; if finfo returns octet-stream for a .pdf, allow it
        // (same WP leniency for pdf), but never for image types.
        if ($sniffed === $expected) {
            return DocumentMime::from($expected);
        }

        if ($ext === 'pdf' && in_array($sniffed, ['application/octet-stream', ''], true)) {
            return DocumentMime::Pdf;
        }

        throw new \InvalidArgumentException('The file content does not match its type. Please upload a valid document.');
    }
}
