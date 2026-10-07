<?php
// app/Support/LpAssembler.php
declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\View;

final class LpAssembler
{
    /** Inject site-wide head, the shared modal flow (+ country-lock shim), and lead attribution. */
    public static function inject(string $html, ?array $flowDest = null): string
    {
        // 1. analytics/consent head right after <head>
        $head = View::make('partials.analytics-head')->render();
        if (($pos = stripos($html, '<head>')) !== false) {
            $at = $pos + strlen('<head>');
            $html = substr($html, 0, $at).$head.substr($html, $at);
        }

        // 2. BP_FLOW_DEST shim + shared flow + utm-capture, right before </body>
        $dest = (is_array($flowDest) && isset($flowDest['dest'], $flowDest['iso']))
            ? ['dest' => $flowDest['dest'], 'iso' => $flowDest['iso']]
            : null;
        $shim = '<script>window.BP_FLOW_DEST='
            .($dest ? json_encode($dest, JSON_UNESCAPED_SLASHES) : 'null')
            .';</script>';
        $flow = View::make('partials.lp-flow')->render();
        $utm  = View::make('partials.utm-capture')->render();

        if (($bpos = strripos($html, '</body>')) !== false) {
            $html = substr($html, 0, $bpos).$shim.$flow.$utm.substr($html, $bpos);
        }

        // 3. Tenure toggle: strip the baked "since 2019" team line on static pages when off.
        if (! config('ukv.stats.show_tenure')) {
            $html = str_replace(' since 2019', '', $html);
        }

        return $html;
    }
}
