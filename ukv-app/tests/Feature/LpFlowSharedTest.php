<?php

namespace Tests\Feature;

use Tests\TestCase;

class LpFlowSharedTest extends TestCase
{
    public static function lpV2Routes(): array
    {
        return [['/schengen-visa-services-uk'], ['/schengen-visa-assistance'],
                ['/schengen-visa-application-help'], ['/schengen-visa-agents-uk'],
                ['/schengen-visa-consultancy']];
    }

    public static function countryRoutes(): array
    {
        return [['/schengen-visa/france', 'France', 'fr'], ['/schengen-visa/spain', 'Spain', 'es'],
                ['/schengen-visa/germany', 'Germany', 'de'], ['/schengen-visa/netherlands', 'Netherlands', 'nl']];
    }

    /** @dataProvider lpV2Routes */
    public function test_lpv2_has_single_modal_full_picker(string $url): void
    {
        $h = $this->get($url)->assertOk()->getContent();
        $this->assertSame(1, substr_count($h, 'id=mov'), "modal not exactly once on $url");
        // 'Priority Concierge' legitimately appears in the page's own (non-modal) pricing-band
        // copy, so it is not single-source-unique. 'data-k=preference' is the modal's own unique
        // step marker (appears exactly once, only inside partials.lp-flow) and proves dedup instead.
        $this->assertSame(1, substr_count($h, 'data-k=preference'), "flow duplicated on $url");
        $this->assertMatchesRegularExpression('/BP_FLOW_DEST\s*=\s*null/', $h, "lp-v2 should keep picker: $url");
        $this->assertStringContainsString('&pound;298', $h);                 // canonical price live
    }

    /** @dataProvider countryRoutes */
    public function test_country_page_locks_flow(string $url, string $dest, string $iso): void
    {
        $h = $this->get($url)->assertOk()->getContent();
        $this->assertSame(1, substr_count($h, 'id=mov'), "modal not exactly once on $url");
        $this->assertSame(1, substr_count($h, 'data-k=preference'), "flow duplicated on $url");
        $this->assertMatchesRegularExpression('/"dest":"'.$dest.'"[^}]*"iso":"'.$iso.'"/', $h, "country not locked: $url");
        $this->assertStringContainsString('&pound;298', $h);
        $this->assertStringNotContainsString('&pound;194', $h);              // old refusal price gone
    }

    public function test_analytics_and_utm_still_injected(): void
    {
        $h = $this->get('/schengen-visa/france')->assertOk()->getContent();
        $this->assertStringContainsString('gtag', $h);        // analytics-head marker
        $this->assertStringContainsString('bpWaUrl', $h);     // utm-capture marker
    }

    public function test_no_stale_start_price_on_served_pages(): void
    {
        foreach (['/schengen-visa-services-uk', '/schengen-visa-assistance',
                  '/schengen-visa-application-help', '/schengen-visa-agents-uk',
                  '/schengen-visa-consultancy', '/schengen-visa/france', '/schengen-visa/spain',
                  '/schengen-visa/germany', '/schengen-visa/netherlands'] as $url) {
            $h = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('£39', $h, "stale £39 on $url");
            $this->assertStringNotContainsString('&pound;39', $h, "stale &pound;39 on $url");
            $this->assertStringNotContainsString('£194', $h, "stale £194 on $url");
            $this->assertStringContainsString('£60', $h, "govt fee £60 missing on $url"); // left-untouched sentinel
            $this->assertStringContainsString('£80', $h, "govt fee £80 missing on $url"); // left-untouched sentinel
            $this->assertTrue(
                str_contains($h, '£49') || str_contains($h, '&pound;49'),
                "canonical start price £49 missing on $url"
            );
            $this->assertTrue(
                str_contains($h, '£238') || str_contains($h, '&pound;238'),
                "canonical refusal price £238 missing on $url"
            );
        }
    }
}
