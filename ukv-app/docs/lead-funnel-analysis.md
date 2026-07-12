# Lead-funnel analysis — Beyond Passports

_Analysis of the sitewide lead funnel: the three-rung lead ladder, per-page coverage,
and the gaps to close. Last reviewed: 2026-07-12._

## The lead ladder (3 rungs, by commitment)

1. **R1 — WhatsApp** ("Check eligibility, free"). Instant, zero-form. Floating button sitewide
   + inline CTAs. Highest volume, lowest intent. Owner reached via the WhatsApp inbox.
2. **R2 — free document checklist** (`/document-checklist`). Value-first lead magnet: the visitor
   gets a tailored checklist, we capture an email/phone → HubSpot + owner inbox
   (`NewChecklistLead`). This is the **email-capture engine** and the leakiest rung.
3. **R3 — apply / callback**. `/apply` (order → Stripe, or manual-review callback) and `/contact`
   (human callback). The money rung. Both notify the owner (`NewApplication`, contact email).

**Guiding rule:** every page should offer the *right rung for where the visitor is* — an instant
option (R1) and a capture option (R2) so nobody leaves without either talking to us or giving an
email. Don't push R3 at an R1 visitor.

## Funnel matrix — every public page × the 3 rungs

Legend: present = yes, missing = no, — = not applicable.

| Page | Stage | R1 WhatsApp | R2 Checklist | R3 Apply/Call | Closing CTA | Gap | Priority |
|------|-------|:---:|:---:|:---:|:---:|-----|:---:|
| Home | entry | yes | yes | yes | yes | none | — |
| Guide detail | TOFU | yes | yes | yes | yes | none (template to copy) | — |
| Guides hub | TOFU | yes | **no** | no | yes | no email capture | **P1** |
| Reviews | TOFU | yes | **no** | yes | yes | no checklist | **P1** |
| Compare | TOFU | yes | **no** | yes | yes | no checklist | **P1** |
| LP bold / fear / refused | TOFU (paid) | yes | **no** | appt form | no | no email fallback beyond appt form | P2 |
| Schengen hub | MOFU | yes | **no** | no | yes | no checklist, no apply | **P1** |
| Destination detail | MOFU | yes | **no** | yes | yes | no per-country checklist | **P1** |
| Find-a-centre | MOFU | yes | no | yes | yes (added) | checklist optional | P3 |
| Tools hub | MOFU | yes | no | yes | yes | should surface checklist tool | P2 |
| Driving-abroad (IDP) | MOFU | yes | — | — | yes | fine (IDP is not an apply funnel) | — |
| Document-checklist | MOFU magnet | yes | yes (self) | soft | yes | the magnet itself | — |
| Apply | BOFU | yes | yes | yes | yes | none | — |
| Contact | BOFU | yes | — | yes (callback) | — | none | — |
| Track | RETAIN | yes | — | upload yes | — | fine | — |
| Confirmation | RETAIN | **no** | — | checkout yes | — | no "questions? WhatsApp" | P3 |
| Documents | RETAIN | yes | — | — | — | fine | — |

## Gap totals by rung

| Rung | Pages with it | Pages missing it (that should have it) |
|------|:---:|-----|
| R1 WhatsApp | 16/17 | confirmation only (minor) |
| **R2 Checklist** | **4/17** | guides-hub, reviews, compare, LPs, schengen-hub, dest-detail, tools (7) |
| R3 Apply / Call | 11/17 | schengen-hub (browse-only, acceptable) |

## Gaps by funnel stage

### TOFU (attract) — the big leak
- Checklist lead-magnet missing on **guides-hub, reviews, compare, all LPs**. These pages offer
  only WhatsApp (instant) or apply (too-high intent). The researcher who is not ready to chat and
  not ready to buy leaves with **no email captured** — the majority of top-funnel traffic.
- Guide *detail* pages are the exception (they carry the checklist) — proof the pattern works; it
  just is not rolled out.

### MOFU (qualify) — second leak
- Checklist missing on the **schengen hub, destination money pages, tools, IDP**. MOFU is exactly
  where "what documents do I need for Greece?" intent peaks — the tailored checklist is the perfect
  capture here and it is absent. A destination page should offer "Get your free Greece checklist".
- Hub has no direct apply CTA (browse-only) — minor; the search-to-destination path works.

### BOFU (convert) — clean
- Apply + contact both solid. No gaps.

### RETAIN (post-order) — minor
- Confirmation page has no "questions? WhatsApp us" reassurance (small trust gap post-payment).
- Review-request is backend/email-driven — fine.

### NURTURE (cross-stage) — structural gap
- Captured leads (checklist, contact) land in HubSpot + the owner inbox, but there is **no defined
  follow-up sequence** pushing checklist-takers toward apply. It is passive. A "you got your
  checklist, ready to start?" nurture would climb the ladder.

## The one dominant conclusion

The funnel has two strong rails — **talk-to-us (R1)** and **buy (R3)** — but a broken middle rung.
**R2 (email capture via the free checklist) is missing from the entire top and most of the middle
of the funnel.** Fixing R2 across those 7 pages is the single highest-leverage move.

## Priority order to close gaps

1. **P1 — roll the checklist lead-magnet block onto TOFU + MOFU**: guides-hub, reviews, compare,
   schengen-hub, destination detail. One reusable partial. Biggest email-capture win.
2. **P2 — mid-page inline capture** on long pages (guides, destination detail) to catch scrollers;
   surface the checklist tool on the tools hub; add an email fallback to LPs.
3. **P3 — nurture sequence**: checklist-taker to apply. Plus a "questions? WhatsApp" line on the
   confirmation page.

## Notes / caveats

- The grep-based scan under-counts R1 on destination detail (it reaches WhatsApp via
  `SiteStats::chatUrl()` / the `wa-cta` partial, not a raw `wa.me` string) — corrected to "yes"
  in the matrix above.
- LP pages are intentionally single-goal (paid-traffic WhatsApp appointment capture via
  `lp-appt-form`); the R2 gap there is a secondary-capture opportunity, not a dead end.
- Related shipped work: `/find-a-centre` CTA band, `/documents` orphan fixes, `/visa/schengen`
  drafted, all form owner-notifications — see git history around 2026-07-12.
