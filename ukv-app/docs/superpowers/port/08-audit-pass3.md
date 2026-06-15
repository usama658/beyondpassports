# 08 — Launch Readiness Audit, Pass 3 (final re-verification)

Scope: re-verify the pass-1 (`04-audit.md`) and pass-2 (`05-audit-pass2.md`) findings against the
**current** code, confirm the NEW-BLOCKER-1 / 2FA / WCAG / role-policy fixes, then a fresh whole-app
sweep. Read-only. Date: 2026-06-16.

States: **FIXED** (verified in code) / **STILL-OPEN-CODE** (code gap remains) / **OPS-ONLY**
(code is correct; needs keys/host/cron/human at cutover).

---

## Verdict: BUILD IS CODE-COMPLETE — no remaining CODE blocker/high

Every CODE blocker and high from passes 1–2 is now fixed in code, verified by reading the files (not
assumed). The single pass-2 funnel blocker (CSP vs inline scripts) is resolved, 2FA is wired, and the
no-JS path is covered by a new regression test. What remains is the OPS cutover checklist (keys, host,
cron, mailer) plus the **H-5 data-accuracy** task (verify placeholder gov/visa fees) which is a
content/owner action, not a code defect. Tests could not be executed in this sandbox (php/PowerShell
denied), but the test files exist and assert the fixed behaviour.

**Does a CODE blocker/high remain? NO.** Only LOW/edge-case code items and OPS items remain.

---

## Verify-fixed claims from the prompt — confirmed

| Claim | State | Evidence |
|-------|-------|----------|
| NEW-BLOCKER-1 CSP `script-src` now has `'unsafe-inline' https://js.stripe.com` | **FIXED** | `SecurityHeaders.php:105`. Inline funnel scripts will now execute under the public strict CSP. |
| ApplyController::store returns redirect for non-JSON (standard→checkout, manual→/apply+flash) | **FIXED** | `ApplyController.php:41-58`; return type `JsonResponse\|RedirectResponse`; covered by `ApplyNoJsTest` (both lanes assert 302, not JSON). |
| CheckoutController guards manual-review/unpriced orders | **FIXED** | `CheckoutController.php:35-38`: ManualReview or null `service_fee` → redirect to `apply` with status, never 500. |
| 2FA (H-1 MFA): BreezyCore `->myProfile()->enableTwoFactorAuthentication()` | **FIXED** | `AdminPanelProvider.php:54-58`. |
| User uses `TwoFactorAuthenticatable` | **FIXED** | `User.php:14,28`. |
| breezy_sessions migrated | **FIXED** | `database/migrations/2026_06_15_194129_create_breezy_sessions_table.php`. |
| WCAG contrast/focus/aria | **FIXED** | per `07-wcag-audit.md` (not re-walked this pass; tokens/aria changes present). |
| DestinationSeeder updated to researched values (flagged unverified) | **PARTIAL/OPS** | Seeder updated but figures still owner-verifiable (H-5, content task). |
| Role policies (H-1b) AuthorizesByRole on all resources | **FIXED** | grep: **10/10** Filament resources `use AuthorizesByRole`. |

---

## Pass-1 / Pass-2 findings → current state

