# SP7: beyondpassports.com launch runbook and programme schedule (6 to 15 Oct 2026)

**Status:** DRAFT for owner review, written 2026-10-06 (Tuesday) by the Plan agent. Sub-project 7 of 7 in the international + tours programme.
**Hard deadline:** Thursday 15 October 2026. Nine calendar days including one weekend (10 to 11 Oct).
**Decision basis:** `docs/product-goals-2026-10.md` (G1 to G6, launch order + gates), `docs/superpowers/research/2026-10-06-README-decision-basis.md` (14 locked reports). Every gate below cites its research file.
**Inputs read:** competitive-service-brief sections 8 to 10; four-market-feasibility; deepdive-market-south-africa; deepdive-market-usa-canada section 7; competition-led-gap-analysis A1 to A20, F1 to F5; foundation spec + plan (13 tasks); GO-LIVE-RUNBOOK.md; DEPLOY-LARAVEL-CLOUD.md; appointment-availability-playbook.md sections 0, 0b, 3, 4; portal-accounts-setup.md; ads-lp-fixes-tasklist.md; canonical-values.md; email-setup-runbook.md.

## How to use this document
1. Read section 1 and pick an option (A, B or C). The schedule in section 2 assumes A.
2. Answer the owner questions in section 5 by the dates shown. Every one of them is on the critical path.
3. Section 3 is the runbook you follow on deploy day and launch day. Each step has a "Done when" check.
4. Section 4 is the RACI. A gate with no named person in the R column does not pass.
5. Facts verified in the repo are stated as facts. Facts not verifiable from the repo are written as a question with the person who answers it.

---

## 1. Honest capacity assessment

### 1.1 Where we actually are on 6 Oct (verified in the repo today)
- `master` HEAD `53513d5` is docs only since the Belgium gold page work. Zero multi-market code on master (feasibility premise check 1).
- `feat/sa-market-phase1` is 6 commits ahead, merge base `08e59db`, `git merge-tree` reports 0 conflicts. Task 0 of the SP1 plan is safe to run.
- Production: cPanel host for `beyondpassports.co.uk`, app deployed as a shallow clone, deploy = `git fetch origin && git reset --hard origin/master` then `php artisan view:clear && php artisan cache:clear`. `.co.uk` is behind Cloudflare. Cloudflare Bot Fight Mode blocked Google's ad-review crawler on `.co.uk` and caused ad disapprovals.
- Analytics: GTM `GTM-5DMLL4HR`, GA4 `G-KR93N3DF55`, Clarity `xbfqfhmnvp`, Meta pixel set; cookie banner OFF, so `partials.analytics-head` renders the static install. `robots.txt` lists one sitemap. `.env.production.example` has `UKV_BASE_URL=https://beyondpassports.co.uk` and no `UKV_INTL_*` keys yet.
- Stripe production is in TEST mode and `StripeService` hardcodes GBP in three places (gap analysis A18).
- Ops: no non-UK VAC account exists; SA VFS tenant returned 403 to automation; SA rate-limit behaviour unconfirmed (playbook 0b; feasibility premise 2). `portal-accounts-setup.md` shows UK accounts still mid-registration.
- Compliance: SA Legal Practice Act memo exists (2026-09-05), "not legal advice until an attorney has reviewed it". No attorney named anywhere in the repo. No AE/US/CA memo yet (being drafted today).
- SP2 to SP6 specs: being drafted 6 Oct; need owner approval before build.

### 1.2 Realistic effort for the SP1 plan (one engineer with an AI pair, TDD as written)
| Task | What | Estimate | Risk note |
|---|---|---|---|
| 0 | Merge `feat/sa-market-phase1`, migrate, run merged suites | 1h | run the full suite not just four filters |
| 1 | Four-market config + `.env.example` | 1h | none |
| 2 | `Market` value object + 9 unit tests | 1.5h | none |
| 3 | `market_url()` helper + composer autoload `files` | 1h | host needs `composer dump-autoload` on deploy (3.5) |
| 4 | `ResolveMarket` middleware, route group, `/south-africa` 301 | 2h | trailing-slash judgement call |
| 5 | Trust strip, null-safe price partial, market home | 3h | owner copy review vs service brief section 7 |
| 6 | Hub stub, 29-destination constant | 2h | none |
| 7 | Market tours route, enquiry-only | 2h | depends on `tours-body` behaving without prices |
| 8 | Market-aware chrome | 3h | touches every UK page; visual diff of 5 UK pages |
| 9 | Canonicals + reciprocal hreflang | 4h | `trustProxies` host rewriting judgement call |
| 10 | `/sitemap-intl.xml`, robots | 2h | robots.txt static, shared by both hosts |
| 11 | dataLayer `bp_market`, CRM beacon field | 1.5h | add Clarity custom tag (11b, 0.5h) |
| 12 | `.com` root chooser, switcher + cookie | 3h | `APP_URL` in phpunit.xml judgement call |
| 13 | Full suite, runbook, spec status | 2h | none |
| | Deploy day: alias domain, env, autoload, caches, smoke | 4h | first time the app answers on two hosts |
| | **Total** | **~33h = 4 working days** | 8, 9, 12, 13 Oct at a safe pace, or 7 to 9 Oct hard |

