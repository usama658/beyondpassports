<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AboutTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_shows_team_and_location_from_config(): void
    {
        // Team lead + company name both come from config('ukv.team') / config('ukv.address').
        $this->get('/about')
            ->assertOk()
            ->assertSee('Sarah Whitmore')
            ->assertSee('Who we are')
            ->assertSee('Beyond Passports Ltd');
    }
}
