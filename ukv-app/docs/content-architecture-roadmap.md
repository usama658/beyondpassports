# Content architecture — state + optimization roadmap

Owner reference. Snapshot 2026-07-05. Captures what the content pipeline does today
and the prioritised optimizations for future runs (lessons from the first live batch).

## Current state (working, committed)
- `content:research` (primary + secondary + **live signals**) → ranked topics CSV.
  - Primary = refusal taxonomy + demand from our DB.
  - Secondary = keyword bank; **signals.json** overlays verified current-affairs facts
    (Claude refreshes each batch via WebSearch/tier-5; >45 days auto-ignored).
- `content:carousels` → 3-slide brand PNGs; **self-QA legibility probe** (GD) flags
  blank / low-contrast text and exits FAILURE.
- `content:batch` → one prompt: research → carousels → QA. ~100s for 4 carousels (SLA <300s).
- Scheduled: `content:research` monthly (routes/console.php).

## Lessons from the first live batch (baked in or to bake in)
1. **Weak copy** — deterministic captions read generic. Copy is the **skill layer**
   (landing-page-copywriter), not a template. Producer should hand the skill a *scaffold*,
   not a finished caption.
2. **Not current-affairs** — fixed by signals.json (verified facts, not the stale bank).
3. **Cite tier-5 only** — gov.uk / eu-LISA / europa.eu / ABTA. Pulse "expert" posts are
   often stale (a live one still said ETIAS €7/2025). See linkedin-competitors-and-sources.md.
4. **Channel routing** — LinkedIn = reach/reposts (B2B trade), IG + communities = consumer
   leads/comments. See niche-audience-analysis.md.
5. **Render bugs are invisible to the writer** — needed a machine probe (contrast/blank).
   Self-QA now does this; extend it.

## Optimization backlog (prioritised)
| # | Optimization | Value | Effort | Notes |
|---|---|---|---|---|
| 1 | **Caption scaffold per topic** — producer writes `caption.txt` (hook + verified fact + honest disclaimer + CTA + UTM + platform hashtags), tagged "SCAFFOLD — polish w/ landing-page-copywriter". | High — closes the hand-written-caption gap; ships a complete post package. | Low | Read `Link (UTM'd)` + Platform from topics CSV; hashtag map from niche-engaging-posts.md. |
| 2 | **Channel + archetype column** — tag each topic with recommended platform + archetype (alert / listicle / story / myth-bust). | High — routes content per audience finding. | Low | Add to research output. |
| 3 | **Story archetype generator** — story posts drive comments (audience analysis); current gen only does hook/body/CTA. | Med | Med | New slide template; consented cases only (Content P14). |
| 4 | **Parallel browser renders** — 4 carousels = ~100s serial; render slides concurrently. | Med (speed) | Med | Bounded worker pool around shot(). |
| 5 | **Extend QA probe** — add overlap detection + min-text-per-slide + WCAG ratio (not just Δlum). | Med | Low | Build on probe(). |
| 6 | **Auto-log to content-sheet** — append each produced topic to the content-log CSV/Sheet with status. | Med | Low | google-sheets skill at schedule stage. |

## The non-negotiables (never drift)
- Step-4 fact + compliance gate on EVERY asset (no approval guarantee — DMCCA; tier-5 facts;
  SiteStats numbers only; tours enquiry-only pre-ATOL). See skills-integration-protocol.md.
- Signals + captions are skill/Claude-refreshed in session; the Artisan commands are the
  deterministic spine. Don't put un-verifiable facts in the deterministic layer.

## Related docs
- skills-integration-protocol.md · skills-cycle-map.md · social-automation-cycle.md
- linkedin-competitors-and-sources.md · niche-engaging-posts.md · niche-audience-analysis.md
