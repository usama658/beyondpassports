<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The seven destinations that have a dedicated, indexable money page at /schengen-visa/{slug}.
 *
 * Those pages are static files served by the `schengen-visa-country` route, separate from the
 * DB-driven Destination rows. Everything else on the site builds its links from Destination rows
 * and points at /visa/{slug}, which 302-redirects to the hub while `UKV_COUNTRY_PAGES_ENABLED`
 * is off. The net effect was that the seven best pages on the site had no internal links at all
 * (sitemap only), so this class is the single place that knows the difference.
 *
 * Keep in sync with the whitelist in routes/web.php and the loop in SitemapController.
 */
final class DestinationLinks
{
    /** @var array<string,string> slug => display label */
    public const PAGES = [
        'france' => 'France',
        'spain' => 'Spain',
        'germany' => 'Germany',
        'netherlands' => 'Netherlands',
        'italy' => 'Italy',
        'switzerland' => 'Switzerland',
        'belgium' => 'Belgium',
    ];

    public static function hasPage(string $slug): bool
    {
        return array_key_exists($slug, self::PAGES);
    }

    /** Absolute URL of the country money page, or null when that country has no page yet. */
    public static function pageUrl(string $slug): ?string
    {
        return self::hasPage($slug) ? url('/schengen-visa/'.$slug) : null;
    }

    /**
     * All countries with a page, ready for a link list.
     *
     * @return array<int,array{slug:string,label:string,url:string}>
     */
    public static function pages(): array
    {
        return array_map(
            fn (string $slug, string $label) => [
                'slug' => $slug,
                'label' => $label,
                'url' => url('/schengen-visa/'.$slug),
            ],
            array_keys(self::PAGES),
            array_values(self::PAGES),
        );
    }

    /**
     * JSON-LD for a country money page: a Service node pointing at the site-wide Organization
     * entity, plus a BreadcrumbList matching the visible page hierarchy.
     *
     * No price is emitted: the site's price ladder is configured off (ukv.pricing.show), and
     * structured data must never advertise a figure the page itself does not show.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function countrySchema(string $slug): array
    {
        $name = self::PAGES[$slug] ?? ucfirst($slug);
        $base = rtrim(config('app.url'), '/');
        $url = url('/schengen-visa/'.$slug);

        return [
            array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => $name.' Schengen visa preparation and document checking',
                'serviceType' => 'Schengen short-stay visa application preparation',
                'provider' => ['@id' => $base.'/#organization'],
                'areaServed' => ['@type' => 'Country', 'name' => 'United Kingdom'],
                'audience' => ['@type' => 'Audience', 'audienceType' => 'UK residents applying for a Schengen visa'],
                'url' => $url,
            ], fn ($v) => $v !== null && $v !== ''),
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Schengen visa', 'item' => url('/schengen-visa')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $url],
                ],
            ],
        ];
    }
}
