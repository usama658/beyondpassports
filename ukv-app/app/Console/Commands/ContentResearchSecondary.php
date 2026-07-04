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

        $topics = collect($bank)
            ->when($product, fn ($c) => $c->where('product', $product))
            ->filter(fn ($k) => (int) ($k['kd'] ?? 100) <= $maxKd)
            ->map(function (array $k) {
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

                return [
                    'title'    => (string) ($k['angle'] ?? $k['term']),
                    'term'     => (string) $k['term'],
                    'product'  => (string) ($k['product'] ?? ''),
                    'campaign' => (string) ($k['campaign'] ?? 'refusals'),
                    'platform' => (string) ($k['platform'] ?? 'Instagram'),
                    'landing'  => (string) ($k['landing'] ?? 'guides'),
                    'uk'       => $uk,
                    'kd'       => $kd,
                    'fresh'    => $fresh,
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
            $note = $t['fresh'] ? 'NEWSJACK — verify current rules before posting' : 'Evergreen';
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
}