SP2 to SP5 are additional and cannot start before approval on 8 Oct. Sizing: SP2 tours catalogue 3 to 5 days; SP3 template 3 to 4 days plus 1 to 2 days verified content per destination; SP4 board 2 to 3 days and useless without logged VAC rows; SP5 payments 3 to 5 days, blocked by Stripe live GBP (A18) and the accountant memo (A17); SP6 is external professionals with unknown turnaround.

### 1.3 The three scope options
**Option A (recommended): foundation + `.com` live, four markets enabled but noindex, South Africa indexable.**
Ships: SP1 complete; `beyondpassports.com/` chooser; `/za`, `/ae`, `/us`, `/ca` served with `X-Robots-Tag: noindex, nofollow`; ZA flipped to indexable on 15 Oct with home, hub stub, tours enquiry page; plus the first verified SA destination pages IF the SP3 template is approved 8 Oct and built by 13 Oct. Payments stay WhatsApp quote. No ad spend on any new market.
Caveats: (1) destination pages are the stretch; fallback = ZA indexable with home + hub + tours only. (2) G2's "price visible above the fold" is NOT met on 15 Oct; the price partial renders nothing rather than a placeholder. (3) Stage gate (iii) "one live local-currency charge" cannot pass before SP5. See 1.5.

**Option B: A plus Canada indexable.** Rejected. Breaks the locked launch order (ZA, AE, then US + CA after US-hours reply capacity). Canada needs reply-hours (8am to 8pm ET = 1pm to 1am UK), a second ops identity (F3), the Quebec French rule, and no CA VAC check exists. Indexing CA without those is the Atlys "generated but not served" pattern we said we avoid.

**Option C: everything SP1 to SP5 for all four markets.** Not achievable with quality in nine days:
- Content: 116 pages at 3,000 to 4,500 unique words = 350,000 to 520,000 words of verified content. Operator matrix 116 cells, 2 verified (A19). Publishing unverified fee tables violates the locked rule.
- Payments: Stripe in test mode, GBP hardcoded (A18); accountant memo (A17) does not exist.
- Compliance: UAE licensing is "memo before any dirham of spend"; US seller-of-travel and CA TICO/OPC/BC memos need attorneys. Four memos reviewed by four local professionals in nine days is a hope, not a plan.
- Ops: no non-UK VAC account in any market; a board per market renders "enquire" x29 (A4). US/CA hours need a night shift.
- Engineering: SP1 4 days; SP2 to SP5 11 to 17 days. 15 to 21 engineer-days in 7 working days does not close.

### 1.4 Recommendation
Option A, destination-page stretch marked "ship if verified by 13 Oct, else noindex". Delivers the structural edge nobody else has (reciprocal hreflang incl. `.co.uk` and x-default, A8), proves the two-host architecture, lets ops and compliance run gates in parallel, puts the first-mover market live before Atlys adds en-ZA (A3). Spends no money, prints no unverified number.

### 1.5 One gate needs an owner ruling before the schedule is valid
Locked gate per market: (i) one logged VAC check per core destination, (ii) compliance memo reviewed by a local professional, (iii) one live local-currency charge. Option A cannot pass (iii) by 15 Oct because SP5 does not exist.
Proposed re-reading for the owner to accept or reject on 8 Oct:
- `ENABLED=true` (staged, noindex): no gate; invisible.
- `INDEX=true` (organic visibility, no price, no ads): requires (i) and (ii).
- Price in config AND any paid spend: requires (iii) plus the accountant memo (A17).
If the owner keeps the three-part gate as written, Option A becomes "foundation live, every market noindex on 15 Oct, ZA indexable when SP5 lands". Still a defensible milestone; just not "SA indexable". The schedule below assumes the re-reading is accepted.

