<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GuideType;
use App\Models\Destination;
use App\Models\Guide;
use App\Services\GuideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guide engine — published-vs-draft scoping, country-guide resolution,
 * cluster ordering, and GuideType topic-slug round-trip.
 */
final class GuideEngineTest extends TestCase
{
    use RefreshDatabase;

    private function service(): GuideService
    {
        return app(GuideService::class);
    }

    private function makeDestination(array $overrides = []): Destination
    {
        return Destination::create(array_merge([
            'name' => 'Turkey',
            'slug' => 'turkey',
            'visa_type' => 'evisa',
            'passport_validity_months' => 6,
        ], $overrides));
    }

    private function makeGuide(array $attrs = []): Guide
    {
        return Guide::create(array_merge([
            'guide_type' => GuideType::DoINeedVisa,
            'slug' => 'guide-'.uniqid(),
            'title' => 'A guide',
            'excerpt' => 'Short excerpt.',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 0,
        ], $attrs));
    }

    public function test_published_scope_excludes_draft_and_unpublished(): void
    {
        $live = $this->makeGuide(['slug' => 'live', 'status' => 'published', 'published_at' => now()]);
        $draft = $this->makeGuide(['slug' => 'draft', 'guide_type' => GuideType::Documents, 'status' => 'draft', 'published_at' => null]);
        // published status but no timestamp -> not live.
        $noStamp = $this->makeGuide(['slug' => 'no-stamp', 'guide_type' => GuideType::PassportValidity, 'status' => 'published', 'published_at' => null]);

        $ids = Guide::published()->pluck('id');

        $this->assertTrue($ids->contains($live->id));
        $this->assertFalse($ids->contains($draft->id));
        $this->assertFalse($ids->contains($noStamp->id));
    }

    public function test_resolve_country_guide_hits_published(): void
    {
        $dest = $this->makeDestination(['slug' => 'turkey']);
        $guide = $this->makeGuide([
            'destination_id' => $dest->id,
            'guide_type' => GuideType::Documents,
            'slug' => 'turkey-documents',
        ]);

        $resolved = $this->service()->resolveCountryGuide('turkey', 'documents');

        $this->assertNotNull($resolved);
        $this->assertSame($guide->id, $resolved->id);
    }

    public function test_resolve_country_guide_misses_on_draft_unknown_topic_and_wrong_destination(): void
    {
        $dest = $this->makeDestination(['slug' => 'turkey']);
        $this->makeGuide([
            'destination_id' => $dest->id,
            'guide_type' => GuideType::Documents,
            'slug' => 'turkey-documents',
            'status' => 'draft',
            'published_at' => null,
        ]);

        // Draft guide -> miss.
        $this->assertNull($this->service()->resolveCountryGuide('turkey', 'documents'));
        // Unknown topic slug -> miss.
        $this->assertNull($this->service()->resolveCountryGuide('turkey', 'not-a-real-topic'));
        // Unknown destination -> miss.
        $this->assertNull($this->service()->resolveCountryGuide('atlantis', 'documents'));
    }

    public function test_cluster_returns_published_only_ordered_by_sort_then_title(): void
    {
        $dest = $this->makeDestination(['slug' => 'turkey']);

        $this->makeGuide(['destination_id' => $dest->id, 'guide_type' => GuideType::CostFees, 'title' => 'Charlie', 'sort_order' => 10]);
        $this->makeGuide(['destination_id' => $dest->id, 'guide_type' => GuideType::Documents, 'title' => 'Alpha', 'sort_order' => 5]);
        $this->makeGuide(['destination_id' => $dest->id, 'guide_type' => GuideType::PassportValidity, 'title' => 'Bravo', 'sort_order' => 5]);
        // A draft must not appear in the cluster.
        $this->makeGuide(['destination_id' => $dest->id, 'guide_type' => GuideType::ProcessingTime, 'title' => 'Hidden', 'sort_order' => 1, 'status' => 'draft', 'published_at' => null]);

        $titles = $this->service()->clusterFor($dest)->pluck('title')->all();

        $this->assertSame(['Alpha', 'Bravo', 'Charlie'], $titles);
    }

    public function test_evergreen_returns_published_destinationless_guides(): void
    {
        $dest = $this->makeDestination(['slug' => 'turkey']);

        $ever = $this->makeGuide(['guide_type' => null, 'slug' => 'eta-explained', 'title' => 'ETA explained', 'destination_id' => null]);
        // Country guide must not show up as evergreen.
        $this->makeGuide(['destination_id' => $dest->id, 'guide_type' => GuideType::Documents, 'slug' => 'turkey-documents']);

        $slugs = $this->service()->evergreen()->pluck('slug')->all();

        // The legacy-guides data migration also seeds evergreen rows, so assert presence/absence
        // rather than an exact set: our evergreen guide is listed, the country guide never is.
        $this->assertContains($ever->slug, $slugs);
        $this->assertNotContains('turkey-documents', $slugs);
    }

    public function test_guide_type_topic_slug_round_trip(): void
    {
        foreach (GuideType::cases() as $case) {
            $this->assertSame($case, GuideType::fromTopicSlug($case->topicSlug()));
        }

        // A couple of explicit mappings from the spec.
        $this->assertSame('do-i-need-a-visa', GuideType::DoINeedVisa->topicSlug());
        $this->assertSame('cost', GuideType::CostFees->topicSlug());
        $this->assertSame('uk-residents', GuideType::Residents->topicSlug());

        // Unknown / null slugs resolve to null.
        $this->assertNull(GuideType::fromTopicSlug('nope'));
        $this->assertNull(GuideType::fromTopicSlug(null));
    }
}
