# CMS coverage audit

**Purpose:** prove that every *content* section on the public site is accounted for by the CMS — either an editable block, a locked (placed-but-not-edited) section, or a deliberate stay-coded exception — so nothing is silently missed.

**Method (repeatable, not eyeballed):**
1. Enumerate every `<section>` across `resources/views/{public,partials,destinations}` and rank by frequency:
   `grep -rhoE '<section[^>]*class="[a-z0-9 _-]+"' resources/views/... | ... | sort | uniq -c | sort -rn`
2. Classify each distinct section into one disposition (table below).
3. Guard it automatically:
   - **`SectionCoverageTest`** — re-derives every public `<section>` class from the Blade views and fails if any is not classified in `App\Cms\SectionManifest::KNOWN`. This is the gate that flags a "section with no block" the moment it ships: a new section type turns the build red until someone adds a block or classifies it (block|locked|functional|layout). It also fails on stale manifest entries.
   - `BlockRegistryGuardTest` — every registered block has a real Blade partial + schema, and a page using every content block renders (no 500).
   - Golden-master (`automation/cms-golden.cjs`) — a page served from CMS blocks is byte-identical to its coded twin.
   - `PublicSmokeTest` — every public URL (incl. orphaned LPs) renders < 500.

Step 1 is now automated by `SectionCoverageTest` — you no longer have to remember to re-run the grep; CI does it every commit.

## Disposition rules

- **Editable block** — a content section the team should be able to word/reorder. Renders the EXISTING themed markup so output is pixel-identical.
- **Locked include** — a themed section that is config/controller-driven or structurally fixed; placeable on a CMS page but not edited inline (`LockedIncludeBlock` whitelist).
- **Stay coded (functional)** — forms, checker, checkout, slot picker, tracker. NEVER blockified — editing them would break functionality. This is by design.
- **Chrome** — header, footer, nav, disclaimer strip. Handled by the nav builder + Site settings, not page blocks.

## Section coverage (by frequency)

| Section (class) | Seen on | Disposition | Status |
| --- | --- | --- | --- |
| `cta-band` | 18× (most pages) | Editable block | ✅ `cta-band` block |
| `hero` / `*-hero` | many (per-page variants) | Editable block (generic) + locked-include (bespoke heroes) | ✅ `hero` block; page-specific heroes → locked-include when a page is migrated |
| `faq-e` | 5× | Editable block | ✅ `faq` block |
| `tbar-f` (trust bar) | 7× | Editable block | ✅ `trust-bar` block |
| rich text / prose | many | Editable block | ✅ `rich-text` block |
| images / figures | many | Editable block | ✅ `image` block (+ media library) |
| `pad` / `alt` / `sec` / `pad-sm` | layout wrappers | n/a (containers, not content) | — |
| `services-body`, `about-body` | 1× each | Locked include | ✅ whitelisted |
| destination grid / appointment board / slot picker | schengen-visa, destinations | Stay coded (controller-data + interactive) | ✅ by design |
| checker / apply form / checklist wizard / checkout / tracker | tools, apply, document-checklist, checkout, track | Stay coded (functional) | ✅ by design |
| `revcred`, `mprev`, `pathband`, per-page one-offs | 1× each | Locked include when that page is migrated | ✅ extract on demand |
| header / topbar / footer / nav | every page (chrome) | Nav builder + Site settings | ✅ shipped |

## Block library (editable content blocks)

Content blocks: `hero`, `rich-text`, `image`, `cta-band`, `faq`, `trust-bar`, `steps`, `feature-grid`, `stats`, `quote`, `split`, `accordion`, `callout`, `testimonials`, `timeline`, `video`, `gallery`, `logo-strip`, `compare-table`, `contact-cards`, `buttons`, `notice-bar`, `tabs`, `checklist`. Widget/toggle blocks (locked, config-driven, no editable content): `trustpilot`, `pricing`. Structural: `locked-include` (place a whitelisted coded section), `global` (reference a reusable block). Any of the content blocks can also be saved as a **reusable global block** (`GLOBAL_ALLOWED`); the widget blocks and structural blocks are excluded from reuse.

Every content block from `steps` onward is self-contained: each renders its own scoped CSS (`.cms-*`) built from brand tokens, so it drops onto any page without depending on that page's stylesheet. `video` accepts only YouTube/Vimeo URLs and emits a privacy-friendly `youtube-nocookie`/Vimeo iframe; any other host resolves to nothing, so an editor can never inject an arbitrary iframe (YouTube/Vimeo are whitelisted in the public `frame-src` CSP). `gallery` + `logo-strip` resolve each tile from the media library first, then a one-off upload, mirroring the Image block's rules. `tabs` is no-JS (pure-CSS radio inputs) with a per-instance `uniqid` group name so multiple tab sets on one page never collide.

## Deliberate non-goals

"Every section editable" is NOT the target. Forms, the eligibility checker, the slot picker, checkout and the tracker are interactive/functional and MUST stay coded (placed as locked sections if needed). Colours/CSS/structure are never editable — the CMS supplies text + images into the existing theme only. This is the theme-and-functionality-safety guarantee the CMS was built around.
