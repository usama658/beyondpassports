<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

/**
 * Public consented-testimonials display (task L4.2 / #184 — testimonials half).
 *
 * Privacy rule (critical): every testimonial here is ANONYMISED and shown with
 * the traveller's consent. No full names, no identifying details — attribution is
 * deliberately generic ("UK traveller · Egypt eVisa"). Do not add real names,
 * faces, order numbers tied to a person, or anything re-identifying.
 *
 * Compliance: Beyond Passports is an independent commercial service, not a government
 * website. Nothing here implies a guaranteed visa outcome.
 *
 * Schema note: we do NOT emit Review / AggregateRating JSON-LD because these are
 * curated marketing quotes, not a verified ratings dataset. The optional `rating`
 * field below drives the visual stars only — never invent a schema aggregate from it.
 *
 * Routes to register (NOT wired here — routes/web.php is owned elsewhere):
 *   GET /reviews -> index()   (name: reviews)
 *
 * Shared source of truth: TESTIMONIALS lives here as a private const and is shared
 * with resources/views/partials/testimonials.blade.php via a view composer bound in
 * boot() so the partial (used on home/about) and the full /reviews page never
 * duplicate the copy.
 */
class ReviewController extends Controller
{
    /**
     * Anonymised, consented testimonials. Single source of truth for both the
     * full /reviews page and the reusable partials.testimonials section.
     *
     * Each row: quote, attribution (generic — no identifying detail), optional rating (1–5).
     *
     * @var array<int, array{quote: string, attribution: string, rating?: int}>
     */
    private const TESTIMONIALS = [
        [
            'quote'       => 'They spotted my passport was a month short of the validity Egypt needed — before I had booked anything. Sorted the renewal first, then the visa. The stress just vanished.',
            'attribution' => 'UK traveller · Egypt eVisa',
            'rating'      => 5,
        ],
        [
            'quote'       => 'I half-expected a faceless form. Instead a real person rang me back, caught a date I had entered wrong, and walked me through the rest. Felt like having a friend who actually knows the rules.',
            'attribution' => 'UK traveller · India eVisa',
            'rating'      => 5,
        ],
        [
            'quote'       => 'Honestly they could have just taken my money, but they told me my trip was short enough that I might not need the express option at all. That kind of honesty is rare.',
            'attribution' => 'UK traveller · Turkey eVisa',
            'rating'      => 5,
        ],
        [
            'quote'       => 'The ESTA wording confused me every time I tried it myself. They checked everything, explained that it is an authorisation and not a stamp, and kept me updated until it came through.',
            'attribution' => 'UK traveller · USA ESTA',
            'rating'      => 4,
        ],
        [
            'quote'       => 'Booking a last-minute work trip to Sydney, I had no time to wade through the eTA rules. One short form with them, a quick check, and it was handled. Clear fee, no surprises.',
            'attribution' => 'UK traveller · Australia eTA',
            'rating'      => 5,
        ],
        [
            'quote'       => 'I just wanted to drive a hire car abroad without a headache. They explained exactly which IDP I needed and how to collect it in person. Simple, calm, sorted.',
            'attribution' => 'UK traveller · International Driving Permit',
            'rating'      => 5,
        ],
    ];

    /**
     * Register the shared view composer so partials.testimonials always receives
     * the same anonymised registry as the full page — no duplicated copy.
     *
     * Called from a service provider's boot(), OR you may simply rely on the
     * `$testimonials` passed by index() on the /reviews page and the `featured()`
     * default inside the partial. Kept here so the data lives in one file.
     */
    public static function shareWithPartial(): void
    {
        ViewFacade::composer('partials.testimonials', static function ($view): void {
            // Only supply a default if the including view did not pass its own.
            if (! array_key_exists('testimonials', $view->getData())) {
                $view->with('testimonials', self::TESTIMONIALS);
            }
        });
    }

    /**
     * Public read accessor for the partial / composer / tests.
     *
     * @return array<int, array{quote: string, attribution: string, rating?: int}>
     */
    public static function all(): array
    {
        return self::TESTIMONIALS;
    }

    /**
     * Full reviews / testimonials page — lists every consented testimonial
     * plus a CTA to /apply.
     */
    public function index(): View
    {
        return view('public.reviews', [
            'testimonials' => self::TESTIMONIALS,
        ]);
    }
}
