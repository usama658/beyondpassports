<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Models\Guide;
use App\Models\Order;
use App\Models\Rejection;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Primary content research (docs/content-research-automation.md).
 *
 * Turns our OWN data into a ranked social-topic list — the highest-converting
 * source because only we have it. Reads:
 *   - Rejection taxonomy + logged rejections  -> "why visas fail" posts
 *   - Checklist requests (demand by destination) -> what to feature
 *   - Orders (popular destinations)            -> trending demand
 *   - Guides (existing content)                -> repurpose to social
 *   - Destinations (seeded)                    -> per-country evergreen
 *
 * Empty tables (pre-launch) degrade gracefully: falls back to the seeded
 * taxonomy + destinations + guides so the output is never blank.
 *
 * Output: a CSV matching docs/content-log-template.csv columns, written to
 * storage/app/content-research/primary-YYYY-MM-DD.csv (path overridable).
 * Rows are ranked by score = base(source) * frequency * product-fit.
 *
 * Read-only. No writes to app data. Safe to run any time / on a schedule.
 */
final class ContentResearchPrimary extends Command
{
    protected $signature = 'content:research-primary
        {--days=90 : Look-back window for rejections/checklists/orders}
        {--limit=25 : Max topics to output}
        {--path= : Override output CSV path}';

    protected $description = 'Primary content research: rank social topics from our own data (rejections, checklist demand, orders, guides)';

    /** Refusal reason -> post angle (the money content). */
    private const REFUSAL_ANGLES = [
        'doc_quality'       => ['They refused it over the documents', 'Why "just send what you have" gets you refused, and the papers a consulate actually checks.'],
        'eligibility'       => ['You applied for the wrong visa', 'The eligibility mistakes that end an application before it starts.'],
        'passport_validity' => ['Your passport was the problem, not your trip', 'The 3-month / 10-year passport rules that quietly cause refusals.'],
        'portal_error'      => ['One wrong field on the portal = refused', 'The booking and form mistakes people make on the visa portal.'],
        'customer_withdrew' => ['Why people give up halfway, and how not to', 'The point most applicants quit, and the fix.'],
        'other'             => ['The refusal reasons nobody warns you about', 'The less obvious grounds a consulate can refuse on.'],
    ];

