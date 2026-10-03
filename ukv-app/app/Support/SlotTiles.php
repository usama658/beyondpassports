<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Hydrates every baked "N slots open" tile on the gold landing pages with the REAL weekly
 * pool value from App\Support\SlotBoard, so country pages (public/lp-<country>.html) AND the
 * lp-v2 money pages (public/lp-v2-preview.html, served via LpVariantController) show one
 * consistent, real set of numbers instead of hardcoded clone values.
 *
 * Two tile shapes exist in the static markup:
 *  - "slc" tiles: a <button> (lp-v2) or <a> (country-page hero) with class="slc[...]" and an
 *    onclick="fbOpen('Country','iso')" — the fbOpen call names the tile's country directly.
 *  - A "bare" slnum/slmeter/slnx block with no enclosing slc tag and no fbOpen nearby — keyed
 *    to the $pageCountry the caller passes (the country-page route's own country).
 * The "Other Schengen / search" tile (fbOpen()/fbOpenSearch() with no country arg, no
 * <div class=slnum> inside) is always left untouched.
 *
 * Pure string/regex transform; no new dependencies. Resilient by design: any failure, or any
 * tile/country it cannot resolve, is left exactly as baked rather than breaking the page.
 *
 * Gated by config('ukv.slots.dynamic') in the CALLERS (routes/web.php, LpVariantController) —
 * this class does not check the flag itself, mirroring the existing pattern.
 */
final class SlotTiles
{
    /** Matches one "slc" tile: <button|a ... class="slc...|class=slc ...>...</button|a>. */
    private const TILE_PATTERN = '/<(button|a)\b(?=[^>]*\bclass="?slc\b)[^>]*>.*?<\/\1>/s';

    /** Matches a slnum block, optionally followed by its slmeter and slnx siblings. */
    private const BARE_PATTERN = '/<div class=slnum><b[^>]*>\d+<\/b><span>[^<]*<\/span><\/div>'
        .'(\s*<div class=slmeter><i style="width:\d+%"><\/i><\/div>)?'
        .'(\s*<div class=slnx>.*?<\/div>)?/s';

    /**
     * Rewrite every slot tile found in $html.
     *
     * @param  string  $html  the page markup
     * @param  string|null  $pageCountry  the page's own country (country-page route only);
     *                                    used only for tiles that have no fbOpen('Country',..) nearby.
     */
    public static function hydrate(string $html, ?string $pageCountry = null): string
    {
        try {
            if (! preg_match_all(self::TILE_PATTERN, $html, $matches, PREG_OFFSET_CAPTURE)) {
                // No "slc" tiles at all — still try to hydrate a bare tile over the whole string.
                return self::hydrateBare($html, $pageCountry);
            }

            $out = '';
            $cursor = 0;
            foreach ($matches[0] as [$tileText, $offset]) {
                $gap = substr($html, $cursor, $offset - $cursor);
                $out .= self::hydrateBare($gap, $pageCountry);
                $out .= self::hydrateTile($tileText, $pageCountry);
                $cursor = $offset + strlen($tileText);
            }
            $out .= self::hydrateBare(substr($html, $cursor), $pageCountry);

            return $out;
        } catch (\Throwable $e) {
            return $html;
        }
    }

    /** Hydrate one "slc" tile substring (a full <button>...</button> or <a>...</a>). */
    private static function hydrateTile(string $tile, ?string $pageCountry): string
    {
        if (! str_contains($tile, '<div class=slnum>')) {
            // Search/"Other Schengen" tile — no number to hydrate.
            return $tile;
        }

        $country = null;
        if (preg_match('/fbOpen\(\s*\'([^\']+)\'\s*,\s*\'[^\']*\'\s*\)/', $tile, $m)) {
            $country = $m[1];
        } elseif ($pageCountry !== null) {
            $country = $pageCountry;
        }

        if ($country === null) {
            return $tile;
        }

        return self::applyTier($tile, $country);
    }

    /** Hydrate a standalone (non-"slc"-wrapped) slnum block, keyed to $pageCountry. */
    private static function hydrateBare(string $html, ?string $pageCountry): string
    {
        if ($pageCountry === null || $html === '') {
            return $html;
        }

        return preg_replace_callback(
            self::BARE_PATTERN,
            fn (array $m) => self::applyTier($m[0], $pageCountry),
            $html
        ) ?? $html;
    }

    /**
     * Rewrite a tile/block's number, noun, meter width+colour, next date (or "near capacity"
     * copy) and, when the block starts with the tile's own opening tag, its "low" class — all
     * keyed to the real SlotBoard::remaining() value for $country.
     */
    private static function applyTier(string $block, string $country): string
    {
        $remaining = SlotBoard::remaining();
        if (! array_key_exists($country, $remaining)) {
            return $block;
        }

        $left = $remaining[$country];
        $noun = $left === 1 ? 'slot open' : 'slots open';
        $low = $left <= 1;

        if ($left <= 1) {
            $color = '#dc2626';
            $grad = '#dc2626,#ef4444';
            $width = 16;
        } elseif ($left <= 3) {
            $color = '#d97706';
            $grad = '#d97706,#f59e0b';
            $width = 38;
        } else {
            $color = 'var(--green)';
            $grad = 'var(--green),#4bad82';
            $width = 82;
        }

        $block = preg_replace(
            '/<div class=slnum><b[^>]*>\d+<\/b><span>[^<]*<\/span><\/div>/',
            '<div class=slnum><b style="color:'.$color.'">'.$left.'</b><span>'.$noun.'</span></div>',
            $block,
            1
        ) ?? $block;

        $block = preg_replace(
            '/<div class=slmeter><i style="width:\d+%"><\/i><\/div>/',
            '<div class=slmeter><i style="width:'.$width.'%;background:linear-gradient(90deg,'.$grad.')"></i></div>',
            $block,
            1
        ) ?? $block;

        $block = preg_replace_callback(
            '/(<div class=slnx>)(.*?)(<\/div>)/s',
            function (array $m) use ($left, $country) {
                $prefix = '';
                if (preg_match('/^(.*<\/svg>)/s', $m[2], $svg)) {
                    $prefix = $svg[1];
                }
                $text = $left <= 1
                    ? 'Near capacity. Act today.'
                    : 'Next: '.SlotBoard::nextDate($country)->format('j M');

                return $m[1].$prefix.$text.$m[3];
            },
            $block,
            1
        ) ?? $block;

        // Toggle the "low" class on the block's own opening tag, when it has one (a "slc" tile).
        $block = preg_replace_callback(
            '/^<(button|a)\b([^>]*)>/',
            fn (array $m) => self::setLowClass($m[0], $low),
            $block,
            1
        ) ?? $block;

        return $block;
    }

    /** Add/remove the "low" class on an opening tag's class attribute (quoted or bare). */
    private static function setLowClass(string $openTag, bool $low): string
    {
        return preg_replace_callback(
            '/\bclass=(?:"([^"]*)"|(\S+))/',
            function (array $m) use ($low) {
                $raw = ($m[1] ?? '') !== '' ? $m[1] : ($m[2] ?? '');
                $classes = array_filter(preg_split('/\s+/', trim($raw)) ?: []);
                $classes = array_values(array_diff($classes, ['low']));
                if ($low) {
                    $classes[] = 'low';
                }

                return 'class="'.implode(' ', $classes).'"';
            },
            $openTag,
            1
        ) ?? $openTag;
    }
}
