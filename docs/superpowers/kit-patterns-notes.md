# Travisa Kit — patterns for building kit-native sections (Kit-1 output)

**Active kit:** post #156. **Elementor:** 4.1.3. **Jeg Kit (jkit_*) is the kit's widget family.**

## Global styles (inherit these — sections become native by referencing them)
System colours: `primary` #0A2540 (navy), `secondary` #1456B8 (blue), `text` #1B1B1B, `accent` #C8A24A (gold). System typography: Inter (primary 400, secondary 300). These are the UKV brand applied to the kit in P1 — so referencing global colours/typography in section JSON = on-brand + native.

## Widgets the kit actually uses (reuse, don't invent)
`heading`, `jkit_heading`, `jkit_icon_box`, `text-editor`, `jkit_button`, `image`, `jkit_image_box`, `spacer`, `jkit_fun_fact` (stats counters), `divider`, `jkit_post_block`, `icon-list`, `icon-box`, `jkit_testimonials`, `jkit_accordion` (FAQ).

## Hybrid build recipe (chosen approach)
- Section template = `elementor_library` post, meta `_elementor_template_type='section'`, `_elementor_data` = JSON array of section→column→widget elements.
- Use `jkit_heading`/`jkit_button`/`jkit_icon_box` for the kit-native chrome; reference global colours via `"__globals__"` color settings (e.g. `"title_color": ""` + `"__globals__":{"title_color":"globals/colors?id=primary"}`).
- Embed live data with the core `shortcode` widget: `{"widgetType":"shortcode","settings":{"shortcode":"[ukv_testimonials]"}}`.
- Templates resolve via Elementor; upsert by title (idempotent) so re-runs don't duplicate.

## 19 kit templates (reference structures): Home #202, Visa #229, Visa Detail #223, Pricing #187, FAQ #193, Contact #211, About #208, Team #199, Blog #220, Header #166, Footer #169.
