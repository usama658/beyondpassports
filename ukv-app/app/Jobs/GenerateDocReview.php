<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Models\Document;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Queued job: run AiService::reviewDocumentImage for ONE uploaded document IMAGE and record the
 * (advisory) vision result as an OrderEvent on that document's order (Phase-2 #99).
 *
 * The recorded event is clearly marked AI-generated and advisory:
 *   - type    = system     (not a human note / stage change)
 *   - channel = internal   (never customer-facing)
 *   - agent   = 'ai-vision' (so it's never attributed to a human operator, and is distinct from the
 *               text 'ai' agent used by GenerateNextBestAction)
 *   - meta.advisory = true, meta.ai_generated = true, meta.source = 'ai_doc_review_vision',
 *     meta.document_id = the reviewed document
 *
 * Safety / leak gate:
 *   - SerializesModels stores only the document id and re-fetches a fresh model on handle().
 *   - The ONLY data that leaves the app is the single document image (see AiService::reviewDocumentImage);
 *     this job adds NO PII to the event text beyond the model's quality note.
 *   - When no Anthropic key is configured, or the document is not a reviewable image, or its file is
 *     gone, AiService no-ops (returns null) and this job records NOTHING — a cheap success pre-launch.
 *   - Nothing here changes the order status or any customer-facing field; it only appends an event.
 *
 * Dispatch this AFTER a committed, successful image upload (see DocumentService::store). Example:
 *
 *   GenerateDocReview::dispatch($document);
 */
final class GenerateDocReview implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** A couple of retries — Anthropic 429/5xx are transient. */
    public int $tries = 3;

    /** Backoff between attempts (seconds). */
    public int $backoff = 30;

    /** Vision calls can be a touch slower than text; allow a little more wall-clock. */
    public int $timeout = 90;

    public function __construct(
        public readonly Document $document,
    ) {}

    public function handle(AiService $ai): void
    {
        // Re-fetch fresh; the document may have been purged/deleted between dispatch and run.
        $document = $this->document->fresh();

        if ($document === null || $document->purged_at !== null) {
            return;
        }

        $note = $ai->reviewDocumentImage($document);

        if ($note === null) {
            // No key / not an image / file gone / AI failure — all already logged by AiService.
            return;
        }

        $order = $document->order;

        if ($order === null) {
            // Orphaned document (should not happen) — nothing to attach the advisory to.
            return;
        }

        $order->events()->create([
            'occurred_at' => now(),
            'agent' => 'ai-vision',
            'channel' => EventChannel::Internal,
            'type' => EventType::System,
            'text' => 'AI document image review (advisory draft): '.$note,
            'meta' => [
                'source' => 'ai_doc_review_vision',
                'advisory' => true,
                'ai_generated' => true,
                'document_id' => $document->getKey(),
            ],
        ]);
    }

    /**
     * Final-failure hook (after all retries). AiService already logs per-call failures; this records
     * that the whole job gave up, so it can be reconciled later. No secrets / PII / bytes logged.
     */
    public function failed(Throwable $e): void
    {
        Log::error('GenerateDocReview permanently failed.', [
            'document_id' => $this->document->getKey(),
            'order_id' => $this->document->order_id,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Coalesce rapid repeat dispatches for the same document onto one key (requires ShouldBeUnique to
     * activate; left as a helper so it can be opted into without changing dispatch sites).
     */
    public function uniqueId(): string
    {
        return 'ai-doc-review-'.$this->document->getKey();
    }
}