---

## 2. Day-by-day schedule, 6 to 15 October (Option A)
Legend: OWNER = Usama; ENG = engineer + AI pair; OPS = appointments identity (second identity is open decision F3); COMP = compliance-research-agent drafting, local professional reviewing. Times UK.

### Tue 6 Oct
- OWNER: Confirm who holds `beyondpassports.com` and where its DNS lives (Q1). If at a registrar, move nameservers to the `.co.uk` Cloudflare account today. Calendar block 8 Oct for the five decisions (F1 to F5). Book the SA attorney consult (Q4), target 8 or 9 Oct.
- ENG: SP1 Tasks 0 to 3. Commit per task, no push (owner pushes).
- OPS: Fill the "evidence row per market" in Portal Accounts (F2): for ZA, AE, US, CA write "no account" with date.
- COMP: Send the 2026-09-05 LPA memo to the attorney with its section 7 questions plus: "Does opening a VFS South Africa portal account in the company's name, to check calendar availability without booking for a client, change the analysis?"

### Wed 7 Oct
- OWNER: SP2 to SP6 specs arrive; read SP3 and SP6 first. Approve or return by end of 8 Oct.
- ENG: Tasks 4 to 6. Run `php artisan test --filter='Lp|Availability|Sitemap|Market'` after Task 4.
- OPS: Prepare the SA account kit without creating accounts: forwarders `vfsza1@`..`vfsza4@`, `blsza1@`, `capagoza1@` on cPanel; read (do not submit) the VFS ZA registration form to learn whether a +27 mobile is needed for OTP (Q5).
- COMP: Draft the ZA POPIA notice line and the "Nobody can buy an earlier date" per-operator lines from SA deep dive section 7 for owner copy review.

### Thu 8 Oct (decision day)
- OWNER by 18:00: F1 Tourloom; F2 VAC accounts evidence; F3 second ops identity; F4 tours phase-1 model; F5 reply hours per market. Also the 1.5 gate ruling and the Stripe live-keys decision (Q3): recommend "decide yes, execute after 15 Oct".
- OWNER: Approve or return SP2 to SP6 specs. SP3 approval today makes the destination-page stretch possible.
- OWNER: Cloudflare `.com` zone (3.3): SSL Full (strict), Always Use HTTPS, Bot Fight Mode OFF, Super Bot Fight Mode OFF, no redirect rules. cPanel: add `beyondpassports.com` as an Alias of the `.co.uk` primary domain (3.2). Confirm AutoSSL.
- ENG: Tasks 7 to 9. Visual diff of `.co.uk` home, `/schengen-visa`, `/tour-packages`, one lp-* page, `/about` before and after Task 8.
- OPS: Prepare Slot Detail rows for ZA with the SA operator map (France Capago; Germany TLScontact; Spain BLS; Italy, Netherlands, Portugal, Switzerland, Austria VFS; Greece GVCW; Belgium TLScontact).
- COMP: Attorney call if booked; file the written answer as `docs/superpowers/research/2026-10-0X-sa-lpa-attorney-note.md`.

### Fri 9 Oct
- OWNER: Search Console Domain property for `beyondpassports.com` (DNS TXT). Do not submit the sitemap yet. GA4: add `.com` to cross-domain list and unwanted referrals; event-scoped custom dimension `bp_market` (3.7). GTM: Data Layer Variable `bp_market` on the GA4 config tag. Push the SP1 branch if green.
- ENG: Tasks 10 to 12 + Clarity custom tag (11b). Start SP3 template only if approved.
- OPS: If the attorney said yes: register ONE VFS South Africa account (Italy or Netherlands) with `vfsza1@`; ONE check per playbook 0b; log the row in Slot Detail with any error codes. This is the "SA tenant confirmed" evidence.
- COMP: If no attorney answer, escalate: critical external path for gate (ii).

### Sat 10 Oct
- ENG: Task 13. Tag the commit. Prepare deploy checklist (3.5) with the exact host path (Q2).
- OWNER: Review trust strip, home, hub copy on a tunnel preview. Check: no "30 minutes", no "guaranteed/early/priority/fast-track", no em-dashes, ZA positioning line as in the service brief.
- OPS: Second SA check if the first produced no 429 (BLS Spain: register `blsza1@`, log whether the calendar is visible pre-application).

### Sun 11 Oct
- Buffer. No deploys. ENG continues SP3 if approved. OWNER drafts the ZA Google Ads campaign PAUSED (3.9).

