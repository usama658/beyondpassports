<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DocumentMime;
use App\Enums\EligibilityLane;
use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Jobs\GenerateDocReview;
use App\Models\Destination;
use App\Models\Document;
use App\Models\Order;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * VISION-based advisory document review (Phase-2 #99).
 *
 * Proves the leak gate: exactly ONE image block + a generic instruction leave the app — no PII,
 * no order refs, no filename, no second document. Also proves the safe-by-default no-op when no
 * Anthropic key is configured, and that the queued job records a clearly-marked advisory event.
 */
final class DocReviewTest extends TestCase
{
    use RefreshDatabase;

    private const FAKE_KEY = 'sk-ant-test-key';

    private function makeOrder(array $overrides = []): Order
    {
        $dest = Destination::create([
            'name' => 'Visionland',
            'slug' => 'visionland',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ]);

        return Order::create(array_merge([
            'name' => 'Vera Vision',
            'applicant_name' => 'Vera Vision',
            'email' => 'vera.vision@example.com',
            'passport_number' => 'X1234567',
            'destination_id' => $dest->getKey(),
            'destination_name' => 'Visionland',
            'status' => OrderStatus::AwaitingDocs->value,
            'eligibility' => EligibilityLane::Standard->value,
        ], $overrides));
    }

    private function makeImageDocument(Order $order, string $mime = 'image/jpeg', string $name = 'John-Smith-passport.jpg'): Document
    {
        // Put real bytes on the faked private disk so AiService can read + base64 them.
        $path = 'order-documents/'.$order->getKey().'/'.uniqid('doc', true).'.bin';
        Storage::disk('local')->put($path, 'PRETEND-IMAGE-BYTES');

        return $order->documents()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => $name,
            'mime' => $mime,
            'size_bytes' => 19,
            'uploaded_by' => 'customer',
        ]);
    }

    // -------------------------------------------------------------------------------------------
    // Safe-by-default: no key => no-op
    // -------------------------------------------------------------------------------------------

    public function test_review_document_image_no_ops_to_null_when_key_is_empty(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', '');
        Http::fake(); // any HTTP call would be a failure of the no-op guarantee

        $order = $this->makeOrder();
        $document = $this->makeImageDocument($order);

        $result = app(AiService::class)->reviewDocumentImage($document);

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    public function test_job_records_nothing_when_key_is_empty(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', '');
        Http::fake();

        $order = $this->makeOrder();
        $document = $this->makeImageDocument($order);

        (new GenerateDocReview($document))->handle(app(AiService::class));

        $this->assertSame(0, $order->events()->where('agent', 'ai-vision')->count());
        Http::assertNothingSent();
    }

    // -------------------------------------------------------------------------------------------
    // Not-an-image => skipped (no HTTP), even with a key
    // -------------------------------------------------------------------------------------------

    public function test_non_image_document_is_skipped_even_with_key(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', self::FAKE_KEY);
        Http::fake();

        $order = $this->makeOrder();
        $pdf = $this->makeImageDocument($order, DocumentMime::Pdf->value, 'evidence.pdf');

        $result = app(AiService::class)->reviewDocumentImage($pdf);

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    public function test_missing_file_returns_null(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', self::FAKE_KEY);
        Http::fake();

        $order = $this->makeOrder();
        $document = $order->documents()->create([
            'disk' => 'local',
            'path' => 'order-documents/'.$order->getKey().'/gone.jpg',
            'original_name' => 'gone.jpg',
            'mime' => 'image/jpeg',
            'size_bytes' => 10,
            'uploaded_by' => 'customer',
        ]);

        $result = app(AiService::class)->reviewDocumentImage($document);

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    // -------------------------------------------------------------------------------------------
    // Happy path with a key: exactly one image + generic text, no PII; job records advisory event
    // -------------------------------------------------------------------------------------------

    public function test_leak_gate_sends_only_one_image_and_generic_text_no_pii(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', self::FAKE_KEY);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => 'Legible. Looks like a passport bio page. Looks usable.']],
            ], 200),
        ]);

        $order = $this->makeOrder();
        $document = $this->makeImageDocument($order);

        $result = app(AiService::class)->reviewDocumentImage($document);

        $this->assertSame('Legible. Looks like a passport bio page. Looks usable.', $result);

        Http::assertSent(function (Request $request) use ($order, $document): bool {
            $body = $request->data();

            // Exactly one user message, with exactly one image block and one text block.
            $content = $body['messages'][0]['content'];
            $images = array_filter($content, fn ($b) => ($b['type'] ?? null) === 'image');
            $this->assertCount(1, $images, 'must send exactly one image block');

            $image = array_values($images)[0];
            $this->assertSame('base64', $image['source']['type']);
            $this->assertSame('image/jpeg', $image['source']['media_type']);
            $this->assertSame(base64_encode('PRETEND-IMAGE-BYTES'), $image['source']['data']);

            // The ENTIRE serialized request must not contain ANY PII or order identifiers.
            $json = json_encode($body);
            // Real PII/identifiers only — NOT the bare numeric id (collides with JSON numbers
            // like max_tokens) and not the empty-string from any null field.
            foreach (array_filter([
                $order->order_ref,
                $order->email,
                $order->name,
                $order->applicant_name,
                $order->passport_number,
                $order->destination_name,
                $document->original_name,         // filename can itself be PII
                $document->path,
            ]) as $needle) {
                $this->assertStringNotContainsString((string) $needle, (string) $json,
                    "leaked '{$needle}' to the vision API");
            }

            // Correct auth header, image content type present.
            $this->assertSame(self::FAKE_KEY, $request->header('x-api-key')[0] ?? null);

            return true;
        });
    }

    public function test_job_records_clearly_marked_advisory_event_on_success(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', self::FAKE_KEY);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => 'Sharp and fully in frame. Looks usable.']],
            ], 200),
        ]);

        $order = $this->makeOrder();
        $document = $this->makeImageDocument($order);

        (new GenerateDocReview($document))->handle(app(AiService::class));

        $event = $order->events()->where('agent', 'ai-vision')->first();
        $this->assertNotNull($event);
        $this->assertSame(EventType::System, $event->type);
        $this->assertSame(EventChannel::Internal, $event->channel);
        $this->assertStringContainsString('advisory', strtolower($event->text));
        $this->assertStringContainsString('Sharp and fully in frame', $event->text);
        $this->assertTrue($event->meta['advisory'] ?? false);
        $this->assertTrue($event->meta['ai_generated'] ?? false);
        $this->assertSame('ai_doc_review_vision', $event->meta['source'] ?? null);
        $this->assertSame($document->getKey(), $event->meta['document_id'] ?? null);

        // Advisory only: the order status must be untouched.
        $this->assertSame(OrderStatus::AwaitingDocs, $order->fresh()->status);
    }

    public function test_job_records_nothing_when_api_returns_error(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', self::FAKE_KEY);
        Http::fake(['api.anthropic.com/*' => Http::response('nope', 500)]);

        $order = $this->makeOrder();
        $document = $this->makeImageDocument($order);

        (new GenerateDocReview($document))->handle(app(AiService::class));

        $this->assertSame(0, $order->events()->where('agent', 'ai-vision')->count());
    }

    // -------------------------------------------------------------------------------------------
    // DocumentService dispatch wiring
    // -------------------------------------------------------------------------------------------

    public function test_upload_does_not_dispatch_review_when_key_is_empty(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', '');
        Queue::fake();

        $order = $this->makeOrder();

        $this->postJson('/documents/upload', [
            'ref' => $order->order_ref,
            'email' => $order->email,
            'file' => UploadedFile::fake()->image('passport.jpg'),
        ])->assertStatus(201);

        Queue::assertNotPushed(GenerateDocReview::class);
    }

    public function test_upload_dispatches_review_for_image_when_key_is_set(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', self::FAKE_KEY);
        Queue::fake();

        $order = $this->makeOrder();

        $this->postJson('/documents/upload', [
            'ref' => $order->order_ref,
            'email' => $order->email,
            'file' => UploadedFile::fake()->image('passport.jpg'),
        ])->assertStatus(201);

        Queue::assertPushed(GenerateDocReview::class, 1);
    }

    public function test_upload_does_not_dispatch_review_for_pdf(): void
    {
        Storage::fake('local');
        Config::set('services.anthropic.key', self::FAKE_KEY);
        Queue::fake();

        $order = $this->makeOrder();

        $this->postJson('/documents/upload', [
            'ref' => $order->order_ref,
            'email' => $order->email,
            'file' => UploadedFile::fake()->create('evidence.pdf', 100, 'application/pdf'),
        ])->assertStatus(201);

        Queue::assertNotPushed(GenerateDocReview::class);
    }
}
