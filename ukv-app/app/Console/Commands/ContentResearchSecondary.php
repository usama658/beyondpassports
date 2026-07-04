<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Secondary content research (docs/content-research-automation.md).
 *
 * Turns the external market keyword bank (config/content_research.php) into a
 * ranked social-topic list. The bank is refreshed periodically from Ahrefs via
 * the Claude Ahrefs MCP (this command runs on the server and can't call Ahrefs
 * directly), so it is a snapshot — see the config's `refreshed` date.
 *
 * Ranking favours winnable + high-reach + on-product terms:
 *   score = volume_weight(uk, global) * difficulty_bonus(kd) * product_fit
 *           + freshness_boost (newsjacks)
 * Low-KD, decent-volume, product-fit terms rise; hard terms sink.
 *
 * Output: content-log-shaped CSV -> storage/app/content-research/secondary-DATE.csv.
 * Merge with `content:research-primary` on batch day (primary weighted heavier).
 *
 * Read-only. No app-data writes. Schedule-safe.
 */
final class ContentResearchSecondary extends Command
{
    protected $signature = 'content:research-secondary
        {--limit=25 : Max topics to output}
        {--max-kd=40 : Drop keywords harder than this KD}
        {--product= : Filter to one product (Schengen prep|Tours|Long-stay)}
        {--path= : Override output CSV path}';

    protected $description = 'Secondary content research: rank social topics from the market keyword bank (Ahrefs snapshot)';

