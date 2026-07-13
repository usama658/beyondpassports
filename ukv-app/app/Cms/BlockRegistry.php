<?php

declare(strict_types=1);

namespace App\Cms;

use App\Cms\Blocks\AccordionBlock;
use App\Cms\Blocks\BlockType;
use App\Cms\Blocks\ButtonsBlock;
use App\Cms\Blocks\CalloutBlock;
use App\Cms\Blocks\ChecklistBlock;
use App\Cms\Blocks\CompareTableBlock;
use App\Cms\Blocks\ContactCardsBlock;
use App\Cms\Blocks\CtaBandBlock;
use App\Cms\Blocks\DividerBlock;
use App\Cms\Blocks\FaqBlock;
use App\Cms\Blocks\FeatureGridBlock;
use App\Cms\Blocks\FinePrintBlock;
use App\Cms\Blocks\GalleryBlock;
use App\Cms\Blocks\GlobalBlockReference;
use App\Cms\Blocks\HeroBlock;
use App\Cms\Blocks\ImageBlock;
use App\Cms\Blocks\LockedIncludeBlock;
use App\Cms\Blocks\LogoStripBlock;
use App\Cms\Blocks\MapEmbedBlock;
use App\Cms\Blocks\PricingBlock;
use App\Cms\Blocks\QuoteBlock;
use App\Cms\Blocks\NoticeBarBlock;
use App\Cms\Blocks\RichTextBlock;
use App\Cms\Blocks\TabsBlock;
use App\Cms\Blocks\TestimonialsBlock;
use App\Cms\Blocks\TimelineBlock;
use App\Cms\Blocks\TrustpilotBlock;
use App\Cms\Blocks\SplitBlock;
use App\Cms\Blocks\StatsBlock;
use App\Cms\Blocks\StepsBlock;
use App\Cms\Blocks\TrustBarBlock;
use App\Cms\Blocks\VideoBlock;
use Filament\Forms\Components\Builder\Block;

/**
 * The CMS extension point. Add a new section by appending its class here (plus a BlockType class
 * and a Blade partial). Never touches existing blocks — each addition is isolated.
 */
class BlockRegistry
{
    /** @var array<int, class-string<BlockType>> */
    private array $types = [
        HeroBlock::class,
        RichTextBlock::class,
        ImageBlock::class,
        CtaBandBlock::class,
        FaqBlock::class,
        TrustBarBlock::class,
        StepsBlock::class,
        FeatureGridBlock::class,
        StatsBlock::class,
        QuoteBlock::class,
        SplitBlock::class,
        AccordionBlock::class,
        CalloutBlock::class,
        TestimonialsBlock::class,
        TimelineBlock::class,
        VideoBlock::class,
        GalleryBlock::class,
        LogoStripBlock::class,
        CompareTableBlock::class,
        ContactCardsBlock::class,
        ButtonsBlock::class,
        NoticeBarBlock::class,
        TabsBlock::class,
        ChecklistBlock::class,
        MapEmbedBlock::class,
        FinePrintBlock::class,
        DividerBlock::class,
        TrustpilotBlock::class,
        PricingBlock::class,
        LockedIncludeBlock::class,
        GlobalBlockReference::class,
    ];

    /**
     * Block keys that a GlobalBlock may wrap. Excludes reference/structural types (global,
     * locked-include) so a reusable block can never reference another reusable block.
     */
    public const GLOBAL_ALLOWED = ['hero', 'rich-text', 'image', 'cta-band', 'faq', 'trust-bar', 'steps', 'feature-grid', 'stats', 'quote', 'split', 'accordion', 'callout', 'testimonials', 'timeline', 'video', 'gallery', 'logo-strip', 'compare-table', 'contact-cards', 'buttons', 'notice-bar', 'tabs', 'checklist', 'map-embed', 'fine-print'];

    /**
     * Category order for the admin block picker. Filament v3 has no native picker groups, so blocks
     * are ordered by category (adjacency = visual grouping) and each label is prefixed with its
     * category. Every block key MUST appear in CATEGORY (guarded by a test).
     */
    public const CATEGORY_ORDER = ['Content', 'Media', 'Trust & proof', 'Calls to action', 'Layout', 'System'];

