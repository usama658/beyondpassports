# Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up a live, branded, SEO-ready, fast WordPress shell (no product logic) that every other subsystem builds on.

**Architecture:** Managed WordPress on SiteGround (staging → production). Hello Elementor base theme + a `hello-child` child theme (the only code we version in git) + Elementor/Jeg Kit/MetForm + the Travisa Elementor Template Kit for visual chrome. Brand direction A applied via Elementor Global Site Settings. RankMath for SEO/schema/sitemap; GA4 + Clarity + Looker for analytics. Everything tracked-in-repo lives under `wordpress/`.

**Tech Stack:** WordPress, Hello Elementor + child theme, Elementor + Jeg Elementor Kit + MetForm, Travisa kit (Envato Elements), RankMath, WP Rocket (or SiteGround Optimizer), GA4, Microsoft Clarity, Looker Studio, WP-CLI.

---

## Inputs (obtain before Task 1 — referenced as `$VARS` below)

These are real values the engineer must gather. Record them in `wordpress/INPUTS.md` (git-ignored — contains no secrets, but keep out of public repo).

- `$DOMAIN` — registered domain (e.g. `ukvisaco.com`). Pick a brandable, UK-trust name; register at SiteGround or Cloudflare Registrar.
- `$STAGING` — `staging.$DOMAIN` (SiteGround-generated staging URL until DNS cutover).
- `$ADMIN_EMAIL` — site admin email (`tools.infactai@gmail.com` or a brand inbox).
- `$GA4_ID` — GA4 Measurement ID (`G-XXXXXXX`), created in Task 9.
- `$CLARITY_ID` — Microsoft Clarity project ID, created in Task 9.
- Envato Elements account (active subscription) for the Travisa kit `.zip`.
- Brand: navy `#0A2540`, blue `#1456B8`, light `#EEF3FA`, gold `#C8A24A` (Premium only), text `#1B1B1B`, white `#FFFFFF`. Heading/body font: **Inter** (Google Fonts). Logo wordmark SVG (Task 5).

---

## File structure (tracked in git under `wordpress/`)

- `wordpress/INPUTS.md` — the inputs above (git-ignored)
- `wordpress/hello-child/style.css` — child theme header
- `wordpress/hello-child/functions.css` *(see Task 2 — actually `functions.php`)*
- `wordpress/hello-child/functions.php` — enqueue, Inter font, disclaimer hook, security headers
- `wordpress/hello-child/template-parts/disclaimer-bar.php` — "not a government site" strip
- `wordpress/brand-tokens.md` — canonical brand values (source of truth for Elementor Global settings)
- `wordpress/rankmath-settings.txt` — exported RankMath config
- `wordpress/legal/*.md` — source copy for the 6 legal/trust pages
- `wordpress/README.md` — how to restore/rebuild the shell

WordPress core, plugins, and the database are **not** in git (managed by SiteGround + staging). Only the child theme + config artifacts above are versioned.

---

### Task 0: Repo scaffolding for tracked artifacts

**Files:**
- Create: `wordpress/README.md`, `wordpress/brand-tokens.md`, `.gitignore` (modify)

- [ ] **Step 1: Create the `wordpress/` tree**

```bash
cd "C:/Users/mumya/OneDrive/Desktop/Claude Projects/UK VIsa"
mkdir -p wordpress/hello-child/template-parts wordpress/legal
```

- [ ] **Step 2: Ignore the inputs file (no secrets in repo)**

Append to `.gitignore`:

```
wordpress/INPUTS.md
```

- [ ] **Step 3: Write `wordpress/brand-tokens.md` (source of truth)**

```markdown
# Brand tokens — direction A (Trust)
Navy   #0A2540  (primary / headers / nav)
Blue   #1456B8  (primary CTA / links / accents)
Light  #EEF3FA  (section backgrounds)
Gold   #C8A24A  (Premium tier ONLY)
Text   #1B1B1B
White  #FFFFFF
Font   Inter (Google Fonts) — H1 700, H2 600, body 400
Buttons: blue #1456B8, 6px radius, white text, 600 weight
```

- [ ] **Step 4: Write `wordpress/README.md`**

```markdown
# WordPress shell (Foundation)
Host: SiteGround GrowBig. Base: Hello Elementor + hello-child.
Plugins: Elementor, Jeg Elementor Kit, MetForm, Travisa kit, RankMath, WP Rocket/SG Optimizer.
Brand: see brand-tokens.md (applied in Elementor → Site Settings → Global Colors/Fonts).
To rebuild: install plugins, import Travisa kit, apply hello-child, apply brand tokens, import rankmath-settings.txt.
```

