# Email Lifecycle — Design Spec

**Date:** 2026-06-12 · **Parent:** Smart Orders Hub · **Depends on:** ukv-orders.php (order CPT + status + journey), ukv-hubspot.php (existing paid email), optional ukv-ai.php (P15).

## Context
Email is a **secondary** channel — calls + WhatsApp are primary. Keep it lean. Build now in log mode (XAMPP can't deliver); flip to real delivery at launch via a pluggable transport.

## Architecture
- **Event→template engine.** Events: `order_paid`, `docs_needed`, `submitted`, `decision`, `delivered`, `review_request`, `checker_abandon`.
- **Templates:** `ukv_email_template( string $event, int $order_id ): array` → `[ 'subject'=>..., 'body'=>... ]`. Plain text + compliance footer ("Independent service — not a government website…"). If P15 key present, optionally polish `body` via `ukv_ai_polish_content` style call; else static template.
- **Pluggable send:** `ukv_email_send( string $to, string $subject, string $body, string $event, int $order_id ): bool`.
  - ALWAYS append a log entry (audit) — to `ukv_email_log` (order meta array: event, to, subject, time) AND a journey note. Works on XAMPP now.
  - Route by option `ukv_email_transport`: `wp_mail` (default; real once SMTP set at launch) | `hubspot` (create an email engagement on the contact via existing `ukv_hs_post`).
  - **Idempotent:** skip if `(order,event)` already in `ukv_email_sent` meta; record after send.

## Triggers
- `order_paid` — keep the existing confirmation (in ukv-hubspot stripe hook) but route it through `ukv_email_send` for logging + idempotency.
- `docs_needed` — the existing completeness auto-chase calls `ukv_email_send('docs_needed')`.
- `submitted` / `decision` / `delivered` — on `ukv_order` status change (`save_post_ukv_order`, compare old→new status), fire the matching event once.
- `review_request` — when status becomes `delivered`, queue a review-request email (ties into P14 consented stories).
- `checker_abandon` — wp-cron: if a visa-checker submission captured an email and no Apply/order followed within 24h, send one nudge. Guard: only when an email was captured (most are anonymous → low volume); idempotent per email.

## Build phases
1. Template engine (7 templates) + `ukv_email_send` with log + idempotency + transport option (default wp_mail). Free.
2. Status-change triggers (submitted/decision/delivered/review_request) + route existing paid + docs_needed through the engine. Free.
3. checker_abandon cron (guarded). Free.
4. HubSpot transport + (at launch) SMTP wiring + P15 copy polish. Free to build; delivery at launch.

## Acceptance
- Each event builds a non-empty subject+body with the compliance footer.
- `ukv_email_send` logs every attempt and never double-sends the same `(order,event)`.
- A status change from `doc_review`→`submitted` fires exactly one `submitted` email (logged).
- `delivered` fires `delivered` + queues `review_request`, once each.
- No fatal when transport can't deliver (XAMPP) — log still records.
