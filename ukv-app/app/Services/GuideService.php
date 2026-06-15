<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\GuideType;
use App\Models\Destination;
use App\Models\Guide;
use Illuminate\Support\Collection;

/**
 * Guide engine — resolution + clustering for the SEO country-silo.
 *
 * Resolves public guide URLs to published rows and assembles a destination's
 * cluster (the hub's spoke list). Drafts are never returned by these methods.
 *
 * See: docs/superpowers/specs/2026-06-16-guide-engine-seo-silo-design.md (Routing & resolution).
 */
final class GuideService
{
    /**
     * Resolve /visa/{destination}/{topic} to a published country guide, or null.
     *
     * Joins the destination by slug and the guide_type by its topic slug. A miss
     * on either the destination, an unknown topic slug, or a draft/missing guide
     * all return null (the caller 404s).
     */
    public function resolveCountryGuide(string $destSlug, string $topicSlug): ?Guide
    {
        $type = GuideType::fromTopicSlug($topicSlug);
        if ($type === null) {
            return null;
        }

        $destination = Destination::where('slug', $destSlug)->first();
        if ($destination === null) {
            return null;
        }

        return Guide::query()
            ->published()
            ->forDestination($destination)
            ->where('guide_type', $type->value)
            ->first();
    }

    /**
     * The published cluster for a destination (the hub's spoke cards),
     * ordered by sort_order then title.
     *
     * @return Collection<int, Guide>
     */
    public function clusterFor(Destination $destination): Collection
    {
        return Guide::query()
            ->published()
            ->forDestination($destination)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    /**
     * Published evergreen guides (no destination), ordered by sort_order then title.
     *
     * @return Collection<int, Guide>
     */
    public function evergreen(): Collection
    {
        return Guide::query()
            ->published()
            ->whereNull('destination_id')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }
}