- [ ] **Step 5: Commit**

```bash
git add wordpress/README.md wordpress/brand-tokens.md .gitignore
git commit -m "chore(foundation): scaffold wordpress/ artifact tree + brand tokens"
```

---

### Task 1: Hosting, domain, staging, SSL

**Files:** none tracked (infra). Verification only.

- [ ] **Step 1: Register `$DOMAIN`** at SiteGround (or Cloudflare Registrar → point nameservers to SiteGround).

- [ ] **Step 2: Provision SiteGround GrowBig** (UK datacentre — London). Create the site for `$DOMAIN`.

- [ ] **Step 3: Force HTTPS** — SiteGround → Security → SSL Manager → install Let's Encrypt → enable "HTTPS Enforce".

- [ ] **Step 4: Create staging** — SiteGround → WordPress → Staging → Create Staging Copy → `$STAGING`.

- [ ] **Step 4b: Security/WAF + backups** — confirm SiteGround daily backups on; enable SiteGround Security (login attempt limit, 2FA, WAF). Install Wordfence (free) only if host WAF proves insufficient.

- [ ] **Step 5: Pick canonical host** — decide `https://$DOMAIN` (non-www) as canonical; add 301 redirect from `www` → non-www (SiteGround → Domain → Redirect).

- [ ] **Step 6: Verify**

Run (replace `$DOMAIN`):
```bash
curl -sI "http://$DOMAIN" | grep -i "location:"      # expect 301 → https://$DOMAIN
curl -sI "https://www.$DOMAIN" | grep -i "location:" # expect 301 → https://$DOMAIN
curl -sI "https://$DOMAIN" | head -1                 # expect HTTP/2 200
```
Expected: HTTP→HTTPS 301, www→non-www 301, HTTPS 200.

---

### Task 2: WordPress base — Hello Elementor + child theme + core plugins

**Files:**
- Create: `wordpress/hello-child/style.css`, `wordpress/hello-child/functions.php`

- [ ] **Step 1: Confirm WP install** on `$STAGING` (SiteGround auto-installs WP). Set Settings → General: Site Title "UKVisaCo" (or brand), tagline empty, admin `$ADMIN_EMAIL`, timezone London, permalinks → **Post name** (`/%postname%/`).

- [ ] **Step 2: Install + activate plugins** (Plugins → Add New, or WP-CLI on SiteGround SSH):

```bash
wp theme install hello-elementor --activate
wp plugin install elementor --activate
# Jeg Elementor Kit + MetForm: required by Travisa — install from Travisa kit installer (Task 3)
```

- [ ] **Step 3: Create child theme `style.css`**

`wordpress/hello-child/style.css`:
```css
/*
Theme Name: Hello Child (UKVisaCo)
Template: hello-elementor
Version: 1.0.0
*/
```

- [ ] **Step 4: Create child theme `functions.php`**

`wordpress/hello-child/functions.php`:
```php
<?php
// Enqueue parent + child styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('hello-parent', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('hello-child', get_stylesheet_uri(), ['hello-parent'], '1.0.0');
    // Inter font
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap', [], null);
});

// Security headers
add_action('send_headers', function () {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
});
```

- [ ] **Step 5: Upload + activate child theme** — zip `hello-child/` and upload via Appearance → Themes → Add New → Upload, or copy to `wp-content/themes/hello-child/` via SiteGround File Manager/SSH, then activate.

- [ ] **Step 6: Verify**

```bash
curl -s "https://$STAGING" | grep -i "fonts.googleapis.com/css2?family=Inter"   # expect a match
curl -sI "https://$STAGING" | grep -i "x-frame-options"                          # expect SAMEORIGIN
```
Expected: Inter enqueued; security header present; active theme = Hello Child.

- [ ] **Step 7: Commit**

```bash
git add wordpress/hello-child/style.css wordpress/hello-child/functions.php
git commit -m "feat(foundation): hello-child theme (Inter font + security headers)"
```

---

### Task 3: Import Travisa kit + prune

**Files:** none tracked (kit imports into DB).

- [ ] **Step 1: Download Travisa** `.zip` from Envato Elements (the page from brainstorming: Travisa – Immigration & Visa Consulting Elementor Template Kit).

