# Social automation cycle — Beyond Passports

Owner reference. The loop that runs social with minimal daily hand-work, mapped onto
the tools already in this stack. Rule at the bottom: automate distribution + capture +
nurture + reporting; NEVER automate real engagement.

## The loop (7 stages)

```
1 Plan ─→ 2 Produce ─→ 3 Schedule ─→ 4 Distribute+Engage ─→ 5 Capture ─→ 6 Nurture ─→ 7 Measure
  ^                                                                                        │
  └────────────────────────────── feeds back (weekly/monthly) ────────────────────────────┘
```

### 1. Plan (monthly, batch)
- Campaign calendar → weekly topics from `docs/social-campaigns.md`.
- One session: 15–20 posts planned at once. Batching is the biggest time-saver.

### 2. Produce (AI-assisted, repurpose)
- 1 pillar → many: one guide → carousel + 3 reels + Pinterest pin + LinkedIn post +
  Reddit answer.
- Source material = the refusal taxonomy + destination data + guides.

### 3. Schedule (set-and-forget)
- One scheduler pushes to all platforms, queued 2–4 weeks ahead.
- Every outbound link auto-UTM'd (`utm_source=<platform>`).

### 4. Distribute + engage (semi-auto)
- Auto-post fires. Engagement stays HUMAN — reply <60 min in the first hour.
- Reddit/Quora: manual, answer real questions.

### 5. Capture (fully auto)
- Click → UTM'd link → site tool (checklist / eligibility) → WhatsApp enquiry.
- Lead → HubSpot, tagged by source. No manual entry.

### 6. Nurture (auto)
- New social lead → existing drip: checklist follow-up → refusal-risk tip → soft CTA.
- WhatsApp: templated first-reply; human takes qualified ones.

### 7. Measure → feed back (weekly/monthly)
- GA4 by `utm_source` → reach → tool starts → enquiries → orders.
- Weekly: kill dead formats, double winners. Feeds stage 1 next month.

---

## Tool map — what's already built vs what to add

| Stage | Need | Already in this stack | To add |
|---|---|---|---|
| 1 Plan | calendar | `docs/social-campaigns.md` | content calendar sheet (Notion/Sheets) |
| 2 Produce | source + design | guides engine (`GuideController`, guides table), refusal taxonomy (#73), destination data, tour photos (`public/assets/tours`) | Canva brand templates |
| 3 Schedule | multi-platform poster + UTM | — | **scheduler: Metricool / Buffer / Publer** (pick one) |
| 4 Engage | human replies | — | calendar block, 30–60 min/day |
| 5 Capture | tool → lead | checklist tool (`ChecklistController`, `checklist_requests`), eligibility/apply, WhatsApp CTA (`SiteStats::chatUrl`), floating WA button (`partials/wa-float`) | UTM on every profile/post link |
| 6 Nurture | drip | email lifecycle (`ukv-emails`, queued mailables in `resources/views/emails/*`), HubSpot sync (`L3.1`), checklist follow-up email | WhatsApp template first-reply |
| 7 Measure | attribution | GA4 + GTM + Meta Pixel (consent-gated, `partials/site-scripts` + `cookie-consent`), conversion events, GSC | GA4 exploration filtered by `linkedin`/`instagram`/etc. |

### Concrete wiring
- **UTM standard:** `?utm_source=<platform>&utm_medium=social&utm_campaign=<campaign>`.
  Profile links + every scheduled post link. GA4 auto-parses.
- **Lead source tag:** HubSpot contact property = first-touch `utm_source` (already
  syncing via the HubSpot integration; ensure the UTM is captured on the checklist /
  apply form submit).
- **Nurture trigger:** checklist/eligibility submit already fires the queued email
  sequence (`ukv-emails`). New social leads inherit it automatically — no new build.
- **WhatsApp:** `SiteStats::chatUrl($msg)` prefills a section-relevant message; the
  social CTA links reuse it. Set real `UKV_WHATSAPP` first (still on fallback).
- **Reporting:** GA4 → Acquisition → Traffic acquisition → filter by `utm_source`;
  Weekly glance, monthly full report against `docs/linkedin-kpis.md`.

## Automated vs human
| Automated | Human (keep) |
|---|---|
| Scheduling / posting | First-hour engagement |
| UTM tagging | Reddit / Quora answers |
| Lead capture → HubSpot | Qualifying enquiries |
| Nurture emails / WA templates | Strategy + monthly review |
| Reporting (GA4) | Batch content approval |

## Cadence
- **Monthly:** plan + batch-produce + schedule (~1 day).
- **Daily:** 30–60 min engagement only.
- **Weekly:** 30-min metrics glance.

## The one missing piece
Stack already covers capture + nurture + reporting + content source. The only gap is
a **scheduler** (Metricool/Buffer) + the **batch-content habit**. Pick the scheduler,
standardise the UTM, and the loop runs.

## Rule
Automate distribution + capture + nurture + reporting. NEVER automate real engagement —
LinkedIn/Meta flag bot comments, and trust is the product.
