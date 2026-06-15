# 04 — Launch Readiness Audit (read-only)

Scope: `ukv-app/` Laravel 12 + MariaDB + Filament. Read-only review of the full public silo, apply
funnel, payments, emails/queue, admin, integrations, security, data, SEO, links, tests, and launch
blockers. Date of audit: 2026-06-16.

Format per finding: `area — STATE — specific issue — fix / owner`.
States: DONE / PARTIAL / MISSING / BROKEN.

---

## Verdict: NOT ready for launch

The back end is genuinely strong — order domain, gates, pricing, Stripe Checkout + signed webhook,
lifecycle emails, document upload, Filament back-office, schedules, security headers, CORS, robots,
sitemap are all real and largely well-built. **But the single most important path — a visitor on the
Laravel site clicking "Apply" and reaching a payment — is broken**, because `/apply` renders the
default Laravel starter page and the real apply form lives on a *separate* static front-end
(`../frontend/apply.html`, Netlify) that is never served by, or linked from, the Laravel app. On top
of that, the `order_paid` confirmation email never fires, no Stripe/queue/keys are configured, and
the in-app refund action doesn't record refunds. A paid transaction is reachable only by
hand-driving the static Netlify form against a live API base + live Stripe keys + a running queue
worker — none of which are wired here.

Two front-ends exist and the boundary is unclear (see BLOCKER-1 / the "Two front-ends" section).
Resolve that first; everything else is downstream of it.

---

## BLOCKERS (must fix before any launch)

**BLOCKER-1 — Apply funnel — BROKEN — `/apply` on the Laravel host is a dead placeholder.**
`routes/web.php:30` maps `GET /apply` to `Route::view('/apply', 'welcome')` — the stock Laravel
`resources/views/welcome.blade.php` starter page. Yet *every* public Laravel page's primary CTA
points at `url('/apply')`: `layouts/public.blade.php:52` (footer), `public/home.blade.php:48,104`,
`public/about.blade.php:84`, `public/compare.blade.php:181`, `public/contact.blade.php:161`,
`public/tools.blade.php:81,145,155`, `public/reviews.blade.php:56`, `public/legal.blade.php:210`,
`public/guides/index.blade.php:93`, `destinations/show.blade.php:35`. So on the Laravel domain the
entire funnel CTA chain lands on the framework's "Laravel" splash page. The actual application form
is the static `frontend/apply.html`, which is NOT inside `ukv-app/` and is not served by Laravel.
Fix / owner: **Decide the canonical host** (see Two-front-ends). Either (a) serve the real apply UI
from Laravel (a Blade `apply` view that POSTs to `apply.store` and reads `?destination=`), or (b)
point all public CTAs at the Netlify apply URL. Until then the Laravel funnel is non-functional.

**BLOCKER-2 — Payments — MISSING config — no Stripe keys, so checkout throws.**
`.env` has `STRIPE_SECRET=` and `STRIPE_WEBHOOK_SECRET=` empty (`.env:69-70`). `StripeService`
(SDK `stripe/stripe-php ^20.2` IS installed per `composer.json:13`) builds a `StripeClient` from
`config('services.stripe.secret')` (`StripeService.php:279`); with an empty secret,
`createCheckoutSession()` (`CheckoutController.php:33`) will fail at the Stripe API call, and
`/stripe/webhook` rejects everything (empty signing secret → `SignatureVerificationException` → 400).
Fix / owner: set live (or test) `STRIPE_SECRET` + `STRIPE_WEBHOOK_SECRET`; register the webhook
endpoint in the Stripe dashboard. Ops/owner.

**BLOCKER-3 — Emails — BROKEN — the order_paid confirmation email never fires.**
On `checkout.session.completed`, `StripeService::markOrderPaid()` sets status directly and leaves an
explicit `TODO(email)` (`StripeService.php:213-216`); it does NOT call
`EmailService::sendOrderPaid()`. It also does NOT route through `OrderService::transition()`
(`StripeService.php:182-193` comment confirms "Until then, set the status directly"), so the
post-payment customer email + central audit/CRM hook are skipped. Result: a paying customer gets no
payment confirmation email. `EmailService::onStageChange()` is also deliberately silent on `paid`
(`EmailService.php:139`), and the order is *created* at `paid` (`OrderService.php:97`), so even a
transition wouldn't fire it — `order_paid` has no live trigger anywhere.
Fix / owner: in `markOrderPaid()` call `$this->emails->sendOrderPaid($order)` (and ideally route the
paid write through `OrderService`). Wire the `EmailService`/`OrderService` deps that are commented
out in `StripeService.php:40-41`. Backend.

