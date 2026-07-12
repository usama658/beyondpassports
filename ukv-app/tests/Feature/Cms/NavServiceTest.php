<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Setting;
use App\Support\NavService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NavServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_when_flag_off(): void
    {
        config(['ukv.cms.enabled' => false]);

        $primary = NavService::primary();
        $this->assertSame('Schengen visa', $primary[0]['label']);
        $this->assertStringEndsWith('/schengen-visa', $primary[0]['url']);

        $ctas = NavService::ctas();
        $this->assertSame('ghost', $ctas[0]['variant']);
        $this->assertTrue($ctas[1]['external']);

        $this->assertCount(3, NavService::footerColumns());
    }

    public function test_override_ignored_while_flag_off(): void
    {
        config(['ukv.cms.enabled' => false]);
        Setting::put('nav_primary', json_encode([['label' => 'Hacked', 'url' => 'https://evil.test']]));

        // Flag off: coded default wins, override never read.
        $this->assertSame('Schengen visa', NavService::primary()[0]['label']);
    }

    public function test_primary_override_replaces_when_flag_on(): void
    {
        config(['ukv.cms.enabled' => true]);
        Setting::put('nav_primary', json_encode([
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Visas', 'url' => '/schengen-visa'],
        ]));

        $primary = NavService::primary();
        $this->assertCount(2, $primary);
        $this->assertSame('Home', $primary[0]['label']);
    }

    public function test_cta_override_keeps_code_side_variant_and_target(): void
    {
        config(['ukv.cms.enabled' => true]);
        // Team edits only label + url; a smuggled variant/external must be ignored.
        Setting::put('nav_ctas', json_encode([
            ['label' => 'Talk to us', 'url' => '/contact', 'variant' => 'evil', 'external' => true],
            ['label' => 'Am I eligible?', 'url' => 'https://wa.me/x'],
        ]));

        $ctas = NavService::ctas();
        $this->assertSame('Talk to us', $ctas[0]['label']);   // label overridden
        $this->assertSame('ghost', $ctas[0]['variant']);      // structural kept from code, not 'evil'
        $this->assertFalse($ctas[0]['external']);             // structural kept from code
        $this->assertSame('Am I eligible?', $ctas[1]['label']);
        $this->assertTrue($ctas[1]['external']);              // eligibility button stays new-tab
    }

    public function test_cta_override_cannot_add_or_remove_slots(): void
    {
        config(['ukv.cms.enabled' => true]);
        Setting::put('nav_ctas', json_encode([['label' => 'Only one', 'url' => '/x']]));

        $ctas = NavService::ctas();
        $this->assertCount(2, $ctas);                         // still exactly two slots
        $this->assertSame('Check eligibility →', $ctas[1]['label']); // untouched slot keeps default
    }
}
