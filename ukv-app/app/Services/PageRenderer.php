<?php

declare(strict_types=1);

namespace App\Services;

use App\Cms\BlockRegistry;
use App\Models\Page;
use Illuminate\Support\Facades\View;

/**
 * Pure block-loop: a Page's block stack rendered to HTML through the registry's partials.
 * No HTTP, so it is trivially testable. Unknown block types are skipped, never fatal.
 */
class PageRenderer
{
    public function __construct(private readonly BlockRegistry $registry) {}

    public function render(Page $page): string
    {
        $html = '';
        foreach ($page->blocks ?? [] as $block) {
            $view = $this->registry->view($block['type'] ?? '');
            if ($view === null) {
                continue;
            }
            $html .= View::make($view, ['data' => $block['data'] ?? []])->render();
        }

        return $html;
    }
}
