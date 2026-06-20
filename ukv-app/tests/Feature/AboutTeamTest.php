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
        $this->get('/about')
            ->assertOk()
            ->assertSee('Sarah Whitfield')
            ->assertSee('A UK-based team you can reach')
            ->assertSee('Beyond Passports Ltd');
    }
}
