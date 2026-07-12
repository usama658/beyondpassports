<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LpEmailStripTest extends TestCase
{
    use RefreshDatabase;

    public static function lpRoutes(): array
    {
        return [
            ['/schengen-visa-agent'],
            ['/schengen-visa-refusal-risk'],
            ['/schengen-visa-appointment'],
            ['/honest-schengen-visa-service'],
        ];
    }

    /**
     * @dataProvider lpRoutes
     */
    public function test_whatsapp_only_lp_carries_the_email_fallback_strip(string $url): void
    {
        $this->get($url)->assertOk()
            ->assertSee('lpes-card', false)                       // the strip rendered
            ->assertSee('name="e"', false)                        // email input present
            ->assertSee(route('appointment.enquiry'), false);     // posts to the capture endpoint
    }
}