### Mon 12 Oct (staging deploy on the real domain, everything noindex)
- ENG + OWNER: Deploy per 3.5 with ZA/AE/US/CA `ENABLED=true`, all `INDEX=false`. Full smoke (3.10). `.co.uk` diff clean.
- OWNER: GSC URL Inspection live test on `/za` must show "Crawl allowed, page fetched, indexing not allowed (noindex)". Proves Cloudflare lets Googlebot through.
- OPS: Capago France: create a France-Visas account and log what is visible without the paid €32 slot; if nothing, log "gated, per client". Germany TLScontact SA: log "gated, per client". These complete gate (i) for the four core SA destinations.
- COMP: Attorney note filed or not. If not, owner decides whether to hold the 15 Oct INDEX flip (recommended: hold).

### Tue 13 Oct
- ENG: SP3 ZA destination pages if the template landed: France (Capago) and Spain (BLS) have the most verified rows. Ship `noindex` by default; owner flips per page after reading. Fix list from the 12 Oct smoke.
- OWNER: Read both pages against the deep dive rows. Every fee cell must trace to a verified row; anything marked [unverified] must not appear as a number.
- OPS: Update the Schengen Availability sheet bands for ZA from logged rows.

### Wed 14 Oct (go/no-go)
- OWNER chairs 30 minutes against section 4. Fails on gate (i) or (ii) mean ZA stays noindex and the milestone is "foundation live, four markets staged".
- ENG: Freeze. Record the pre-launch commit SHA on the host.
- OWNER: Final Cloudflare check, AutoSSL valid for apex and www, `www` redirects to apex.

### Thu 15 Oct (launch)
- 09:00 ENG + OWNER: `UKV_MARKET_ZA_INDEX=true`, `php artisan config:cache`, re-run smoke; `curl -sI https://beyondpassports.com/za` has no `X-Robots-Tag`; `.co.uk/schengen-visa` lists `hreflang="en-ZA"`; `/sitemap-intl.xml` lists ZA URLs.
- 09:30 OWNER: GSC `.com`: submit `sitemap-intl.xml`; Request indexing for `/za`, `/za/schengen-visa`, `/za/tour-packages` and any flipped destination page.
- 10:00 OWNER: Clarity segment `bp_market = za` saved; GA4 realtime by hostname shows the test visit. Lead Chats `[ZA]` filter view.
- Ads: ZA campaign stays PAUSED until price in config and gate (iii).
- 17:00: first daily watch entry (3.12).

---

## 3. Go-live runbook

### 3.1 DNS for `beyondpassports.com`
Cloudflare account fronting `.co.uk`. Records: `A @ -> <origin IP of the .co.uk cPanel host>` proxied; `CNAME www -> beyondpassports.com` proxied. Take the origin IP from the existing `.co.uk` A record, never from memory.
Done when: `dig +short beyondpassports.com` returns Cloudflare anycast IPs and the host answers (even with a default page) rather than NXDOMAIN.

### 3.2 Alias domain on the cPanel host
cPanel > Domains > Aliases > add `beyondpassports.com`. An alias shares the primary domain's document root: the same Laravel `public/` answers both hosts and the app decides by URL segment (spec 4.2), except the single `Route::domain` root route (spec 4.4). Do not create a new domain with its own document root. Fallback if no alias slots: "Create a New Domain" with document root pointed at the exact same `.../ukv-app/public` (Q2). AutoSSL: run for both names; if it fails behind the proxy, install a Cloudflare Origin CA certificate for both.
Done when: `curl -sI --resolve beyondpassports.com:443:<origin IP> https://beyondpassports.com/` returns a Laravel response and the chain validates.

### 3.3 Cloudflare settings for the `.com` zone
- SSL/TLS Full (strict); Always Use HTTPS; Automatic HTTPS Rewrites; Minimum TLS 1.2.
- Security > Bots: **Bot Fight Mode OFF. Super Bot Fight Mode OFF.** It blocked Google's ad-review crawler on `.co.uk`. Verified bots must never be challenged.
- WAF: mirror `.co.uk` managed rules; no custom rule challenging `/za`, `/ae`, `/us`, `/ca`. Security Level as `.co.uk`.
- Rules: no Redirect Rules or Page Rules at the root (spec 4.4). Only acceptable rule: `www` to apex 301 if cPanel does not already.
- Caching: Standard, respect origin headers. Do not cache HTML (X-Robots-Tag changes with flags). Do not copy any "Cache Everything".
- Speed: Rocket Loader OFF (reorders inline scripts). Auto Minify as `.co.uk`.
- Google tag gateway: if `ukv.ga4_fp_path` is set on `.co.uk`, the same path on `.com` 404s unless the gateway is enabled on the `.com` zone (Q12).
Done when: `curl -sI -A "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)" https://beyondpassports.com/za` returns 200 with no `cf-mitigated` header; GSC live test 12 Oct says "page fetched".

