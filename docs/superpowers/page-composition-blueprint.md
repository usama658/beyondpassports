# Page composition blueprint — section stacks per page type

Build pages one at a time in Elementor by cloning the mapped kit blocks (see `kit-section-map.md`) in this order.
Header/footer stay as-is. Each row = a section; clone its source, edit, add the shortcode where listed.

## Priority order
1. Homepage (front door) → 2. Money/destination page (converter) → 3. Apply → 4. Track → 5. Tools/checker →
6. Guides/comparison/hub → 7. Contact/About (kit templates already close).

## 1. Homepage (`/`)
1. Hero — Home #202 [0] — headline "UK visas & eVisas, sorted" + button → /apply/
2. Trust / stats — FAQ #193 [3] — `[ukv_trust_bar]` or 4 fun-facts
3. Choose your visa — Home #202 [4] — icon-box categories
4. Destinations grid — Home #202 [5] — `[ukv_dest_grid]`
5. How we help (process) — Visa #229 [4]
6. Testimonials — Home #202 [6] — `[ukv_testimonials]`
7. Pricing teaser — Pricing #187 [1] (condensed) — link → /apply/
8. FAQ — FAQ #193 [1]
9. Contact CTA — Home #202 [3] — `[ukv_whatsapp]`

## 2. Money/destination page (`/<dest>/`)
Rendered by the Pods `destination` template + glue (mostly dynamic already). Section stack to polish:
1. Hero (destination name + visa type + fee) — dynamic via template
2. Fees / tiers — `[ukv_visa_table]` styled like Pricing #187 cards
3. Requirements — `[ukv_dest_field field=...]` / list
4. How to apply (steps) — Visa #229 [4]
5. FAQ — auto per-destination (already renders) — accordion style
6. Apply/Contact CTA — Home #202 [3] — button → /apply/?dest= + `[ukv_whatsapp]`

## 3. Apply (`/apply/`)
1. Hero (short, reassurance) — Home #202 [0] condensed
2. The funnel — `[forminator_form id="300"]`
3. Trust strip — `[ukv_trust_bar]`
4. Contact CTA — Home #202 [3]

## 4. Track (`/track/`)
1. Hero "Track your application" — Home #202 [0] condensed
2. Tracker — `[ukv_tracker]`
3. Contact CTA — Home #202 [3]

## 5. Tools / checker (`/do-i-need-a-visa/`)
1. Hero — condensed
2. Checker form — Forminator checker
3. Destinations grid — `[ukv_dest_grid]`
4. CTA — Home #202 [3]

## 6. Guides / comparison / hub
Mostly content. Light section design: content body → related-links block (icon-list) → Contact CTA (Home [3]).
Comparison pages: keep the HTML table but wrap responsive; add a CTA band.

## 7. Contact / About
Kit Contact #211 / About #208 templates already close — set real text + numbers (`[ukv_whatsapp]`, contact info).

## Per-page checklist (apply to each)
- [ ] Clone mapped sections, edit text/images, drop shortcodes.
- [ ] Set the page's H1 + RankMath title/description.
- [ ] Check tablet + mobile in Elementor device switcher (grids/cards stack).
- [ ] Internal links to money pages / related guides.
