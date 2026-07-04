# Content research automation — Beyond Passports

Owner reference. Pipelines that auto-surface WHAT to post about, so batch day starts
with a ranked topic list, not a blank page. Feeds stage 1 (Plan) of
`docs/social-automation-cycle.md`. Two engines: primary (your own data) + secondary
(external market). Primary > secondary for conversion.

## Principle
- **Primary research** = your own data. Highest-converting topics — only you can make
  them (real refusals, real FAQs). Weight heaviest.
- **Secondary research** = the market. Reach + trend topics (keyword demand, competitor
  gaps, rising searches, rule changes).
- **Always verify facts** (visa rules change) via the guide freshness check before posting.

---

## PRIMARY research — your own data

| Source | Where (in stack) | Auto-surfaces | Automatable |
|---|---|---|---|
| Refusal taxonomy | rejection-reason table (#73) | each reason → a "why visas fail #N" post | ✅ DB read |
| WhatsApp / contact questions | inbox + HubSpot notes | real FAQs = highest-intent posts | ⚠️ semi (HubSpot export) |
| Checklist / eligibility inputs | `checklist_requests` table | which destinations/visas asked about → what to feature | ✅ DB read |
| GA4 + GSC search queries | Ahrefs GSC tools (`gsc-keywords`, `gsc-pages`, `gsc-page-history`) | what people search to find you = proven topics | ✅ Ahrefs MCP |
| Order patterns / outcomes | orders DB | trending problems, popular destinations | ✅ DB read |
| Guide performance | guides table + GSC pages | which guides get traffic → repurpose to social | ✅ |

**Automation:** monthly script → top refusal reasons + top GSC queries + new checklist
destinations + popular guides → ranked topic list into the content-log
(`docs/content-log-template.csv`) as "Idea" rows. Claude can build this (reads DB +
Ahrefs GSC).

---

## SECONDARY research — external market

| Source | Tool / skill | Auto-surfaces | Automatable |
|---|---|---|---|
| Keyword demand | Ahrefs MCP (`keywords-explorer-matching-terms`, `-related-terms`) + `semrush-keyword-research` skill | high-volume low-KD topics (Spain DNV, Chancenkarte, D7) | ✅ |
| Trends / timing | `google-trends` skill | rising searches → newsjack timing | ✅ |
| Competitor content | `firecrawl-lead-research` + `seo-competitor-pages` skills | what ranks for rivals → gaps to fill | ✅ |
| Reddit / Quora questions | `research` skill + browser (claude-in-chrome) | real unanswered questions = post + answer | ✅ |
| Rule changes (EES/ETIAS/Chancenkarte) | WebSearch + guide freshness/change-detection engine (#245) | newsjack topics, big reach | ✅ |
| SERP / question boxes | Ahrefs `serp-overview`, "people also ask" | question-format post ideas | ✅ |

**Automation:** monthly Ahrefs pull (new low-KD terms in the visa/long-stay cluster) +
Google Trends check + competitor scan + rule-change search → topic candidates.

---

## Combined loop
```
PRIMARY (your data: refusals, FAQs, GSC, orders) ─┐
                                                   ├─→ ranked topic list
SECONDARY (market: Ahrefs, Trends, competitors) ──┘        │
      ↑                                                     ▼
      │                                    content-log "Idea" rows (intent × volume)
      │                                                     │
      └────── GA4 / native metrics feed back ──────────────┘
              (winning angles → make more)
```

## Ranking (how topics are prioritised in the list)
Score = **intent** (primary data beats keyword) × **volume/reach** (secondary) ×
**product fit** (Schengen prep / tours / long-stay) × **freshness** (newsjack bonus).
Primary-sourced topics get a fixed uplift — they convert.

## Cadence
- **Monthly:** full pull — primary (DB + GSC) + secondary (Ahrefs + Trends + competitor)
  → ~20-topic ranked list. Runs on batch day.
- **Weekly:** scan Reddit/Quora + news for 1–2 newsjacks.
- **Continuous:** log GSC queries + WhatsApp FAQs as they arrive.

## Tool/skill inventory (what's available now)
- **Ahrefs MCP:** keywords-explorer, matching/related terms, GSC (keywords/pages/history),
  serp-overview — primary GSC + secondary demand.
- **Skills:** `google-trends`, `semrush-keyword-research`, `seo-competitor-pages`,
  `firecrawl-lead-research`, `research`, `seo-content`, `seo-audit`, `find-skills`.
- **In-stack data:** rejection taxonomy, `checklist_requests`, orders, guides table,
  HubSpot, GA4/GSC.
- **Claude code:** DB reads + Ahrefs calls + WebSearch → ranked CSV output.

## What Claude can automate now
- ✅ Primary: script over DB (refusal taxonomy, checklist destinations, orders, guide
  traffic) + Ahrefs GSC queries → ranked topic list.
- ✅ Secondary: Ahrefs keyword pulls, Google Trends, competitor scan, WebSearch
  rule-changes → topic candidates.
- ✅ Merge + rank → content-log "Idea" rows.

## Compliance
- Verify every visa fact before posting (rules change) — route through guide freshness.
- No fabricated stats; numbers from SiteStats.
- Long-stay = prep/assist framing, not legal advice.

## Next build options
1. Primary-research script: reads DB + GSC → ranked topics into content-log.
2. Live secondary pull now (Ahrefs + Trends) → 20 ready topics.
3. Wire both into a monthly command that outputs the batch-day list.
