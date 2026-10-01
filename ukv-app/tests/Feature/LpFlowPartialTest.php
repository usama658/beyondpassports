<?php
// tests/Feature/LpFlowPartialTest.php
namespace Tests\Feature;

use Tests\TestCase;

class LpFlowPartialTest extends TestCase
{
    private function html(): string
    {
        return view('partials.lp-flow')->render();
    }

    public function test_modal_appears_exactly_once(): void
    {
        $this->assertSame(1, substr_count($this->html(), 'id=mov'));
    }

    public function test_canonical_prices_present(): void
    {
        $h = $this->html();
        foreach (['&pound;49', '&pound;109', '&pound;249', '&pound;158', '&pound;298', '&pound;238', '&pound;89', '&pound;209'] as $p) {
            $this->assertStringContainsString($p, $h, "missing $p");
        }
    }

    public function test_removed_prices_absent(): void
    {
        $h = $this->html();
        foreach (['&pound;39', '&pound;99', '&pound;119', '&pound;138', '&pound;145', '&pound;159', '&pound;184', '&pound;194', '&pound;198'] as $p) {
            $this->assertStringNotContainsString($p, $h, "stale price $p still present");
        }
    }

    public function test_runtime_globals_are_guarded(): void
    {
        $h = $this->html();
        foreach (['window.CTRY=window.CTRY||', 'window.vSnap=window.vSnap||'] as $g) {
            $this->assertStringContainsString($g, $h, "unguarded global: $g");
        }
        $this->assertStringContainsString('window.postLead', $h);          // guarded call
        $this->assertStringContainsString('window.bpWaUrl||String', $h);   // wa fallback
        $this->assertStringContainsString('window.BP_FLOW_DEST', $h);      // country-lock shim read
    }
}
