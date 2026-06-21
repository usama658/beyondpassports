<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChatCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_wa_cta_builds_link_with_encoded_message_and_label(): void
    {
        config(['ukv.whatsapp' => '447700900123']);

        $html = view('partials.wa-cta', [
            'message' => 'Help with Turkey',
            'label' => 'Ask on WhatsApp',
        ])->render();

        $this->assertStringContainsString('https://wa.me/447700900123', $html);
        $this->assertStringContainsString('text=Help+with+Turkey', $html); // urlencode → spaces as '+'
        $this->assertStringContainsString('Ask on WhatsApp', $html);
        $this->assertStringContainsString('rel="noopener"', $html);
        $this->assertStringContainsString('data-wa-cta', $html);
    }

    public function test_wa_cta_without_message_has_no_text_param(): void
    {
        config(['ukv.whatsapp' => '447700900123']);

        $html = view('partials.wa-cta', ['label' => 'Chat'])->render();

        $this->assertStringContainsString('https://wa.me/447700900123', $html);
        $this->assertStringNotContainsString('?text=', $html);
    }

    public function test_wa_cta_falls_back_to_placeholder_number_when_unset(): void
    {
        config(['ukv.whatsapp' => '']);

        $html = view('partials.wa-cta', ['label' => 'Chat'])->render();

        $this->assertStringContainsString('https://wa.me/440000000000', $html);
    }

    public function test_floating_button_present_on_layout_page(): void
    {
        $this->get('/')->assertOk()->assertSee('data-wa-float', false);
    }

    public function test_floating_button_present_on_standalone_track_page(): void
    {
        $this->get('/track')->assertOk()->assertSee('data-wa-float', false);
    }
}
