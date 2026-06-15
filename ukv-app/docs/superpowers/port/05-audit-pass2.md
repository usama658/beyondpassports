# 05 — Launch Readiness Audit, Pass 2 (read-only re-verification)

Scope: re-verify the pass-1 findings (`04-audit.md`) against the **current** code, then a fresh
sweep. Read-only. Date: 2026-06-16.

States: **FIXED** (verified in code) / **STILL OPEN** (code gap remains) / **OPS-ONLY** (code is
correct; needs keys/host/cron at cutover).

---

## Verdict: ONE new CODE blocker, otherwise launch-ready pending OPS

The pass-1 backend fixes are all real and well-implemented — I read every file. The canonical-host
decision was made (Laravel), `/apply` is now a real eligibility-aware Blade intake form, the Stripe
webhook fires the paid email + CRM sync with proper `paid_at` idempotency (and is now covered by
`WebhookEmailTest`), the Filament Refund action and Viewer read-only gating are in place, the bespoke
Payment Link calls the live API, and `/documents`, `orders.phone`, and the sitemap-from-registry are
all done. Internal links all resolve to registered routes.

**However, porting the static HTML pages into Blade introduced a new regression:** the public site's
**strict CSP blocks every inline `<script>`**, and the apply form's lane-routing + checkout handoff is
an inline script. In a real browser the apply funnel's JS will not execute, and the no-JS fallback POST
renders raw JSON (the controller returns `JsonResponse`), not a redirect. This is a CODE blocker for the
funnel and must be fixed before launch. Everything else outstanding is OPS (keys/host/cron) or the
known data-accuracy item (H-5 fees).

**Does a CODE blocker/high remain? YES** — one new BLOCKER (CSP vs inline funnel JS) and the pre-existing
H-1 Filament-MFA portion (HIGH, partial). Details below.

---

## Pass-1 findings — re-verified state

