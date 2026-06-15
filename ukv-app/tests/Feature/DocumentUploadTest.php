<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EligibilityLane;
use App\Enums\OrderStatus;
use App\Models\Destination;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Customer document upload (POST /documents/upload -> DocumentUploadController::store
 * -> DocumentService). Auth is by order ref + email (non-enumerating). The disk is faked
 * so no real files are written.
 *
 * Contract assertions: accepted upload persists a documents row; wrong email is a generic
 * reject that never echoes the email or another order's data; bad type/oversize are rejected.
 */
final class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $dest = Destination::create([
            'name' => 'Docland',
            'slug' => 'docland',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ]);

        return Order::create(array_merge([
            'name' => 'Doc Customer',
            'applicant_name' => 'Doc Customer',
            'email' => 'doc.customer@example.com',
            'destination_id' => $dest->getKey(),
            'destination_name' => 'Docland',
            'status' => OrderStatus::AwaitingDocs->value,
            'eligibility' => EligibilityLane::Standard->value,
        ], $overrides));
    }

    public function test_valid_pdf_with_matching_ref_and_email_is_accepted_and_persisted(): void
    {
        Storage::fake('local');
        $order = $this->makeOrder();

        $response = $this->postJson('/documents/upload', [
            'ref' => $order->order_ref,
            'email' => $order->email,
            'file' => UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('ok', true);
        $response->assertJsonCount(1, 'accepted');

        // A documents row was written against this order.
        $this->assertSame(1, $order->documents()->count());
        $document = $order->documents()->first();
        $this->assertSame('passport.pdf', $document->original_name);
        $this->assertSame('application/pdf', $document->mime->value);

        // The file landed on the (faked) private disk under the per-order directory.
        Storage::disk('local')->assertExists($document->path);
    }

    public function test_email_case_insensitive_match_is_accepted(): void
    {
        Storage::fake('local');
        $order = $this->makeOrder();

        $response = $this->postJson('/documents/upload', [
            'ref' => strtolower($order->order_ref),
            'email' => strtoupper($order->email),
            'file' => UploadedFile::fake()->create('p.pdf', 50, 'application/pdf'),
        ]);

        $response->assertStatus(201);
        $this->assertSame(1, $order->documents()->count());
    }

    public function test_wrong_email_is_a_generic_reject_with_no_document_and_no_email_echo(): void
    {
        Storage::fake('local');
        $order = $this->makeOrder();

        $wrongEmail = 'attacker@example.com';

        $response = $this->postJson('/documents/upload', [
            'ref' => $order->order_ref,
            'email' => $wrongEmail,
            'file' => UploadedFile::fake()->create('p.pdf', 50, 'application/pdf'),
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('ok', false);
        // Generic copy — never confirms which of ref/email was wrong, never echoes the email.
        $response->assertDontSee($wrongEmail, escape: false);
        $response->assertDontSee($order->email, escape: false);
        // No document was attached to the order.
        $this->assertSame(0, $order->documents()->count());
    }

    public function test_unknown_ref_is_a_generic_reject(): void
    {
        Storage::fake('local');
        $order = $this->makeOrder();

        $response = $this->postJson('/documents/upload', [
            'ref' => 'UKV-2099-999999',
            'email' => $order->email,
            'file' => UploadedFile::fake()->create('p.pdf', 50, 'application/pdf'),
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('ok', false);
        $this->assertSame(0, $order->documents()->count());
    }

    public function test_disallowed_mime_is_rejected(): void
    {
        Storage::fake('local');
        $order = $this->makeOrder();

        // An executable masquerading by extension — not in the allow-list.
        $response = $this->postJson('/documents/upload', [
            'ref' => $order->order_ref,
            'email' => $order->email,
            'file' => UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream'),
        ]);

        // Auth passed but the file is rejected: 422 with a per-file rejection, no row written.
        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $response->assertJsonCount(0, 'accepted');
        $this->assertSame(0, $order->documents()->count());
    }

    public function test_oversized_file_is_rejected(): void
    {
        Storage::fake('local');
        $order = $this->makeOrder();

        // 11 MB > the 10 MB (10485760 byte) cap. UploadedFile::fake size is in KB.
        $response = $this->postJson('/documents/upload', [
            'ref' => $order->order_ref,
            'email' => $order->email,
            'file' => UploadedFile::fake()->create('huge.pdf', 11 * 1024, 'application/pdf'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $this->assertSame(0, $order->documents()->count());
    }

    public function test_no_file_supplied_is_rejected_with_422(): void
    {
        Storage::fake('local');
        $order = $this->makeOrder();

        $response = $this->postJson('/documents/upload', [
            'ref' => $order->order_ref,
            'email' => $order->email,
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, $order->documents()->count());
    }
}
