<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomePreventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_prevention_section(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('How we prevent refusals');
    }

    public function test_prevention_section_has_no_forbidden_claims(): void
    {
        $html = strtolower($this->get('/')->assertOk()->getContent());

        foreach (['guarantee approval', 'guaranteed approval', '99%', '95%', '% approved'] as $bad) {
            $this->assertStringNotContainsString($bad, $html, "forbidden phrase '{$bad}' on home");
        }
    }
}
