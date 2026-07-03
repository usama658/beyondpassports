<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Guide;
use Illuminate\Http\Response;

/**
 * Runtime XML sitemap.
 *
 * Lists only public, indexable URLs:
 *   - Home (/)
 *   - Destinations index (/destinations)
 *   - One money page per Destination (/visa/{slug})
 *
 * Intentionally EXCLUDED (private / transactional / noindex):
 *   - /apply, /checkout/*            (funnel — not landing targets)
 *   - /confirmation/*                (per-order thank-you, gated by order_ref)
 *   - /track, /track/lookup          (status lookup, not a landing page)
 *   - /documents/upload              (POST-only authenticated action)
 *   - /stripe/webhook                (machine endpoint)
 *   - /admin (Filament) and anything under it
 *
 * Register with:
 *   Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $today = now()->toDateString();

        // Static public pages: [path, changefreq, priority]
        $static = [
            ['/', 'weekly', '1.0'],
            ['/services', 'weekly', '0.9'],
            ['/plan-a-trip', 'weekly', '0.8'],
            ['/schengen-visa-consultancy', 'weekly', '0.9'],
            ['/destinations', 'weekly', '0.8'],
            ['/tools', 'monthly', '0.7'],
            ['/guides', 'weekly', '0.6'],
            ['/document-checklist', 'monthly', '0.7'],
            ['/find-a-centre', 'monthly', '0.6'],
            ['/compare', 'monthly', '0.5'],
            ['/about', 'monthly', '0.4'],
            ['/contact', 'monthly', '0.4'],
            ['/legal', 'yearly', '0.2'],
        ];

        // Guide articles — read straight from the GuideController registry so the
        // sitemap can never drift from the guides that actually exist (audit M-7).
        $guideSlugs = GuideController::slugs();

        $urls = [];

        foreach ($static as [$path, $changefreq, $priority]) {
            $urls[] = [
                'loc' => $base . ($path === '/' ? '/' : $path),
                'lastmod' => $today,
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        }

        foreach ($guideSlugs as $slug) {
            $urls[] = [
                'loc' => $base . '/guides/' . $slug,
                'lastmod' => $today,
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];
        }

        // One money page per destination.
        // Schengen-only pivot (2026-06-24): skip non-Schengen (non-ETIAS) destinations so their
        // /visa/{slug} money pages and /visa/{slug}/{topic} guides stay out of the sitemap. Reversible.
        Destination::query()
            ->whereNotNull('slug')
            ->orderBy('name')
            ->each(function (Destination $destination) use (&$urls, $base) {
                if ($destination->visa_type !== 'Schengen') {
                    return;
                }
                $urls[] = [
                    'loc' => $base . '/visa/' . $destination->slug,
                    'lastmod' => optional($destination->updated_at)->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            });

        // Nested country guides (spokes): /visa/{slug}/{topic} for each PUBLISHED country guide.
        Guide::query()
            ->published()
            ->whereNotNull('destination_id')
            ->with('destination:id,slug,visa_type')
            ->get()
            ->each(function (Guide $guide) use (&$urls, $base) {
                if (! $guide->destination?->slug || ! $guide->guide_type) {
                    return;
                }
                // Schengen-only pivot (2026-06-24): skip nested guides for non-Schengen destinations.
                if ($guide->destination->visa_type !== 'Schengen') {
                    return;
                }
                $urls[] = [
                    'loc' => $base . '/visa/' . $guide->destination->slug . '/' . $guide->guide_type->topicSlug(),
                    'lastmod' => optional($guide->published_at ?? $guide->updated_at)->toDateString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>' . "\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>' . "\n";

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
