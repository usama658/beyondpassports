<?php

namespace App\Http\Controllers;

use App\Models\Destination;
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
            ['/destinations', 'weekly', '0.8'],
            // Static info pages — uncomment/adjust to match the routes you register:
            // ['/about', 'monthly', '0.5'],
            // ['/contact', 'monthly', '0.5'],
            // ['/idp', 'monthly', '0.7'],
            // ['/guide', 'monthly', '0.6'],
            // ['/legal', 'yearly', '0.3'],
        ];

        $urls = [];

        foreach ($static as [$path, $changefreq, $priority]) {
            $urls[] = [
                'loc' => $base . ($path === '/' ? '/' : $path),
                'lastmod' => $today,
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        }

        // One money page per destination.
        Destination::query()
            ->whereNotNull('slug')
            ->orderBy('name')
            ->each(function (Destination $destination) use (&$urls, $base) {
                $urls[] = [
                    'loc' => $base . '/visa/' . $destination->slug,
                    'lastmod' => optional($destination->updated_at)->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
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