| # | Pass-1 finding | New state | Evidence |
|---|----------------|-----------|----------|
| BLOCKER-1 | `/apply` was a dead `welcome` placeholder | **FIXED** | `routes/web.php:30` → `view('public.apply')`; `apply.blade.php` is a full intake form POSTing to `apply.store`, reads `navDestinations`, branches on the server lane response. |
| BLOCKER-2 | No Stripe keys → checkout throws | **OPS-ONLY** | `.env:69-70` `STRIPE_SECRET=`/`STRIPE_WEBHOOK_SECRET=` still empty. Code path correct (`StripeService::createCheckoutSession`). Set keys + register webhook at cutover. |
| BLOCKER-3 | `order_paid` email never fired | **FIXED** | `StripeService::markOrderPaid()` (`:211-212`) calls `$this->emails->sendOrderPaid($order)` + dispatches `SyncOrderToHubSpot`; records an audit event. `EmailService::sendOrderPaid` exists (`:95-98`). Covered by `WebhookEmailTest`. |
| BLOCKER-4 | Nothing runs the queue / mailer is log | **OPS-ONLY** | `.env:38` `QUEUE_CONNECTION=database`, `:50` `MAIL_MAILER=log`. Code uses `->queue()` correctly. Needs supervised `queue:work` + `schedule:run` + real `MAIL_*`. |
| BLOCKER-5 | FE had no API base; host mismatch | **FIXED (code) / OPS (host)** | Laravel chosen as canonical: `apply.blade.php` POSTs same-origin (`url('/apply')`), checkout handoff is `url('/checkout')/{ref}`. No `UKV_API_BASE` needed. `APP_URL`/`UKV_BASE_URL` still localhost (OPS). |
| H-1 | No Filament MFA; coarse access gate | **PARTIAL — STILL OPEN (MFA), FIXED (roles)** | Role gating fixed via H-1b. MFA still absent: `AdminPanelProvider` has `->login()` only, no `->multiFactorAuthentication(...)`. |
| H-1b | Viewer could reach create/edit on all resources | **FIXED** | `Concerns/AuthorizesByRole` returns `! isViewer()` for create/edit/delete; **all 10** resources `use` it (grep confirmed 10/10). |
| H-2 | No real Filament Refund action; Refunded in advance-stage | **FIXED** | `OrderResource.php:384-433` dedicated `refund` action collects amount/reason → `OrderService::refund()`. `nextStatuses()` (`:460-488`) excludes Refunded from every adjacency. |
| H-3 | Weak webhook idempotency (status-based) | **FIXED** | Idempotency now keyed on `paid_at` (`StripeService.php:176`); migration `2026_06_16_000001` adds `orders.paid_at`; model casts it `datetime`. Retry test passes. |
| H-4 | Bespoke Payment Link was a hard-coded placeholder | **FIXED (guarded)** | `createBespokeQuotePaymentLink()` (`:229-268`) calls `\Stripe\Price::create` + `\Stripe\PaymentLink::create`, persists URL to the latest quote; returns `QUOTE_PLACEHOLDER_LINK` only when no secret is configured. |
| H-5 | Seeded fees/gov fees are placeholders | **STILL OPEN** | `DestinationSeeder.php:14-20` still flagged "PLACEHOLDER FIGURES … MUST be verified against gov.uk before launch"; every `govt_fee_gbp` carries "— verify"; `passport_validity_months` flat 6. Content/owner task, not code. |
| M-1 | No GET document-upload page | **FIXED** | `routes/web.php:47` `Route::view('/documents','public.documents')`; `documents.blade.php` is a real auth-by-ref+email upload page, noindex, JS-enhanced + no-JS fallback. |
| M-2 | CORS defaults to wildcard origin | **OPS-ONLY** | `config/cors.php:39` still defaults `UKV_FRONTEND_ORIGIN` to `*`; `.env:73` blank. Now lower risk — apply is same-origin so the wildcard only exposes the 3 listed POST paths. Set allowlist at cutover. |
| M-3 | CSP form-action vs cross-origin apply | **FIXED (host decision)** | Apply is same-origin now, so `form-action 'self' https://checkout.stripe.com` is correct. (But see NEW-BLOCKER-1 on `script-src`.) |
| M-4 | Phone not persisted to a column | **FIXED** | Migration `2026_06_16_000001` adds `orders.phone`; `Order::$fillable` includes `phone`; `OrderService::createFromIntake` (`:86`) sets `$order->phone`; surfaced in `OrderResource` form + table column. |
| M-5 | APP env local/debug | **OPS-ONLY** | `.env:1-5` still `APP_NAME=Laravel`, `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://localhost`. Prod `.env` at cutover. |
| M-6 | Sitemap host + robots host + OG image | **OPS-ONLY (host) / VERIFY (OG)** | `SitemapController` builds from `config('app.url')` (= localhost until APP_URL set); `robots.txt:15` hard-codes `https://localhost/sitemap.xml` with an inline "update host after deploy" note. OG default `asset('images/og-default.jpg')` — confirm the asset exists. |
| M-7 | Sitemap guide slugs hand-maintained / drift | **FIXED** | `SitemapController.php:49` uses `GuideController::slugs()`; `GuideController::slugs()` (`:129-132`) returns `array_keys(self::GUIDES)`. All 6 registry slugs resolve (3 have `body_view`, 3 fall back to `_template`). |
| L-1 | Stale wiring comments | **MOSTLY FIXED** | `StripeService`/`CheckoutController` docblocks rewritten to describe the wired state; a couple of "Wire as:" lines linger in `CheckoutController:26-30` (harmless). |
| L-2 | `/checkout/{ref}` double route registration | **STILL OPEN (cosmetic)** | `routes/web.php:34-36` still registers `match(['get','post'])` + a separate `get` on the same URI. Works; redundant. |
| L-3 | Test gaps (paid email, refund) | **PARTIALLY FIXED** | New `WebhookEmailTest` asserts paid email + idempotency + CRM. Still no dedicated refund-action test and no CSP/CORS header test. |
| L-4 | Document disk is `local` | **OPS-ONLY** | Unchanged; fine for single-host. S3+private ACL optional for multi-server. |

8 destinations seed correctly (Turkey, Egypt, India, USA(ESTA), Australia, Thailand, UAE, Vietnam);
slugs match the public `/visa/{slug}` links. View composer (`AppServiceProvider:33`) shares
`navDestinations` with home, tools **and apply** — all 8 load everywhere a picker appears.

---

## NEW findings (this pass)

