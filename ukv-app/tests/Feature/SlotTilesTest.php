<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\SlotBoard;
use App\Support\SlotTiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * NOTE: SlotBoard::remaining() is the LIVE weekly pool (App\Support\SlotBoard) — its exact
 * numbers vary week to week (reseeded every Saturday). These tests pin the country list via
 * SlotBoard::setCountriesForTesting() for determinism, but still read the REAL allocation/
 * decrement values rather than hardcoding them, and assert STRUCTURE (a colour is present, the
 * baked "32" default is gone, noun is singular/plural-correct) rather than one fixed number,
 * so they stay green regardless of which week they run in.
 */
class SlotTilesTest extends TestCase
{
    use RefreshDatabase;

    private array $countries = [
        'France', 'Spain', 'Italy', 'Germany', 'Greece', 'Netherlands', 'Switzerland', // lp-v2/country tiles
        'Portugal', 'Austria', 'Belgium', 'Czechia', 'Poland', 'Sweden', 'Croatia', 'Malta',
    ];

    protected function tearDown(): void
    {
        SlotBoard::setCountriesForTesting(null);
        parent::tearDown();
    }

    /** Tier the same way SlotTiles does, for assertions — avoids hardcoding a weekly number. */
    private function tierFor(int $left): array
    {
        if ($left <= 1) {
            return ['#dc2626', 16, '#dc2626,#ef4444'];
        }
        if ($left <= 3) {
            return ['#d97706', 38, '#d97706,#f59e0b'];
        }

        return ['var(--green)', 82, 'var(--green),#4bad82'];
    }

    public function test_hydrates_fbopen_tile_from_real_pool_and_bare_tile_from_page_country(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);

        $fixture = '<button type=button class="slc" onclick="fbOpen(\'Germany\',\'de\')"><div class=slac></div><div class=slin>'
            .'<div class=slrow><div class=slcty>Germany</div></div>'
            .'<div class=slnum><b>99</b><span>slots open</span></div>'
            .'<div class=slmeter><i style="width:82%"></i></div>'
            .'<div class=slnx><svg viewBox="0 0 24 24"></svg>Next: 24 Aug</div>'
            .'</div></button>'
            // A "bare" hero tile: no enclosing slc tag, no fbOpen nearby — must use $pageCountry.
            .'<div class=slnum><b>32</b><span>slots open</span></div>'
            .'<div class=slmeter><i style="width:82%"></i></div>'
            .'<div class=slnx><svg viewBox="0 0 24 24"></svg>Next: 24 Aug</div>';

        $out = SlotTiles::hydrate($fixture, 'Switzerland');

        $germanyLeft = SlotBoard::remainingFor('Germany');
        $germanyNoun = $germanyLeft === 1 ? 'slot open' : 'slots open';
        [$germanyColor, $germanyWidth] = $this->tierFor($germanyLeft);
        $this->assertStringContainsString(
            '<div class=slnum><b style="color:'.$germanyColor.'">'.$germanyLeft.'</b><span>'.$germanyNoun.'</span></div>',
            $out,
            'fbOpen-tagged tile must hydrate from its own country (Germany), not the page country'
        );
        $this->assertStringContainsString('width:'.$germanyWidth.'%', $out);

        $swissLeft = SlotBoard::remainingFor('Switzerland');
        $swissNoun = $swissLeft === 1 ? 'slot open' : 'slots open';
        [$swissColor, $swissWidth] = $this->tierFor($swissLeft);
        $this->assertStringContainsString(
            '<div class=slnum><b style="color:'.$swissColor.'">'.$swissLeft.'</b><span>'.$swissNoun.'</span></div>',
            $out,
            'bare tile (no fbOpen) must hydrate from the passed $pageCountry (Switzerland)'
        );
        $this->assertStringContainsString('width:'.$swissWidth.'%', $out);

