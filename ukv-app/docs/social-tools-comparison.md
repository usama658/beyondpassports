# Social scheduler comparison + free stack — Beyond Passports

Research date: 2026-07-04 (verified via vendor pages). Decision: **Buffer free** to
start; **Postiz self-host** when volume outgrows the free cap.

## Free scheduler comparison (2026)
| | Metricool | **Buffer (chosen)** | Publer | Postiz (self-host) |
|---|---|---|---|---|
| Cost | Free | Free forever | Free | Free (open-source, AGPL-3.0) |
| Scheduled posts | 20/mo total | 10 per channel (×3 = 30) | 10 per account (×3 = 30) | Unlimited |
| Channels | 1 brand | 3 | 3 | 30+, unlimited |
| LinkedIn | ❌ excluded | ✅ | ✅ | ✅ |
| X / Twitter | ❌ excluded | ✅ | ❌ excluded | ✅ |
| IG / FB / TikTok / Pinterest / YT | ✅ | ✅ | ✅ | ✅ |
| Analytics | ✅ 30d + 5 competitors (best) | basic | basic | basic |
| AI captions | ✅ | ✅ | ✅ (+Canva) | ✅ (+image gen) |
| Ad management | ✅ Meta+Google | ❌ | ❌ | ❌ |
| Post history | 30d analytics | ~24h | 24h | full |
| Catch | **no LinkedIn/X** | 3 channels only | no X | needs hosting/setup |

## Why Buffer (not Metricool)
- Metricool free **excludes LinkedIn** — and LinkedIn is the #1 platform for the
  long-stay play. Disqualified unless we drop LinkedIn (we don't).
- Buffer free: LinkedIn ✅ + Instagram + Facebook (our top 3), 30 posts/mo, zero setup.
- Post the long-tail (Pinterest / Reddit / Quora) manually.

## Free-tier reality
Plan wants 4–5 posts/wk ≈ 90/mo. Buffer free caps ~30/mo across 3 channels. Options:
- **Now:** Buffer for top 3, manual for the rest. Good enough to start.
- **Scale:** self-host **Postiz** on our existing cPanel/LiteSpeed server — unlimited
  posts, 30+ platforms incl LinkedIn + Reddit, AI built in. Removes every cap. $0 +
  setup/maintenance. The only truly uncapped free option.

## Filling Buffer's two gaps (both free, both better than a bundled tool)

### Ad management — run natively, never in the scheduler
- **Meta ads** → Meta Ads Manager (free tool; pay only ad spend). Meta Pixel already
  live + consent-gated, so retargeting ("refused before") works day one.
- **LinkedIn ads** → LinkedIn Campaign Manager (free tool, pay spend).
- Ads are always run in the platform's own manager. Metricool's "ad management" perk
  is irrelevant — we'd run ads natively regardless.

### Post history / analytics — three free layers
1. **Native analytics** (free, full history): Meta Business Suite (FB+IG), LinkedIn
   post analytics, TikTok / YouTube / Pinterest built-in. Buffer's short window
   doesn't matter — the platforms keep the history.
2. **GA4** (already live): the history that pays — UTM'd clicks → checklist /
   eligibility starts → enquiries. Real post-performance record.
3. **Content-log sheet** (free): one row per post (date, platform, campaign, link,
   impressions, saves, clicks). Manual archive + doubles as the KPI tracker from
   `docs/linkedin-kpis.md`.

Net: Buffer = scheduling only. Ads → native managers. History → native + GA4 + sheet.

## Full free stack (£0/mo)
| Need | Free pick |
|---|---|
| Schedule (top 3) | **Buffer** (LinkedIn + Instagram + Facebook) |
| Schedule (uncapped, later) | Postiz self-hosted |
| Design | Canva Free (brand kit) |
| Video | CapCut |
| Content / repurpose | Claude / ChatGPT free |
| UTM links | Google Campaign URL Builder |
| Attribution | GA4 (live) |
| Ads | Meta Ads Manager + LinkedIn Campaign Manager (pay spend only) |
| Capture / nurture / CRM | app tools + `ukv-emails` + HubSpot Free |
| Post-history log | Google Sheet |

## Pay only when it works
- Buffer/Metricool paid (~£15–18/mo) once >3 channels or >30 posts justified — or
  jump straight to Postiz self-host for $0.
- Meta ad spend — the one thing worth paying for (retargeting the refused).

Sources: metricool.com/pricing, metricool.com/metricool-vs-buffer, publer.com blog,
github.com/gitroomhq/postiz-app, postory.io free-buffer-alternatives.
