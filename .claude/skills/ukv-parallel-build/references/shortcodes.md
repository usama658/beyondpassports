# UKV shortcodes — live data to embed in kit sections / pages

These render live data and are the bridge for the hybrid kit-native approach (drop one into a `shortcode`
Elementor widget inside a kit-styled section).

| Shortcode | Output | Source |
|---|---|---|
| `[ukv_dest_grid]` | Grid of destination money-page cards (name, fee, CTA) | ukv-forminator-glue |
| `[ukv_dest_fee dest="egypt"]` | A destination's government fee number | ukv-forminator-glue |
| `[ukv_dest_field dest="egypt" field="visa_type"]` | Any allowlisted Pods destination field | ukv-forminator-glue |
| `[ukv_visa_table]` | Visa requirements table for the current/passed destination | ukv-forminator-glue |
| `[ukv_idp_table]` | IDP permit-type table | ukv-forminator-glue |
| `[ukv_whatsapp]` | Primary WhatsApp lead CTA (page-aware pre-filled message) | ukv-forminator-glue |
| `[ukv_trust_bar]` | Trust strip (independent service · secure Stripe · UK support) | ukv-conversion |
| `[ukv_testimonials]` | Published testimonials (category `testimonial`) as cards | ukv-conversion |
| `[ukv_tracker]` | Public order status lookup (ref+email) → safe status view | ukv-tracker |

## Notes
- `[ukv_trust_bar]` also auto-prepends on `destination` singles via a `the_content` filter.
- Floating WhatsApp + Call buttons + the top call bar are injected site-wide (read `ukv_whatsapp_number` /
  `ukv_phone_number`) — not shortcodes.
- The Apply funnel + visa/IDP checkers are **Forminator** forms embedded via `[forminator_form id="…"]`
  (Apply #300 on `/apply/`, checker on `/do-i-need-a-visa/`).
- Adding a new dynamic surface? Prefer a shortcode (so it can be embedded in a kit section) over hardcoded
  template output.
