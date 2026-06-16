<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\GuideType;
use App\Models\Destination;
use App\Models\Guide;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Hand-authored, publishable "gold-standard" Turkey cluster guides — the editorial template the
 * AI drafts (guides:draft) should match. Facts are taken ONLY from the stored Turkey destination
 * record (visa-free for UK tourists up to 90 days; passport valid 150+ days with a blank page;
 * no government visa fee). No figure here is asserted that the destination data does not support;
 * everything else points the reader to the official source. Idempotent (updateOrCreate on
 * destination + guide_type).
 */
class TurkeyGoldGuidesSeeder extends Seeder
{
    public function run(): void
    {
        $turkey = Destination::query()
            ->where('slug', 'turkey')
            ->orWhere('name', 'Turkey')
            ->first();

        if (! $turkey) {
            $this->command?->warn('TurkeyGoldGuidesSeeder: no Turkey destination — skipped.');

            return;
        }

        $now = Carbon::now();
        $reviewer = 'UKVisaCo editorial team';

        // ---- DOCUMENTS -----------------------------------------------------------------------
        // The show template auto-embeds the LIVE requirements checklist for the `documents` type,
        // so the body adds context around it rather than re-listing the items.
        $documentsBody = <<<'HTML'
<p>Good news for most UK travellers: <strong>Turkey is visa-free for British citizens visiting as tourists</strong> for stays of up to 90 days in any 180-day period. That means there is usually no visa to buy and no government visa fee — but you still need the right <em>entry documents</em> ready before you fly, and a passport that meets Turkey's validity rule.</p>

<h2>The documents that actually matter</h2>
<p>Turkish border officers can ask to see proof that you are a genuine short-stay visitor. The personalised checklist above is built for your trip; in almost every case it comes down to three things:</p>
<ul>
  <li><strong>A passport valid well beyond your trip.</strong> Turkey requires your passport to be valid for at least 150 days from the date you arrive, and to have at least one blank page. Many refusals at the border are simply passports that are too close to expiry — check yours early.</li>
  <li><strong>Onward or return travel.</strong> Be ready to show a ticket out of Turkey within your permitted stay.</li>
  <li><strong>Proof of accommodation.</strong> A hotel booking, or the address and an invitation if you are staying with someone.</li>
</ul>

<h2>When youwould need more than the basics</h2>
<p>The visa-free rule covers <strong>tourism and short visits only</strong>. If you are going to work, study, stay longer than 90 days, or travel for certain business purposes, you will likely need a visa or permit — and a different document set. If that is you, start your application with us and we will confirm exactly what is required for your situation.</p>

<h2>How we help</h2>
<p>We check your passport validity against Turkey's rule, make sure your supporting documents are in order before you travel, and — where a visa or permit <em>is</em> needed — prepare and track the whole thing for you. We are an independent service, not a government website, and our service fee is separate from any official fee.</p>
HTML;

        $this->upsert($turkey, GuideType::Documents, [
            'slug' => 'turkey-documents',
            'title' => 'Turkey travel documents for UK citizens: what you need',
            'excerpt' => 'Turkey is visa-free for UK tourists up to 90 days — but your passport must be valid 150+ days and you need onward travel and accommodation proof. Here is the full checklist.',
            'quick_answer' => 'For tourism up to 90 days UK citizens need <strong>no visa</strong> for Turkey. You do need a passport valid for at least <strong>150 days</strong> from arrival with a blank page, proof of onward/return travel, and proof of accommodation. Longer stays, work or study need a visa.',
            'body' => $documentsBody,
            'faq' => [
                ['q' => 'Do UK citizens need a visa for Turkey?', 'a' => 'Not for tourism or short visits of up to 90 days in any 180-day period. You travel visa-free on a valid passport. Work, study or stays over 90 days do need a visa or permit.'],
                ['q' => 'How long must my passport be valid for Turkey?', 'a' => 'At least 150 days from the date you arrive, with at least one blank page. Always confirm the current rule on the official source before you travel.'],
                ['q' => 'Do I need to show a return ticket?', 'a' => 'Yes — be ready to show onward or return travel within your permitted stay, along with proof of where you are staying.'],
            ],
            'meta_title' => 'Turkey Documents for UK Travellers (Visa-Free Rules) | UKVisaCo',
            'meta_description' => 'What documents UK citizens need for Turkey: visa-free up to 90 days, passport valid 150+ days with a blank page, onward travel and accommodation proof. Independent service.',
        ], $now, $reviewer);

        // ---- PROCESSING TIME -----------------------------------------------------------------
        $processingBody = <<<'HTML'
<p>Because <strong>Turkey is visa-free for UK tourists</strong> (up to 90 days), there is normally <strong>no visa to process and no waiting time</strong> — you can book and travel once your documents are in order. The timings that genuinely matter are about your <em>passport</em> and, if you need one, an <em>actual visa or permit</em>.</p>

<h2>The timing that matters most: your passport</h2>
<p>Turkey requires your passport to be valid for at least 150 days from arrival. His Majesty's Passport Office advises allowing plenty of time for a renewal, and demand is heaviest before the summer and school holidays. If your passport is anywhere near expiry, renew it <strong>before</strong> you book — this is the single most common cause of refused travel.</p>

<h2>If you DO need a visa or permit</h2>
<p>For work, study, longer stays or certain business trips, a visa or residence permit is required and processing times vary by route and by how complete your application is. We cannot speed up an official decision, but we can speed up <em>our</em> handling so your application is submitted correctly and quickly:</p>
<ul>
  <li><strong>Standard</strong> handling at our normal service fee.</li>
  <li><strong>Express</strong> handling moves your file to the front of <em>our</em> queue for faster preparation and submission.</li>
</ul>
<p>Express changes how fast <strong>we</strong> work, not how fast the authorities decide, and no service can guarantee approval or a specific decision date.</p>

<h2>Our advice</h2>
<p>Check your passport today, get your documents ready early, and if a visa is needed, start as soon as your travel dates are set. Always confirm the current rules and any official timescales on the official source before you travel.</p>
HTML;

        $this->upsert($turkey, GuideType::ProcessingTime, [
            'slug' => 'turkey-processing-time',
            'title' => 'How long does it take to get into Turkey from the UK?',
            'excerpt' => 'Turkey is visa-free for UK tourists, so there is no visa wait — but passport renewals and any required visa do have timescales. Here is what to plan for.',
            'quick_answer' => 'For visa-free tourism there is <strong>no visa processing time</strong> — travel once your documents are ready. The timings that matter are renewing a passport that is close to expiry, and, if you actually need a visa or permit, applying early. No service can guarantee an official decision date.',
            'body' => $processingBody,
            'faq' => [
                ['q' => 'How long does a Turkey visa take for UK citizens?', 'a' => 'For tourism up to 90 days no visa is needed, so there is no processing time. If you need a visa or permit for work, study or a longer stay, timescales vary — apply as early as your dates allow.'],
                ['q' => 'Does express handling get me approved faster?', 'a' => 'No. Express speeds up our preparation and submission of your application, not the authority\'s decision. No service can guarantee approval or a specific decision date.'],
                ['q' => 'What should I sort out first?', 'a' => 'Your passport. Turkey needs at least 150 days validity from arrival — renew early if you are anywhere near expiry, as renewals are busiest before holidays.'],
            ],
            'meta_title' => 'Turkey Processing Time for UK Travellers (Visa-Free) | UKVisaCo',
            'meta_description' => 'Turkey is visa-free for UK tourists so there is no visa wait — but passport renewals and any required visa have timescales. What to plan for before you travel.',
        ], $now, $reviewer);

        $this->command?->info('TurkeyGoldGuidesSeeder: documents + processing-time guides published.');
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function upsert(Destination $turkey, GuideType $type, array $attrs, Carbon $now, string $reviewer): void
    {
        Guide::query()->updateOrCreate(
            ['destination_id' => $turkey->getKey(), 'guide_type' => $type],
            array_merge($attrs, [
                'status' => 'published',
                'published_at' => $now,
                'reviewed_by' => $reviewer,
                'reviewed_at' => $now,
                'sort_order' => 0,
            ]),
        );
    }
}