- [ ] **Step 2: Install required plugins** bundled with Travisa: **Jeg Elementor Kit** + **MetForm** (Plugins → Add New → Upload, or the kit's "Install Required Plugins" prompt). Activate both.

- [ ] **Step 3: Import the kit** — Templates → Kit Library / Jeg importer → import Travisa. Import **templates + global settings**, skip dummy blog/news posts not needed.

- [ ] **Step 4: Prune** — delete imported demo pages/templates we won't use (consulting service pages, team, testimonials we don't need). Keep: header, footer, a flexible page template, FAQ block, pricing block, hero block.

- [ ] **Step 5: Verify**

```bash
wp plugin list --status=active --field=name    # expect: elementor, jeg-elementor-kit, metform-core (names may vary)
```
Expected: Elementor + Jeg Kit + MetForm active; Travisa header/footer/templates present under Templates.

---

### Task 4: Brand tokens (direction A) via Elementor Global Settings

**Files:** none new (values sourced from `wordpress/brand-tokens.md`).

- [ ] **Step 1: Set Global Colors** — Elementor → Hamburger → Site Settings → Global Colors:
  - Primary `#1456B8` (CTAs/links) · Secondary `#0A2540` (navy) · Text `#1B1B1B` · Accent `#C8A24A` (Premium) · add custom `Light #EEF3FA`.

- [ ] **Step 2: Set Global Fonts** — Site Settings → Global Fonts: Primary (Headings) Inter 700/600; Secondary (Text) Inter 400.

- [ ] **Step 3: Set Buttons** — Site Settings → Buttons: background `#1456B8`, text white, typography Inter 600, border-radius 6px.

- [ ] **Step 4: Verify**

Load `https://$STAGING` and confirm a sample Travisa button renders blue `#1456B8`, 6px radius, Inter. In DevTools:
```
Computed style of .elementor-button → background-color rgb(20,86,184); font-family Inter
```
Expected: matches brand tokens.

---

### Task 5: Logo + header navigation

**Files:** none tracked (menu config); logo asset stored in Media.

- [ ] **Step 1: Add logo** — upload wordmark SVG to Media; set in Travisa header template (Elementor → edit Header template → Site Logo widget).

- [ ] **Step 2: Build the nav menu** — Appearance → Menus → create "Primary": items (use `#` placeholders that resolve as routes get built):
  - `Destinations` (dropdown parent — children stubbed: Turkey, Egypt, India, Morocco, UAE, Australia, USA, Schengen)
  - `IDP` → `/idp/`
  - `Tools` (dropdown — `Do I need a visa?`, `Visa photo`)
  - `How it works` → `/how-it-works/`
  - `Pricing` → `/pricing/`
  - CTA button `Start application` → `/apply/`
  Assign menu to header location.

- [ ] **Step 3: Verify**

```bash
curl -s "https://$STAGING" | grep -i -E "How it works|Pricing|Start application"   # expect matches
```
Expected: nav renders with all top items + CTA; dropdowns present (empty children OK until silos built).

---

### Task 6: Footer silo index + disclaimer bar

**Files:**
- Create: `wordpress/hello-child/template-parts/disclaimer-bar.php`
- Modify: `wordpress/hello-child/functions.php`

- [ ] **Step 1: Create the disclaimer partial**

`wordpress/hello-child/template-parts/disclaimer-bar.php`:
```php
<div class="ukv-disclaimer" style="background:#0A2540;color:#cdd8e8;font:12px/1.4 Inter,Arial,sans-serif;text-align:center;padding:8px 12px">
  Independent visa &amp; permit service — we are <strong>not a government website</strong> and charge a service fee in addition to any official fees.
</div>
```

- [ ] **Step 2: Hook the disclaimer into header + footer**

Append to `wordpress/hello-child/functions.php`:
```php
add_action('wp_body_open', function () {
    get_template_part('template-parts/disclaimer-bar');
});
add_action('wp_footer', function () {
    if (!is_admin()) get_template_part('template-parts/disclaimer-bar');
}, 5);
```

- [ ] **Step 3: Build footer link-hub** in the Travisa footer template (Elementor): 4 columns —
  - **Destinations**: Turkey · Egypt · India · Morocco · UAE · Australia · USA · Schengen
  - **Products**: Visa · IDP · Visa+IDP
  - **Tools**: Visa checker · Photo maker
  - **Company**: How it works · Pricing · Refunds · Terms · Privacy · About
  (links use route stubs until pages exist; legal links resolve after Task 7).

- [ ] **Step 4: Re-upload child theme** (sync changed `functions.php` + new partial to `wp-content/themes/hello-child/`).

