<?php

declare(strict_types=1);

namespace App\Cms;

/**
 * The section-coverage manifest: every public <section> class the site currently ships, each mapped
 * to a CMS disposition. SectionCoverageTest re-derives the live set from the Blade views and fails if
 * any section class is NOT listed here — forcing a human decision (add a block, or classify it) the
 * moment a new section type ships. This is how "a section with no CMS block" is flagged now and in
 * future, automatically, rather than by eye.
 *
 * Dispositions:
 *   block      — an editable CMS block exists (or the generic hero block covers it)
 *   locked     — a themed, coded section; place-able as a locked-include when a page is migrated
 *   functional — interactive/form/checkout; stays coded by design, never blockified
 *   layout     — a generic wrapper (padding/alt band), not a content section
 */
class SectionManifest
{
    /** @var array<string,string> section class => disposition */
    public const KNOWN = [
        // Editable content blocks
        'cta-band' => 'block',
        'faq-e' => 'block',
        'tbar-f' => 'block',
        'hero' => 'block',

        // Generic layout wrappers (not content)
        'pad' => 'layout',
        'pad-sm' => 'layout',
        'alt' => 'layout',
        'sec' => 'layout',

        // Interactive / lead-capture strips (stay coded)
        'lpes' => 'functional',
        'ct-thanks' => 'functional',

        // Themed, coded, page-specific sections (locked-include when a page is migrated). Includes the
        // per-page hero variants and one-off content bands.
        'ab-hero' => 'locked', 'ap-hero' => 'locked', 'cmp-hero' => 'locked', 'crp-hero' => 'locked',
        'ct-hero' => 'locked', 'dct-hero' => 'locked', 'di-hero' => 'locked', 'dmoney-hero' => 'locked',
        'fc-hero' => 'locked', 'gi-hero' => 'locked', 'hp-hero' => 'locked', 'idp-hero' => 'locked',
        'lg-hero' => 'locked', 'mesh-hero' => 'locked', 'rv-hero' => 'locked', 'sg-hero' => 'locked',
        'sgc-hero' => 'locked', 'sv-hero' => 'locked', 'tr-hero' => 'locked',
        'abc6' => 'locked', 'abproc' => 'locked', 'abrev' => 'locked', 'abt' => 'locked',
        'bpc-bd' => 'locked', 'bpc-pr' => 'locked', 'cr-section' => 'locked', 'cvb' => 'locked',
        'dsv' => 'locked', 'fc-section' => 'locked', 'fc-section-sm' => 'locked',
        'gi-guides-wrap' => 'locked', 'gi-hubs-wrap' => 'locked', 'hpv-b' => 'locked',
        'idp-guide' => 'locked', 'mprev' => 'locked', 'pathband' => 'locked', 'revcred' => 'locked',
        'tbar-b' => 'locked', 'tr-sec' => 'locked',
    ];

    /**
     * Scan the public Blade views and return every distinct <section> class (first token).
     *
     * @return list<string>
     */
    public static function liveSectionClasses(): array
    {
        $dirs = ['public', 'partials', 'destinations'];
        $base = resource_path('views');
        $found = [];

        foreach ($dirs as $dir) {
            $path = $base.'/'.$dir;
            if (! is_dir($path)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                if (! str_ends_with((string) $file, '.blade.php')) {
                    continue;
                }
                $html = (string) file_get_contents((string) $file);
                if (preg_match_all('/<section[^>]*class="([a-z0-9 _-]+)"/i', $html, $m)) {
                    foreach ($m[1] as $classAttr) {
                        $first = strtok(trim($classAttr), ' ');
                        if ($first !== false && $first !== '') {
                            $found[$first] = true;
                        }
                    }
                }
            }
        }

        return array_keys($found);
    }
}
