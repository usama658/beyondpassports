# Skills integration protocol — maintain quality every batch

Owner reference. Skills run **in-session (Claude)**, not on the server cron — so
"integration" = a fixed protocol that fires the right skill at the right cycle point
**every batch**, plus quality gates. The automated commands (monthly cron) enforce the
deterministic half; this protocol enforces the skill-assisted half.

## Two quality layers
- **Deterministic (auto, no skill)** — `content:research` (+primary/secondary) runs
  monthly and enforces the same rules every run: UTM standard, product-fit ranking,
  primary uplift, newsjack "verify" flags, compliance notes baked in the config.
  Nothing to maintain — it can't drift.
- **Skill-assisted (batch day, Claude)** — the human/Claude layer. Needs this protocol
  so quality is identical each batch.

---

## Batch-day protocol (run in order, every month)

### Step 0 — Refresh inputs (before the cron output is trusted)
| Trigger | Skill / tool | Quality gate |
|---|---|---|
| **Every batch** (current-affairs) | **WebSearch** (official + gov.uk) + LinkedIn-trend check | rewrite `storage/app/content-research/signals.json` with the *verified* headline + fact + `verified` date. This is what makes newsjacks current-affairs-grounded, not stale — the command merges a signal over any matching keyword (real fact replaces the vague angle + a rank boost). Signals >45 days old are auto-ignored. |
| Keyword bank is >30 days old | **semrush-keyword-research** or Ahrefs MCP | update `config/content_research.php` + bump `refreshed` date |
| Rule change suspected | **google-trends** + WebSearch | any EES/ETIAS/Chancenkarte shift → update `signals.json` (primary) + bank `fresh` flags |
| New competitor angle | **seo-competitor-pages** / **firecrawl-lead-research** | log gaps as candidate topics |

> **signals.json is the live-trends bridge.** The server cron can't WebSearch; Claude refreshes this file each batch and the deterministic command reads it. No refresh = newsjacks fall back to the (dated) bank angles and go stale. Refresh it first, every batch.

### Step 1 — Generate topic list
| Trigger | Command | Quality gate |
|---|---|---|
| Batch day | `php artisan content:research --limit=30` | output has both engines represented; newsjacks flagged |

### Step 2 — Produce · copy
| Trigger | Skill | Quality gate |
|---|---|---|
| Each topic → caption/script | **landing-page-copywriter** | brand voice; no em-dashes; no approval guarantee (DMCCA) |
| Repurpose a guide | **seo-content** | fact-checked against the guide (already verified) |
| Lead-magnet CTA | **lead-magnets** | points to a real live tool (checklist/eligibility) |

### Step 3 — Produce · visuals
| Trigger | Skill / code | Quality gate |
|---|---|---|
| Static graphic | Claude code (HTML→PNG) + **frontend-design / ui-ux-pro-max / web-design-guidelines** | ukv.css tokens (petrol/teal/ink, Outfit); brand-consistent |
| Photo needed | **open-images-for-free-use** | license verified before use |
| Photo edit | **nano-banana-edit** | subject/brand preserved |
| Carousel/deck | **google-slides** or code | ≤7 slides; one idea per slide |
| Animated reel | Remotion (code) | 3-sec hook; captions burned |

### Step 4 — Fact + compliance gate (MANDATORY, every asset)
| Check | Skill / process |
|---|---|
| Visa facts current? | route through guide freshness (`destinations:freshness`) + **research** for anything time-sensitive |
| No approval/visa guarantee? | manual review — "cut refusal risk" OK, "removes"/"guaranteed" NOT (DMCCA) |
| Tours enquiry-only? | no priced sale until ATOL |
| Long-stay = prep/assist, not legal advice? | check framing (RDG) |
| Stats real? | only SiteStats numbers; no fabricated figures |
| Newsjack verified? | the CSV flags it — confirm current rule before posting |

### Step 5 — Schedule
| Trigger | Tool | Quality gate |
|---|---|---|
| Assets ready | Buffer + **google-sheets** content-log | every link UTM'd (`utm_source=<platform>`); logged as a row |

### Step 6 — Engage (human)
| Trigger | Skill | Quality gate |
|---|---|---|
| Proactive DM/outreach | **cold-outreach / draft-outreach** | on-brand, non-spammy, no guarantees |
| Reddit/Quora answers | **research** | answer genuinely; link sparingly |

### Step 7 — Measure
| Trigger | Skill | Quality gate |
|---|---|---|
| Weekly/monthly | **looker-studio** (GA4 dashboard) + **google-sheets** (KPI log) | report vs `docs/linkedin-kpis.md`; kill dead formats |

### Cross-cutting — Ads (when running paid)
| Skill | Gate |
|---|---|
| **google-ads-manager / -segmentation / -product-research / -performance / -revival** | native manager only; retarget with live Pixel; no approval-guarantee ad copy |

### Cross-cutting — Organic SEO (the content source)
| Skill | Gate |
|---|---|
| **seo-plan / -page / -programmatic / -schema / -technical / -audit / -geo** | build long-stay + refusal silos → repurposable social content; compliance same as above |

---

## The one non-negotiable
**Step 4 (fact + compliance gate) runs on EVERY asset, no exceptions.** It is what
keeps quality and keeps us legal. A missed fact-check or an approval-guarantee slip is
the only real quality failure that matters — everything else is polish.

## What guarantees consistency
- **Deterministic layer:** the cron command — identical output rules every run.
- **Skill layer:** this protocol — same skills, same order, same gates, every batch.
- **Compliance:** baked into config + enforced at Step 4.
Follow the protocol top-to-bottom each batch and quality can't drift.

## Honest limits
- Skills are invoked by Claude at batch time; they are NOT auto-run by the server cron.
- Live-footage video edit has no skill — CapCut, human.
- nano-banana-edit needs the paid RunComfy CLI; free fallback = re-render in code.