### 3.4 Environment flags (production `.env`)
```
UKV_INTL_BASE_URL=https://beyondpassports.com
UKV_MARKET_ZA_ENABLED=true
UKV_MARKET_ZA_INDEX=false        # true on 15 Oct after the go/no-go
UKV_MARKET_AE_ENABLED=true
UKV_MARKET_AE_INDEX=false
UKV_MARKET_US_ENABLED=true
UKV_MARKET_US_INDEX=false
UKV_MARKET_CA_ENABLED=true
UKV_MARKET_CA_INDEX=false
UKV_MARKET_ZA_WHATSAPP=
UKV_MARKET_ZA_PHONE=
```
Keep `APP_URL` and `UKV_BASE_URL` at `https://beyondpassports.co.uk`. Check `SESSION_DOMAIN` is unset (a `.beyondpassports.co.uk` value breaks sessions on `.com`); check `config/cors.php` and `SecurityHeaders.php` for hardcoded `.co.uk` in `form-action`/`connect-src` (Q13).

### 3.5 Deploy sequence on the host (shallow clone)
Record first: `cd <app path>; git rev-parse HEAD > ~/deploy-prev-sha-$(date +%F).txt`.
```
git fetch origin && git reset --hard origin/master
composer dump-autoload -o            # REQUIRED this release: Task 3 adds autoload "files"
php artisan migrate --force          # Task 0 brings add_market_to_supply_nodes
php artisan view:clear && php artisan cache:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan filament:optimize
```
If `composer.lock` changed, run `composer install --no-dev --optimize-autoloader` instead. Use the PHP CLI path the existing deploy routine uses (Q2). Cache config BEFORE routes (`Route::domain` resolves the host from cached config).
Done when: `php artisan about` shows production, debug off, config cached, routes cached; `php artisan route:list --name=intl.home` shows the `.com` domain.

### 3.6 Search Console
Domain property `beyondpassports.com`, DNS TXT verification (9 Oct). 15 Oct after INDEX=true: submit `sitemap-intl.xml`; Request indexing for the ZA URLs. `.co.uk` property unchanged (its robots.txt carries the second Sitemap line; cross-host references honoured once both verified). Validate hreflang with the curl checks in 3.10 and a third-party checker.
Done when: sitemap row "Success" with URL count equal to `/sitemap-intl.xml`.

### 3.7 GA4 and GTM
GA4 `G-KR93N3DF55`: Configure your domains add `beyondpassports.com`; unwanted referrals both hosts; event-scoped custom dimension `bp_market`. GTM `GTM-5DMLL4HR`: Data Layer Variable `dlv - bp_market` on the GA4 config tag; publish 9 Oct. Also pass `bp_market` on the direct `gtag('config')` call in `analytics-head` (Task 11, 15 min).
Done when: GA4 Realtime filtered by hostname `beyondpassports.com` shows `bp_market = za`.

### 3.8 Clarity by market
Keep project `xbfqfhmnvp`. Add `window.clarity && clarity("set", "bp_market", "<code>")` after the Clarity loader (Task 11b). Clarity filter by custom tag `bp_market = za`, save segment "ZA". Fallback: URL contains `beyondpassports.com/za`. Apply `clarity-lp-report-playbook.md` per market from 15 Oct.