**BLOCKER-4 — Emails/queue — MISSING — nothing runs the queue in production.**
`QUEUE_CONNECTION=database` (`.env:38`) and every lifecycle email is dispatched via `Mail::...
->queue()` (`EmailService.php:184,125`); HubSpot sync is also a queued job (`OrderService.php:167`).
A `queue:worker` is only present in the local `composer dev` script (`composer.json:47`). With no
worker running, **no emails send and no CRM sync happens** — jobs pile up in the `jobs` table.
`MAIL_MAILER=log` (`.env:50`) further means even a running worker only writes mail to the log, never
delivers. Fix / owner: run a supervised `php artisan queue:work` (and `schedule:run` cron) in prod;
set real `MAIL_*` SMTP/Resend/Postmark creds. Ops.

**BLOCKER-5 — Apply funnel — MISSING config — front-end has no API base + cancel/success host mismatch.**
The static form reads `window.UKV_API_BASE` and redirects the browser to
`API + '/checkout/' + order_ref` (`frontend/apply.html:311,490`). `UKV_API_BASE` is never set in the
repo, so the Netlify form POSTs nowhere by default. Also Stripe `success_url`/`cancel_url` are built
with Laravel `route('confirmation')` / `route('apply')` (`StripeService.php:286-298`) → after payment
the user lands on the *Laravel* `/confirmation/{ref}` and on cancel hits the broken Laravel `/apply`
(BLOCKER-1), even though they started on the Netlify form. The two confirmation/track pages
(`frontend/confirmation.html`, `frontend/track.html`) are duplicated and orphaned.
Fix / owner: set `UKV_API_BASE` to the Laravel origin, set `APP_URL`/`UKV_BASE_URL` to the real
domain, and pick ONE host for the post-payment pages. Ops + frontend.

---

## HIGH

**H-1 — Admin/ops — PARTIAL — Filament has no 2FA and a single coarse access gate.**
`AdminPanelProvider.php` enables `->login()` but no MFA/email-verification (the
`SECURITY-WIRING.md:84-99` recommendation is unimplemented). `User::canAccessPanel()`
(`User.php:21-24`) lets Admin **or Agent or Viewer** into the whole panel; there is no per-resource
policy, so a Viewer can reach create/edit on every resource (all resources ship Create/Edit pages).
Fix / owner: enable Filament MFA; add resource policies / `canEdit`–`canCreate` per role. Backend.

**H-2 — Payments/refund — PARTIAL — refund flow not reachable as a real refund from Filament.**
`OrderService::refund()` (`OrderService.php:286-294`) records `refund_amount/reason/refunded_at` then
transitions to `refunded`, but **no Filament action calls it**. The only path to `refunded` in the
admin is the generic `advanceStage` action (`OrderResource.php:322-327`), which calls
`OrderService::transition($record, Refunded)` directly — flipping status WITHOUT recording the refund
amount/reason and WITHOUT issuing any Stripe refund. So "refunding" in the UI produces an
inconsistent order (refunded status, null refund fields, money still captured at Stripe).
Fix / owner: add a dedicated "Refund" Filament action that collects amount/reason, calls
`OrderService::refund()`, and (optionally) issues the Stripe refund via the API. Backend.

**H-3 — Payments — PARTIAL — webhook idempotency is weak + bypasses the gate/audit pattern.**
Idempotency relies only on `if ($order->status === Paid) return` (`StripeService.php:176`). Because
orders are *created* at `paid` (`OrderService.php:97`), a legitimate `checkout.session.completed`
arriving for a brand-new order sees status already `paid` and returns early — the payment audit
event + (intended) email never run; conversely there is no event-id/processed-webhook dedupe table,
so Stripe retries are deduped only by current status. Fix / owner: introduce a distinct
"paid/unpaid" signal (e.g. a `paid_at` or `payment_status` column, or a processed-event ledger)
rather than overloading the `paid` pipeline stage as both "created" and "money received". Backend.

**H-4 — Bespoke (manual-review) lane — PARTIAL — payment is a hard-coded placeholder link.**
`StripeService::createBespokeQuotePaymentLink()` returns `PricingService::QUOTE_PLACEHOLDER_LINK`
(`StripeService.php:243-247`) — the live Payment Links API call is only a docblock. Manual-review
customers (the higher-value lane) therefore cannot actually pay. Fix / owner: implement the Payment
Links call. Backend.