    /** @var array<string, array{cat: string, icon: string}> block key => picker category + icon */
    public const CATEGORY = [
        // Content
        'hero' => ['cat' => 'Content', 'icon' => 'heroicon-o-sparkles'],
        'rich-text' => ['cat' => 'Content', 'icon' => 'heroicon-o-document-text'],
        'steps' => ['cat' => 'Content', 'icon' => 'heroicon-o-list-bullet'],
        'feature-grid' => ['cat' => 'Content', 'icon' => 'heroicon-o-squares-2x2'],
        'split' => ['cat' => 'Content', 'icon' => 'heroicon-o-view-columns'],
        'accordion' => ['cat' => 'Content', 'icon' => 'heroicon-o-chevron-down'],
        'callout' => ['cat' => 'Content', 'icon' => 'heroicon-o-information-circle'],
        'timeline' => ['cat' => 'Content', 'icon' => 'heroicon-o-clock'],
        'tabs' => ['cat' => 'Content', 'icon' => 'heroicon-o-rectangle-group'],
        'checklist' => ['cat' => 'Content', 'icon' => 'heroicon-o-check-circle'],
        'faq' => ['cat' => 'Content', 'icon' => 'heroicon-o-question-mark-circle'],
        'fine-print' => ['cat' => 'Content', 'icon' => 'heroicon-o-document'],
        // Media
        'image' => ['cat' => 'Media', 'icon' => 'heroicon-o-photo'],
        'gallery' => ['cat' => 'Media', 'icon' => 'heroicon-o-photo'],
        'video' => ['cat' => 'Media', 'icon' => 'heroicon-o-play-circle'],
        'logo-strip' => ['cat' => 'Media', 'icon' => 'heroicon-o-building-office-2'],
        'map-embed' => ['cat' => 'Media', 'icon' => 'heroicon-o-map-pin'],
        // Trust & proof
        'trust-bar' => ['cat' => 'Trust & proof', 'icon' => 'heroicon-o-shield-check'],
        'stats' => ['cat' => 'Trust & proof', 'icon' => 'heroicon-o-chart-bar'],
        'quote' => ['cat' => 'Trust & proof', 'icon' => 'heroicon-o-chat-bubble-bottom-center-text'],
        'testimonials' => ['cat' => 'Trust & proof', 'icon' => 'heroicon-o-user-group'],
        'compare-table' => ['cat' => 'Trust & proof', 'icon' => 'heroicon-o-table-cells'],
        'trustpilot' => ['cat' => 'Trust & proof', 'icon' => 'heroicon-o-star'],
        // Calls to action
        'cta-band' => ['cat' => 'Calls to action', 'icon' => 'heroicon-o-megaphone'],
        'buttons' => ['cat' => 'Calls to action', 'icon' => 'heroicon-o-cursor-arrow-rays'],
        'contact-cards' => ['cat' => 'Calls to action', 'icon' => 'heroicon-o-phone'],
        'notice-bar' => ['cat' => 'Calls to action', 'icon' => 'heroicon-o-bell-alert'],
        'pricing' => ['cat' => 'Calls to action', 'icon' => 'heroicon-o-banknotes'],
        // Layout
        'divider' => ['cat' => 'Layout', 'icon' => 'heroicon-o-minus'],
        // System
        'locked-include' => ['cat' => 'System', 'icon' => 'heroicon-o-lock-closed'],
        'global' => ['cat' => 'System', 'icon' => 'heroicon-o-square-2-stack'],
    ];

    /** @return array<string, class-string<BlockType>> keyed by block key */
    public function all(): array
    {
        $out = [];
        foreach ($this->types as $class) {
            $out[$class::key()] = $class;
        }

        return $out;
    }

    public function view(string $type): ?string
    {
        $class = $this->all()[$type] ?? null;

        return $class ? $class::view() : null;
    }

    /** Filament form components for one block type's fields (used by the GlobalBlock editor). */
    public function schemaFor(string $type): array
    {
        $class = $this->all()[$type] ?? null;

        return $class ? $class::schema() : [];
    }

    /**
     * @return array<int, Block> Filament Builder blocks for the admin form, ordered by category
     * (Content, Media, Trust, CTA, Layout, System) with a category-prefixed label + an icon, so the
     * flat v3 picker reads as grouped clusters.
     */
    public function builderBlocks(): array
    {
        $all = $this->all();
        $order = array_flip(self::CATEGORY_ORDER);

        // Stable sort by category order; blocks keep their in-category declaration order.
        $keys = array_keys($all);
        usort($keys, function (string $a, string $b) use ($order, $keys): int {
            $ca = $order[self::CATEGORY[$a]['cat'] ?? 'System'] ?? 99;
            $cb = $order[self::CATEGORY[$b]['cat'] ?? 'System'] ?? 99;

            return $ca <=> $cb ?: array_search($a, $keys, true) <=> array_search($b, $keys, true);
        });

        return array_map(function (string $key) use ($all): Block {
            $class = $all[$key];
            $meta = self::CATEGORY[$key] ?? ['cat' => 'System', 'icon' => 'heroicon-o-cube'];

            return Block::make($key)
                ->label($meta['cat'].' · '.$class::label())
                ->icon($meta['icon'])
                ->schema($class::schema());
        }, $keys);
    }
}
