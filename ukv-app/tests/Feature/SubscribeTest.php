<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SubscribeTest extends TestCase
{
    public function test_valid_consented_opt_in_succeeds(): void
    {
        $response = $this->post('/subscribe', [
            'email' => 'traveller@example.com',
            'consent' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('subscribe_status');
        $response->assertSessionHasNoErrors();
    }

    public function test_missing_consent_is_rejected(): void
    {
        $response = $this->from('/')->post('/subscribe', [
            'email' => 'traveller@example.com',
            // no consent
        ]);

        $response->assertSessionHasErrors('consent');
    }

    public function test_invalid_email_is_rejected(): void
    {
        $response = $this->from('/')->post('/subscribe', [
            'email' => 'not-an-email',
            'consent' => '1',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