    public function handle(): int
    {
        $limit   = max(1, (int) $this->option('limit'));
        $maxKd   = max(1, (int) $this->option('max-kd'));
        $product = $this->option('product');

        $bank = config('content_research.keywords', []);
        if (empty($bank)) {
            $this->warn('Keyword bank empty — see config/content_research.php.');
            return self::SUCCESS;
        }

        $refreshed = (string) config('content_research.refreshed', 'unknown');
        $this->line("Keyword bank snapshot: {$refreshed}");

        // Live current-affairs signals (Claude-refreshed each batch via WebSearch).
        // A topic whose keyword matches a signal takes the verified headline + fact
        // and a rank boost — this is what keeps newsjacks current, not stale.
        [$signals, $signalDate] = $this->loadSignals();
        if ($signals) {
            $this->line("Live signals verified: {$signalDate} (".count($signals).' active)');
        }

        $topics = collect($bank)
            ->when($product, fn ($c) => $c->where('product', $product))
            ->filter(fn ($k) => (int) ($k['kd'] ?? 100) <= $maxKd)
            ->map(function (array $k) use ($signals) {
                $uk     = (int) ($k['uk'] ?? 0);
                $global = (int) ($k['global'] ?? 0);
                $kd     = (int) ($k['kd'] ?? 50);
                $fresh  = (bool) ($k['fresh'] ?? false);

                // Volume weight: UK weighted heavier (target market), global as reach.
                $vol = ($uk * 1.0) + ($global * 0.15);
                // Difficulty bonus: easier = higher. KD 0 -> 2.0x, KD 50 -> 1.0x.
                $diff = max(0.4, 2.0 - ($kd / 50));
                // Product fit: winnable clusters we can actually sell.
                $fit = match ($k['product'] ?? '') {
                    'Long-stay' => 1.2,   // low competition, high-value buyer
                    'Schengen prep' => 1.15,
                    'Tours' => 0.9,       // ATOL-gated, softer
                    default => 1.0,
                };
                $score = (int) round(($vol * $diff * $fit) / 100);
                if ($fresh) $score += 40; // newsjack boost

                $title = (string) ($k['angle'] ?? $k['term']);
                $note  = $fresh ? 'NEWSJACK — verify current rules before posting' : 'Evergreen';

                // Overlay a live signal if this keyword matches one: real verified
                // headline + fact replace the stale angle, plus a current-affairs boost.
                if ($sig = $this->matchSignal((string) $k['term'], $signals)) {
                    $title  = $sig['headline'];
                    $note   = $sig['fact'];
                    $fresh  = true;
                    $score += (int) ($sig['boost'] ?? 80);
                }

                return [
                    'title'    => $title,
                    'term'     => (string) $k['term'],
                    'product'  => (string) ($k['product'] ?? ''),
                    'campaign' => (string) ($k['campaign'] ?? 'refusals'),
                    'platform' => (string) ($k['platform'] ?? 'Instagram'),
                    'landing'  => (string) ($k['landing'] ?? 'guides'),
                    'uk'       => $uk,
                    'kd'       => $kd,
                    'fresh'    => $fresh,
                    'note'     => $note,
                    'score'    => $score,
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();

        if ($topics->isEmpty()) {
            $this->warn('No keywords passed the filters.');
            return self::SUCCESS;
        }

        $date = Carbon::now()->format('Y-m-d');
        $path = $this->option('path')
            ?: storage_path("app/content-research/secondary-{$date}.csv");
        @mkdir(\dirname($path), 0775, true);

        $base = rtrim((string) config('app.url', 'https://beyondpassports.co.uk'), '/');
        $header = ['Date', 'Status', 'Platform', 'Campaign', 'Format', 'Hook/Title', 'Product', 'CTA', 'Link (UTM\'d)', 'Score', 'Keyword', 'UK vol', 'KD', 'Notes'];

        $fh = fopen($path, 'w');
        fputcsv($fh, $header);
        foreach ($topics as $t) {
            $cta = $t['campaign'] === 'tours' ? 'WhatsApp'
                 : ($t['campaign'] === 'move-to-europe' ? 'Eligibility check' : 'Free checklist');
            $src = strtolower($t['platform']);
            $link = "{$base}/{$t['landing']}?utm_source={$src}&utm_medium=social&utm_campaign={$t['campaign']}";
            $note = (string) ($t['note'] ?? ($t['fresh'] ? 'NEWSJACK — verify current rules before posting' : 'Evergreen'));
            fputcsv($fh, [
                '', 'Idea', $t['platform'], $t['campaign'], 'Carousel', $t['title'],
                $t['product'], $cta, $link, $t['score'], $t['term'], $t['uk'], $t['kd'], $note,
            ]);
        }
        fclose($fh);

        $this->info("Ranked {$topics->count()} secondary topics -> {$path}");
        $this->table(['#', 'Score', 'KD', 'UK', 'Platform', 'Topic'],
            $topics->take(10)->values()->map(fn ($t, $i) => [
                $i + 1, $t['score'], $t['kd'], $t['uk'], $t['platform'],
                Str::limit($t['title'], 44) . ($t['fresh'] ? '  [newsjack]' : ''),
            ])->all());

        return self::SUCCESS;
    }

    /**
     * Load Claude-refreshed live signals (storage/app/content-research/signals.json).
     * Returns [signals[], verifiedDate]. Signals older than 45 days are ignored
     * (stale current-affairs is worse than none) — refresh them each batch.
     */
    private function loadSignals(): array
    {
        $path = storage_path('app/content-research/signals.json');
        if (! is_file($path)) return [[], null];

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data) || empty($data['signals'])) return [[], null];

        $verified = (string) ($data['verified'] ?? '');
        if ($verified !== '') {
            try {
                if (Carbon::parse($verified)->diffInDays(Carbon::now()) > 45) {
                    $this->warn("Live signals are stale ({$verified}) — refresh signals.json before trusting newsjacks.");
                    return [[], $verified];
                }
            } catch (\Throwable) { /* unparseable date: treat as usable but flag */ }
        }

        return [array_values($data['signals']), $verified];
    }

    /** First signal whose `match` token appears in the keyword term, or null. */
    private function matchSignal(string $term, array $signals): ?array
    {
        $t = Str::lower($term);
        foreach ($signals as $sig) {
            foreach ((array) ($sig['match'] ?? []) as $needle) {
                if ($needle !== '' && str_contains($t, Str::lower((string) $needle))) {
                    return $sig;
                }
            }
        }
        return null;
    }
}