**H-5 — Data accuracy — PARTIAL — seeded fees & gov fees are explicit placeholders (#129).**
`DestinationSeeder.php:14-20` states "PLACEHOLDER FIGURES … MUST be verified against gov.uk before
launch"; every `govt_fee_gbp` carries a "— verify" comment (e.g. Turkey £0, Egypt £20, India £30,
ESTA £17). `passport_validity_months` is a flat 6 everywhere and `max_stay_days`/`required_docs` are
"indicative". Shipping these as live prices/requirements is a commercial + compliance risk.
Fix / owner: verify and replace all fees/validity/docs against authoritative sources. Owner/content.

---

## MEDIUM

**M-1 — Customer journey — PARTIAL — confirmation→track handoff works, but track is the only
self-service post-pay touchpoint.** `/confirmation/{ref}` (Blade, `confirmation.blade.php`) and
`/track` (`TrackController`) are real, privacy-safe, and no-JS friendly (POST form). Good. But there
is no in-app authenticated document-upload *page* — `/documents/upload` is POST-only
(`routes/web.php:47`) with no GET view in Laravel; the upload UI presumably lives only on the static
front-end. Fix / owner: confirm the upload UI host; add a Blade upload page if Laravel is canonical.

**M-2 — Security/CORS — PARTIAL — defaults to wildcard origin.** `config/cors.php:39` defaults
`UKV_FRONTEND_ORIGIN` to `*`; `.env:73` leaves it blank → any site can POST to `/apply`,
`/track/lookup`, `/documents/upload`. Documented in `SECURITY-WIRING.md` but unset.
Fix / owner: set the explicit allowlist at cutover. Ops.

**M-3 — Security/CSP — PARTIAL — apply form posts cross-origin but `form-action` is `'self'` +
Stripe only.** `SecurityHeaders::strictCsp()` (`SecurityHeaders.php:99`) sets
`form-action 'self' https://checkout.stripe.com`. Fine for the Laravel-hosted funnel, but if the
canonical apply form stays on Netlify and submits to this origin via fetch it's a `connect-src`
concern not `form-action`; verify once the host decision is made. Headers + middleware ARE registered
(`bootstrap/app.php:18-22`). Fix / owner: revisit after BLOCKER-1 resolution. Backend.

**M-4 — Phone field not persisted to a column.** `ApplyRequest` requires `phone`
(`ApplyRequest.php:95`) but `OrderService::createFromIntake()` stores it only in the opening event
meta (`OrderService.php:84-86,143`) — there is no `orders.phone` column. Agents must dig into the
event log to find a callback number. Fix / owner: add a `phone` column or accept the event-meta
convention deliberately. Backend.

**M-5 — APP env still in local/debug.** `.env:2,4` `APP_ENV=local`, `APP_DEBUG=true`,
`APP_URL=http://localhost`, `APP_NAME=Laravel`. The default APP_KEY is committed in `.env` (the file
is gitignored per `SECURITY-WIRING.md`, but this is a local key). Fix / owner: prod `.env` with
`APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL`, fresh `APP_KEY`. Ops.

**M-6 — SEO — PARTIAL — sitemap host + OG image.** `SitemapController` builds URLs from
`config('app.url')` (=`http://localhost`) and `public/robots.txt` hard-codes
`Sitemap: https://localhost/sitemap.xml`. Canonical/noindex handling is otherwise solid
(`seo-meta.blade.php` supports `noindex`; robots disallows `/admin`, `/apply`, `/checkout/`, etc.;
sitemap excludes private routes). Default OG image `asset('images/og-default.jpg')` likely missing.
Fix / owner: set prod `APP_URL`, update robots host, add OG image. Ops/content.

**M-7 — Sitemap guide slugs are a hand-maintained list that may drift.**
`SitemapController.php:48-52` lists 6 guide slugs but only 3 article Blades exist
(`guides/articles/*.blade.php`) plus a `_template`. The other 3 (`idp-which-type`,
`applied-and-refused-next-steps`, `documents-before-you-apply`) may 404 if `GuideController::GUIDES`
doesn't define them — verify the registry matches. Fix / owner: drive sitemap from the controller's
GUIDES registry. Backend.

---

## LOW

**L-1 — Stale wiring comments.** `StripeService` and several controllers carry "wire as …" /
"NOT done here" docblocks (e.g. `StripeService.php:40-41`, `CheckoutController.php:26`,
`DocumentUploadController.php:22`, `TrackController.php:23`) describing wiring that IS now done in
`routes/web.php` / `bootstrap/app.php`. Harmless but misleading. Fix: tidy comments. Backend.

