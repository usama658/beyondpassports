<?php
// tests/Feature/TenureToggleTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenureToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenure_claims_show_by_default(): void
    {
        config(['ukv.stats.show_tenure' => true]);

        $spain = $this->get('/schengen-visa/spain')->assertOk()->getContent();
        $this->assertStringContainsString('since 2019', $spain);

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('Applications filed in', $home);
        $this->assertStringContainsString('years', $home);
    }

    public function test_tenure_claims_hidden_when_flag_off(): void
    {
        config(['ukv.stats.show_tenure' => false]);

        $spain = $this->get('/schengen-visa/spain')->assertOk()->getContent();
        $this->assertStringNotContainsString('since 2019', $spain);
        // Factual visa-rule year claim (refusal record retention) must survive.
        $this->assertStringContainsString('five years', $spain);

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('Applications filed in', $home);
        $this->assertStringContainsString('Applications filed', $home);

        $fear = $this->get('/schengen-visa-refusal-risk')->assertOk()->getContent();
        $this->assertStringNotContainsString('Applications filed in', $fear);
    }
}
