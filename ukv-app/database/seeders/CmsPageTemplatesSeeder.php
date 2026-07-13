<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeds ready-made starter pages the team can Duplicate and fill in — the "page templates" gallery.
 * Each is a DRAFT cms page slugged template-* so it never goes public; duplicating it (Duplicate
 * action) makes an editable draft copy to build from. Idempotent (updateOrCreate by slug).
 */
class CmsPageTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $tpl) {
            Page::updateOrCreate(['slug' => $tpl['slug']], $tpl);
        }
    }

    /** @return array<int,array<string,mixed>> */
    private function templates(): array
    {
        return [
            [
                'slug' => 'template-landing-page',
                'title' => 'Template — Landing page',
                'mode' => 'cms',
                'layout' => 'public',
                'status' => 'draft',
                'in_sitemap' => false,
                'noindex' => true,
                'blocks' => [
                    ['type' => 'hero', 'data' => ['eyebrow' => 'Eyebrow', 'title' => 'A clear, benefit-led headline', 'lede' => 'One sentence that says who this is for and what they get.']],
                    ['type' => 'trust-bar', 'data' => ['items' => [
                        ['bold' => 'Schengen', 'rest' => 'experts'], ['bold' => 'No hidden', 'rest' => 'fees'],
                        ['bold' => '7-day', 'rest' => 'support'], ['bold' => 'UK & Europe', 'rest' => 'registered'],
                    ]]],
                    ['type' => 'steps', 'data' => ['eyebrow' => 'How it works', 'heading' => 'Three simple steps', 'items' => [
                        ['title' => 'Tell us your trip', 'text' => 'Share destination and dates.'],
                        ['title' => 'We prepare + check', 'text' => 'We remove the avoidable reasons for refusal.'],
                        ['title' => 'You attend, done', 'text' => 'One appointment, one signature.'],
                    ]]],
                    ['type' => 'feature-grid', 'data' => ['heading' => 'Why travellers choose us', 'items' => [
                        ['title' => 'Human-checked', 'text' => 'A UK specialist reviews every application.'],
                        ['title' => 'Honest pricing', 'text' => 'One fixed fee, shown before you pay.'],
                        ['title' => 'Fast support', 'text' => 'Reach us on WhatsApp any day.'],
                    ]]],
                    ['type' => 'quote', 'data' => ['quote' => 'They handled everything and I was approved without stress.', 'name' => 'A traveller', 'detail' => 'Schengen visa', 'stars' => 5]],
                    ['type' => 'faq', 'data' => ['heading' => 'Common questions', 'items' => [
                        ['q' => 'Can you guarantee my visa?', 'a' => 'No. The embassy decides. We remove the avoidable reasons they say no.'],
                        ['q' => 'What does it cost?', 'a' => 'A clear fixed service fee, shown before you pay, separate from any government fee.'],
                    ]]],
                    ['type' => 'cta-band', 'data' => ['heading' => 'Ready to start?', 'subtext' => 'Message our UK team with your trip.', 'button_label' => 'Start now', 'button_url' => '/apply']],
                ],
            ],
            [
                'slug' => 'template-content-page',
                'title' => 'Template — Content page',
                'mode' => 'cms',
                'layout' => 'public',
                'status' => 'draft',
                'in_sitemap' => false,
                'noindex' => true,
                'blocks' => [
                    ['type' => 'hero', 'data' => ['eyebrow' => 'Section', 'title' => 'Page title goes here', 'lede' => 'A short standfirst introducing the page.']],
                    ['type' => 'rich-text', 'data' => ['body' => '<p>Write the main content here. Use headings and paragraphs.</p>']],
                    ['type' => 'split', 'data' => ['heading' => 'A supporting point', 'body' => 'Explain it here, with an image beside it.', 'button_label' => 'Learn more', 'button_url' => '/services']],
                    ['type' => 'cta-band', 'data' => ['heading' => 'Questions?', 'subtext' => 'Talk to our UK team.', 'button_label' => 'Contact us', 'button_url' => '/contact']],
                ],
            ],
        ];
    }
}
