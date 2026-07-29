# Ads / ASA Compliance Sweep — Task Log

Public-site honesty + Google Ads / ASA compliance work on **Beyond Passports**
(beyondpassports.co.uk). All changes committed + pushed to `origin/master`.
Reversible drafting uses env flags (default OFF) so nothing is destructive.

> **Deploy note:** `routes/web.php` uses closures, so `php artisan route:cache`
> **errors**. Use `route:clear`. Full deploy for flag/config changes:
> `cd ~/beyondpassports && git pull && cd ukv-app && php artisan config:cache && php artisan route:clear && php artisan view:clear`

## Completed (user-numbered tasks)

| # | Task | Commit(s) |
|---|------|-----------|
| 1 | Sitewide disclosure copy (private consulting, not a govt intermediary) | — |
| 2 | Funnel pages drafted OFF (`/track` `/apply` `/documents` `/confirmation` → WhatsApp) | — |
| 3 | Order-ref prefix `UKV-` → `BP-` | — |
| 4 | "Registered in England and Wales" + UK-only flag | — |
| 5 | Removed accompaniment implication + false OISC claim + "AI" | — |
| 6 | "reviews your documents, checking funds" (not "reviews funds") | — |
| 7 | Removed unsubstantiated "642 refusal letters" (draft saved) | — |
| 8 | Heading "We help you with everything" | — |
| 9 | Reply-time claims → "within 2 hours" (incl. config) | — |
| 10 | Appointment time-of-day picker toggled OFF (`UKV_APPT_TIME_PICKER`) | 462c03d |
| 11 | 3 path-chip descriptions keyword-optimized (lp-bold) | b0d6d39 |
| 12 | Legal draft/solicitor placeholders hidden behind flag (`UKV_LEGAL_DRAFT_NOTICE`) | 10384eb |
| 13 | Legal `[to complete]` filled from config (Terms + Privacy) | 8565df5, 9fcab22 |
| 14 | Flights removed from tour packages (ATOL) + visa claims softened | 4c52b73, 3631bb4 |
| 15 | "UK & Europe" location claims → England and Wales (code) | 1b61ac0 |
| 16 | "Professionally insured" claim drafted out of About | 1ea4f51 |
| 17 | "live Trustpilot score" clause removed from testimonials note | 07c0caa |
| 18 | Indicative-availability disclosure under appointment board | 4994b53 |
| 19 | `/services` page drafted OFF (`UKV_SERVICES_ENABLED`) + gated links/sitemap | 341dabf |
| 20 | Reviews cleanup — non-Schengen product names + em-dash/AI-tell removed | c19443f, 667e9ef |
| 21 | `/tools` visa checker drafted OFF (`UKV_TOOLS_ENABLED`) → WhatsApp redirect + gated nav/sitemap | 9c32e25 |
| 22 | Home appointment finder de-numbered (dropped synthetic 2122/84 counts, kept chip design) | 24b00f4, 0c04d4e, 1b1bac0 |
| 23 | "Apply yourself vs us" section confirmed OFF (`/compare` drafted, redirects home) | — |
| 24 | Tour bullets → "Schengen visa documents prepared" | fc67162, 45fd923 |
| 25 | Removed "Biometric appointment booked" from tour packages | 9642000 |
| 26 | Tours "prepare the visa" → "prepare the visa documents" (hero + how-it-works) | 9b93b69, 56dd2b1, ec9ddff |

## Reversible flags (env, default OFF)

| Flag | Effect when true |
|------|------------------|
| `UKV_APPT_TIME_PICKER` | Bring back the day → time-of-day step in the slot modal |
| `UKV_LEGAL_DRAFT_NOTICE` | Show the "draft / have a solicitor review" legal banners |
| `UKV_SERVICES_ENABLED` | Relaunch `/services` hub + its nav/footer/sitemap links |
| `UKV_TOOLS_ENABLED` | Relaunch `/tools` visa checker (stops the WhatsApp redirect) |
| `UKV_COMPARE_ENABLED` | Relaunch `/compare` ("Apply yourself vs us") |
| `UKV_TRACK/APPLY/DOCUMENTS/CONFIRMATION_ENABLED` | Relaunch the drafted funnel pages |

## Open (not numbered)

- **Admin `/admin` login** — `app:make-admin` command shipped (commit 6bf7f32); run on the server: `php artisan app:make-admin admin@beyondpassports.co.uk`.
- **Live-DB About items** — "With offices in the UK and Germany" (and possibly "Professionally insured") are CMS content in the prod DB, **not in git**. Coded fixes don't clear them; edit in the admin panel or via a DB find/replace.
- **lp-trust** (`/honest-schengen-visa-service`) — still has "UK & Europe Registered", "Frankfurt office", and fabricated approval/"applications submitted" stats. Not yet fixed.
- **Main-footer "Apply yourself vs us" link** (NavService) — ungated; currently redirects home while `/compare` is off. Could gate on `ukv.compare.enabled`.

## Deferred (user decision)

- **Testimonials** — the 6 quotes are fabricated marketing copy (staff-linked reviewer, fake order refs, "approved" outcomes).
- **"15+ years combined casework"** — unsubstantiated stat.
- **Refund "free next application"** — legal-vs-marketing mismatch.

## Other pending infra (unrelated to this sweep)

- Prod scheduler cron `schedule:run` (#196) — without it `slots:provision` + purges don't fire.
- Token/password rotation (#367); ICO fee registration (#215); real WhatsApp number (#339).