- [ ] **Step 5: Verify**

```bash
curl -s "https://$STAGING" | grep -ci "not a government website"   # expect 2 (top + footer)
curl -s "https://$STAGING" | grep -i -E "Refunds|Privacy|About"    # expect matches
```
Expected: disclaimer appears top + footer; footer shows 4-column index.

- [ ] **Step 6: Commit**

```bash
git add wordpress/hello-child/functions.php wordpress/hello-child/template-parts/disclaimer-bar.php
git commit -m "feat(foundation): disclaimer bar (header+footer) + footer silo index"
```

---

### Task 7: Legal / trust pages

**Files:**
- Create: `wordpress/legal/how-it-works.md`, `pricing.md`, `refunds.md`, `terms.md`, `privacy.md`, `about.md`

- [ ] **Step 1: Write source copy** for each page (real copy, not placeholder). Example `wordpress/legal/refunds.md`:

```markdown
# Refund Policy
We are an independent service; we charge a service fee in addition to any government/official fee.
- Government/official fees: refundable until your application is submitted to the authority; non-refundable once submitted.
- Our service fee: non-refundable once we have begun work on your application.
- IDP: the official permit cost + postage are non-refundable once the permit is obtained/dispatched.
- To request a refund, contact support@DOMAIN with your order reference.
```
(Write the analogous real copy for how-it-works, pricing skeleton, terms, privacy/GDPR, about — each grounded in parent spec §9/§15. Privacy must cover: data collected, encrypted document handling, retention/deletion, GDPR rights, ICO registration.)

- [ ] **Step 2: Create the 6 WordPress pages** with slugs `/how-it-works/`, `/pricing/`, `/refunds/`, `/terms/`, `/privacy/`, `/about/`; paste the copy.

- [ ] **Step 3: Verify**

```bash
for s in how-it-works pricing refunds terms privacy about; do
  echo "$s: $(curl -sI "https://$STAGING/$s/" | head -1)"
done
```
Expected: each returns `200`.

- [ ] **Step 4: Commit**

```bash
git add wordpress/legal/
git commit -m "content(foundation): legal/trust page source copy (6 pages)"
```

---

### Task 8: RankMath SEO + schema + sitemap

**Files:**
- Create: `wordpress/rankmath-settings.txt` (exported)

- [ ] **Step 1: Install + activate RankMath**; run Setup Wizard → Business type "Local Business/Organization" → set Organization name, logo, `$ADMIN_EMAIL`; connect RankMath account (free).

- [ ] **Step 2: Title templates** — Titles & Meta: site title format `%title% | UKVisaCo`; set Organization (WebSite + Organization schema auto-enabled). Set social/OG defaults.

- [ ] **Step 3: Breadcrumbs** — enable RankMath breadcrumbs; add to Travisa templates via `[rank_math_breadcrumb]` or theme hook.

- [ ] **Step 4: Sitemap** — Sitemap Settings → enable; exclude `/apply/` and tag archives. Note sitemap URL `https://$DOMAIN/sitemap_index.xml`.

- [ ] **Step 5: `/apply` noindex stub** — create `/apply/` page (placeholder "Application — coming soon"), set RankMath → Advanced → Robots → `noindex`.

- [ ] **Step 6: robots.txt** — RankMath → General → Edit robots.txt:
```
User-agent: *
Allow: /
Sitemap: https://$DOMAIN/sitemap_index.xml
```

- [ ] **Step 7: Export settings** — RankMath → Status & Tools → Import/Export → Export → save output to `wordpress/rankmath-settings.txt`.

- [ ] **Step 8: Verify**

```bash
curl -s "https://$STAGING/sitemap_index.xml" | head -3            # expect <?xml ... <sitemapindex
curl -s "https://$STAGING" | grep -o '"@type":"Organization"'      # expect a match
curl -s "https://$STAGING" | grep -o '"@type":"WebSite"'           # expect a match
curl -s "https://$STAGING/apply/" | grep -i "noindex"              # expect a match
```
Also: paste `https://$STAGING` into Google Rich Results Test → Organization + WebSite valid, 0 errors.

- [ ] **Step 9: Commit**

```bash
git add wordpress/rankmath-settings.txt
git commit -m "feat(foundation): RankMath SEO config + Org/WebSite schema + sitemap export"
```

---

### Task 9: Analytics — GA4 + Search Console + Clarity + Looker

**Files:** none tracked (external).

