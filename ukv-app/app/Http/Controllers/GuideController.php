<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Public GUIDES/blog silo — the content cluster (task L4.2 / #184).
 *
 * Static, DB-free content. The guide registry below is the single source of
 * truth shared by index() and show(): the index lists the cards, show() looks
 * up one slug and renders the long-form article. Each entry that carries a
 * `body_view` renders a dedicated partial under
 * resources/views/public/guides/articles/{view}.blade.php; entries without one
 * fall back to a sensible templated body (articles/_template.blade.php).
 *
 * Routes to register (NOT wired here — routes/web.php is owned elsewhere):
 *   GET /guides          -> index()
 *   GET /guides/{slug}   -> show()
 *
 * Content is GENERAL guidance only. Not a government website; requirements
 * depend on nationality/residence; an ETA is an authorisation not a document;
 * no service can guarantee a government decision. All examples are anonymised —
 * no client identifiers anywhere in this file or the views.
 */
class GuideController extends Controller
{
    /**
     * Shared guide registry. Keyed by slug so show() can do an O(1) lookup.
     *
     * Fields:
     *   title, excerpt        — card + header copy
     *   category              — 'guides' | 'tips' | 'stories' (drives chip + band colour)
     *   category_label        — human label shown on the card/eyebrow
     *   read_time             — e.g. '6 min read'
     *   date                  — display date (e.g. '08 Jan 2026')
     *   date_iso              — ISO date for <time>/JSON-LD (e.g. '2026-01-08')
     *   quick_answer          — the "Quick answer" callout HTML (plain sentences)
     *   body_view             — optional dedicated article partial name; null => templated body
     *   related               — array of 2-3 other slugs to link as related guides
     */
    private const GUIDES = [
        'eta-vs-visa-difference' => [
            'title' => 'ETA vs visa: what\'s the difference?',
            'excerpt' => 'Two very different things travellers often confuse — and why it matters before you book.',
            'category' => 'guides',
            'category_label' => 'Guide',
            'read_time' => '6 min read',
            'date' => '08 Jan 2026',
            'date_iso' => '2026-01-08',
            'quick_answer' => 'An <strong>ETA</strong> (electronic travel authorisation) is digital permission to travel that is linked to your passport — there is no sticker, stamp or paper document. A <strong>visa</strong> is a separate, usually more involved permission that often does produce a document or stamp. Which one you need depends on where you are going and your nationality — not on which word sounds more official.',
            'body_view' => 'eta-vs-visa-difference',
            'related' => ['passport-validity-mistake', 'documents-before-you-apply', 'applied-and-refused-next-steps'],
        ],
        'passport-validity-mistake' => [
            'title' => 'Avoid the #1 passport-validity mistake',
            'excerpt' => 'Many countries count validity from your return date, not departure. Here is how to check yours.',
            'category' => 'guides',
            'category_label' => 'Guide',
            'read_time' => '4 min read',
            'date' => '22 Dec 2025',
            'date_iso' => '2025-12-22',
            'quick_answer' => 'Most countries do not count passport validity from the day you fly out — they count from the day you are due to <strong>return</strong>, and many require several months left beyond that date. Check the months-of-validity rule for your destination as soon as you start planning, because renewing a passport takes time you may not have closer to the trip.',
            'body_view' => 'passport-validity-mistake',
            'related' => ['documents-before-you-apply', 'eta-vs-visa-difference', 'idp-which-type'],
        ],
        'do-uk-travellers-need-visa-turkey' => [
            'title' => 'Do UK travellers need a visa for Turkey?',
            'excerpt' => 'What has changed, who is eligible for an eVisa, and how long it covers — at a glance.',
            'category' => 'tips',
            'category_label' => 'Destination tips',
            'read_time' => '5 min read',
            'date' => '12 Jan 2026',
            'date_iso' => '2026-01-12',
            'quick_answer' => 'Entry rules for any single country change over time and depend on your nationality and residence, so always confirm the current rule before you book. As a general orientation, many UK travellers heading to Turkey for a short tourist trip use an <strong>eVisa</strong> — an online authorisation you arrange in advance — rather than a sticker visa. The length of stay it permits is set by the destination, not by us.',
            'body_view' => 'do-uk-travellers-need-visa-turkey',
            'related' => ['eta-vs-visa-difference', 'documents-before-you-apply', 'passport-validity-mistake'],
        ],
        'idp-which-type' => [
            'title' => 'International Driving Permit: which type for which country?',
            'excerpt' => '1949 vs 1968 IDP, what each covers, and how to work out which one your trip needs.',
            'category' => 'guides',
            'category_label' => 'Guide',
            'read_time' => '7 min read',
            'date' => '03 Dec 2025',
            'date_iso' => '2025-12-03',
            'quick_answer' => 'An International Driving Permit (IDP) is a translation of your UK licence, not a replacement — you carry both. There are different IDP types (the 1949 and 1968 conventions are the common ones), and the type you need depends on the country you are driving in. Some trips even need more than one. Check the destination before you travel and arrange the correct type in good time.',
            'body_view' => null,
            'related' => ['do-uk-travellers-need-visa-turkey', 'documents-before-you-apply', 'eta-vs-visa-difference'],
        ],
        'applied-and-refused-next-steps' => [
            'title' => 'Applied and refused — what happens next?',
            'excerpt' => 'A calm, practical look at what a refusal can mean and the sensible steps that follow.',
            'category' => 'stories',
            'category_label' => 'Traveller story',
            'read_time' => '6 min read',
            'date' => '26 Nov 2025',
            'date_iso' => '2025-11-26',
            'quick_answer' => 'A refusal is not always the end of the road, but the right next step depends entirely on the reason given and the destination\'s own rules. Read any decision notice carefully, do not simply re-submit the same application unchanged, and take time to understand what was missing. No service can overturn or guarantee a government decision — but a careful, accurate fresh application often addresses the cause.',
            'body_view' => null,
            'related' => ['documents-before-you-apply', 'eta-vs-visa-difference', 'passport-validity-mistake'],
        ],
        'documents-before-you-apply' => [
            'title' => 'Documents to prepare before you apply',
            'excerpt' => 'A simple checklist that helps most applications go through without back-and-forth.',
            'category' => 'guides',
            'category_label' => 'Guide',
            'read_time' => '5 min read',
            'date' => '10 Nov 2025',
            'date_iso' => '2025-11-10',
            'quick_answer' => 'Most delayed or refused applications come down to documents, not eligibility. Before you apply, have your passport (checked for validity and blank pages), a clear digital photo that meets the spec, and details of your trip — dates, accommodation and onward travel — ready. Getting these right up front is the single biggest thing you can do to avoid back-and-forth.',
            'body_view' => null,
            'related' => ['passport-validity-mistake', 'eta-vs-visa-difference', 'applied-and-refused-next-steps'],
        ],
    ];

    /**
     * Public slugs of every guide in the registry, in registry order.
     *
     * Single source of truth for anything outside this controller that needs the
     * canonical guide URL list — notably SitemapController, which would otherwise
     * keep a hand-maintained copy that silently drifts when a guide is added or
     * removed here (audit M-7).
     *
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::GUIDES);
    }

    /**
     * Guides hub — category chips + responsive card grid.
     */
    public function index(): View
    {
        // Pass slug-keyed entries; the view turns the key into the card link.
        return view('public.guides.index', [
            'guides' => self::GUIDES,
        ]);
    }

    /**
     * Single guide article, looked up by slug. 404 on an unknown slug.
     */
    public function show(string $slug): View
    {
        if (! array_key_exists($slug, self::GUIDES)) {
            abort(404);
        }

        $guide = self::GUIDES[$slug];

        // Resolve related entries (slug + title + label + read_time) for the
        // "Keep reading" list, skipping any that no longer exist in the registry.
        $related = [];
        foreach ($guide['related'] ?? [] as $relatedSlug) {
            if (isset(self::GUIDES[$relatedSlug])) {
                $related[$relatedSlug] = self::GUIDES[$relatedSlug];
            }
        }

        return view('public.guides.show', [
            'slug' => $slug,
            'guide' => $guide,
            'related' => $related,
        ]);
    }
}
