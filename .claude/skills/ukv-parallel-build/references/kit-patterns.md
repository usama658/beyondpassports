# Travisa kit — patterns for kit-native sections

**Active kit:** post #156. **Elementor:** 4.1.3. **Jeg Kit (`jkit_*`) is the kit's widget family.**

## Global styles (reference these so a section is on-brand + native)
System colours (UKV brand, applied to the kit in P1):
- `primary` #0A2540 (navy) · `secondary` #1456B8 (blue) · `text` #1B1B1B · `accent` #C8A24A (gold).
Typography: **Inter** (primary 400, secondary 300).

In `_elementor_data`, reference a global instead of a literal so it tracks the kit:
`"settings": { "title_color": "", "__globals__": { "title_color": "globals/colors?id=accent" },
"typography_typography": "globals/typography?id=primary" }`.

## Widgets the kit actually uses (reuse, don't invent)
`heading`, `jkit_heading`, `jkit_icon_box`, `text-editor`, `jkit_button`, `image`, `jkit_image_box`, `spacer`,
`jkit_fun_fact` (stat counters), `divider`, `jkit_post_block`, `icon-list`, `icon-box`,
`jkit_testimonials`, `jkit_accordion` (FAQ).

## Kit-native section workflow (CLONE-AND-MODIFY — the operator's actual method)
Do NOT hand-author `_elementor_data` JSON from scratch — it doesn't match how the operator works and is hard to
verify. Instead: **duplicate the closest existing kit block, then modify it in the Elementor frontend.** Header
and footer are left as-is; work happens on sections only.

Claude's job is NOT to generate the section, but to **map each needed section to the closest kit block** and tell
the operator what to change + which `[ukv_*]` shortcode to drop in (via Elementor's core **Shortcode** widget).
The authoritative map lives at `docs/superpowers/kit-section-map.md` — read it. It pairs each UKV section with a
source (template # → section index), the edits to make, and the live-data shortcode.

To clone in Elementor: open the source template → right-click the section → Copy → Paste onto the target page (or
Save as Template) → edit inline. Global colours/typography carry over automatically, and the block is already
responsive (just check the device switcher after editing).

Only generate `_elementor_data` programmatically if the operator explicitly asks for it; the shortcodes
(`references/shortcodes.md`) are the live-data bridge either way.

## Reference templates to copy structures from
Home #202, Visa #229, Visa Detail #223, Pricing #187, FAQ #193 (accordion), Contact #211, Header #166, Footer #169.

## Build mechanics
Author sections in `automation/build-kit-sections.php` (run via wp-cli eval-file). Store `_elementor_data` as a
PHP array → `wp_slash( wp_json_encode( $data ) )`. After building, the section appears in Elementor's library and
can be inserted onto any page; the embedded shortcode renders live data with the kit's styling around it.

## Responsiveness
The kit + its widgets are responsive by default. Set padding/columns per breakpoint (desktop/tablet/mobile) on
the section + column. This is the main reason to prefer kit-native sections over bespoke inline-CSS shortcode output.
