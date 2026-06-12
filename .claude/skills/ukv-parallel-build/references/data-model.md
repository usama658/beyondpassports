# UKV data model

## Custom post types
- **`destination`** (Pods) — money pages. Slug-based (e.g. `egypt`). Read fields via `ukv_dest_value($slug, $field)`
  (allowlisted in `ukv-forminator-glue.php`). Fields incl: `govt_fee_gbp`, `tier_standard_gbp`,
  `tier_express_gbp`, `tier_premium_gbp`, `visa_type`, `max_stay_days`, `required_for_uk`, `idp_*`,
  `passport_validity_months`.
- **`ukv_order`** — one per paid order (admin only). Created by `ukv_create_order(array $d): int`.
- **`ukv_barrier`** — case or destination-wide barriers (the Smart Stories spine). Created by
  `ukv_barrier_create(array $d): int` (idempotent on `rule_key`).
- **`elementor_library`** — Elementor templates/sections (meta `_elementor_template_type`, `_elementor_data`).

## Order post-meta — ALWAYS `ukv_`-prefixed
`ukv_order_ref`, `ukv_name`, `ukv_email`, `ukv_destination` (DISPLAY name e.g. "Egypt", NOT a slug),
`ukv_tier` ("Standard"/"Express"/"Premium"), `ukv_status` (see vocab), `ukv_total`, `ukv_blocker`
(none/docs_missing/payment_pending/eligibility/customer_deciding), `ukv_travel_date` (Y-m-d),
`ukv_journey` (array of `['date'=>'Y-m-d H:i','agent'=>str,'channel'=>str,'text'=>str]`), `ukv_risk_flag`
('1'/''), `ukv_passport_number`, `ukv_hubspot_deal` (int), `ukv_created` (epoch), `ukv_documents` (array),
`ukv_status_last`, `ukv_email_sent` (array of event keys), `ukv_email_log` (array), `ukv_story_consent` ('1'/'').

## Barrier post-meta — BARE keys (no prefix)
`nature` (temporary|permanent), `scope` (case|destination|all), `destination` (SLUG), `order_ref`,
`guidance` (client-facing free-text), `status` (open|resolved), `detected_by` (agent|auto|destination),
`rule_key` (idempotency key for auto-detect).

> **The gotcha:** reading order meta without `ukv_`, or passing the display-name destination where a slug is
> expected, returns empty and breaks logic silently. Normalise with `ukv_dest_slug()`. See memory
> `ukv-meta-key-conventions`.

## Status vocabulary (agree everywhere)
`paid, awaiting_docs, doc_review, submitted, awaiting_decision, delivered, won, rejected, refunded`.
Closed/terminal: `delivered, won, rejected, refunded` (const `UKV_ORDER_CLOSED`). Success = `won`+`delivered`;
fail = `rejected`+`refunded`.

## Options (get_option/update_option)
`ukv_phone_number`, `ukv_whatsapp_number`, `ukv_contact_hours`, `ukv_hubspot_token` (LIVE — handle carefully),
`ukv_anthropic_key`, `ukv_callback_form_id`, `ukv_email_transport` (wp_mail|hubspot), `ukv_zapier_hook_url`,
`elementor_active_kit` (=156).

## Key helper functions (loaded; reuse, don't reimplement)
`ukv_create_order`, `ukv_order_sla_hours($tier)`, `ukv_dest_slug($v)`, `ukv_dest_value($slug,$field)`,
`ukv_barrier_create`, `ukv_open_barriers`, `ukv_barriers_for_order($oid)`, `ukv_affected_orders($bid)`,
`ukv_open_orders($slug='')`, `ukv_dest_rejection_rate($slug)`, `ukv_success_stats`, `ukv_case_risk($oid)`,
`ukv_redact_pii($t,$known=[])`, `ukv_redact_competitor($t)`, `ukv_story_has_leak($t,$known=[])`,
`ukv_ai($system,$user,$opts)` (key-gated, returns null without key; test hook filter `ukv_ai_pre_response`),
`ukv_email_send/_fire/_template`, `ukv_hs_post($path,$body)`.
