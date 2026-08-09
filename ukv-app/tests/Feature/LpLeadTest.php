<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Bold LP hero case-form lead endpoint (/schengen-visa-consultancy/lead).
 * Uses the 'array' mail transport so we can inspect the raw message Mail::raw() produced.
 */
class LpLeadTest extends TestCase
{
    public function test_lead_is_emailed_to_owner_inbox(): void
    {
        config(['mail.default' => 'array', 'ukv.owner_email' => 'owner@example.test']);

        $res = $this->postJson('/schengen-visa-consultancy/lead', [
            'name' => 'Jane Smith',
            'phone' => '+44 7911 000123',
            'dest' => 'Italy',
            'intent' => 'case',
        ]);

        $res->assertOk()->assertExactJson(['ok' => true]);

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages, 'exactly one lead email should be sent');

        $email = $messages[0]->getOriginalMessage();
        $this->assertSame('owner@example.test', $email->getTo()[0]->getAddress());
        $this->assertStringContainsString('Jane Smith', (string) $email->getSubject());
        $body = (string) $email->getTextBody();
        $this->assertStringContainsString('Italy', $body);
        $this->assertStringContainsString('+44 7911 000123', $body);
        $this->assertStringContainsString('Intent: case', $body);
    }

    public function test_honeypot_drops_bot_without_emailing(): void
    {
        config(['mail.default' => 'array']);

        $res = $this->postJson('/schengen-visa-consultancy/lead', [
            'name' => 'Spammy Bot',
            'website' => 'http://spam.example',
            'intent' => 'case',
        ]);

        $res->assertOk()->assertExactJson(['ok' => true]);
        $this->assertCount(0, app('mailer')->getSymfonyTransport()->messages(), 'bot post must not email');
    }

    public function test_empty_post_still_ok_and_emails_placeholder(): void
    {
        config(['mail.default' => 'array', 'ukv.owner_email' => 'owner@example.test']);

        $res = $this->postJson('/schengen-visa-consultancy/lead', ['intent' => 'appointment']);

        $res->assertOk()->assertExactJson(['ok' => true]);
        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Intent: appointment', (string) $messages[0]->getOriginalMessage()->getTextBody());
    }
}