- [ ] **Step 1: Create GA4 property** at analytics.google.com → get `$GA4_ID` (`G-XXXXXXX`). Add the GA4 tag via RankMath → General → Analytics, or a head-snippet plugin (e.g. "WPCode"). Record `$GA4_ID` in `wordpress/INPUTS.md`.

- [ ] **Step 2: Verify GSC** — search.google.com/search-console → add `https://$DOMAIN` (Domain property via DNS TXT, or URL-prefix via GA4). Submit `sitemap_index.xml`.

- [ ] **Step 3: Microsoft Clarity** — clarity.microsoft.com → create project → get `$CLARITY_ID` → add tracking snippet (WPCode head). Record `$CLARITY_ID`.

- [ ] **Step 3b: GDPR cookie consent** — install a consent plugin (CookieYes or Complianz). Configure: block GA4 + Clarity until consent (Consent Mode v2), link to `/privacy/`, UK/EU banner. Verify analytics tags only fire after "Accept".

- [ ] **Step 4: Looker Studio** — create a report; add data sources: GA4 (the property) + Search Console (the site). Save a blank "UKVisaCo — Foundation" report shell.

- [ ] **Step 5: Verify**

- GA4 → Realtime: load `https://$STAGING` (or production after cutover) → see 1 active user.
```bash
curl -s "https://$STAGING" | grep -o "$GA4_ID"        # expect the GA4 ID present in page (substitute)
curl -s "https://$STAGING" | grep -i "clarity.ms"      # expect Clarity script present
```
- GSC: sitemap status "Success". Looker: both data sources connected.

---

### Task 10: Performance baseline (Core Web Vitals)

**Files:** none tracked.

- [ ] **Step 1: Caching** — enable SiteGround Optimizer (or install WP Rocket): page caching, CSS/JS minify + combine (test for breakage), lazy-load images.

- [ ] **Step 2: Images** — enable WebP + lazy-load (SiteGround Optimizer / WP Rocket). Ensure logo is SVG.

- [ ] **Step 3: Trim** — remove unused Travisa demo assets/plugins; disable Elementor features not used; defer non-critical JS.

- [ ] **Step 4: Verify CWV / Lighthouse**

Run Lighthouse (Chrome DevTools, mobile) on the homepage shell, or PageSpeed Insights on `https://$STAGING`:
- Performance ≥ 90 (mobile)
- LCP < 2.5s · INP < 200ms · CLS < 0.1

Expected: all targets met. If not, iterate on caching/image/JS-defer before go-live.

---

### Task 11: Go-live + acceptance

**Files:** none tracked.

- [ ] **Step 1: Push staging → production** — SiteGround → Staging → "Push to Live" (or full-site sync). Ensure DNS for `$DOMAIN` points to SiteGround; HTTPS enforced on production.

- [ ] **Step 2: Production smoke test**

```bash
curl -sI "https://$DOMAIN" | head -1                                   # 200
for s in how-it-works pricing refunds terms privacy about idp apply; do
  echo "$s: $(curl -sI "https://$DOMAIN/$s/" | head -1)"               # legal pages 200; apply 200 (noindex)
done
curl -s "https://$DOMAIN" | grep -ci "not a government website"        # 2
curl -s "https://$DOMAIN/sitemap_index.xml" | head -1                  # <?xml
```

- [ ] **Step 3: Final acceptance checklist (sub-spec §Acceptance)** — tick all:
  - [ ] Branded homepage shell + nav + footer live over HTTPS
  - [ ] 6 legal/trust routes resolve + disclaimer present (top + footer)
  - [ ] RankMath active; sitemap submitted to GSC; Org + WebSite schema valid (0 errors in Rich Results Test)
  - [ ] GA4 + GSC + Clarity recording; Looker data sources connected
  - [ ] Lighthouse mobile ≥ 90; LCP/INP/CLS within targets

- [ ] **Step 4: Tag the foundation milestone**

```bash
cd "C:/Users/mumya/OneDrive/Desktop/Claude Projects/UK VIsa"
git add -A && git commit -m "chore(foundation): go-live acceptance passed" || true
git tag foundation-live
```

---

## Notes for subsequent sub-plans
- Nav `Destinations` dropdown children + footer destination links are **stubs**; subsystem #2 (Content silos) fills them as money pages go live.
- `/apply/` is a `noindex` placeholder; subsystem #4 replaces it with the funnel.
- Travisa hero/pricing/FAQ blocks are reused by #2 (money pages) and the Pricing content task.