**L-2 — `/checkout/{ref}` accepts GET+POST without CSRF nuance.** `routes/web.php:34-36` registers
both a `match(['get','post'])` and a separate `get` on the same URI/name pattern — the second `get`
(`checkout.show`) overlaps the first. Works, but the double registration is redundant. Fix: collapse
to one route. Backend.

**L-3 — Tests — PARTIAL coverage.** Feature tests exist for the apply flow, Stripe webhook, doc
upload, track lookup, order events, pricing, loyalty/groups, admin smoke, plus unit tests for
eligibility/pricing (`tests/Feature`, `tests/Unit`). Gaps: no test asserts the order_paid email
fires on webhook (because it doesn't — BLOCKER-3), no refund-action test, no end-to-end
checkout-redirect test, no CSP/CORS header test. Fix: add tests once BLOCKER-3/H-2 are fixed.

**L-4 — Document disk is `local` (not a dedicated private disk).** `DocumentService::DISK = 'local'`
(`DocumentService.php:39`) — fine (storage/app is non-public) and upload validation is strict
(allow-list + size cap + finfo sniff, `DocumentService.php:169-212`). For multi-server prod consider
S3 with private ACL. Fix: optional. Ops.

---

## Two front-ends — which is canonical?

There are two complete, overlapping public front-ends:

- **Laravel public silo** (`ukv-app/resources/views/public/*`, served at the app's routes) — DB-driven
  destinations, guides, SEO meta, schema.org, sitemap, track, confirmation. Polished and dynamic.
- **Static `frontend/`** (sibling of `ukv-app/`, Netlify — has `netlify.toml`, `_redirects`,
  `robots.txt`, `sitemap.xml`) — `apply.html`, `confirmation.html`, `track.html`, `destinations.html`,
  etc. The **apply form only exists here**, and it POSTs cross-origin to the Laravel `/apply` via
  `window.UKV_API_BASE` then redirects to the Laravel `/checkout/{ref}`.

This is the root cause of BLOCKER-1/5. Today the Laravel pages link to `/apply` (broken placeholder),
while the working form is on Netlify pointing back at Laravel for the API + checkout + confirmation.
The confirmation and track pages are duplicated on both hosts. **Decision required:** make Laravel the
single canonical host (port `apply.html` to a Blade `apply` view, retire the Netlify duplicates) — OR
keep Netlify as the public host (repoint Laravel CTAs to Netlify, treat Laravel as API + admin +
post-pay pages only, and reconcile the duplicated confirmation/track). The current half-and-half state
ships a broken funnel either way.

---

## Punch-list — fastest path to a working paid transaction

Ordered so each step unblocks the next. Target: a real card payment that lands an order at `paid`,
emails the customer, and shows on the tracker.

1. **Pick the canonical host** (Two-front-ends). Recommend: Laravel canonical. (BLOCKER-1/5)
2. **Make `/apply` real.** Replace `Route::view('/apply','welcome')` with a Blade apply form that
   reads `?destination=` and POSTs to `apply.store`; OR repoint all CTAs to the chosen apply URL.
   (BLOCKER-1)
3. **Set Stripe keys + register webhook.** `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` in prod `.env`;
   add the `/stripe/webhook` endpoint in the Stripe dashboard. Use test keys first. (BLOCKER-2)
4. **Fire the paid email + central transition.** In `StripeService::markOrderPaid()` call
   `EmailService::sendOrderPaid()` and route the paid write through `OrderService`; fix the
   created-at-`paid` vs paid-event idempotency clash. (BLOCKER-3, H-3)
5. **Run the queue + real mailer.** Supervisor `queue:work`, `schedule:run` cron, real `MAIL_*`.
   (BLOCKER-4)
6. **Set host config.** `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `UKV_BASE_URL`,
   `UKV_API_BASE` (frontend), HTTPS, `UKV_FRONTEND_ORIGIN` allowlist. (BLOCKER-5, M-2, M-5)
7. **Smoke the full path:** home → tools/checker → apply → checkout (test card) → webhook → paid →
   confirmation → /track. Confirm the order_paid email in the log/inbox.
8. **Then** verify fees/gov fees (#129, H-5), wire a real Filament Refund action (H-2), implement the
   bespoke Payment Link (H-4), enable Filament 2FA + role policies (H-1), rotate HubSpot/Stripe/
   Anthropic tokens at cutover, fix sitemap host + guide-slug drift (M-6/M-7).
