# UK Visa Site — Pre-Launch QA Checklist

Practical launch-readiness checklist for the whole site. Work top to bottom; do not flip
DNS / go live until every box in **Security/hardening**, **Payments**, and **Compliance** is ticked.

Verification harnesses referenced below live in `automation/` and are run with wp-cli:

```
cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file automation/<file>.php
```

Every `automation/test-*.php` self-seeds, asserts, cleans up, and prints `RESULT: ALL PASS`.
They blank `ukv_hubspot_token` + `ukv_anthropic_key` for the run so no real CRM/AI calls fire.

---

## 1. Infrastructure

- [ ] Production host provisioned (PHP 8.x, MySQL/MariaDB, HTTPS-capable, `memory_limit` >= 256M).
- [ ] Domain registered and DNS configured (A/AAAA + `www` CNAME); TTL lowered before cutover.
- [ ] SSL/TLS certificate installed and valid; HTTP -> HTTPS redirect enforced site-wide.
- [ ] `WP_HOME` / `WP_SITEURL` set to the live `https://` domain (no `localhost`/XAMPP paths).
- [ ] Automated backups configured (DB + `wp-content`), scheduled, and a **restore** test performed.
- [ ] Staging environment available for post-launch changes (don't edit prod live).
- [ ] PHP error logging enabled to file; `display_errors` OFF in production.
- [ ] Server timezone / WP timezone confirmed (SLA + barrier crons depend on correct time).

## 2. WordPress core

- [ ] Permalinks set to "Post name" and flushed (visa guides + destination pages resolve).
- [ ] WordPress core, all plugins, and theme updated to latest stable.
- [ ] mu-plugins (`wp-content/mu-plugins/ukv-*.php`) all present on production and auto-loading.
- [ ] Page caching + object cache configured; cache excludes the apply/checkout + admin flows.
- [ ] SMTP transport configured (see Smart stack) — WordPress default `mail()` does not deliver.
- [ ] `WP_DEBUG` OFF on production; `WP_DEBUG_LOG` may stay on (to file, not displayed).
- [ ] Search-engine visibility ON (Settings → Reading: "Discourage search engines" UNCHECKED).
- [ ] Admin user has a strong unique password; default `admin` username not used.

## 3. Security / hardening

- [ ] **ROTATE the exposed HubSpot token** — a live value was found in option `ukv_hubspot_token`
      (confirmed by both harnesses reporting "had value: yes"). Revoke the old token in HubSpot,
      issue a new private-app token, store it in `ukv_hubspot_token` on production only.
- [ ] **Rotate / scope all other API keys**: Stripe (live keys, restricted where possible),
      Anthropic (`ukv_anthropic_key`), any third-party keys. Never commit keys to the repo.
- [ ] Restrict API keys to least privilege (Stripe restricted key; HubSpot scoped to needed objects).
- [ ] Disable file editing in admin: `define( 'DISALLOW_FILE_EDIT', true );` in `wp-config.php`.
- [ ] Limit login attempts (plugin or host WAF) + reCAPTCHA on login if exposed.
- [ ] Move/protect `wp-login.php` or enable 2FA for all admin accounts.
- [ ] Security headers set (HSTS, X-Content-Type-Options, Referrer-Policy, CSP where feasible).
- [ ] File/dir permissions correct; `wp-config.php` not world-readable; XML-RPC disabled if unused.
- [ ] Confirm no secrets in version control (`git log -p` for tokens; scrub history if any leaked).

## 4. Payments (Stripe)

- [ ] Stripe account fully activated (business details, bank payout account verified).
- [ ] Live publishable + secret keys swapped in (remove all `pk_test`/`sk_test` references).
- [ ] Stripe webhook endpoint configured on the live URL; signing secret stored; events verified.
- [ ] Charge -> order creation flow works end to end: a real (small) live transaction creates a
      `ukv_order` via `ukv_create_order()` with correct ref/destination/tier/total, then refunded.
- [ ] Service fee vs government fee shown separately at checkout (`ukv_service_fee` / `ukv_govt_fee`).
- [ ] Receipt/confirmation email fires on paid (`order_paid` event) — see Smart stack.
- [ ] Failed/declined card path handled gracefully (no order created, user sees a clear message).

## 5. CRM (HubSpot)

- [ ] HubSpot token rotated (see Security) and stored on production.
- [ ] Deal created in HubSpot on a successful charge (verify against a real test transaction).
- [ ] Contact properties / pipeline stage mapping correct for new orders.
- [ ] Confirm test/seed records are NOT pushed to live HubSpot — the harnesses blank the token,
      but verify no leftover automation writes to the live portal.

## 6. Smart stack (orders / barriers / AI / email)

- [ ] **`automation/test-admin-render.php` → RESULT: ALL PASS** — meta boxes + dashboard widgets
      render with no PHP fatal (Lead Journey, Barriers live, Story consent, AI Doc Review,
      Affected clients; Orders insights, Open barriers, Success intelligence widgets).
- [ ] **`automation/test-triggers.php` → RESULT: ALL PASS** — real hooks fire side effects:
      `do_action('ukv_barriers_detect')` (SLA barrier, idempotent), `do_action('ukv_refresh_risk')`
      (sets `ukv_risk_flag`), and `ukv_email_on_status_change()` (submitted/delivered/review_request).
- [ ] `automation/test-barriers.php` → ALL PASS (fan-out, idempotency, live surface).
- [ ] `automation/test-insights.php`, `test-emails.php`, `test-doc-review.php`,
      `test-client-updates.php`, `test-story-consent.php` → all ALL PASS.
- [ ] Anthropic key set on production (`ukv_anthropic_key`) so AI Doc Review + content polish run.
- [ ] Email transport flipped from XAMPP log-mode to real delivery: `ukv_email_transport` set
      (`wp_mail` over working SMTP, or `hubspot`); confirm a live email actually arrives.
- [ ] Barrier detection cron scheduled: `wp_next_scheduled('ukv_barriers_detect')` returns a time;
      risk refresh cron `ukv_refresh_risk` scheduled; verify WP-Cron runs (or a real system cron
      hits `wp-cron.php`, since cache/low traffic can stall WP-Cron).
- [ ] Dashboard widgets visible + correct for a real admin (orders count, open barriers, success rate).

## 7. Content / SEO

- [ ] XML sitemap generated (RankMath) and submitted to Google Search Console + Bing.
- [ ] RankMath configured: titles/meta on all guides + destination pages, no "noindex" leaks.
- [ ] Schema markup present and valid (Organization, Service/Product, FAQ, BreadcrumbList).
- [ ] `robots.txt` correct (allows crawl, points to sitemap); `llms.txt` published if used.
- [ ] GA4 + Microsoft Clarity installed and firing **only after cookie consent** (CMP wired).
- [ ] Internal links + canonical tags correct; no orphan pages; 404 page friendly.
- [ ] Open Graph / Twitter cards render for key pages (share preview check).
- [ ] Page speed / Core Web Vitals acceptable on mobile (images optimised, caching on).

## 8. Compliance (visa-service specific)

- [ ] "**Not a government website / independent service**" disclaimer on home, apply, footer,
      and transactional emails (`UKV_EMAIL_FOOTER` / `UKV_UPDATE_COMPLIANCE` already enforce this).
- [ ] Service fee clearly separated from any government fee, everywhere a price is shown.
- [ ] "**Express speeds up our handling, not the government's decision**" stated wherever express
      tiers are sold (honesty constraint — express ≠ faster government decision).
- [ ] No claim that any product guarantees approval or a faster official outcome; ETA has no document.
- [ ] Refund policy + disclaimers published and linked from checkout.
- [ ] GDPR: privacy policy, cookie policy, lawful basis; cookie consent banner blocks non-essential
      tags pre-consent; data-subject request route documented.
- [ ] Terms of service + complaints/contact route published.
- [ ] AI Doc Review surfaced as **advisory only** (never auto-rejects, never changes status) — the
      meta box copy already states a human confirms before submission; confirm it stays visible.
