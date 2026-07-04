<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Content produce — carousel graphics (docs/social-automation-cycle.md stage 2).
 *
 * Turns the ranked topic CSV (from content:research) into brand-consistent
 * carousel slide PNGs — deterministic, in-repo, no design tool. Each topic
 * becomes a 3-slide carousel: hook slide, one body slide, CTA slide.
 *
 * Render path: build a self-contained HTML per slide (ukv.css tokens inline —
 * petrol/teal/ink, Outfit) then shell a headless Chromium (Edge/Chrome) to
 * screenshot it at 1080x1350. Falls back to leaving the HTML if no browser is
 * found (prod Linux without Chrome) so the command never hard-fails.
 *
 * Output: storage/app/content-assets/DATE/<slug>/slide-{1,2,3}.png (+ .html)
 * Read-only re: app data.
 */
final class ContentCarousels extends Command
{
    protected $signature = 'content:carousels
        {--source= : Topics CSV (defaults to latest content-research/topics-*.csv)}
        {--count=8 : How many top topics to render}
        {--path= : Override output dir}';

    protected $description = 'Produce brand carousel PNGs from the ranked topic list';

    public function handle(): int
    {
        $source = $this->option('source') ?: $this->latestTopics();
        if (! $source || ! is_file($source)) {
            $this->warn('No topics CSV. Run `php artisan content:research` first.');
            return self::SUCCESS;
        }

        $topics = $this->readTopics($source, (int) $this->option('count'));
        if (empty($topics)) {
            $this->warn('No topics parsed from '.$source);
            return self::SUCCESS;
        }

        $date = Carbon::now()->format('Y-m-d');
        $root = $this->option('path') ?: storage_path("app/content-assets/{$date}");
        @mkdir($root, 0775, true);

        $browser = $this->findBrowser();
        $rendered = 0;
        $htmlOnly = 0;

        foreach ($topics as $t) {
            $slug = Str::slug(Str::limit($t['title'], 48, ''));
            $dir  = "{$root}/{$slug}";
            @mkdir($dir, 0775, true);

            foreach ($this->slides($t) as $i => $html) {
                $n    = $i + 1;
                $hf   = "{$dir}/slide-{$n}.html";
                $pf   = "{$dir}/slide-{$n}.png";
                file_put_contents($hf, $html);

                if ($browser) {
                    $this->shot($browser, $hf, $pf);
                    is_file($pf) ? $rendered++ : $htmlOnly++;
                } else {
                    $htmlOnly++;
                }
            }
        }

        $this->info(sprintf(
            'Carousels: %d topics -> %s  (%d PNGs%s)',
            count($topics), $root, $rendered,
            $browser ? '' : ' — no browser found; HTML only, render on a box with Chrome'
        ));

        return self::SUCCESS;
    }

    /** Newest topics CSV in content-research. */
    private function latestTopics(): ?string
    {
        $glob = glob(storage_path('app/content-research/topics-*.csv')) ?: [];
        rsort($glob);
        return $glob[0] ?? null;
    }

    /** Parse the top-N topic rows by header name. */
    private function readTopics(string $path, int $count): array
    {
        $fh = fopen($path, 'r');
        $head = fgetcsv($fh);
        if (! $head) { fclose($fh); return []; }
        $idx = array_flip($head);
        $get = fn (array $r, string $c) => isset($idx[$c]) ? ($r[$idx[$c]] ?? '') : '';

        $out = [];
        while (($r = fgetcsv($fh)) !== false && count($out) < max(1, $count)) {
            $title = (string) $get($r, 'Hook/Title');
            if ($title === '') continue;
            $out[] = [
                'title'    => $title,
                'product'  => (string) $get($r, 'Product'),
                'platform' => (string) $get($r, 'Platform'),
                'campaign' => (string) $get($r, 'Campaign'),
                'cta'      => (string) $get($r, 'CTA'),
                'notes'    => (string) $get($r, 'Notes'),
            ];
        }
        fclose($fh);
        return $out;
    }

