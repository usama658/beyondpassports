<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Serves the lp-v2 money page (public/lp-v2-preview.html) at its keyword URLs.
 *
 * The file is a STANDALONE static document (its own <head>, no Blade layout), so it does
 * not inherit the site-wide head. This controller stitches in the canonical
 * partials.analytics-head (GTM + GA4 + Clarity + consent) at serve time — single source of
 * truth, so future analytics/consent changes apply here automatically without editing the
 * static file. It also swaps the hero block per keyword variant.
 *
 * Default hero (services-uk) passes a variant key that is NOT in config('ukv.lp_variants'),
 * so the swap is skipped and the file's baked-in hero is served as-is. Copy for the real
 * variants lives in config('ukv.lp_variants.<key>'); the anchors it replaces are in
 * config('ukv.lp_default_hero').
 */
class LpVariantController extends Controller
{
    public function show(string $variant = 'services-uk'): Response
    {
        $path = public_path('lp-v2-preview.html');
        abort_unless(is_file($path), 404);
        $html = (string) file_get_contents($path);

        // Per-variant hero swap (skipped for the default/services hero, which isn't a variant key).
        $v = config("ukv.lp_variants.$variant");
        if (is_array($v)) {
            $d = config('ukv.lp_default_hero');
            $gold = '<span style="font-style:italic;color:var(--gold)">';
            $html = strtr($html, [
                '<title>'.$d['title'].'</title>'            => '<title>'.$v['title'].'</title>',
                'content="'.$d['meta'].'"'                  => 'content="'.$v['meta'].'"',
                $d['h1_lead'].$gold.$d['h1_gold'].'</span>' => $v['h1_lead'].$gold.$v['h1_gold'].'</span>',
                $d['hook']                                  => $v['hook'],
                $d['sub']                                   => $v['sub'],
            ]);
        }

        // Inject the canonical site-wide analytics/consent head once, right after <head>.
        // str-based (not preg_replace) so JS containing $ in the partial isn't mangled.
        $head = view('partials.analytics-head')->render();
        $pos = stripos($html, '<head>');
        if ($pos !== false) {
            $at = $pos + strlen('<head>');
            $html = substr($html, 0, $at).$head.substr($html, $at);
        }

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