        // Neither baked default survives.
        $this->assertStringNotContainsString('<b>99</b>', $out);
        $this->assertStringNotContainsString('<b>32</b>', $out);
    }

    public function test_second_hydrate_pass_fully_resyncs_meter_width_and_gradient(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);

        // Pick the country with the most remaining slots this week, so the FIRST pass lands on a
        // non-red tier (a strong, visible contrast once we force it down to 1 before the second pass).
        $remaining = SlotBoard::remaining();
        arsort($remaining);
        $country = (string) array_key_first($remaining);
        $firstLeft = $remaining[$country];
        $this->assertGreaterThan(1, $firstLeft, 'test setup: need a non-red starting tier');
        [$firstColor, $firstWidth, $firstGrad] = $this->tierFor($firstLeft);

        $fixture = '<button type=button class="slc" onclick="fbOpen(\''.$country.'\',\'xx\')"><div class=slac></div><div class=slin>'
            .'<div class=slnum><b>32</b><span>slots open</span></div>'
            .'<div class=slmeter><i style="width:82%"></i></div>'
            .'<div class=slnx><svg viewBox="0 0 24 24"></svg>Next: 24 Aug</div>'
            .'</div></button>';

        $firstPass = SlotTiles::hydrate($fixture);
        $this->assertStringContainsString(
            'width:'.$firstWidth.'%;background:linear-gradient(90deg,'.$firstGrad.')',
            $firstPass,
            'first pass must hydrate the meter for the starting tier'
        );

        // Change the pool for that country: force it all the way down to 1 (the red tier), so a
        // SECOND hydrate() pass over the ALREADY-HYDRATED markup must change the tier entirely.
        $i = 0;
        while (SlotBoard::remainingFor($country) > 1 && $i < 500) {
            SlotBoard::decrement($country, 'idempotency-test-visitor-'.$i);
            $i++;
        }
        $this->assertSame(1, SlotBoard::remainingFor($country), 'test setup: could not pin country to 1');

        $secondPass = SlotTiles::hydrate($firstPass);

        // The meter must be FULLY re-synced: new width AND new gradient, not just the number/colour.
        $this->assertStringContainsString(
            '<div class=slmeter><i style="width:16%;background:linear-gradient(90deg,#dc2626,#ef4444)"></i></div>',
            $secondPass,
            'second pass must re-sync the meter width + gradient to the new tier'
        );
        $this->assertStringContainsString('<b style="color:#dc2626">1</b><span>slot open</span>', $secondPass);
        $this->assertStringNotContainsString('width:'.$firstWidth.'%;background:linear-gradient(90deg,'.$firstGrad.')', $secondPass, 'stale first-tier meter must not survive a second pass');
    }

    public function test_unknown_country_tile_is_left_unchanged(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);

        $fixture = '<button type=button class="slc" onclick="fbOpen(\'Narnia\',\'na\')"><div class=slac></div><div class=slin>'
            .'<div class=slnum><b>32</b><span>slots open</span></div>'
            .'<div class=slmeter><i style="width:82%"></i></div>'
            .'</div></button>';

        $this->assertSame($fixture, SlotTiles::hydrate($fixture, null));
    }

    public function test_search_tile_without_slnum_is_left_untouched(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);

        $fixture = '<button type=button class="slc ask" onclick="fbOpenSearch()"><div class=slac></div><div class=slin>'
            .'<div class=slcty>Somewhere else?</div><div class=slflags></div>'
            .'</div></button>';

        $this->assertSame($fixture, SlotTiles::hydrate($fixture, 'France'));
    }

    public function test_lpv2_route_has_no_baked_default_and_shows_coloured_tiles(): void
    {
        config(['ukv.slots.dynamic' => true]);
        SlotBoard::setCountriesForTesting($this->countries);

        $h = $this->get('/schengen-visa-services-uk')->assertOk()->getContent();

        $this->assertStringNotContainsString('<b>32</b><span>slots open', $h, 'baked default must be gone');
        $this->assertMatchesRegularExpression(
            '/<div class=slnum><b style="color:[^"]+">\d+<\/b><span>slots? open<\/span><\/div>/',
            $h,
            'at least one tile must be hydrated with a tier colour'
        );
    }

    public function test_switzerland_country_page_shows_real_tier_colour_and_singular_noun_near_capacity(): void
    {
        config(['ukv.slots.dynamic' => true]);
        SlotBoard::setCountriesForTesting($this->countries);

        // Force Switzerland down to exactly 1 remaining for this slot week so the red/singular/
        // "near capacity" branch is exercised deterministically, without hardcoding a weekly seed.
        $i = 0;
        while (SlotBoard::remainingFor('Switzerland') > 1 && $i < 200) {
            SlotBoard::decrement('Switzerland', 'slot-tiles-test-visitor-'.$i);
            $i++;
        }
        $this->assertSame(1, SlotBoard::remainingFor('Switzerland'), 'test setup: could not pin Switzerland to 1');

        $h = $this->get('/schengen-visa/switzerland')->assertOk()->getContent();

        $this->assertStringContainsString('<b style="color:#dc2626">1</b><span>slot open</span>', $h);
        $this->assertStringContainsString('Near capacity. Act today.', $h);
    }
}