**NEW-BLOCKER-1 — Funnel/Security — public strict CSP blocks all inline `<script>`, breaking the apply funnel JS.**
`SecurityHeaders::strictCsp()` (`SecurityHeaders.php:103`) sets `script-src 'self' https://js.stripe.com`
— **no `'unsafe-inline'` and no nonce** — and is applied to every non-admin HTML page
(`:48-55`). But porting the static pages to Blade brought their inline scripts with them: 7 public
views carry inline `<script>` blocks — `apply.blade.php:335`, `documents.blade.php:148`,
`tools.blade.php:151`, `layouts/public.blade.php:79`, `contact.blade.php:169`, `legal.blade.php:213`,
`guides/index.blade.php:98`. In a real browser the CSP will refuse to execute all of them.
The critical one is **apply**: its inline script does the eligibility lane routing and the
"Continue to secure payment → `/checkout/{ref}`" handoff. With JS blocked, the form falls back to a
plain POST — and `ApplyController::store()` returns a **`JsonResponse`** (201), so the visitor sees raw
JSON instead of being routed to checkout. Net: the funnel is broken in any CSP-respecting browser even
after all the BLOCKER-1 work. (The CSP doc comment even says "keep funnel JS in static files" — that
assumption no longer holds now that the funnel is inline Blade.)
Fix (pick one): (a) add a per-request CSP **nonce** and stamp it on every inline `<script>` (best); or
(b) move the inline scripts to `resources/js/*` files served from `'self'`; or (c) add `'unsafe-inline'`
to public `script-src` (weakest — defeats much of the CSP). Also make `ApplyController::store()` return a
redirect for non-JSON/`Accept: text/html` requests so the no-JS path lands somewhere sane. Backend.

**NEW-LOW-1 — `CheckoutController::create()` has no lane/state guard.**
A direct GET/POST to `/checkout/{ref}` for a **manual_review** order (no tier, null fees) makes
`StripeService::createCheckoutSession()` throw `InvalidArgumentException` → 500. The apply JS only sends
the standard lane here, so it's an edge case (hand-typed URL / bespoke ref), but it should return a
friendly redirect or 404 rather than a 500. Also no guard for an already-`paid_at` order (double-pay).
Backend, low.

**NEW-LOW-2 — Stray dead view `welcome.blade.php`.**
`resources/views/welcome.blade.php:28` links to `url('/dashboard')` (a route that does not exist). The
file is no longer routed (`/` → `public.home`), so it's dead, but the CSP comment still references
"welcome" as a funnel page. Delete the stub. Cosmetic.

**NEW-LOW-3 — Placeholder contact endpoints across views.**
`tel:+440000000000` and `https://wa.me/440000000000` appear in `apply`, `documents`, and others. These
are obvious placeholders; replace with the real number at cutover. Content/OPS.

**NEW-NOTE — CORS allowed_headers omits `X-CSRF-TOKEN`/`X-Requested-With`.**
`config/cors.php:63` allows only `Content-Type, Accept`. Irrelevant while apply is same-origin (CORS
doesn't apply), but if any cross-origin front-end is ever reintroduced, its preflight for the CSRF
header would fail. Leave as-is for the Laravel-canonical decision; just be aware.

---

## OPS-only checklist (no code change required)

1. `STRIPE_SECRET` + `STRIPE_WEBHOOK_SECRET`; register `/stripe/webhook` in the Stripe dashboard. (B-2)
2. Supervised `php artisan queue:work` + `schedule:run` cron; real `MAIL_*` (SMTP/Resend/Postmark). (B-4)
3. Prod `.env`: `APP_ENV=production`, `APP_DEBUG=false`, fresh `APP_KEY`, real `APP_URL`/`UKV_BASE_URL`. (M-5)
4. `UKV_FRONTEND_ORIGIN` allowlist (lower priority now apply is same-origin). (M-2)
5. `HUBSPOT_TOKEN`, `ANTHROPIC_API_KEY` (for `GenerateNextBestAction`) at cutover. (`.env:71,74` empty)
6. Update `robots.txt` host + confirm `public/images/og-default.jpg` exists. (M-6)
7. Replace placeholder phone/WhatsApp numbers. (NEW-LOW-3)

## CODE gaps still open (must/should fix in code)

- **NEW-BLOCKER-1** — CSP vs inline funnel scripts (funnel-breaking). **MUST.**
- **H-1 (MFA portion)** — enable Filament multi-factor auth. **HIGH.**
- **H-5** — verify/replace placeholder fees, validity, docs in `DestinationSeeder`. (data; commercial/compliance) **HIGH (content).**
- NEW-LOW-1 (checkout guard), L-2 (double route), L-3 (refund/header tests), NEW-LOW-2 (dead welcome view). **LOW.**