### 3.9 Google Ads (built now, PAUSED until gate (iii) and a config price)
- One campaign per market geo. `ZA | Schengen | Search`, location South Africa "Presence", English, final URLs on `beyondpassports.com/za/...`, tracking template adds `utm_source=google&utm_medium=cpc&utm_campaign={campaignid}&bp_market=za`.
- UK campaigns: campaign-level negatives `south africa`, `johannesburg`, `cape town`, `dubai`, `uae`, `usa`, `canada`, `toronto` (G6).
- ZA keywords from verified demand: "schengen visa south africa", "schengen visa application", "schengen visa cost", "schengen visa price", "italy visa south africa", "france visa capago", "spain visa bls south africa". Avoid "appointment" as lead term in ZA (70/mo).
- US/CA future negatives: `etias`, `esta`, `do us citizens need a visa for europe`, `american citizens`, `us passport`, `canadian citizens`, `canadian passport`, `visa free`, `travel authorization`. CA: exclude Quebec until a French mirror exists.
- AE future: no "priority", "VIP", "urgent", "fast" anywhere; DET memo gates spend.
- Policy-safe wording. Say: "Not the government, not VFS, TLScontact, BLS or any embassy"; "You can apply without us on the official portal for the government and centre fees alone"; "Document preparation by a named consultant"; "We watch the calendar, you book on your own account"; service fee with govt and centre fees separate and not collected; CH 17331903 + ICO ZC197159 in footer; "Appointments are free and booked only on the official visa centre site, in your name". Never: "guaranteed", "guaranteed appointment", "early appointment", "priority appointment", "fast-track", "express", "VIP slot", "we get you a slot", dated promises, success/approval percentages, "beat the 37% rejection rate", counters, fabricated offices/registrations, "we submit on your behalf", "get you the visa", strike-through discounts, countdown timers, em-dashes.
- Landing-page rule before any ad: price visible above the fold in local currency (G2), "not the government" above the fold, disclaimer strip present, no [unverified] number.
Done when: ZA campaign exists Paused with the negatives and the name "DO NOT ENABLE before SP5 price + gate iii".

### 3.10 Smoke-test checklist (12 Oct noindex; again 15 Oct after the flip)
```
curl -sI https://beyondpassports.com/                      # 200, no X-Robots-Tag, chooser
curl -sI https://beyondpassports.com/za                    # 200; noindex header (12 Oct); header ABSENT (15 Oct)
curl -sI https://beyondpassports.com/ae                    # 200 + noindex
curl -sI https://beyondpassports.com/us                    # 200 + noindex
curl -sI https://beyondpassports.com/ca                    # 200 + noindex
curl -sI https://beyondpassports.com/xx                    # 404
curl -sI https://beyondpassports.com/ZA                    # 404
curl -sI https://beyondpassports.com/za/                   # 301 to /za, or 200 (plan Task 4 step 7)
curl -sI https://beyondpassports.com/south-africa          # 301, Location: /za
curl -sI https://www.beyondpassports.com/                  # 301 to apex
# disabled-market 404: set UKV_MARKET_CA_ENABLED=false + config:cache on 12 Oct, curl /ca -> 404, set back
curl -s https://beyondpassports.com/za | grep -o '<link rel="canonical"[^>]*>'                 # https://beyondpassports.com/za
curl -s https://beyondpassports.com/schengen-visa | grep -o '<link rel="canonical"[^>]*>'      # https://beyondpassports.co.uk/schengen-visa
curl -s https://beyondpassports.co.uk/schengen-visa | grep -o '<link rel="alternate"[^>]*>'    # en-GB + x-default; en-ZA after flip
curl -s https://beyondpassports.com/za/schengen-visa | grep -o '<link rel="alternate"[^>]*>'   # en-GB, en-ZA, x-default
curl -s https://beyondpassports.com/sitemap-intl.xml       # valid XML; empty (12 Oct); ZA URLs (15 Oct)
curl -s https://beyondpassports.co.uk/sitemap.xml | diff - sitemap-pre-deploy.xml   # no diff (save copy 11 Oct)
curl -s https://beyondpassports.com/robots.txt             # two Sitemap lines + AI crawler block
for p in / /schengen-visa /tour-packages /about /schengen-visa-agency; do curl -sI https://beyondpassports.co.uk$p | head -1; done
curl -s https://beyondpassports.co.uk/ | grep -o '<title>[^<]*</title>'                        # same as 11 Oct snapshot
curl -vI https://beyondpassports.com 2>&1 | grep -E 'subject:|expire date|server: cloudflare|cf-ray'
curl -sI -A "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)" https://beyondpassports.com/za | head -1   # 200
curl -sI -A "AdsBot-Google (+http://www.google.com/adsbot.html)" https://beyondpassports.com/za | head -1                       # 200
```
Browser: `/za` WhatsApp link opens `wa.me/<number>?text=...%5BZA%5D`; `dataLayer.find(e => e.bp_market)` returns `{bp_market:'za'}`; `.com/` chooser shows market cards + UK card; switcher cookie `bp_market=za` set after `/za`, banner appears on `/ae` and dismisses; `/admin` on `.com` behaves as on `.co.uk`; no CSP errors on `/za`; mobile render at 375px.
Done when: every line matches and GSC live test on `/za` says "page fetched".

