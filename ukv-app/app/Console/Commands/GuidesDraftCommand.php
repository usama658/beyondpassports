<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\GuideType;
use App\Models\Destination;
use App\Models\Guide;
use App\Services\GuideContentService;
use Illuminate\Console\Command;

/**
 * Batch AI-draft the missing country × guide-type guides (content pipeline, spec §"content workflow").
 *
 * For each in-scope destination and each of the 15 GuideType "spokes", if no guide row exists yet
 * (or --redraft is passed) this asks GuideContentService to draft a body from the destination's
 * STRUCTURED facts. Every result is a status=draft row — nothing is ever published; a human reviews
 * and publishes via the GuideResource publish gate.
 *
 * FACTUALITY / LEAK-SAFETY: drafting uses destination facts ONLY (no customer data). Any £-amount,
 * day-count, or date the model emits that is not in the injected facts is reported via the
 * no-invention validator so the reviewer is warned before they ever open the guide.
 *
 * No-AI behaviour: with no Anthropic key configured GuideContentService still creates the empty
 * draft shells (so they appear in Filament for hand-authoring) and the command reports them as
 * "shell only" rather than failing.
 */
class GuidesDraftCommand extends Command
{
    /** @var string */
    protected $signature = 'guides:draft
        {--destination= : Limit to one destination by slug (default: all)}
        {--type=* : Limit to specific GuideType values (default: all 15)}
        {--redraft : Re-draft guides that already exist (otherwise only missing ones)}
        {--only-draft : When re-drafting, skip any guide already published (never clobber live content)}
        {--dry-run : List what would be drafted without calling AI or writing}';

    /** @var string */
    protected $description = 'AI-draft the missing country × type guides from structured destination facts';

    public function handle(GuideContentService $service): int
    {
        $destinations = $this->destinations();

        if ($destinations->isEmpty()) {
            $this->warn('No destinations matched. Nothing to draft.');

            return self::SUCCESS;
        }

        $types = $this->types();
        $dryRun = (bool) $this->option('dry-run');
        $redraft = (bool) $this->option('redraft');
        $onlyDraft = (bool) $this->option('only-draft');

        $created = 0;
        $drafted = 0;
        $shells = 0;
        $skipped = 0;
        $flagged = 0;

        foreach ($destinations as $destination) {
            foreach ($types as $type) {
                $existing = Guide::query()
                    ->where('destination_id', $destination->id)
                    ->where('guide_type', $type->value)
                    ->first();

                if ($existing !== null && ! $redraft) {
                    $skipped++;

                    continue;
                }

                // Never clobber a published guide when re-drafting in --only-draft mode.
                if ($existing !== null && $redraft && $onlyDraft && $existing->status === 'published') {
                    $this->line("  skip (published): {$destination->slug} / {$type->topicSlug()}");
                    $skipped++;

                    continue;
                }

                if ($dryRun) {
                    $verb = $existing === null ? 'create+draft' : 'redraft';
                    $this->line("  [{$verb}] {$destination->slug} / {$type->topicSlug()}");
                    $existing === null ? $created++ : $drafted++;

                    continue;
                }

                $result = $service->draftFor($destination, $type);

                if ($existing === null) {
                    $created++;
                }

                if ($result['drafted']) {
                    $drafted++;
                    $flagCount = count($result['flags']);
                    if ($flagCount > 0) {
                        $flagged++;
                        $this->warn("  flagged ({$flagCount}): {$destination->slug} / {$type->topicSlug()} — "
                            .implode(' | ', $result['flags']));
                    } else {
                        $this->line("  drafted: {$destination->slug} / {$type->topicSlug()}");
                    }
                } else {
                    $shells++;
                    $this->line("  shell only (AI unavailable): {$destination->slug} / {$type->topicSlug()}");
                }
            }
        }

        $this->info(sprintf(
            'Done. created=%d drafted=%d shells=%d skipped=%d flagged=%d',
            $created,
            $drafted,
            $shells,
            $skipped,
            $flagged,
        ));

        if ($flagged > 0) {
            $this->warn("{$flagged} guide(s) contain figures not found in the source facts — review before publishing.");
        }

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Destination>
     */
    private function destinations(): \Illuminate\Support\Collection
    {
        $slug = trim((string) ($this->option('destination') ?? ''));

        $query = Destination::query()->orderBy('name');

        if ($slug !== '') {
            $query->where('slug', $slug);
        }

        return $query->get();
    }

    /**
     * @return list<GuideType>
     */
    private function types(): array
    {
        $requested = array_filter(array_map('trim', (array) $this->option('type')));

        if ($requested === []) {
            return GuideType::cases();
        }

        $types = [];
        foreach ($requested as $value) {
            $type = GuideType::tryFrom($value);
            if ($type === null) {
                $this->warn("Unknown guide type \"{$value}\" — ignored.");

                continue;
            }
            $types[] = $type;
        }

        return $types;
    }
}
