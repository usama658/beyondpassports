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
            [
                'slug' => 'template-premium-landing',
                'title' => 'Template — Premium landing (full kit)',
                'mode' => 'cms',
                'layout' => 'public',
                'status' => 'draft',
                'in_sitemap' => false,
                'noindex' => true,
                'blocks' => [
                    ['type' => 'notice-bar', 'data' => ['tone' => 'brand', 'text' => 'Applying soon? Get your document checklist first.', 'link_label' => 'Get it free', 'link_url' => '/document-checklist']],
                    ['type' => 'hero', 'data' => ['eyebrow' => 'Schengen visas', 'title' => 'A calmer way to apply for your Schengen visa', 'lede' => 'A UK specialist prepares and checks your application so avoidable reasons for refusal are removed before you attend.']],
                    ['type' => 'logo-strip', 'data' => ['heading' => 'As featured in', 'items' => [
                        ['src' => '', 'name' => 'Add a partner or press logo'],
                    ]]],
                    ['type' => 'steps', 'data' => ['eyebrow' => 'How it works', 'heading' => 'Three simple steps', 'items' => [
                        ['title' => 'Tell us your trip', 'text' => 'Share your destination and dates.'],
                        ['title' => 'We prepare and check', 'text' => 'A specialist reviews every document.'],
                        ['title' => 'You attend, done', 'text' => 'One appointment, fully prepared.'],
                    ]]],
                    ['type' => 'checklist', 'data' => ['heading' => "What's included", 'items' => [
                        ['text' => 'A UK specialist reviews your whole application'],
                        ['text' => 'A document checklist tailored to your trip'],
                        ['text' => 'Support on WhatsApp any day of the week'],
                    ]]],
                    ['type' => 'compare-table', 'data' => ['heading' => 'With us vs going it alone', 'col_a' => 'With us', 'col_b' => 'On your own', 'items' => [
                        ['label' => 'Human document check', 'has_a' => true, 'has_b' => false],
                        ['label' => 'Avoidable refusal reasons removed', 'has_a' => true, 'has_b' => false],
                        ['label' => 'One clear fixed fee', 'has_a' => true, 'has_b' => false],
                        ['label' => 'You do the paperwork', 'has_a' => false, 'has_b' => true],
                    ]]],
                    ['type' => 'tabs', 'data' => ['heading' => 'Common questions, answered', 'items' => [
                        ['label' => 'Timing', 'body' => 'We work to your travel date and tell you the soonest realistic window.'],
                        ['label' => 'Documents', 'body' => 'We send a checklist tailored to your destination and reason for travel.'],
                        ['label' => 'Guarantees', 'body' => 'No one can guarantee a visa. The embassy decides. We remove the avoidable reasons they say no.'],
                    ]]],
                    ['type' => 'testimonials', 'data' => ['heading' => 'What travellers say', 'items' => [
                        ['quote' => 'Replace with a genuine, consented testimonial.', 'name' => 'Traveller name', 'detail' => 'Schengen visa'],
                    ]]],
                    ['type' => 'contact-cards', 'data' => ['heading' => 'Talk to our UK team', 'items' => [
                        ['title' => 'WhatsApp', 'text' => 'Fastest reply, any day.', 'button_label' => 'Message us', 'button_url' => '/contact'],
                        ['title' => 'Email', 'text' => 'For longer questions.', 'button_label' => 'Email us', 'button_url' => '/contact'],
                    ]]],
                    ['type' => 'cta-band', 'data' => ['heading' => 'Ready to start?', 'subtext' => 'Tell us your trip and we will take it from there.', 'button_label' => 'Start now', 'button_url' => '/apply']],
                    ['type' => 'fine-print', 'data' => ['text' => 'We are a private service and are not affiliated with any government or embassy. The embassy makes the final decision on every application.']],
                ],
            ],
        ];
    }
}