### 3.11 Rollback
Level 1 (seconds): `UKV_MARKET_ZA_INDEX=false` or `ENABLED=false`, `php artisan config:cache`. If already indexed, prefer 200 + noindex over 404.
Level 2 (minutes): `git reset --hard $(cat ~/deploy-prev-sha-<date>.txt)` then `composer dump-autoload -o && php artisan config:cache && php artisan route:cache && php artisan view:clear && php artisan cache:clear`. Leave the Task 0 migration in place. No `migrate:rollback` during an incident.
Level 3: owner reverts on GitHub (`git revert`, push), then the standard deploy.
Level 4 (edge): unproxy or remove the `.com` A record. Last resort.

### 3.12 Post-launch watch list, 15 to 22 Oct (daily row in the Clarity sheet)
GSC `.com`: Indexed vs submitted; sitemap Success; URL Inspection `/za`, `/za/schengen-visa` days 1, 3, 7; "Blocked by robots"/"Excluded by noindex" on a ZA URL = bug. `.co.uk`: Indexed count must not drop; no new "Duplicate, Google chose different canonical". `site:beyondpassports.com` day 7: only `/`, `/za`, `/za/schengen-visa`, `/za/tour-packages` + flipped pages. hreflang checker days 1 and 7. Clarity ZA: sessions, quick-back, dead clicks, scroll to CTA, mobile share vs UK baselines. GA4 by hostname: `.com` by `bp_market`; `.co.uk` flat vs prior 7 days. WhatsApp `[ZA]` chats per day; reply time vs `support_hours`; first-question themes. Logs: grep `market_url`, `RuntimeException`; 5xx on `.com`; Cloudflare Security Events zero challenged verified bots. Ads: UK IS/CTR unchanged; ZA Paused. Week-one exit: ZA home + hub indexed, zero `.co.uk` regressions, at least one `[ZA]` lead or an explicit "none" note.

---

## 4. RACI for every gate
R = does, A = signs off, C = consulted, I = informed. OWNER (Usama), ENG, OPS, COMP (agent drafts), PRO (SA attorney), ACC (accountant).

| # | Gate | Evidence | Date | R | A | C | I | Research |
|---|---|---|---|---|---|---|---|---|
| 1 | SP1 spec approved | status line in plan header | 6 Oct | OWNER | OWNER | ENG | all | foundation spec |
| 2 | SP2-SP6 specs approved/returned | written approval per spec | 8 Oct | OWNER | OWNER | ENG, COMP | OPS | gap analysis F; spec 13 |
| 3 | Five owner decisions F1-F5 | one line each, dated note | 8 Oct | OWNER | OWNER | OPS (F2, F3), COMP (F4) | ENG | gap analysis F1-F5; feasibility (c) |
| 4 | Gate re-reading (1.5) | owner line | 8 Oct | OWNER | OWNER | ENG, COMP | OPS | product-goals gates; feasibility (a) |
| 5 | Stripe live-keys decision | yes/no + date | 8 Oct | OWNER | OWNER | ENG | ACC | feasibility (c) row 3; A18 |
| 6 | DNS + alias + SSL | 3.1, 3.2 done-when | 8 Oct | OWNER | OWNER | ENG | | GO-LIVE-RUNBOOK Phases 1, 10 |
| 7 | Cloudflare `.com`, Bot Fight OFF | 3.3 checklist + Googlebot curl 200 | 8 Oct, 14 Oct | OWNER | OWNER | ENG | | bot-fight lesson; ads-lp-fixes-tasklist |
| 8 | SP1 code complete, suite green | `php artisan test` pass; 13 commits | 10 Oct | ENG | OWNER | | OPS | plan Tasks 0-13; spec 11 |
| 9 | UK regression check | visual + curl diff of 5 pages clean | 10, 12 Oct | ENG | OWNER | | | plan Task 8; spec 1 |
| 10 | Staging deploy, all noindex | 3.10 passes; GSC live test | 12 Oct | ENG | OWNER | | OPS, COMP | spec 4.1 |
| 11 | ZA gate (i): logged VAC check per core destination | 4 rows Slot Detail + Portal Accounts | 12 Oct | OPS | OWNER | COMP | ENG | G1; feasibility premise 2; playbook 0, 0b, 3, 4; SA deep dive 2 |
| 12 | ZA gate (ii): memo reviewed by local professional | attorney written note filed | 13 Oct | COMP | OWNER | PRO | ENG, OPS | service brief 10; LPA memo section 7 |
| 13 | ZA gate (iii): live ZAR charge | Stripe live ZAR payment + order | after SP5 | ENG | OWNER | ACC | OPS | feasibility (a), (c) rows 3, 9; A17, A18 |
| 14 | Destination pages verified (stretch) | every number traces to a verified row | 13 Oct | ENG | OWNER | COMP | | SA deep dive 2; service brief 12; README rules |
| 15 | Go/no-go | gates 6-12, 14 pass/fail with links | 14 Oct | OWNER | OWNER | ENG, OPS, COMP | all | this doc |
| 16 | ZA INDEX flip | no X-Robots-Tag on /za; en-ZA on .co.uk | 15 Oct 09:00 | ENG | OWNER | | OPS | spec 7 |
| 17 | GSC `.com` + sitemap-intl | property verified 9 Oct; sitemap Success 15 Oct | 9, 15 Oct | OWNER | OWNER | ENG | | spec 7 |
| 18 | GA4 + Clarity by `bp_market` | realtime + segment populated | 15 Oct | OWNER (UI), ENG (Task 11, 11b) | OWNER | | | spec 8; G4 target |
| 19 | Ads built, PAUSED | ZA campaign paused with negatives; UK negatives | 11 Oct | OWNER | OWNER | | ENG | G6, G2; service brief 10; US+CA deep dive 7 |
| 20 | Post-launch 7-day watch | daily row 15-22 Oct | 22 Oct | OWNER, OPS, ENG | OWNER | | | clarity-lp-report-playbook; lead-chats-log |

