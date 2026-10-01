<?php
// tests/Feature/LpAssemblerTest.php
namespace Tests\Feature;

use App\Support\LpAssembler;
use Tests\TestCase;

class LpAssemblerTest extends TestCase
{
    private string $doc = '<html><head></head><body><h1>Hi</h1></body></html>';

    public function test_injects_head_flow_and_utm(): void
    {
        $out = LpAssembler::inject($this->doc, null);
        $this->assertStringContainsString('id=mov', $out);                 // flow injected
        $this->assertStringContainsString('window.BP_FLOW_DEST', $out);    // shim present
        $this->assertStringContainsString('<h1>Hi</h1>', $out);            // original kept
        $this->assertStringContainsString('</body>', $out);
    }

    public function test_country_lock_sets_dest_iso(): void
    {
        $out = LpAssembler::inject($this->doc, ['dest' => 'Spain', 'iso' => 'es']);
        $this->assertMatchesRegularExpression('/BP_FLOW_DEST\s*=\s*\{[^}]*"dest":"Spain"[^}]*"iso":"es"/', $out);
    }

    public function test_null_flowdest_renders_null_shim(): void
    {
        $out = LpAssembler::inject($this->doc, null);
        $this->assertMatchesRegularExpression('/BP_FLOW_DEST\s*=\s*null/', $out);
    }

    public function test_malformed_flowdest_degrades_to_null(): void
    {
        $out = LpAssembler::inject($this->doc, ['iso' => 'es']); // no dest
        $this->assertMatchesRegularExpression('/BP_FLOW_DEST\s*=\s*null/', $out);
    }
}
