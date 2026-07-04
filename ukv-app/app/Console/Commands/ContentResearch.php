<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Content research — merge (docs/content-research-automation.md).
 *
 * Runs both engines, combines into ONE ranked topic list for batch day:
 *   - content:research-primary   (our data — refusals, checklist demand, orders)
 *   - content:research-secondary (market — Ahrefs keyword bank, newsjacks)
 *
 * Primary is weighted heavier (it converts — only we have it). Rows are
 * de-duplicated across engines (by normalised title), re-ranked, and written
 * as a single content-log-shaped CSV ready to drop into the content-log
 * (docs/content-log-template.csv) as "Idea" rows.
 *
 * Output: storage/app/content-research/topics-DATE.csv
 * Read-only. Schedule-safe (this is the one to put on a monthly cron).
 */
final class ContentResearch extends Command
{
    protected $signature = 'content:research
        {--limit=30 : Max merged topics}
        {--days=90 : Primary look-back window}
        {--max-kd=40 : Secondary KD ceiling}
        {--primary-weight=1.3 : Multiplier applied to primary-sourced scores}
        {--path= : Override merged CSV path}';

    protected $description = 'Merge primary + secondary content research into one ranked topic list';

    public function handle(): int
    {
        $limit  = max(1, (int) $this->option('limit'));
        $pWeight = max(1.0, (float) $this->option('primary-weight'));
        $date   = Carbon::now()->format('Y-m-d');
        $dir    = storage_path('app/content-research');
        @mkdir($dir, 0775, true);

        $pPath = "{$dir}/_primary-tmp.csv";
        $sPath = "{$dir}/_secondary-tmp.csv";

        $this->line('Running primary engine…');
        $this->call('content:research-primary', ['--days' => (int) $this->option('days'), '--limit' => 100, '--path' => $pPath]);

        $this->line('Running secondary engine…');
        $this->call('content:research-secondary', ['--max-kd' => (int) $this->option('max-kd'), '--limit' => 100, '--path' => $sPath]);

        $rows = collect()
            ->merge($this->read($pPath, 'primary', $pWeight))
            ->merge($this->read($sPath, 'secondary', 1.0));

        @unlink($pPath);
        @unlink($sPath);

        if ($rows->isEmpty()) {
            $this->warn('No topics from either engine.');
            return self::SUCCESS;
        }

        // De-dupe across engines by normalised title; keep the higher score.
        $merged = $rows
            ->sortByDesc('score')
            ->unique(fn ($r) => Str::of($r['title'])->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->value())
            ->take($limit)
            ->values();

        $path = $this->option('path') ?: "{$dir}/topics-{$date}.csv";
        $header = ['Date', 'Status', 'Platform', 'Campaign', 'Format', 'Hook/Title', 'Product', 'CTA', 'Link (UTM\'d)', 'Score', 'Engine', 'Source', 'Notes'];

        $fh = fopen($path, 'w');
        fputcsv($fh, $header);
        foreach ($merged as $r) {
            fputcsv($fh, [
                '', 'Idea', $r['platform'], $r['campaign'], 'Carousel', $r['title'],
                $r['product'], $r['cta'], $r['link'], $r['score'], $r['engine'], $r['source'], $r['notes'],
            ]);
        }
        fclose($fh);

        $prim = $merged->where('engine', 'primary')->count();
        $sec  = $merged->where('engine', 'secondary')->count();
        $this->info("Merged {$merged->count()} topics ({$prim} primary, {$sec} secondary) -> {$path}");
        $this->table(['#', 'Score', 'Engine', 'Platform', 'Topic'],
            $merged->take(12)->values()->map(fn ($r, $i) => [
                $i + 1, $r['score'], $r['engine'], $r['platform'], Str::limit($r['title'], 46),
            ])->all());

        return self::SUCCESS;
    }

    /**
     * Read one engine's CSV by header name (robust to differing columns) and
     * normalise to a common row shape, applying the engine score weight.
     */
    private function read(string $path, string $engine, float $weight): array
    {
        if (! is_file($path)) return [];
        $fh = fopen($path, 'r');
        $head = fgetcsv($fh);
        if (! $head) { fclose($fh); return []; }
        $idx = array_flip($head);
        $get = fn (array $row, string $col) => isset($idx[$col]) ? ($row[$idx[$col]] ?? '') : '';

        $out = [];
        while (($row = fgetcsv($fh)) !== false) {
            $title = (string) $get($row, 'Hook/Title');
            if ($title === '') continue;
            $out[] = [
                'engine'   => $engine,
                'platform' => (string) $get($row, 'Platform'),
                'campaign' => (string) $get($row, 'Campaign'),
                'title'    => $title,
                'product'  => (string) $get($row, 'Product'),
                'cta'      => (string) $get($row, 'CTA'),
                'link'     => (string) $get($row, "Link (UTM'd)"),
                'score'    => (int) round(((int) $get($row, 'Score')) * $weight),
                'source'   => (string) ($get($row, 'Source') ?: $get($row, 'Keyword')),
                'notes'    => (string) $get($row, 'Notes'),
            ];
        }
        fclose($fh);
        return $out;
    }
}
