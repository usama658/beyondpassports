# Visual rebrand — warm-light "Sunset Coast" design

**Why:** the current navy + gold + Fraunces-serif + Space-Mono passport-MRZ system reads as the generic "AI-generated premium" cluster (navy/gold/serif + cream backgrounds are the most over-used auto-design defaults). Investors liked a competitor (breakoutholidays.co.uk) for its bright, photographic, friendly travel-brand feel. This rebrand moves Beyond Passports to a warm, human, photo-led identity that is distinctive (not a Breakout clone) and not "AI-looking".

Chosen via visual-companion brainstorm: Direction A (warm/photo-led) → Terracotta & Sage accents → cool-grey background (cream rejected as the AI tell).

## Design tokens (replace the current :root in public/assets/ukv.css)
- **Primary / CTA:** terracotta `#C75D38` (hover `#b04e2c`)
- **Secondary:** sage `#5C9A7B` (deep sage `#3f7259` for text-on-light)
- **Highlight:** warm amber `#E0894F` (sparingly)
- **Body background:** cool neutral grey `#F4F5F6`; **sections/cards:** white `#FFFFFF`
- **Ink text:** `#22282b`; muted `#697079`; hairline `#e6e8ea`
- **Type:** `Plus Jakarta Sans` for display + body (weights 400/600/700/800). Remove Fraunces + Space Mono.
- **Radius:** cards/inputs 14–16px, pills 999px. **Shadow:** soft `0 18px 44px -26px rgba(40,50,70,.30)`.
- Rating chip: white pill, `★ 4.9`, ink text. Price: "from £X" in terracotta-700.

## Removed (the AI-cluster signals)
- Navy `#0A2540` + gold `#C8A24A` palette
- Fraunces serif headings + Space Mono
- Passport-MRZ strips (`.mrz`, `P<GBR<...`) and boarding-pass "stub/MRZ code" gimmickry
- The navy `hp-hero` band added earlier (replace with the light terracotta/sage hero)

## Components
- **Hero:** light (white→faint grey), big Plus Jakarta headline (ink), terracotta eyebrow, rounded white "where are you going?" checker card with soft shadow, a photo/teal panel, trust row (★ 4.9 · UK-based · fixed fees). Terracotta CTA.
- **Destination cards:** photo header + rating chip + flag, Plus Jakarta name, "from £X" terracotta, sage/teal hover. Rounded 16px.
- **Sections:** alternate white / faint sage-or-grey bands. Rounded step cards for "how it works". CTA band = terracotta→amber gradient (or deep-sage) with white text.
- Keep ALL existing copy + compliance lines + structure/sections; this is a re-skin, not a re-architecture.

## Imagery
Photo-led design needs destination photos. Add a nullable `image_path`/`image_url` to destinations + admin field; fallback to the current per-destination gradient tint when absent. Source real photos from a free-commercial-licence pool (Unsplash/Pexels licence) self-hosted, OR owner-supplied — owner decision, not blocking the re-skin (gradients stand in until then).

## Page scope (re-skin all public pages)
home · destinations index · /visa/{slug} money pages · /visa/{slug}/{topic} guides · guides index · tools · document-checklist + result · find-a-centre · driving-abroad · apply · track · documents · confirmation · compare · reviews · about · contact · legal · emails. Admin (Filament) keeps its own theme — out of scope.

## Build approach
Token-first: rewrite `public/assets/ukv.css` `:root` + core component classes to the new system (most of the site inherits from it). Then sweep page-level inline styles/blades that hardcode navy/gold/MRZ (hero, brand markup, mrz strips, checker stub, the earlier hp-hero, schema/seo brand colours). Swap the Google Fonts link to Plus Jakarta Sans. Add destination image field + fallback. Verify every public page renders + 141 tests stay green + a11y contrast holds (terracotta-on-white, white-on-terracotta).

## Reversibility
Current theme is in git history; this lands as a distinct commit (revertable). No DB-destructive changes (only an additive image column).
