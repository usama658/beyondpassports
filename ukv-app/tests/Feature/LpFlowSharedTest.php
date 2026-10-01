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
}
