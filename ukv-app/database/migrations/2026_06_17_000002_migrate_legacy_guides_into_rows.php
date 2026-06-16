<?php

use App\Enums\GuideType;
use App\Models\Destination;
use App\Models\Guide;
use Illuminate\Database\Migrations\Migration;

/**
 * Data migration — move the 6 legacy registry guides into `guides` rows.
 *
 * The old GuideController held a `const GUIDES` registry of 6 entries (5 evergreen + 1 Turkey
 * destination tip). The Guide engine makes guides DATA, so this lifts them into real rows:
 *   - 5 evergreen guides  -> destination_id null, guide_type null, original slug kept.
 *   - the Turkey guide     -> destination_id = Turkey, guide_type = do_i_need_visa, topic slug.
 *
 * Body HTML is taken verbatim from the legacy article partials
 * (resources/views/public/guides/articles/*), with the few Blade {{ url(...) }} expressions
 * resolved to absolute URLs so the stored HTML is self-contained. The three guides that used
 * the shared _template get a general, compliance-safe templated body.
 *
 * All rows: status=published, published_at=now, reviewed_at=now, reviewed_by recorded for the
 * E-E-A-T byline. Idempotent: a row matching the target (slug for evergreen; destination+type
 * for the country guide) is skipped, so re-running never duplicates.
 *
 * Legacy slugs -> new paths (301s wired in routes/web.php, owned elsewhere — reported separately):
 *   /guides/do-uk-travellers-need-visa-turkey -> /visa/turkey/do-i-need-a-visa
 *   the other five evergreen slugs keep /guides/{slug} (no redirect needed).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // url() resolves against config('app.url'); used to bake links into stored HTML.
        $u = fn (string $path): string => url($path);

        // ---- Evergreen guides (destination_id null, guide_type null) --------------------
        $evergreen = [
            [
                'slug'    => 'eta-vs-visa-difference',
                'title'   => "ETA vs visa: what's the difference?",
                'excerpt' => 'Two very different things travellers often confuse — and why it matters before you book.',
                'meta_title'       => "ETA vs visa: what's the difference? | Beyond Passports Guides",
                'meta_description' => 'An ETA is digital permission linked to your passport; a visa is a formal permission that often produces a document. Here is the plain-English difference for UK travellers.',
                'quick_answer' => 'An <strong>ETA</strong> (electronic travel authorisation) is digital permission to travel that is linked to your passport — there is no sticker, stamp or paper document. A <strong>visa</strong> is a separate, usually more involved permission that often does produce a document or stamp. Which one you need depends on where you are going and your nationality — not on which word sounds more official.',
                'body'    => $this->etaVsVisaBody($u),
            ],
            [
                'slug'    => 'passport-validity-mistake',
                'title'   => 'Avoid the #1 passport-validity mistake',
                'excerpt' => 'Many countries count validity from your return date, not departure. Here is how to check yours.',
                'meta_title'       => 'Avoid the #1 passport-validity mistake | Beyond Passports Guides',
                'meta_description' => 'Most countries count passport validity from your return date and require months of validity beyond it. Here is how to check yours before you book.',
                'quick_answer' => 'Most countries do not count passport validity from the day you fly out — they count from the day you are due to <strong>return</strong>, and many require several months left beyond that date. Check the months-of-validity rule for your destination as soon as you start planning, because renewing a passport takes time you may not have closer to the trip.',
                'body'    => $this->passportValidityBody($u),
            ],
            [
                'slug'    => 'idp-which-type',
                'title'   => 'International Driving Permit: which type for which country?',
                'excerpt' => '1949 vs 1968 IDP, what each covers, and how to work out which one your trip needs.',
                'meta_title'       => 'International Driving Permit: which type for which country? | Beyond Passports Guides',
                'meta_description' => 'There are different IDP types (1949 and 1968 conventions). The type you need depends on the country you are driving in — here is how to work it out.',
                'quick_answer' => 'An International Driving Permit (IDP) is a translation of your UK licence, not a replacement — you carry both. There are different IDP types (the 1949 and 1968 conventions are the common ones), and the type you need depends on the country you are driving in. Some trips even need more than one. Check the destination before you travel and arrange the correct type in good time.',
                'body'    => $this->templateBody($u),
            ],
            [
                'slug'    => 'applied-and-refused-next-steps',
                'title'   => 'Applied and refused — what happens next?',
                'excerpt' => 'A calm, practical look at what a refusal can mean and the sensible steps that follow.',
                'meta_title'       => 'Applied and refused — what happens next? | Beyond Passports Guides',
                'meta_description' => 'A refusal is not always the end of the road. Read your decision notice, understand the reason, and take a careful, accurate fresh application — here is how.',
                'quick_answer' => 'A refusal is not always the end of the road, but the right next step depends entirely on the reason given and the destination\'s own rules. Read any decision notice carefully, do not simply re-submit the same application unchanged, and take time to understand what was missing. No service can overturn or guarantee a government decision — but a careful, accurate fresh application often addresses the cause.',
                'body'    => $this->templateBody($u),
            ],
            [
                'slug'    => 'documents-before-you-apply',
                'title'   => 'Documents to prepare before you apply',
                'excerpt' => 'A simple checklist that helps most applications go through without back-and-forth.',
                'meta_title'       => 'Documents to prepare before you apply | Beyond Passports Guides',
                'meta_description' => 'Most delayed or refused applications come down to documents, not eligibility. Here is the simple checklist to prepare before you apply.',
                'quick_answer' => 'Most delayed or refused applications come down to documents, not eligibility. Before you apply, have your passport (checked for validity and blank pages), a clear digital photo that meets the spec, and details of your trip — dates, accommodation and onward travel — ready. Getting these right up front is the single biggest thing you can do to avoid back-and-forth.',
                'body'    => $this->templateBody($u),
            ],
        ];

        $sort = 0;
        foreach ($evergreen as $g) {
            $sort++;

            if (Guide::query()->whereNull('destination_id')->where('slug', $g['slug'])->exists()) {
                continue; // idempotent
            }

            Guide::create([
                'destination_id'   => null,
                'guide_type'       => null,
                'slug'             => $g['slug'],
                'title'            => $g['title'],
                'excerpt'          => $g['excerpt'],
                'quick_answer'     => $g['quick_answer'],
                'body'             => $g['body'],
                'meta_title'       => $g['meta_title'],
                'meta_description' => $g['meta_description'],
                'faq'              => null,
                'status'           => 'published',
                'published_at'     => $now,
                'reviewed_by'      => 'Beyond Passports editorial team',
                'reviewed_at'      => $now,
                'sort_order'       => $sort,
            ]);
        }

        // ---- Turkey country guide (destination + do_i_need_visa) ------------------------
        $turkey = Destination::query()
            ->where('slug', 'turkey')
            ->orWhere('name', 'Turkey')
            ->first();

        if ($turkey !== null) {
            $type = GuideType::DoINeedVisa;

            $exists = Guide::query()
                ->where('destination_id', $turkey->id)
                ->where('guide_type', $type->value)
                ->exists();

            if (! $exists) {
                Guide::create([
                    'destination_id'   => $turkey->id,
                    'guide_type'       => $type->value,
                    'slug'             => $turkey->slug.'-'.$type->topicSlug(),
                    'title'            => 'Do UK travellers need a visa for Turkey?',
                    'excerpt'          => 'What has changed, who is eligible for an eVisa, and how long it covers — at a glance.',
                    'quick_answer'     => 'Entry rules for any single country change over time and depend on your nationality and residence, so always confirm the current rule before you book. As a general orientation, many UK travellers heading to Turkey for a short tourist trip use an <strong>eVisa</strong> — an online authorisation you arrange in advance — rather than a sticker visa. The length of stay it permits is set by the destination, not by us.',
                    'body'             => $this->turkeyBody($u),
                    'meta_title'       => 'Do UK travellers need a visa for Turkey? | Beyond Passports',
                    'meta_description' => 'Many UK travellers use a Turkey eVisa for short tourist trips — an online authorisation arranged in advance. Here is the general picture, plus what to check before you book.',
                    'faq'              => null,
                    'status'           => 'published',
                    'published_at'     => $now,
                    'reviewed_by'      => 'Beyond Passports editorial team',
                    'reviewed_at'      => $now,
                    'sort_order'       => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Remove only the rows this migration could have created.
        Guide::query()
            ->whereNull('destination_id')
            ->whereIn('slug', [
                'eta-vs-visa-difference',
                'passport-validity-mistake',
                'idp-which-type',
                'applied-and-refused-next-steps',
                'documents-before-you-apply',
            ])
            ->delete();

        $turkey = Destination::query()->where('slug', 'turkey')->orWhere('name', 'Turkey')->first();
        if ($turkey !== null) {
            Guide::query()
                ->where('destination_id', $turkey->id)
                ->where('guide_type', GuideType::DoINeedVisa->value)
                ->delete();
        }
    }

    /** ETA vs visa — body lifted from articles/eta-vs-visa-difference.blade.php (links resolved). */
    private function etaVsVisaBody(callable $u): string
    {
        return <<<HTML
  <p>If you've started planning a trip, you've probably seen both words used almost interchangeably. The travel page says you need an "ETA", a forum post says "visa", and a friend insists you don't need anything at all. They can all be right — for different people. Below is the plain-English version.</p>

  <h2>What an ETA actually is</h2>
  <p>An ETA is an <strong>electronic travel authorisation</strong>. You apply online, the authority screens your details before you travel, and approval is recorded digitally against your passport number. The key thing to understand: <strong>an ETA is an authorisation, not a document</strong>. Nothing gets stuck in your passport. There's no sticker to collect and no certificate you must print and carry.</p>
  <p>Because it's tied to your passport electronically, the system "knows" your authorisation when you check in and when you reach the border. Most ETAs are quick to apply for, relatively low cost, and intended for short visits such as tourism or business trips.</p>
  <p>You'll see ETA-style schemes under different names in different countries — for example the US ESTA and Australia's eTA work on the same principle.</p>

  <h2>What a visa is</h2>
  <p>A visa is a formal permission to enter a country, usually for a defined purpose and length of stay. Compared with an ETA, a visa is generally a bigger process: more questions, sometimes supporting documents, occasionally an appointment, biometrics or an interview. A visa often <strong>does</strong> result in something physical or formal — a sticker in your passport, a stamp, or an official electronic record you may be asked to show.</p>
  <p>Visas tend to cost more, take longer, and cover a wider range of purposes — tourism, study, work, family visits and so on. The exact rules vary enormously from country to country.</p>

  <h2>The key differences at a glance</h2>
  <ul>
    <li><strong>What it is:</strong> an ETA is digital permission linked to your passport; a visa is a formal permission that often produces a document or stamp.</li>
    <li><strong>Cost:</strong> ETAs are usually cheaper; visas tend to cost more.</li>
    <li><strong>Processing:</strong> ETAs are often fast; visas can take days or weeks and may need an appointment.</li>
    <li><strong>What it permits:</strong> ETAs typically cover short tourism or business visits; visas can cover a much wider range of purposes and longer stays.</li>
    <li><strong>Evidence:</strong> with an ETA there's nothing to carry — it's recorded electronically; with a visa you may have a sticker, stamp or record to show.</li>
  </ul>

  <p class="inline-note">Important: requirements depend on your nationality and where you live. Two people on the same flight can need completely different things. This guide explains the concepts — it doesn't tell you your personal requirement.</p>

  <h3>Which popular destinations use which</h3>
  <p>As a rough orientation for UK travellers:</p>
  <ul>
    <li><strong>ETA-style authorisations:</strong> the United States (ESTA) and Australia (eTA) use electronic authorisations linked to your passport.</li>
    <li><strong>eVisas:</strong> destinations such as Turkey, Egypt and India commonly use an online visa you apply for in advance.</li>
    <li><strong>Visa-free (with conditions):</strong> some destinations let UK citizens enter for short stays without applying in advance — but entry rules and limits still apply.</li>
  </ul>
  <p>These are general patterns and they change. Browse your <a href="{$u('/destinations')}">destination guide</a> and always confirm the current rule for your specific passport before you book.</p>

  <h2>How to be sure for your trip</h2>
  <p>The honest answer is that there's no shortcut around checking your exact situation. To get it right:</p>
  <ul>
    <li>Know your <strong>nationality and country of residence</strong> — both can affect what you need.</li>
    <li>Check your <strong>passport validity</strong> early; many countries require several months' validity beyond your travel dates.</li>
    <li>Confirm your <strong>purpose and length of stay</strong> — tourism, business and longer visits can have different rules.</li>
    <li>Apply <strong>in good time</strong>, especially for visas, which can take longer than expected.</li>
  </ul>
  <p>If you'd rather not piece it together yourself, you can <a href="{$u('/tools')}">use our free checker</a> to see what applies to your trip, and we'll confirm the details for your nationality and destination.</p>

  <p class="inline-note">A reminder on what we are: Beyond Passports is an independent service and not a government website. We can't change or guarantee any government decision — we help you understand the requirements, prepare your application carefully and submit it correctly.</p>
HTML;
    }

    /** Passport validity — body lifted from articles/passport-validity-mistake.blade.php. */
    private function passportValidityBody(callable $u): string
    {
        $docs = $u('/guides/documents-before-you-apply');

        return <<<HTML
  <p>It's the detail that catches out more travellers than almost anything else — and it has nothing to do with whether you're eligible to travel. People assume their passport is "valid until 2027" and stop thinking about it. Then, weeks before a trip, they discover the destination needs several months of validity left <em>beyond</em> the day they fly home. By then, a renewal may not arrive in time.</p>

  <h2>Why the date you think matters often isn't</h2>
  <p>Most countries do not measure passport validity from the day you depart. They measure it from the date you are scheduled to <strong>return</strong>, and many add a buffer on top — commonly three or six months. So a passport that expires shortly after your trip can still fall foul of the rule, even though it's technically "in date" on the day you fly.</p>
  <p>This is why two travellers with the same expiry date can get different answers depending on where they're going: the required buffer is set by the destination, not by your passport.</p>

  <h2>The blank-pages trap</h2>
  <p>Validity isn't the only thing border officials look at. Some destinations also require one or more <strong>blank pages</strong> for stamps or visa stickers. A passport that's nearly full can be refused entry even when the expiry date is fine. If you're a frequent traveller, check your remaining pages as well as the date.</p>

  <h2>How to check yours in two minutes</h2>
  <ul>
    <li>Find your passport's <strong>expiry date</strong> and your planned <strong>return date</strong>.</li>
    <li>Look up the <strong>months-of-validity rule</strong> for your destination (often three or six months beyond return).</li>
    <li>Count forward from your return date by that buffer — your expiry must be later than that.</li>
    <li>Glance at your <strong>blank pages</strong> while you're at it.</li>
  </ul>
  <p class="inline-note">Rules vary by destination and change over time. This is the general principle, not a statement of any specific country's current requirement — always confirm before you book.</p>

  <h2>What to do if you're cutting it fine</h2>
  <p>If your passport doesn't clear the buffer, the safest move is to renew it before you commit to travel dates, because a renewal takes time you may not have closer to departure. Plan around the renewal, not the other way round.</p>
  <p>Checking this early is the single cheapest insurance there is. Once you know your passport is good, you can <a href="{$u('/tools')}">use our free checker</a> to see what else your trip needs, browse <a href="{$u('/destinations')}">destination requirements</a>, or read our guide to the <a href="{$docs}">documents to prepare before you apply</a>.</p>

  <p class="inline-note">Beyond Passports is an independent service and not a government website. We help you check requirements and prepare accurately — we can't change a passport rule or guarantee any government decision.</p>
HTML;
    }

    /** Turkey — body lifted from articles/do-uk-travellers-need-visa-turkey.blade.php. */
    private function turkeyBody(callable $u): string
    {
        $pv = $u('/guides/passport-validity-mistake');

        return <<<HTML
  <p>Turkey is one of the most-searched destinations for UK travellers, and the question comes up again and again: do I need a visa, and if so, what kind? The honest starting point is that entry rules are set by the destination, depend on your nationality and residence, and change over time — so anything below is general orientation, not a statement of today's exact requirement for you.</p>

  <h2>The general picture for short tourist trips</h2>
  <p>For short tourist visits, many UK travellers use an <strong>eVisa</strong> rather than a sticker visa. An eVisa is an online authorisation you arrange in advance: you apply, it's linked to your passport, and there's no embassy appointment for the typical short stay. It sits in the family of "apply online before you fly" permissions — more involved than a light travel authorisation, but far simpler than a traditional consular visa.</p>
  <p>Whether you personally need one — and what it costs and covers — depends on your passport and your reason for travelling. A holiday, a business trip and a longer stay are not the same case.</p>

  <h2>What an eVisa typically covers</h2>
  <ul>
    <li><strong>Purpose:</strong> usually tourism or short business visits — not work or long-term stays.</li>
    <li><strong>Duration:</strong> a maximum stay set by the destination, often counted within a wider window. The destination decides this, not us.</li>
    <li><strong>Format:</strong> electronic and tied to your passport; you apply ahead of travel rather than at the border.</li>
  </ul>
  <p class="inline-note">Don't rely on a friend's experience from a previous year. Allowances, eligibility and even the category can change — confirm the current rule for your specific passport before you book.</p>

  <h2>Before you apply: the usual checks</h2>
  <p>Two things trip people up far more often than eligibility:</p>
  <ul>
    <li><strong>Passport validity.</strong> Many destinations want several months left beyond your return date. See our guide on the <a href="{$pv}">#1 passport-validity mistake</a>.</li>
    <li><strong>Accurate details.</strong> Names, dates and passport numbers must match your passport exactly — small typos are a common cause of problems.</li>
  </ul>

  <h2>How to be sure</h2>
  <p>If you'd rather not work it out alone, <a href="{$u('/tools')}">run our free checker</a> for your trip, or browse our <a href="{$u('/destinations')}">destinations</a> to see the current category and what's usually required. When you're ready, our UK team can prepare and check your application before it's submitted — <a href="{$u('/apply')}">start your application here</a>.</p>

  <p class="inline-note">Beyond Passports is an independent service and not a government website. This is general guidance only; requirements depend on your nationality and residence and can change, and no service can guarantee a government decision.</p>
HTML;
    }

    /** Generic templated body for the guides that used articles/_template.blade.php. */
    private function templateBody(callable $u): string
    {
        $eta  = $u('/guides/eta-vs-visa-difference');
        $docs = $u('/guides/documents-before-you-apply');

        return <<<HTML
  <p>This is one of the questions our UK team hears most, so here's a plain-English overview. Treat it as general orientation rather than a personal answer: the exact rules depend on your nationality, your country of residence and the destination, and they can change over time.</p>

  <h2>Start with the principle, not the rumour</h2>
  <p>Travel admin attracts a lot of confident-sounding advice — from forums, from friends, from a half-remembered trip a few years ago. The trouble is that the same trip can look completely different for two people, and rules quietly change between years. The reliable approach is to understand the underlying principle, then confirm the current detail for your own situation before you commit to dates.</p>

  <h2>What usually matters most</h2>
  <ul>
    <li><strong>Your nationality and residence.</strong> Both can change what you need, even for an identical itinerary.</li>
    <li><strong>Your purpose and length of stay.</strong> Tourism, business and longer visits often follow different rules.</li>
    <li><strong>Your documents.</strong> Most delays and refusals come down to paperwork — passport validity, blank pages, a clear photo and accurate details — rather than eligibility.</li>
    <li><strong>Timing.</strong> Applying in good time leaves room to fix a detail or renew a passport before it affects your trip.</li>
  </ul>

  <p class="inline-note">Requirements depend on your nationality and where you live, and they change. This guide explains the general idea — it doesn't tell you your personal requirement. Always confirm the current rule before you travel.</p>

  <h2>A simple way to get it right</h2>
  <p>You don't have to piece it together alone. The quickest route is to check your specific trip, then have a real person review the details before anything is submitted:</p>
  <ul>
    <li><a href="{$u('/tools')}">Run our free checker</a> to see what your trip is likely to need.</li>
    <li>Browse our <a href="{$u('/destinations')}">destinations</a> for the current category and typical requirements.</li>
    <li>If driving is part of your trip, see our <a href="{$u('/driving-abroad')}">driving-abroad (IDP) guidance</a>.</li>
    <li>Read up first, then <a href="{$u('/apply')}">start your application</a> when you're ready and we'll check it for errors.</li>
  </ul>

  <p>For more background, the <a href="{$eta}">ETA vs visa explainer</a> and our checklist of <a href="{$docs}">documents to prepare before you apply</a> are good companions to this guide.</p>

  <p class="inline-note">Beyond Passports is an independent service and not a government website. We help you understand requirements, prepare carefully and submit correctly — we can't change a rule or guarantee any government decision.</p>
HTML;
    }
};
