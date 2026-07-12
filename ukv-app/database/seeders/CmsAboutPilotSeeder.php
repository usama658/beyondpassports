<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * CMS Phase 3: the About page as a single locked-include of partials.about-body (the full coded
 * About content, verbatim). SEO title/description mirror about.blade.php so the head is identical.
 * Idempotent; only takes effect when UKV_CMS_ENABLED is on. Editable prose blocks for About can be
 * layered on later by extracting specific sections; the body stays byte-identical meanwhile.
 */
class CmsAboutPilotSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About',
                'mode' => 'cms',
                'status' => 'published',
                'seo_title' => 'About Us: Independent Schengen Visa Service, UK & Europe | Beyond Passports',
                'seo_description' => 'Beyond Passports is an independent Schengen visa consultancy registered in the UK and Europe. Not a government website. Clear fees, real human checks, honest advice.',
                'blocks' => [
                    ['type' => 'locked-include', 'data' => ['partial' => 'about-body']],
                ],
            ],
        );

        $this->command?->info('CmsAboutPilotSeeder: cms About page seeded (published).');
    }
}
