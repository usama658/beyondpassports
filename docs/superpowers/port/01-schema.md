# Laravel rebuild — relational schema spec (`port/01-schema.md`)

**Date:** 2026-06-15
**Source of truth:** the WordPress mu-plugins at `C:\xampp\htdocs\ukvisa\wp-content\mu-plugins\` (extracted file-by-file below) plus the Pods `destination` CPT fields exposed in `ukv-forminator-glue.php`.
**Companion design:** `docs/superpowers/specs/2026-06-15-laravel-rebuild-design.md`.

This is the migration target for MySQL + Laravel (Eloquent). It converts the WordPress model — orders are `ukv_order` posts with **`ukv_`-prefixed** post-meta; destinations are a Pods `destination` CPT; barriers are a `ukv_barrier` CPT with **bare** meta keys; several engines use WP `options` and a serialized `ukv_journey` array — into proper typed columns and relations.

## Conventions for all tables
- Engine **InnoDB**, charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- Every table has `id` (`bigIncrements`, PK) and `timestamps()` (`created_at`, `updated_at`) unless noted. WP stored creation as the `ukv_created` UNIX epoch / post `post_date`; map to `created_at`.
- Money: WP stored fees as free-form text meta (`£` rendered at display). In the port use `decimal(10,2)` GBP, nullable where the WP value could be absent. (Ambiguity flagged per table.)
- WP UNIX-epoch `*_at` meta (e.g. `ukv_govt_fee_paid_at`, `ukv_closed_at`, `ukv_refunded_at`) becomes a real `timestamp` (nullable).
- Destination is stored on the order as a **display name** (`ukv_destination` = "Egypt") but matched everywhere via `ukv_dest_slug()` (slug). The port replaces this dual representation with a real FK `destination_id` + keeps a denormalized `destination_name` snapshot for history. **This is the single biggest correctness fix** — the memory note records two real bugs caused by display-name-vs-slug mixing.
- WP "free-form text that is really an enum" is converted to a typed `enum`/`string` with a CHECK-style allow-list, noted per column.

## Table count: 13
`destinations`, `orders`, `order_events`, `barriers`, `client_updates`, `documents`, `appointments`, `supply_nodes`, `discounts`, `rejections`, `feedback`, `quotes`, `users`.
(Plus a join table `barrier_order` for the `client_updates` "already-sent" set; documented under `client_updates`.)

---

## 1. `destinations`
From the Pods `destination` CPT. Fields are the allow-list in `ukv-forminator-glue.php::ukv_dest_value()` plus `ukv-passport-validity.php` and `ukv-required-docs.php`. WP key = the Pods field name (resolved by post slug).

| Column | Migration type | Null | Default | Maps from (Pods field / source) | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new) | PK |
| `name` | `string(120)` | no | — | post title ("Egypt") | display name |
| `slug` | `string(140)` | no | — | post slug (`sanitize_title`) | **unique** — the join key orders/barriers/supply match on |
| `visa_type` | `string(60)` | yes | null | `visa_type` | e.g. "eVisa", "ETA", "Tourist visa" — free text today; consider enum (evisa/eta/visa-free/sticker) but keep string until taxonomy confirmed |
| `required_for_uk` | `boolean` | no | `false` | `required_for_uk` | "does a UK citizen need a visa" |
| `max_stay_days` | `unsignedSmallInteger` | yes | null | `max_stay_days` | |
| `govt_fee_gbp` | `decimal(10,2)` | yes | null | `govt_fee_gbp` | official fee; added to tier price at checkout (`ukv-hubspot.php`) |
| `tier_standard_gbp` | `decimal(10,2)` | yes | null | `tier_standard_gbp` | per-destination tier pricing (overrides the global 29/49/79 map) |
| `tier_express_gbp` | `decimal(10,2)` | yes | null | `tier_express_gbp` | |
| `tier_premium_gbp` | `decimal(10,2)` | yes | null | `tier_premium_gbp` | |
| `passport_validity_months` | `unsignedTinyInteger` | no | `6` | `passport_validity_months` | seeded to 6 (`ukv-passport-validity.php`); drives Barrier Rule 2 + AI doc review |
| `idp_permit_type` | `string(40)` | yes | null | `idp_permit_type` | IDP convention (1926/1949/1968) |
| `idp_required_photocard` | `boolean` | no | `false` | `idp_required_photocard` | |
| `idp_required_paper` | `boolean` | no | `false` | `idp_required_paper` | |
| `required_docs` | `json` | yes | null | `required_docs` (paragraph: comma/newline list) | parsed by `ukv_required_docs_parse()`; store as JSON array of labels. Default `["Passport bio page","Passport photo"]` when empty (`ukv_required_docs_default()`) |

**Indexes:** unique `slug`; index `required_for_uk`.
**Relationships:** has-many `orders`, `barriers` (by slug today → FK in port), `appointments`, `quotes`; many-to-many `supply_nodes` (a node lists `destinations` or is global).
**Ambiguity:** `visa_type` is free text in WP; the design doc proposes an enum (evisa/eta/visa-free). Keep `string` + a separate nullable `visa_type_key` enum if a clean taxonomy is agreed. Tier prices may be null per-destination (global fallback map lives in code) — null is meaningful.

---

## 2. `orders`
The core. WP = `ukv_order` post + many `ukv_`-prefixed meta keys created in `ukv-orders.php::ukv_create_order()` and enriched by ~15 other plugins. Title was `"{ref} — {dest} ({name})"` (derived, not stored separately).

### Identity / customer
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | post ID | PK |
| `order_ref` | `string(32)` | no | — | `ukv_order_ref` | format `UKV-YYYY-NNNNNN` (`ukv-orders.php` / `ukv-hubspot.php`). **unique** |
| `name` | `string(160)` | yes | null | `ukv_name` | customer full name |
| `email` | `string(190)` | yes | null | `ukv_email` | tracker/upload auth match (case-insensitive); loyalty "returning customer" lookup |
| `passport_number` | `string(40)` | yes | null | `ukv_passport_number` | PII — never sent to AI (`ukv-doc-review.php`); candidate for encryption-at-rest |
| `hubspot_deal_id` | `string(40)` | yes | null | `ukv_hubspot_deal` | external HubSpot deal id |

### Destination + pricing
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `destination_id` | `foreignId` | yes | null | `ukv_destination` (display name → slug → FK) | nullable for legacy/unknown |
| `destination_name` | `string(120)` | yes | null | `ukv_destination` | denormalized snapshot (history) |
| `tier` | `enum('standard','express','premium')` | yes | null | `ukv_tier` | WP stored the **label** ("Standard"); normalize to lowercase key. SLA derived from substring match (`ukv_order_sla_hours`) |
| `service_fee` | `decimal(10,2)` | yes | null | `ukv_service_fee` | our fee (tier price). Refundable amount = this (`ukv-refunds.php`) |
| `govt_fee` | `decimal(10,2)` | yes | null | `ukv_govt_fee` | official fee snapshot; **non-refundable** |
| `total` | `decimal(10,2)` | yes | null | `ukv_total` | service_fee + govt_fee at checkout |

### Pipeline / journey-critical
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `status` | `enum(...)` | no | `'paid'` | `ukv_status` | see **Enum: order status** below |
| `status_last` | `string(30)` | yes | null | `ukv_status_last` | last persisted stage; gate revert target + email transition detector (`ukv-emails.php` p12, `ukv-stage-gates.php`, `ukv-qa-gate.php`). In the port this can become an `order_events` lookup, but keep the column for the gate logic |
| `blocker` | `enum('none','docs_missing','payment_pending','eligibility','customer_deciding')` | no | `'none'` | `ukv_blocker` | `UKV_BLOCKERS` |
| `priority` | `enum('normal','high','urgent')` | no | `'normal'` | `ukv_priority` | |
| `next_action` | `string(255)` | yes | null | `ukv_next_action` | |
| `next_due` | `date` | yes | null | `ukv_next_due` | |
| `travel_date` | `date` | yes | null | `ukv_travel_date` | drives Barrier Rule 2/3, AI doc review |
| `risk_flag` | `boolean` | no | `false` | `ukv_risk_flag` | rejection-likely |
| `value_note` | `string(255)` | yes | null | `ukv_value_note` | upsell note |

### Eligibility lane + intake axes (`ukv-eligibility.php`, `ukv-eligibility-intake.php`)
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `eligibility` | `enum('standard','manual_review','cleared','referred')` | yes | null | `ukv_eligibility` | lane; computed (standard/manual_review) or agent-set (cleared/referred). Gate at `ukv-eligibility.php` |
| `eligibility_note` | `text` | yes | null | `ukv_eligibility_note` | agent decision note |
| `nationality` | `string(80)` | yes | null | `ukv_nationality` | free text (country name) |
| `residence_country` | `string(80)` | yes | null | `ukv_residence_country` | free text |
| `residency_status` | `enum('citizen','permanent','visa_holder','other')` | yes | null | `ukv_residency_status` | `UKV_RESIDENCY_STATUS` |
| `residency_visa_expiry` | `date` | yes | null | `ukv_residency_visa_expiry` | captured but only stored today |
| `trip_purpose` | `enum('tourist','business','transit','study','other')` | yes | `'tourist'` | `ukv_trip_purpose` | `UKV_TRIP_PURPOSE` |
| `visa_entries` | `string(20)` | yes | null | `ukv_visa_entries` | free text today (single/multiple) — **candidate enum** `single`/`double`/`multiple` |
| `applicant_name` | `string(160)` | yes | null | `ukv_applicant_name` | when payer ≠ applicant |
| `guardian_name` | `string(160)` | yes | null | `ukv_guardian_name` | for minors |
| `dual_nationality` | `string(80)` | yes | null | `ukv_dual_nationality` | free text |
| `is_minor` | `boolean` | no | `false` | `ukv_is_minor` | forces manual_review |
| `prior_refusal` | `boolean` | no | `false` | `ukv_prior_refusal` | forces manual_review |
| `insurance_required` | `boolean` | no | `false` | `ukv_insurance_required` | captured flag |

### Government submission (`ukv-govt-fields.php`)
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `govt_ref` | `string(80)` | yes | null | `ukv_govt_ref` | GWF/IHS/submission no.; required to enter `awaiting_decision`/`delivered` (`ukv-stage-gates.php`) |
| `govt_fee_paid` | `boolean` | no | `false` | `ukv_govt_fee_paid` | |
| `govt_fee_paid_at` | `timestamp` | yes | null | `ukv_govt_fee_paid_at` (epoch) | stamped once on first paid |

### Passport (`ukv-passport-validity.php`)
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `passport_expiry` | `date` | yes | null | `ukv_passport_expiry` | Y-m-d; Barrier Rule 2 |

### Documents / QA
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `required_docs_count` | `unsignedTinyInteger` | yes | null | `ukv_required_docs` | mirror of destination count (`ukv-required-docs.php`); QA gate floor |
| `qa_signed_off` | `boolean` | no | `false` | `ukv_qa_signed_off` | human sign-off gate (`ukv-qa-gate.php`) |
| `doc_review` | `json` | yes | null | `ukv_doc_review` | AI advisory verdict `{pass:bool|null, flags:[{check,severity,note}], reviewed_at}` (`ukv-doc-review.php`) — read-only advisory |
| `docs_purged` | `boolean` | no | `false` | `ukv_docs_purged` | retention purge done (`ukv-retention.php`) |

### Ownership / SLA (`ukv-ownership.php`)
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `owner_id` | `foreignId` | yes | null | `ukv_owner` (WP user id; 0 = unassigned) | FK → `users.id`; 0 becomes null |
| `sla_escalated` | `boolean` | no | `false` | `ukv_sla_escalated` | fire-once escalation guard |

### Add-ons / logistics
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `group_id` | `string(16)` | yes | null | `ukv_group_id` | `GRP-XXXXXXXX` deterministic trip-group link (`ukv-order-groups.php`). Index — self-grouping, not a FK |
| `premium_slot` | `boolean` | no | `false` | `ukv_premium_slot` | paid fast-track add-on (`ukv-premium-slot.php`) |
| `premium_slot_fee` | `decimal(10,2)` | yes | null | `ukv_premium_slot_fee` | paid to centre; NOT in `total` |
| `premium_slot_added_at` | `timestamp` | yes | null | `ukv_premium_slot_added_at` (epoch) | |
| `story_consent` | `boolean` | no | `false` | `ukv_story_consent` | consent to anonymized testimonial (`ukv-story-consent.php`, `ukv-apply-intake.php`) |

### Refund (`ukv-refunds.php`)
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `refund_amount` | `decimal(10,2)` | yes | null | `ukv_refund_amount` | service fee only |
| `refund_reason` | `string(255)` | yes | null | `ukv_refund_reason` | |
| `refunded_at` | `timestamp` | yes | null | `ukv_refunded_at` (epoch) | |

### Lifecycle / retention
| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `closed_at` | `timestamp` | yes | null | `ukv_closed_at` (epoch) | stamped when status enters a closed set; starts retention clock (`ukv-retention.php`) |
| `created_at` | `timestamp` | no | now | `ukv_created` (epoch) / post_date | |

**Email idempotency:** WP stored `ukv_email_sent` (array of events) + `ukv_email_log` (array). Port these as `order_events` rows of type `email` (see table 3); the per-(order,event) once-only check becomes a unique index there. Do **not** carry the serialized arrays.

### Enum: order `status` (`UKV_ORDER_STATUSES`)
`paid`, `awaiting_docs`, `doc_review`, `submitted`, `awaiting_decision`, `delivered`, `won`, `rejected`, `refunded`.
**Closed set** (`UKV_ORDER_CLOSED`, used by barriers/retention/SLA): `delivered`, `won`, `rejected`, `refunded`.

**Indexes (orders):** unique `order_ref`; index `status`, `destination_id`, `owner_id`, `group_id`, `email`, `eligibility`, `closed_at`, composite `(status, destination_id)` for the rejection-rate / open-orders scans.
**Relationships:** belongsTo `destinations`, `users` (owner); hasMany `order_events`, `documents`, `appointments`, `quotes`, `rejections`; barriers relate by destination slug + per-case `order_ref` (port: nullable FK on `barriers.order_id` for case barriers).

---

## 3. `order_events` (journey log + email log + stage transitions)
WP stored this as a **single serialized array** `ukv_journey` of `['date','agent','channel','text']` appended by ~12 plugins (orders, quicknote, eligibility, appointments, barriers/updates, rejection, refunds, premium-slot, passport-return, retention, stage/QA gates, emails). Also folds in `ukv_email_log`/`ukv_email_sent`. Normalize to one row per event.

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new) | |
| `order_id` | `foreignId` | no | — | parent order | FK → `orders.id`, cascade delete |
| `occurred_at` | `timestamp` | no | — | journey `date` (`Y-m-d H:i` UTC) | |
| `agent` | `string(120)` | no | `'system'` | journey `agent` | `system`/`customer`/`agent`/display name |
| `channel` | `enum('call','whatsapp','email','internal','upload')` | no | `'internal'` | journey `channel` | call/whatsapp/email/internal from notes; `upload` from doc-upload |
| `type` | `enum('note','email','stage_change','system')` | no | `'note'` | derived | distinguishes a free note from an email-sent / gate / system event |
| `text` | `text` | no | — | journey `text` | |
| `meta` | `json` | yes | null | (new) | optional structured payload (e.g. email `event` key from `ukv_email_sent`) |

**Indexes:** index `order_id`, composite `(order_id, occurred_at)`; for email idempotency add a partial/unique index `(order_id, type, meta->event)` — or a dedicated `email_event` string column with unique `(order_id, email_event)` to replicate the once-only send guard. **Recommended:** add nullable `email_event` `string(40)` + unique `(order_id, email_event)`.
**Relationships:** belongsTo `orders`.
**Ambiguity:** WP `date` is minute-precision UTC string with no seconds/timezone object — store as UTC `timestamp`. `agent` mixes role keywords and human display names; keep free `string` (optionally add nullable `user_id` FK when the agent maps to a `users` row).

---

## 4. `barriers`
WP = `ukv_barrier` CPT with **bare** meta keys (`ukv-barriers.php`). Single source of truth, surfaced live by query (case vs destination/all scope).

| Column | Type | Null | Default | Maps from (bare meta) | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | post ID | |
| `title` | `string(190)` | yes | null | post title | derived ("Case barrier — Egypt (REF)") |
| `nature` | `enum('temporary','permanent')` | no | `'temporary'` | `nature` | `UKV_BARRIER_NATURE` |
| `scope` | `enum('case','destination','all')` | no | `'case'` | `scope` | `UKV_BARRIER_SCOPE` |
| `destination_id` | `foreignId` | yes | null | `destination` (slug → FK) | null for `all` scope |
| `destination_slug` | `string(140)` | yes | null | `destination` | snapshot; matching key today |
| `order_id` | `foreignId` | yes | null | `order_ref` → order | set only for `case` scope |
| `order_ref` | `string(32)` | yes | null | `order_ref` | snapshot |
| `guidance` | `text` | yes | null | `guidance` | client-facing plain-English text |
| `status` | `enum('open','resolved')` | no | `'open'` | `status` | only `open` today; `resolved` implied by closure |
| `detected_by` | `enum('agent','auto')` | no | `'agent'` | `detected_by` | |
| `rule_key` | `string(80)` | yes | null | `rule_key` | idempotency key e.g. `{ref}:sla_breach`, `{ref}:passport_validity`, `{ref}:high_rejection_blocker` |

**Indexes:** index `status`, `scope`, `destination_id`, `order_id`; unique partial `(rule_key)` where `status = 'open'` (replicates the open-barrier idempotency in `ukv_barrier_create()`).
**Relationships:** belongsTo `destinations`, `orders` (case scope, nullable); hasMany `client_updates`.
**Ambiguity:** WP `status` only ever set to `open`; add `resolved` for the port so barriers can be closed explicitly rather than relying on the order closing.

---

## 5. `client_updates`
WP had **no dedicated store** — `ukv-client-updates.php` drafts updates purely from (barrier, order) and only records a per-barrier "already sent" set in barrier meta `ukv_update_sent` (array of order ids) plus a journey note. Materialize each send as a row for audit/idempotency.

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new) | |
| `barrier_id` | `foreignId` | no | — | barrier | FK → `barriers.id` |
| `order_id` | `foreignId` | no | — | affected order | FK → `orders.id` |
| `subject` | `string(255)` | yes | null | `ukv_draft_client_update()` subject | |
| `body` | `text` | yes | null | draft body | redacted, plain-English |
| `channel` | `enum('email','whatsapp','call')` | no | `'email'` | send path | sent via wp_mail today |
| `sent_at` | `timestamp` | yes | null | send time | null = drafted only |

**Indexes:** unique `(barrier_id, order_id)` — replicates the `ukv_update_sent` "don't re-send for this barrier" guard; index `order_id`.
**Relationships:** belongsTo `barriers`, `orders`.
**Note on the join table:** the WP `ukv_update_sent` array maps cleanly onto this table's unique `(barrier_id, order_id)` — a separate `barrier_order` pivot is **not** needed; `client_updates` is the pivot-with-payload.

---

## 6. `documents`
WP = WP **attachments** (private, parented to the order via `post_parent`), with the order keeping an array of attachment ids in `ukv_documents` and the attachment carrying `_ukv_order_doc = order_id` (`ukv-doc-upload.php`). Retention purge force-deletes the files (`ukv-retention.php`).

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | attachment ID | |
| `order_id` | `foreignId` | no | — | `_ukv_order_doc` / parent | FK → `orders.id`, cascade |
| `disk` | `string(20)` | no | `'private'` | (new) | Laravel storage disk — files must be private/non-public |
| `path` | `string(255)` | no | — | attachment file path | |
| `original_name` | `string(255)` | yes | null | sanitized upload name | |
| `mime` | `enum('image/jpeg','image/png','application/pdf','image/heic')` | no | — | `wp_check_filetype_and_ext` | `UKV_DOC_UPLOAD_ALLOWED` allow-list (jpg/jpeg→image/jpeg) |
| `size_bytes` | `unsignedInteger` | yes | null | upload size | max 10 MB (`UKV_DOC_UPLOAD_MAX_BYTES`) |
| `uploaded_by` | `enum('customer','agent')` | no | `'customer'` | journey agent on upload | |
| `purged_at` | `timestamp` | yes | null | retention purge | when file force-deleted; row kept for audit |
| `created_at` | `timestamp` | no | now | upload time | |

**Indexes:** index `order_id`, `purged_at`.
**Relationships:** belongsTo `orders`.
**Security carry-over:** files must NOT be web-accessible (WP used `post_status=inherit` private attachments); enforce a private disk + signed-URL/authorized download. Upload auth = order_ref + email match (non-enumerating). Allowed types strictly jpg/jpeg/png/pdf/heic, ≤10 MB.

---

## 7. `appointments`
WP stored these as **order meta**, shared between `ukv-govt-fields.php` and `ukv-appointments.php` (ref/date are the same fields). One appointment per order today. Port as a 1:1 (or 1:n future-proof) table; the workflow status carries the booking lifecycle.

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new) | |
| `order_id` | `foreignId` | no | — | order | FK → `orders.id`, cascade |
| `centre` | `string(160)` | yes | null | `ukv_appointment_centre` | e.g. "VFS London" |
| `reference` | `string(120)` | yes | null | `ukv_appointment_ref` | shared with govt-fields |
| `scheduled_at` | `date` | yes | null | `ukv_appointment_at` | date field (no time today) |
| `status` | `enum('not_required','to_book','booked','attended','completed')` | no | `'not_required'` | `ukv_appointment_status` | `UKV_APPOINTMENT_STATUSES` |

**Indexes:** index `order_id`, `status`, `scheduled_at`.
**Relationships:** belongsTo `orders`.
**Ambiguity:** `ukv_appointment_ref`/`ukv_appointment_at` are owned by **two** plugins writing the same meta — in the port they are single columns here; the govt "appointment ref/date" inputs in the govt-fields meta box collapse into this row. `scheduled_at` is a date only (no time) in WP.

---

## 8. `supply_nodes`
WP = single serialized option `ukv_supply_nodes` (array of normalized node arrays), NOT a CPT (`ukv-supply-chain.php`). A node with empty `destinations` is global.

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new; WP had string id `type-slug`) | |
| `node_key` | `string(80)` | no | — | node `id` (`type-slug`) | deterministic; **unique** |
| `type` | `enum('centre','courier','paypoint','embassy')` | no | — | `type` | `UKV_SUPPLY_TYPES` |
| `name` | `string(160)` | no | — | `name` | |
| `contact` | `string(255)` | yes | null | `contact` | URL/phone |
| `sla` | `string(160)` | yes | null | `sla` | free text |
| `notes` | `text` | yes | null | `notes` | |
| `is_global` | `boolean` | no | `false` | `destinations` empty | true → applies to all destinations |

**Pivot `destination_supply_node`:** `destination_id` FK, `supply_node_id` FK, unique `(destination_id, supply_node_id)`. WP stored `destinations` as a slug list inside the node; port to this many-to-many (skip when `is_global`).
**Indexes:** unique `node_key`; index `type`.
**Relationships:** belongsToMany `destinations`.

---

## 9. `discounts`
WP = single serialized option `ukv_discount_codes` keyed by CODE (`ukv-discounts.php`).

| Column | Type | Null | Default | Maps from (record key) | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new) | |
| `code` | `string(48)` | no | — | array key (`CONTEXT-XXXX`) | **unique** |
| `amount` | `decimal(10,2)` | no | `0` | `amount` | £ off (loyalty/review = 10.00) |
| `context` | `enum('loyal','review','code')` | no | `'code'` | `context` | issuance context (`UKV_LOYALTY`/`REVIEW`); keep `string` if more contexts expected |
| `email` | `string(190)` | yes | null | `email` | tied to a customer email |
| `used` | `boolean` | no | `false` | `used` | single-use |
| `order_ref` | `string(32)` | yes | null | `order_ref` | redeemed-on order |

**Indexes:** unique `code`; index `email`, `used`.
**Relationships:** optional belongsTo `orders` via `order_ref` (loose link; WP stored ref string, not id). Consider adding nullable `order_id` FK in the port.
**Ambiguity:** WP `amount` is a fixed £10 for both contexts; modelled as a real money column to allow variable future discounts.

---

## 10. `rejections`
WP = order meta `ukv_rejection_reason` (taxonomy) + `ukv_rejection_note` (`ukv-rejection.php`). One per order today; a child table allows history.

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new) | |
| `order_id` | `foreignId` | no | — | order | FK → `orders.id`, cascade |
| `reason` | `enum('doc_quality','eligibility','passport_validity','portal_error','customer_withdrew','other')` | no | — | `ukv_rejection_reason` | `UKV_REJECTION_REASONS` |
| `note` | `text` | yes | null | `ukv_rejection_note` | |
| `recorded_at` | `timestamp` | no | now | save time / journey note | |

**Indexes:** index `order_id`, `reason`; composite `(order_id)` unique if enforcing one-per-order (WP effectively did, last-write-wins).
**Relationships:** belongsTo `orders`. Feeds the `feedback` (suggestions) and rejection-rate analytics — those are **derived at query time** today (`ukv_rejection_stats`, `ukv_dest_rejection_rate`).

---

## 11. `feedback`
**Two senses in the WP build — disambiguated:**
1. **Improvement suggestions** (`ukv-feedback-loop.php`) are **derived/advisory only**, NOT stored (computed from `ukv_rejection_stats()` each render). Do NOT create a table for these — expose as a query/service (`FeedbackService::suggestions()`).
2. **Customer reviews/testimonials:** there is **no customer-review capture table** in WP — `review_request` is only an email event and consented testimonials become WP `post` drafts (`ukv-story-consent.php`). The `feedback` table is therefore **new** in the port, to capture the customer review the `review_request` email solicits.

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new) | |
| `order_id` | `foreignId` | yes | null | order | FK → `orders.id` |
| `rating` | `unsignedTinyInteger` | yes | null | (new) | 1–5 |
| `comment` | `text` | yes | null | (new) | raw customer text |
| `consented` | `boolean` | no | `false` | `ukv_story_consent` (mirrors order) | gate for publishing a testimonial |
| `testimonial_draft_id` | `unsignedBigInteger` | yes | null | drafted post id (`ukv_generate_testimonial_draft`) | link to generated draft if any |
| `source` | `enum('review_request','manual','import')` | no | `'review_request'` | (new) | |
| `created_at` | `timestamp` | no | now | (new) | |

**Indexes:** index `order_id`, `rating`, `consented`.
**Relationships:** belongsTo `orders`.
**Ambiguity (call out):** this is the only table NOT directly backed by existing meta. If the rebuild does not add a review-capture UI, this table stays empty and the "suggestions" remain a derived service. Confirm scope with the product owner; modelled here because the deliverable lists it and the `review_request`/consent flow implies it.

---

## 12. `quotes`
WP = order meta for the bespoke (manual-review) lane (`ukv-quote.php`). One active quote per order; a child table preserves history.

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | (new) | |
| `order_id` | `foreignId` | no | — | order | FK → `orders.id`, cascade |
| `amount` | `decimal(10,2)` | no | `0` | `ukv_quote_amount` | bespoke price (replaces fixed tiers for manual_review) |
| `status` | `enum('none','sent','paid')` | no | `'none'` | `ukv_quote_status` | `UKV_QUOTE_STATUSES`; default `none` |
| `payment_link` | `string(255)` | yes | null | `ukv_quote_link` | Stripe Payment Link (placeholder until live keys) |
| `sent_at` | `timestamp` | yes | null | `ukv_quote_sent_at` (epoch) | |

**Indexes:** index `order_id`, `status`.
**Relationships:** belongsTo `orders`. Applies only when `orders.eligibility ∈ (manual_review, cleared)` (`ukv_quote_applies()`).

---

## 13. `users`
WP users with `edit_posts` capability act as order owners / agents (`ukv-ownership.php`); the round-robin rota stores `ukv_owner_rota_last` (an option). Standard Laravel `users` + roles (Filament/Fortify per the design doc).

| Column | Type | Null | Default | Maps from | Notes |
|---|---|---|---|---|---|
| `id` | `bigIncrements` | no | — | WP user ID | |
| `name` | `string(160)` | no | — | `display_name` | shown as journey `agent` |
| `email` | `string(190)` | no | — | `user_email` | **unique**; SLA escalation mail target |
| `password` | `string(255)` | no | — | (re-auth at cutover) | |
| `role` | `enum('admin','agent','viewer')` | no | `'agent'` | WP capability | "eligible owner" = can edit orders |
| `remember_token` / `email_verified_at` | std | yes | null | (new) | Laravel auth scaffolding |

**Indexes:** unique `email`.
**Relationships:** hasMany `orders` (as owner via `orders.owner_id`).
**Carry-over options (NOT columns):** `ukv_owner_rota_last`, `ukv_retention_days`, `ukv_email_transport`, `ukv_whatsapp_number`, `ukv_phone_number`, `ukv_hubspot_token`, `ukv_anthropic_key`, HubSpot pipeline/stage ids → a `settings`/config table or Laravel `config`/`.env`. Listed here so they are not lost in the port; not part of the 13 core tables.

---

## Foreign-key summary
- `orders.destination_id` → `destinations.id` (nullable, restrict on delete)
- `orders.owner_id` → `users.id` (nullable, set null)
- `order_events.order_id` → `orders.id` (cascade)
- `documents.order_id` → `orders.id` (cascade)
- `appointments.order_id` → `orders.id` (cascade)
- `quotes.order_id` → `orders.id` (cascade)
- `rejections.order_id` → `orders.id` (cascade)
- `feedback.order_id` → `orders.id` (set null)
- `barriers.destination_id` → `destinations.id` (nullable)
- `barriers.order_id` → `orders.id` (nullable, case scope only)
- `client_updates.barrier_id` → `barriers.id`; `client_updates.order_id` → `orders.id`
- `destination_supply_node`: `destination_id` → `destinations.id`, `supply_node_id` → `supply_nodes.id`
- `orders.group_id` is a self-referential trip-group token (string), **not** a FK.

## Key index list (driving migrations)
- `orders`: **unique** `order_ref`; index `status`, `destination_id`, `owner_id`, `group_id`, `email`, `eligibility`, `closed_at`; composite `(status, destination_id)`.
- `destinations`: **unique** `slug`.
- `order_events`: index `(order_id, occurred_at)`; **unique** `(order_id, email_event)` (email idempotency).
- `barriers`: index `status`, `scope`, `destination_id`, `order_id`; **unique-where-open** `rule_key`.
- `client_updates`: **unique** `(barrier_id, order_id)`.
- `documents`: index `order_id`, `purged_at`.
- `appointments`: index `order_id`, `status`.
- `supply_nodes`: **unique** `node_key`.
- `discounts`: **unique** `code`; index `email`.
- `rejections`: index `(order_id, reason)`.
- `quotes`: index `(order_id, status)`.
- `users`: **unique** `email`.

## Free-form-in-WP → typed-in-port (explicit call-outs)
| WP storage | Was | Port |
|---|---|---|
| `ukv_destination` | display name string ("Egypt"), also slugged for matching | FK `destination_id` + snapshot `destination_name` |
| `ukv_tier` | label string ("Standard") | enum `standard/express/premium` |
| fees (`ukv_total`, `ukv_govt_fee`, `ukv_service_fee`, tier_* on Pods) | free text (`£` at render) | `decimal(10,2)` |
| `ukv_journey` | one serialized array of mixed events | normalized `order_events` rows + typed `channel`/`type` enums |
| `ukv_email_sent`/`ukv_email_log` | serialized arrays | `order_events` (type `email`) with unique `(order_id, email_event)` |
| boolean flags (`ukv_is_minor`, `ukv_prior_refusal`, `ukv_qa_signed_off`, `ukv_premium_slot`, `ukv_story_consent`, `ukv_risk_flag`, `ukv_govt_fee_paid`, `ukv_sla_escalated`, `ukv_docs_purged`) | `'1'`/`''` strings | `boolean` |
| epoch `*_at` meta | UNIX ints | `timestamp` |
| `ukv_residency_status`, `ukv_trip_purpose`, `ukv_status`, `ukv_blocker`, `ukv_priority`, `ukv_eligibility`, appointment/return/quote/rejection statuses, barrier nature/scope, supply type, discount context | string keys validated against PHP `const` maps | `enum` (allow-lists copied above) |
| `ukv_visa_entries`, `visa_type` | free text | left `string` — **candidate enums** flagged, taxonomy to confirm |
| supply nodes / discount codes | serialized WP `options` | real tables (`supply_nodes` + pivot, `discounts`) |
| barrier meta | **bare** keys (no `ukv_` prefix), destination = slug | columns; destination via FK + slug snapshot |

## Notes carried for the porter
- **Refund rule:** only `service_fee` is refundable; `govt_fee` is never refunded (`ukv-refunds.php`). Reflected by `refund_amount = service_fee`.
- **Closed set** drives barriers/SLA/retention — keep `UKV_ORDER_CLOSED` as an app constant, not a DB enum subset.
- **Eligibility lane** is both computed (standard/manual_review) and agent-set (cleared/referred); the gate blocks non-cleared manual_review orders past `paid`.
- **SLA hours** were derived from the tier label substring (express=24, premium=12, else 72) — port as a config map keyed by the `tier` enum, not a stored column.
- **PII:** `passport_number` must never reach the AI doc-review; consider column-level encryption + the retention purge already deletes uploaded `documents` after `closed_at` + retention window (default 90 days).
