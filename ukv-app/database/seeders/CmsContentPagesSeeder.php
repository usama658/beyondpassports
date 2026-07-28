<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Migrates the remaining coded content pages (legal, compare, tour-packages) onto the CMS as a single
 * locked-include of their extracted body partial. Byte-identical to the coded page (both render the
 * same partial in the same layout), DB-served, reversible: unpublish or flip UKV_CMS_ENABLED and the
 * route falls back to the coded Blade. SEO title/description mirror the coded views so the <head> is
 * identical. Idempotent (updateOrCreate on slug).
 */
class CmsContentPagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }

    /** @return array<int,array<string,mixed>> */
    private function pages(): array
    {
        return [
            [
                'slug' => 'legal',
                'title' => 'Legal & policies',
                'mode' => 'cms',
                'status' => 'published',
                'seo_title' => 'Legal & policies: Privacy, Terms, Complaints, Disclaimer | Beyond Passports',
                'seo_description' => 'Beyond Passports legal centre: privacy policy, terms of service, complaints procedure and disclaimer. Independent visa and eVisa facilitation. We are not a government website, our service fee is separate from government fees, and we cannot guarantee visa approval.',
                'blocks' => [['type' => 'locked-include', 'data' => ['partial' => 'legal-body']]],
            ],
            [
                'slug' => 'compare',
                'title' => 'Apply yourself vs use us',
                'mode' => 'cms',
                'status' => 'published',
                'seo_title' => 'Apply Yourself vs Use Beyond Passports: Honest Comparison | Beyond Passports',
                'seo_description' => 'An honest, balanced comparison of applying for your visa, eVisa, ETA or IDP yourself versus using Beyond Passports. Real trade-offs on cost, time, error-checking and support. Not a government website.',
                'blocks' => [['type' => 'locked-include', 'data' => ['partial' => 'compare-body']]],
            ],
            [
                'slug' => 'tour-packages',
                'title' => 'Plan a trip',
                'mode' => 'cms',
                'status' => 'published',
                'seo_title' => 'Plan a trip — Europe tours with the Schengen visa built in | Beyond Passports',
                'seo_description' => 'Visa-led European tour packages. We prepare the Schengen visa and book the appointment first, then wrap flights and hotels. Registered in England and Wales. No payment until after your free risk check.',
                'blocks' => [['type' => 'locked-include', 'data' => ['partial' => 'tours-body']]],
            ],
        ];
    }
}
