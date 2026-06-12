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

## Kit-native section recipe (the hybrid approach)
A section template = an `elementor_library` post with meta `_elementor_template_type='section'` and
`_elementor_data` = a JSON array of `section → column → widget` elements. To stay native + dynamic:
- Use `jkit_heading` / `jkit_button` / `jkit_icon_box` for the kit chrome, referencing global colours/typography.
- Embed LIVE data with the core `shortcode` widget:
  `{ "id":"<8hex>", "elType":"widget", "widgetType":"shortcode", "settings":{ "shortcode":"[ukv_testimonials]" } }`.
- Section/column wrappers carry padding + responsive controls (`_element_width_tablet`, `padding_mobile`, etc.).
- Give every element a unique 7–8 char id (`substr(md5(uniqid()),0,8)` in the build script).
- Upsert the template by title (idempotent) so re-runs don't duplicate.

## Reference templates to copy structures from
Home #202, Visa #229, Visa Detail #223, Pricing #187, FAQ #193 (accordion), Contact #211, Header #166, Footer #169.

## Build mechanics
Author sections in `automation/build-kit-sections.php` (run via wp-cli eval-file). Store `_elementor_data` as a
PHP array → `wp_slash( wp_json_encode( $data ) )`. After building, the section appears in Elementor's library and
can be inserted onto any page; the embedded shortcode renders live data with the kit's styling around it.

## Responsiveness
The kit + its widgets are responsive by default. Set padding/columns per breakpoint (desktop/tablet/mobile) on
the section + column. This is the main reason to prefer kit-native sections over bespoke inline-CSS shortcode output.