| # | Finding | State | Evidence |
|---|---------|-------|----------|
| B-1 | `/apply` dead placeholder | **FIXED** | `web.php:30` → `public.apply`; full intake form POSTing to `apply.store`. |
| B-2 | No Stripe keys | **OPS-ONLY** | Code path correct (`createCheckoutSession`); keys empty in `.env`. |
| B-3 | order_paid email never fired | **FIXED** | `StripeService::markOrderPaid` `:211-212` sends `sendOrderPaid` + `SyncOrderToHubSpot`; audit event written. |
| B-4 | Queue/mailer not running | **OPS-ONLY** | `->queue()` used correctly; needs supervised `queue:work` + real `MAIL_*`. |
| B-5 | FE no API base / host mismatch | **FIXED (code)** | Laravel canonical; same-origin POST + `checkout.create` redirect. |
| NEW-B-1 (pass2) | CSP blocks inline funnel JS; no-JS shows raw JSON | **FIXED** | `SecurityHeaders.php:105` + `ApplyController` redirect fallback + `ApplyNoJsTest`. |
| H-1 | No Filament MFA | **FIXED** | BreezyCore TOTP wired (above). |
| H-1b | Viewer could create/edit everywhere | **FIXED** | `AuthorizesByRole` on 10/10 resources. |
| H-2 | No real Refund action | **FIXED** | dedicated `refund` action → `OrderService::refund()`; Refunded removed from advance-stage adjacency. |
| H-3 | Weak webhook idempotency | **FIXED** | keyed on `paid_at` (`StripeService.php:176`); `2026_06_16_000001` adds column. |
| H-4 | Bespoke Payment Link placeholder | **FIXED (guarded)** | `createBespokeQuotePaymentLink` `:229-268` calls live Stripe API; placeholder only when no secret. |
| H-5 | Seeded fees/validity placeholders | **STILL-OPEN (content/OPS)** | Verify gov/visa fees, `passport_validity_months`, `required_docs` against gov.uk. Not a code defect. |
| M-1 | No GET upload page | **FIXED** | `web.php:47` `/documents` view; auth-by-ref+email upload page. |
| M-2 | CORS wildcard origin | **OPS-ONLY** | Set `UKV_FRONTEND_ORIGIN` allowlist at cutover (low risk — apply is same-origin). |
| M-3 | CSP form-action vs cross-origin | **FIXED** | same-origin host decision. |
| M-4 | Phone not persisted | **FIXED** | `2026_06_16_000001` adds `orders.phone`; set in `createFromIntake`; in OrderResource. |
| M-5 | APP env local/debug | **OPS-ONLY** | prod `.env` at cutover. |
| M-6 | Sitemap/robots host + OG image | **OPS-ONLY / VERIFY** | URLs built from `app.url`; set `APP_URL`, fix robots host, confirm `og-default.jpg`. |
| M-7 | Sitemap guide-slug drift | **FIXED** | sitemap driven by `GuideController::slugs()`. |
| L-1 | Stale wiring comments | **MOSTLY FIXED** | a couple of "Wire as:" docblocks linger (`CheckoutController:26-29`) — harmless. |
| L-2 | `/checkout/{ref}` double route | **STILL-OPEN (cosmetic)** | `web.php:34-36` still double-registers GET; works. |
| L-3 | Test gaps | **MOSTLY FIXED** | `WebhookEmailTest` (paid email + idempotency + CRM) and `ApplyNoJsTest` added. Still no dedicated refund-action test and no CSP/CORS header assertion. |
| L-4 | Document disk `local` | **OPS-ONLY** | fine single-host; S3 optional. |
| NEW-LOW-2 (pass2) | Dead `welcome.blade.php` | **FIXED** | file deleted (glob: not found). |
| NEW-LOW-3 (pass2) | Placeholder phone/WhatsApp | **OPS-ONLY** | replace real number at cutover. |

Internal-link sweep: every `url('/...')` in views (home, about, apply, checkout, compare, contact,
destinations, documents/upload, driving-abroad, guides + 3 guide slugs, legal, reviews, tools, track)
resolves to a registered route or guide-registry slug. No broken links.

---

## NEW findings (this pass)

**NEW-LOW-1 — no double-pay guard in `createCheckoutSession`.**
`CheckoutController` now guards manual-review/unpriced orders, but neither it nor
`StripeService::createCheckoutSession` early-returns when an order already has `paid_at` set. A
customer who re-opens a stale `/checkout/{ref}` for an already-paid standard order would get a second
Stripe Checkout session (and could pay twice). The webhook idempotency (`paid_at`) prevents
double-fulfilment/double-email, but not a duplicate charge. Low severity (requires hand-revisiting a
checkout URL for a paid order). Fix: in `CheckoutController::create`, redirect to
`confirmation` when `$order->paid_at !== null`. Backend, LOW.

No new blocker/high found. No new broken links, no new authz/CSP/CSRF gaps.

---

## Final answer

### IS THE BUILD CODE-COMPLETE? **YES.**

No remaining CODE blocker or high. The funnel (home → apply → checkout → webhook → paid →
confirmation → track), payments, paid-email + CRM, admin authz + 2FA, CSP/CORS/CSRF/headers, and
internal links are all correct in code.

### Remaining CODE items (all LOW / optional — none block launch)
- **NEW-LOW-1** — add a double-pay guard (redirect already-`paid_at` orders to confirmation).
- **L-2** — collapse the redundant `/checkout/{ref}` double route registration.
- **L-3** — add a refund-action test and a CSP/CORS header-assertion test.
- **L-1** — tidy the lingering "Wire as:" docblocks in `CheckoutController`.

### OPS-only launch checklist (no code change)
1. `STRIPE_SECRET` + `STRIPE_WEBHOOK_SECRET`; register `/stripe/webhook` in the Stripe dashboard. (B-2)
2. Supervised `php artisan queue:work` + `schedule:run` cron; real `MAIL_*` (SMTP/Resend/Postmark). (B-4)
3. Prod `.env`: `APP_ENV=production`, `APP_DEBUG=false`, fresh `APP_KEY`, real `APP_URL`/`UKV_BASE_URL`. (M-5)
4. `UKV_FRONTEND_ORIGIN` allowlist. (M-2)
5. `HUBSPOT_TOKEN`, `ANTHROPIC_API_KEY` (for GenerateNextBestAction). (.env empty)
6. Update `robots.txt` host; confirm `public/images/og-default.jpg` exists. (M-6)
7. Replace placeholder phone/WhatsApp numbers. (NEW-LOW-3)
8. **H-5 (content/owner):** verify & replace placeholder gov/visa fees, `passport_validity_months`,
   and `required_docs` in `DestinationSeeder` against gov.uk before charging live customers. (commercial/compliance)
9. Run `php artisan test` in a php-enabled shell (could not run in this sandbox) to confirm green.
