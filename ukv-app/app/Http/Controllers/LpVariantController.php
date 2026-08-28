<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Serves keyword-page variants of the lp-v2 money page from a SINGLE source file
 * (public/lp-v2-preview.html), swapping only the hero block (title/meta/H1/hook/sub)
 * per route. This avoids maintaining N near-identical copies of the ~1900-line page.
 *
 * Copy lives in config('ukv.lp_default_hero') — the strings currently baked into the
 * file — and config('ukv.lp_variants.<key>') for each variant. Add a variant + a route
 * to add a page. Pages inherit the file's `robots noindex` until the go-live decision.
 */
class LpVariantController extends Controller
{
    public function show(string $variant): Response
    {
        $v = config("ukv.lp_variants.$variant");
        abort_unless(is_array($v), 404);

        $d = config('ukv.lp_default_hero');
        $path = public_path('lp-v2-preview.html');
        abort_unless(is_file($path), 404);
        $html = (string) file_get_contents($path);

        // Italic-gold emphasis span used in the hero H1 (must match the file exactly).
        $gold = '<span style="font-style:italic;color:var(--gold)">';

        $html = strtr($html, [
            '<title>'.$d['title'].'</title>'                       => '<title>'.$v['title'].'</title>',
            'content="'.$d['meta'].'"'                             => 'content="'.$v['meta'].'"',
            $d['h1_lead'].$gold.$d['h1_gold'].'</span>'            => $v['h1_lead'].$gold.$v['h1_gold'].'</span>',
            $d['hook']                                             => $v['hook'],
            $d['sub']                                              => $v['sub'],
        ]);

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
