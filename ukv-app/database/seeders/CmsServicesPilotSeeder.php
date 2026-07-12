<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * CMS Phase 2 pilot: the Services page as blocks. Hero prose is editable; the config-driven
 * catalogue + how/why/faq/cta stay verbatim via a locked-include of partials.services-body.
 * SEO title/description mirror the coded services.blade.php so the <head> is identical too.
 * Idempotent (updateOrCreate on slug). Published only takes effect when UKV_CMS_ENABLED is on.
 */
class CmsServicesPilotSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(
            ['slug' => 'services'],
            [
                'title' => 'Services',
                'mode' => 'cms',
                'status' => 'published',
                'seo_title' => 'Our Services: UK Visa, eVisa, ETA & IDP Help | Beyond Passports',
                'seo_description' => 'Every Beyond Passports service in one place: Schengen and eVisa preparation, appointments, documents, refusal prevention, driving permits and free tools. Independent UK team. Not a government website.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Our services',
                            'title' => 'Visa and travel services, all in one place',
                            'lede' => 'Tell us where you are going. We sort the visa, the documents and the appointment so your trip goes ahead.',
                        ],
                    ],
                    [
                        'type' => 'locked-include',
                        'data' => ['partial' => 'services-body'],
                    ],
                ],
            ],
        );

        $this->command?->info('CmsServicesPilotSeeder: cms Services page seeded (published).');
    }
}
