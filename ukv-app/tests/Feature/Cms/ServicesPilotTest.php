<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServicesPilotTest extends TestCase
{
    use RefreshDatabase;

    private function seedServicesPage(): void
    {
        Page::create([
            'slug' => 'services',
            'title' => 'Services',
            'mode' => 'cms',
            'status' => 'published',
            'seo_title' => 'Our Services: UK Visa, eVisa, ETA & IDP Help | Beyond Passports',
            'blocks' => [
                ['type' => 'hero', 'data' => ['eyebrow' => 'Our services', 'title' => 'Visa and travel services, all in one place', 'lede' => 'Tell us where you are going.']],
                ['type' => 'locked-include', 'data' => ['partial' => 'services-body']],
            ],
        ]);
    }

    public function test_flag_off_serves_coded_services(): void
    {
        config(['ukv.cms.enabled' => false]);
        $this->seedServicesPage();

        // Coded page: hero title + a catalogue-driven section both present.
        $this->get('/services')
            ->assertOk()
            ->assertSee('Visa and travel services, all in one place')
            ->assertSee('Why choose us');
    }

    public function test_flag_on_serves_cms_services_with_hero_and_locked_body(): void
    {
        config(['ukv.cms.enabled' => true]);
        $this->seedServicesPage();

        // CMS page: same hero (editable block) + the locked-include body (Why choose us from services-body).
        $this->get('/services')
            ->assertOk()
            ->assertSee('Visa and travel services, all in one place')
            ->assertSee('Why choose us');
    }

    public function test_cms_hero_edit_shows_on_services(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'services',
            'title' => 'Services',
            'mode' => 'cms',
            'status' => 'published',
            'blocks' => [
                ['type' => 'hero', 'data' => ['eyebrow' => 'Our services', 'title' => 'A brand new services headline', 'lede' => 'x']],
                ['type' => 'locked-include', 'data' => ['partial' => 'services-body']],
            ],
        ]);

        $this->get('/services')->assertOk()->assertSee('A brand new services headline');
    }
}