---

## 5. Open questions (exact question, who, by when)
| Q | Question | Who | By |
|---|---|---|---|
| Q1 | Is `beyondpassports.com` registered, at which registrar, and is its DNS a zone in the same Cloudflare account as `.co.uk`? Can nameservers move today? | OWNER | 6 Oct |
| Q2 | cPanel: primary domain, exact app path (`.../ukv-app`), document root (`.../ukv-app/public`), PHP CLI binary used by the deploy routine, free Alias slot? | OWNER / host support | 7 Oct |
| Q3 | Is Stripe production still in test mode, and are live GBP keys wanted the week after 15 Oct? | OWNER | 8 Oct |
| Q4 | Which SA attorney reviews the LPA memo, cost, written answer by 13 Oct? If none, hold the ZA INDEX flip (recommended) or proceed on the memo alone? | OWNER | 6 Oct book, 13 Oct answer |
| Q5 | Does VFS South Africa registration require a +27 mobile for OTP? Which number, whose identity (F3)? | OPS reads form 7 Oct; OWNER decides | 8 Oct |
| Q6 | Tourloom sister brand or not; UK auction re-cut before or after 15 Oct? | OWNER | 8 Oct |
| Q7 | Second ops identity: name, phone, time zone, start; or "none before 15 Oct" (acceptable for Option A, ZA is UTC+2) | OWNER | 8 Oct |
| Q8 | Tours phase 1: referral to a licensed SA operator (Trafalgar/Costsaver/Contiki en-za, Thompsons) or BP enquiry-only on the existing catalogue (Task 7 default)? | OWNER | 8 Oct |
| Q9 | ZA reply hours string: keep "UK business hours, replies same day" or "Replies within one business hour on WhatsApp" (only if OPS can keep it)? | OWNER with OPS | 8 Oct |
| Q10 | Does a GA4/Ads conversion exist for WhatsApp clicks on `.co.uk`; should it fire on `.com` with `bp_market`? | OWNER (GTM) with ENG | 9 Oct |
| Q11 | Does the Ads account have a Government Documents policy strike history that would hold a new campaign in review? | OWNER (Policy manager) | 11 Oct |
| Q12 | Is `ukv.ga4_fp_path` set in production; if yes, is the Google tag gateway configured on the `.com` zone? | ENG + OWNER | 12 Oct |
| Q13 | Does production `.env` set `SESSION_DOMAIN`; do `config/cors.php` or `SecurityHeaders.php` hardcode `.co.uk` in a way that breaks `.com`? | ENG | 12 Oct |

---

## 6. What this draft deliberately does not promise
- No price on any `.com` page on 15 Oct (owner sets in config after SP5 + accountant memo).
- No board data on `.com` until SP4 and at least one logged snapshot per market.
- No ad spend outside the UK (G6: UK impression share back to 19% first).
- No AE, US or CA visibility; enabled noindex for internal review only.
- No page called "verified" unless each number traces to a verified row in the 2026-10-06 deep dives.