    /** Three slide HTML strings for a topic: hook, body, CTA. */
    private function slides(array $t): array
    {
        $ink = '#16222E'; $petrol = '#155E7A'; $teal = '#2E9A8C'; $paper = '#F4F6FA'; $soft = '#A9CCDA';
        $font = "font-family:'Outfit','Segoe UI',system-ui,Arial,sans-serif";
        // base solid + separate gradient image, so it is NEVER white even if the
        // headless paint of the radial-gradients doesn't flush in time.
        // A solid full-bleed backdrop DIV (always paints in headless, unlike a
        // body background-color) guarantees the slide is never white.
        // Content sits in a relative z-index:1 layer so it always paints ABOVE the
        // full-bleed backdrop (a positioned z-index:0 backdrop otherwise covers
        // static text — that blanked slide 2). Big top/bottom padding keeps the
        // vertically-centred block clear of the brand label + footer.
        $wrap = fn (string $baseBg, string $textCol, string $body, string $overlay = '') =>
            "<!doctype html><meta charset='utf-8'><body style='margin:0;width:1080px;height:1350px;{$font};{$textCol};box-sizing:border-box;position:relative;overflow:hidden'>"
            . "<div style='position:absolute;inset:0;background:{$baseBg};z-index:0'></div>"
            . $overlay
            . "<div style='position:absolute;top:88px;left:96px;font:800 26px/1 sans-serif;letter-spacing:-.02em;z-index:2'>Beyond&nbsp;Passports</div>"
            // content anchored absolutely between brand and footer — headless ignores
            // flex-centering + body padding, so place it explicitly.
            . "<div style='position:absolute;top:360px;left:96px;right:96px;z-index:1'>".$body."</div>"
            . "<div style='position:absolute;bottom:80px;left:96px;font:600 22px sans-serif;opacity:.75;z-index:2'>Registered in the UK &amp; Germany</div>"
            . "</body>";

        // dark mesh = full-bleed gradient overlay at body level (z-index:0, over the
        // solid backdrop, behind the z-index:1 content layer).
        $meshOverlay =
            "<div style='position:absolute;inset:0;background:radial-gradient(700px 380px at 12% 0%,rgba(21,94,122,.6),transparent 60%),radial-gradient(700px 380px at 92% 100%,rgba(46,154,140,.55),transparent 60%);z-index:0'></div>";

        // Slide 1 — hook (dark mesh)
        $s1 = $wrap($ink, 'color:#fff',
            "<div style='width:56px;height:4px;background:{$teal};margin:0 0 28px'></div>"
            . "<h1 style='font:800 76px/1.05 sans-serif;letter-spacing:-.03em;margin:0;max-width:16ch;color:#fff'>".e($t['title'])."</h1>"
            . "<p style='font:600 30px sans-serif;color:{$soft};margin:34px 0 0'>Why it happens, and how we prevent it.</p>",
            $meshOverlay);

        // Slide 2 — body (paper). Use the research note only if it's real copy,
        // not a flag ("NEWSJACK…") or empty; otherwise a strong product default.
        $note = trim((string) $t['notes']);
        $body2 = ($note === '' || Str::startsWith($note, ['NEWSJACK', 'Evergreen', 'taxonomy', 'checklist', 'guide', 'destination', 'orders']))
            ? 'Most refusals come down to the avoidable: weak ties, thin funds, the wrong consulate. We catch them before you apply.'
            : $note;
        $s2 = $wrap($paper, "color:{$ink}",
            "<div style='width:56px;height:4px;background:{$petrol};margin:0 0 28px'></div>"
            . "<h2 style='font:800 58px/1.15 sans-serif;letter-spacing:-.02em;color:{$ink};margin:0;max-width:19ch'>".e(Str::limit($body2, 150))."</h2>"
            . "<p style='font:600 28px sans-serif;color:#5d6b76;margin:30px 0 0'>Beyond Passports &middot; ".e($t['product'])."</p>");

        // Slide 3 — CTA (dark mesh)
        $ctaLabel = $t['cta'] ?: 'Free checklist';
        $s3 = $wrap($ink, 'color:#fff',
            "<div style='width:56px;height:4px;background:{$teal};margin:0 0 28px'></div>"
            . "<h2 style='font:800 66px/1.05 sans-serif;letter-spacing:-.03em;margin:0;max-width:15ch;color:#fff'>Check your eligibility, free.</h2>"
            . "<p style='font:600 30px sans-serif;color:{$soft};margin:30px 0 40px;max-width:24ch'>We spot what could get you refused, then handle it. No payment until after your free check.</p>"
            . "<div style='display:inline-block;background:#25D366;color:#fff;font:800 32px sans-serif;padding:22px 40px;border-radius:16px'>".e($ctaLabel)." &rarr;</div>",
            $meshOverlay);

        return [$s1, $s2, $s3];
    }

    /** Find a headless-capable Chromium binary, or null. */
    private function findBrowser(): ?string
    {
        $candidates = [
            'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
            'C:/Program Files/Microsoft/Edge/Application/msedge.exe',
            'C:/Program Files/Google/Chrome/Application/chrome.exe',
            '/usr/bin/google-chrome', '/usr/bin/chromium', '/usr/bin/chromium-browser',
        ];
        foreach ($candidates as $c) {
            if (is_file($c)) return $c;
        }
        return null;
    }

    /** Screenshot one HTML file to PNG via headless Chromium. */
    private function shot(string $browser, string $html, string $png): void
    {
        $url = 'file:///'.str_replace('\\', '/', $html);
        $cmd = escapeshellarg($browser)
            . ' --headless=new --disable-gpu --hide-scrollbars --window-size=1080,1350'
            . ' --virtual-time-budget=2500 --screenshot='.escapeshellarg($png)
            . ' '.escapeshellarg($url).' 2>&1';
        @exec($cmd);
    }
}
