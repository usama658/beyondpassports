<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Sitewide navigation source of truth. Each method returns an array of links; the team can override
 * the label + URL of any entry via the Setting store, but structural chrome (logo, topbar, forms,
 * social, disclaimer strip) and each link's styling/target stay in code. When the CMS flag is off, or
 * no override is saved, the CODED DEFAULT is returned — byte-identical to the hand-written partial —
 * so the header/footer never change until the team deliberately edits them, and clearing an override
 * reverts instantly.
 */
class NavService
{
    /** Header primary links (plain nav anchors). */
    public static function primary(): array
    {
        return static::override('nav_primary') ?? [
            ['label' => 'Schengen visa', 'url' => url('/schengen-visa')],
            ['label' => config('ukv.tours.nav_label', 'Plan a trip'), 'url' => url('/tour-packages')],
            ['label' => 'Who we are', 'url' => url('/about')],
        ];
    }

    /**
     * Header call-to-action buttons. The two slots are fixed in code; `variant` (ghost|cta) and
     * `external` (the styling + funnel target) always come from code. An override may only change the
     * label + url of each existing slot — merged by position — so an edit can never add/remove a
     * button, restyle it, or drop the eligibility button's new-tab behaviour.
     */
    public static function ctas(): array
    {
        $default = [
            ['label' => 'Contact', 'url' => url('/contact'), 'variant' => 'ghost', 'external' => false],
            ['label' => 'Check eligibility →', 'url' => SiteStats::chatUrl(), 'variant' => 'cta', 'external' => true],
        ];
        $override = static::override('nav_ctas');
        if ($override === null) {
            return $default;
        }

        // Keep code-side structural keys; only label/url may be overridden, matched by slot index.
        return array_map(function (array $slot, int $i) use ($override) {
            $edit = is_array($override[$i] ?? null) ? array_intersect_key($override[$i], array_flip(['label', 'url'])) : [];

            return array_merge($slot, $edit);
        }, $default, array_keys($default));
    }

    /** Footer link columns: each column = [heading, links[]]. Structural brand column stays in code. */
    public static function footerColumns(): array
    {
        return static::override('nav_footer') ?? [
            ['heading' => 'Service', 'links' => [
                ['label' => 'All services', 'url' => url('/services')],
                ['label' => config('ukv.tours.nav_label', 'Plan a trip'), 'url' => url('/tour-packages')],
                ['label' => 'Schengen visa', 'url' => url('/schengen-visa')],
                ['label' => 'Check eligibility →', 'url' => SiteStats::chatUrl(), 'external' => true],
                ['label' => 'Track application', 'url' => url('/track')],
            ]],
            ['heading' => 'Free tools & guides', 'links' => [
                ['label' => 'Visa checker', 'url' => url('/tools')],
                ['label' => 'Document checker', 'url' => url('/document-checklist')],
                ['label' => 'Find a centre', 'url' => url('/find-a-centre')],
                ['label' => 'Visa guides & stories', 'url' => url('/guides')],
                ['label' => 'Traveller reviews', 'url' => url('/reviews')],
                ['label' => 'Apply yourself vs us', 'url' => url('/compare')],
            ]],
            ['heading' => 'Company & legal', 'links' => [
                ['label' => 'Who we are', 'url' => url('/about')],
                ['label' => 'Contact', 'url' => url('/contact')],
                ['label' => 'Privacy', 'url' => url('/legal').'#privacy'],
                ['label' => 'Terms', 'url' => url('/legal').'#terms'],
                ['label' => 'Complaints', 'url' => url('/legal').'#complaints'],
                ['label' => 'Disclaimer', 'url' => url('/legal').'#disclaimer'],
            ]],
        ];
    }

    /**
     * Return a saved override for $key when the CMS is enabled and the stored JSON is a non-empty
     * array, else null (→ coded default). No structural keys are ever read for plain link lists, so
     * overridden links render as plain anchors; the eligibility button's target is re-applied in
     * ctas() from code.
     */
    private static function override(string $key): ?array
    {
        if (! config('ukv.cms.enabled')) {
            return null;
        }
        $json = Setting::get($key);
        if (! $json) {
            return null;
        }
        $decoded = json_decode($json, true);

        return is_array($decoded) && $decoded !== [] ? $decoded : null;
    }
}