    public function handle(): int
    {
        $days  = max(1, (int) $this->option('days'));
        $limit = max(1, (int) $this->option('limit'));
        $since = Carbon::now()->subDays($days);

        $rows = collect();

        // ── 1. Refusals — the highest-value primary source ──────────────────
        // Real counts if logged; otherwise every taxonomy reason as evergreen.
        $counts = [];
        try {
            $counts = Rejection::query()
                ->where('recorded_at', '>=', $since)
                ->selectRaw('reason, COUNT(*) as n')
                ->groupBy('reason')->pluck('n', 'reason')->toArray();
        } catch (\Throwable) {
            // table missing / not migrated — fall through to evergreen
        }
        foreach (self::REFUSAL_ANGLES as $reason => [$title, $desc]) {
            $n = (int) ($counts[$reason] ?? 0);
            $rows->push($this->row(
                title: $title, desc: $desc, product: 'Schengen prep', campaign: 'refusals',
                platform: 'Instagram', landing: 'document-checklist',
                // base 100; +12 per real logged case (caps the evergreen at a floor)
                score: 100 + ($n * 12), source: $n > 0 ? "rejections:{$reason} ({$n})" : "taxonomy:{$reason}",
            ));
        }

        // ── 2. Checklist demand by destination — what people ask about ──────
        try {
            ChecklistRequest::query()
                ->where('created_at', '>=', $since)
                ->whereNotNull('destination_id')
                ->selectRaw('destination_id, COUNT(*) as n')
                ->groupBy('destination_id')->orderByDesc('n')->limit(8)
                ->get()->each(function ($r) use ($rows) {
                    $d = Destination::find($r->destination_id);
                    if (! $d) return;
                    $rows->push($this->row(
                        title: "{$d->name}: the documents that actually get you approved",
                        desc: "People are asking about {$d->name} right now. The checklist that avoids a refusal.",
                        product: 'Schengen prep', campaign: 'refusals', platform: 'Pinterest',
                        landing: 'destinations/'.$d->slug, score: 90 + ((int) $r->n * 10),
                        source: "checklist_demand:{$d->slug} ({$r->n})",
                    ));
                });
        } catch (\Throwable) {
        }

        // ── 3. Orders — popular destinations (demand proof) ─────────────────
        try {
            Order::query()
                ->where('created_at', '>=', $since)
                ->whereNotNull('destination_name')
                ->selectRaw('destination_name, COUNT(*) as n')
                ->groupBy('destination_name')->orderByDesc('n')->limit(6)
                ->get()->each(function ($r) use ($rows) {
                    $rows->push($this->row(
                        title: "How we get {$r->destination_name} visas approved",
                        desc: "{$r->destination_name} is one of our most-booked. Behind the prep.",
                        product: 'Schengen prep', campaign: 'refused-before', platform: 'LinkedIn',
                        landing: 'destinations', score: 80 + ((int) $r->n * 8),
                        source: "orders:{$r->destination_name} ({$r->n})",
                    ));
                });
        } catch (\Throwable) {
        }

        // ── 4. Guides — repurpose existing content to social ────────────────
        try {
            Guide::query()->latest('updated_at')->limit(10)->get()
                ->each(function (Guide $g) use ($rows) {
                    $rows->push($this->row(
                        title: (string) ($g->title ?: Str::headline((string) $g->slug)),
                        desc: 'Repurpose this guide into a carousel/reel.',
                        product: 'Schengen prep', campaign: 'refusals', platform: 'LinkedIn',
                        landing: 'guides/'.$g->slug, score: 60,
                        source: 'guide:'.$g->slug,
                    ));
                });
        } catch (\Throwable) {
        }

        // ── 5. Destinations — seeded evergreen floor (never blank) ──────────
        try {
            Destination::query()->orderBy('name')->limit(12)->get()
                ->each(function (Destination $d) use ($rows) {
                    $rows->push($this->row(
                        title: "{$d->name} visa from the UK: what you need",
                        desc: "Evergreen per-country explainer for {$d->name}.",
                        product: 'Schengen prep', campaign: 'refusals', platform: 'Instagram',
                        landing: 'destinations/'.$d->slug, score: 50,
                        source: 'destination:'.$d->slug,
                    ));
                });
        } catch (\Throwable) {
        }

        // ── Rank, de-dupe by title, cap ─────────────────────────────────────
        $topics = $rows->sortByDesc('score')->unique('title')->take($limit)->values();

        if ($topics->isEmpty()) {
            $this->warn('No primary data found. Seed destinations/guides or run after go-live.');
            return self::SUCCESS;
        }

        // ── Write CSV (content-log columns) ─────────────────────────────────
        $date = Carbon::now()->format('Y-m-d');
        $path = $this->option('path')
            ?: storage_path("app/content-research/primary-{$date}.csv");
        @mkdir(\dirname($path), 0775, true);

        $header = ['Date', 'Status', 'Platform', 'Campaign', 'Format', 'Hook/Title', 'Product', 'CTA', 'Link (UTM\'d)', 'Score', 'Source', 'Notes'];
        $fh = fopen($path, 'w');
        fputcsv($fh, $header);
        foreach ($topics as $t) {
            fputcsv($fh, [
                '', 'Idea', $t['platform'], $t['campaign'], 'Carousel', $t['title'],
                $t['product'], $t['cta'], $t['link'], $t['score'], $t['source'], $t['desc'],
            ]);
        }
        fclose($fh);

        $this->info("Ranked {$topics->count()} primary topics -> {$path}");
        $this->table(['#', 'Score', 'Platform', 'Topic', 'Source'],
            $topics->take(10)->values()->map(fn ($t, $i) => [
                $i + 1, $t['score'], $t['platform'], Str::limit($t['title'], 48), $t['source'],
            ])->all());

        return self::SUCCESS;
    }

    /**
     * Build one topic row. CTA + UTM link derived from campaign/landing so the
     * output drops straight into the content-log and GA4 attributes it.
     */
    private function row(
        string $title, string $desc, string $product, string $campaign,
        string $platform, string $landing, int $score, string $source,
    ): array {
        $cta  = $campaign === 'tours' ? 'WhatsApp'
              : ($campaign === 'move-to-europe' ? 'Eligibility check' : 'Free checklist');
        $base = rtrim((string) config('app.url', 'https://beyondpassports.co.uk'), '/');
        $src  = strtolower($platform);
        $link = "{$base}/{$landing}?utm_source={$src}&utm_medium=social&utm_campaign={$campaign}";

        return compact('title', 'desc', 'product', 'campaign', 'platform', 'landing', 'score', 'source', 'cta', 'link');
    }
}
