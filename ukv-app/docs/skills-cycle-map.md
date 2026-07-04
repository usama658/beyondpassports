# Skills mapped across the content cycle

Owner reference. Every installed skill mapped to the cycle stage / architecture
component it serves (`docs/social-automation-cycle.md`). Skills are invoked by Claude
in-session; they augment the built commands + code, they don't run on the server cron.

## Inventory source
- Global: `~/.claude/skills` · Project: `.claude/skills`.
- Marked ✅ = directly useful here · ➖ = tangential/other-model · ❌ = not applicable
  (Shopify/ecom/WordPress — WP was dropped).

---

## Stage 1 — Plan / Research
| Skill | Role |
|---|---|
| **semrush-keyword-research** ✅ | secondary keyword demand (alt to Ahrefs MCP) — refresh `config/content_research.php` |
| **google-trends** ✅ | rising searches → newsjack timing (EES/ETIAS/Chancenkarte) |
| **seo-competitor-pages** ✅ | what ranks for rivals → content gaps |
| **seo-plan** ✅ | topic-cluster / silo planning |
| **research** ✅ | Reddit/Quora + open-web question mining |
| **firecrawl-lead-research** ✅ | scrape competitor/market pages for angles |
| **find-skills** ✅ | discover skills not yet loaded |
| Ahrefs MCP (not a skill) ✅ | primary GSC queries + secondary demand — feeds both research commands |

## Stage 2 — Produce · Copy
| Skill | Role |
|---|---|
| **landing-page-copywriter** ✅ | post copy, hooks, captions at quality |
| **seo-content** ✅ | long-form (guides) that repurpose to carousels/reels |
| **lead-magnets** ✅ | design the free tools posts drive to (checklist/eligibility) |
| **draft-outreach / cold-outreach** ✅ | DM + comment outreach copy (LinkedIn/IG) |

## Stage 2 — Produce · Graphics / Image
| Skill | Role |
|---|---|
| **open-images-for-free-use** ✅ | license-safe stock photos (beats hand-picked Unsplash) |
| **seo-images** ✅ | sourcing/optimising imagery for posts + guides |
| **nano-banana-edit** ✅ | AI image edit / background swap (paid RunComfy CLI) — the "Canva tweak" gap |
| **frontend-design / ui-ux-pro-max / web-design-guidelines** ✅ | brand-consistent layout principles for code-rendered graphics |
| Claude code (HTML→PNG + PIL) ✅ | actual static-graphic render, in-repo |

## Stage 2 — Produce · Carousels / Decks
| Skill | Role |
|---|---|
| **google-slides** ✅ | generate carousel/deck sequences programmatically |

## Stage 2 — Produce · Video
| Tool | Role |
|---|---|
| Claude code — **Remotion** ✅ | animated/templated reels (code, no skill exists) |
| FFmpeg | slideshow / caption-burn |
| CapCut (human) | live-footage edit — no skill covers this |

## Stage 3 — Schedule
| Skill/tool | Role |
|---|---|
| **google-sheets** ✅ | the content-log (plan + archive + KPI) as a live sheet |
| Buffer + GA Campaign URL Builder | scheduling + UTM (no skill; free tools) |

## Stage 4 — Distribute + engage
| Skill | Role |
|---|---|
| **cold-outreach / draft-outreach** ✅ | proactive DM/connection outreach (LinkedIn) |
| **research** ✅ | find Reddit/Quora threads to answer |
| (engagement stays human — never automate) | |

## Stage 5 — Capture
| Skill / built | Role |
|---|---|
| **lead-magnets** ✅ | the checklist/eligibility magnets posts convert to |
| **firecrawl-lead-gen** ➖ | external prospecting (B2B, secondary) |
| Built (app) ✅ | checklist/eligibility tools, WhatsApp CTA, HubSpot sync — live |

## Stage 6 — Nurture
| Skill / built | Role |
|---|---|
| **cold-outreach** ✅ | DM follow-up sequences |
| Built (`ukv-emails` + HubSpot) ✅ | email drip fires on submit — live |

## Stage 7 — Measure
| Skill | Role |
|---|---|
| **looker-studio** ✅ | build the LinkedIn/social KPI dashboard from GA4 |
| **google-sheets** ✅ | content-log KPI columns + monthly report |
| **seo-audit / seo-technical** ✅ | site-health that affects conversion of the traffic |

## Cross-cutting — Ads (paid, native managers)
| Skill | Role |
|---|---|
| **google-ads-manager** ✅ | search/PMax campaigns (complements Meta + LinkedIn) |
| **google-ads-product-research** ✅ | keyword/audience research for paid |
| **google-ads-segmentation** ✅ | audience structure |
| **google-ads-performance** ✅ | optimise live campaigns |
| **google-ads-revival** ✅ | fix underperforming/paused campaigns |

## Cross-cutting — Organic SEO (feeds the content engine)
| Skill | Role |
|---|---|
| **seo-plan / seo-page / seo-programmatic** ✅ | build the long-stay + refusal silos (source of repurposable content) |
| **seo-schema / seo-sitemap / seo-technical / seo-audit** ✅ | technical SEO health |
| **seo-geo** ✅ | AI-search / GEO optimisation |
| **seo-hreflang** ➖ | only if multi-language later |

## Infra / meta
| Skill | Role |
|---|---|
| **build / monitor / optimize** ✅ | dev workflow for the automation commands |
| **decomposition-planning-roadmap / cross-cutting** ✅ | planning larger builds |
| **improve-codebase-architecture** ✅ | refactor the content commands as they grow |
| **ukv-parallel-build** ✅ | project's own parallel-build skill |

## Not applicable (this project)
❌ shopify-store-builder, ecom-niche-research, ecom-store-plan, micro-saas-launcher,
t01-populator, wordpress-publisher (WP dropped), vercel-react-best-practices (no React),
gbp-reinstate (only if Google Business Profile suspended).

---

## The complete cycle, skill-mapped
```
1 Plan       semrush-keyword-research · google-trends · seo-competitor-pages · research · Ahrefs MCP
             → feeds: content:research (primary+secondary built commands)
2 Produce    copy: landing-page-copywriter · seo-content · lead-magnets
             image: open-images · seo-images · nano-banana-edit · frontend-design + code(HTML→PNG)
             carousel: google-slides · video: Remotion(code) / CapCut(human)
3 Schedule   google-sheets (content-log) + Buffer + UTM builder
4 Engage     cold-outreach · draft-outreach · research  (+ human)
5 Capture    lead-magnets + built app (checklist/eligibility/WhatsApp/HubSpot)
6 Nurture    cold-outreach + built ukv-emails drip
7 Measure    looker-studio · google-sheets · seo-audit
Ads          google-ads-* (search/PMax) + Meta/LinkedIn native
SEO          seo-plan/page/programmatic/schema/technical/geo (organic feed)
```

## Read
- Every cycle stage has at least one skill or built component. No blind spots except
  **live-footage video edit** (CapCut, human — no skill/tool automates it).
- Skills run **in-session (Claude)**; the built Artisan commands run **on the server**.
  Use skills to refresh inputs (keyword bank, competitor scan) + produce assets; the
  commands turn them into ranked topics deterministically.
