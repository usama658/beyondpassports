# Social cycle — tool setup guide

Owner reference. How to set up each free tool in the automation cycle
(`docs/social-automation-cycle.md`). Buffer chosen; ads + history handled natively
(`docs/social-tools-comparison.md`).

---

## 0. Content-log sheet (the spine — set up first)
Import `docs/content-log-template.csv` into Google Sheets (File → Import → Upload).
It is the plan calendar + post archive + KPI tracker in one.

**Columns:** Date · Status · Platform · Campaign · Format · Hook/Title · Product ·
CTA · Link (UTM'd) · Impressions · Reactions · Comments · Saves · Shares · Link
clicks · Enquiries · Notes.

**Use it as:**
- *Plan:* fill Date→Link during the monthly batch. Status = Idea → Draft → Scheduled → Posted.
- *Archive:* every post logged, permanent (fixes Buffer's short history).
- *KPI tracker:* fill the metric columns weekly from native analytics + GA4.

**Handy formulas (add a summary tab):**
- Engagement rate: `=(Reactions+Comments+Saves+Shares)/Impressions`
- Enquiries by source: `=SUMIF(Platform,"LinkedIn",Enquiries)`
- Posts this month: `=COUNTIFS(Date,">="&DATE(2026,7,1),Status,"Posted")`

Colour Status (conditional formatting): Idea grey, Draft amber, Scheduled blue, Posted green.

---

## 1. Buffer (scheduler)
- Sign up free at buffer.com → connect **3 channels: LinkedIn, Instagram, Facebook**.
- Set a posting schedule (e.g. LinkedIn 8am Tue/Thu, IG 6pm daily).
- Queue 2–4 weeks ahead during the monthly batch.
- Every link pasted must already be UTM'd (see §4).
- Pinterest / Reddit / Quora: post manually (not on the free 3 channels).

## 2. Canva Free (design)
- Set up a **Brand Kit**: logo (`public/assets/brand`), petrol `#155E7A`, teal `#2E9A8C`,
  ink `#16222E`, Outfit font.
- Build reusable templates: carousel (refusal reasons), quote card, destination card,
  checklist PDF. Duplicate per post — don't design from scratch.
- Export: 1080×1350 (IG/LI portrait), 1080×1080 (square), 1080×1920 (reel cover).

## 3. CapCut (video/reels)
- Free. Templates for short reels; add captions (auto-caption), trending audio.
- Keep a 3-second hook (the refusal fear) — first frame decides the scroll.
- Export 1080×1920, <60s for reels.

## 4. UTM links (attribution — non-negotiable)
Google Campaign URL Builder (ga-dev-tools.google → Campaign URL Builder). Standard:
```
https://beyondpassports.co.uk/<landing>?utm_source=<platform>&utm_medium=social&utm_campaign=<campaign>
```
- `<platform>`: linkedin | instagram | facebook | tiktok | pinterest | reddit | quora
- `<campaign>`: refusals | tours | refused-before | move-to-europe
- `<landing>`: document-checklist | tools | tour-packages | destinations
- Profile bio link uses `utm_campaign=profile`.
GA4 auto-parses. Read: GA4 → Acquisition → Traffic acquisition → filter `utm_source`.

## 5. Ads (native, when ready — pay spend only)
- **Meta Ads Manager** (business.facebook.com): retargeting "refused before" using the
  live Pixel; lookalike from checklist leads. Start £5–10/day.
- **LinkedIn Campaign Manager**: sponsor a top long-stay post to UK + expat audiences.
- Never in Buffer — always the platform's own manager.

## 6. Capture + nurture (already built — nothing to set up)
- Site tools: document-checklist, eligibility/apply, WhatsApp CTA (`SiteStats::chatUrl`).
- Lead → HubSpot (sync live), first-touch `utm_source` captured on form submit.
- Nurture drip: `ukv-emails` sequence fires on checklist/apply submit — social leads
  inherit it automatically.
- ⚠️ Set real `UKV_WHATSAPP` first (still on fallback), or WhatsApp CTAs go nowhere.

## 7. Measure (weekly)
- Native analytics → fill content-log metric columns.
- GA4 by `utm_source` → tool starts + enquiries.
- Weekly 30-min glance; monthly full report vs `docs/linkedin-kpis.md`.

---

## Setup order (do in this sequence)
1. Content-log sheet (import CSV).
2. UTM standard locked (§4).
3. Buffer + 3 channels.
4. Canva brand kit + 4 templates.
5. Set real `UKV_WHATSAPP`.
6. Batch first 2 weeks of posts → queue.
7. Ads later, only once organic proves a format.
