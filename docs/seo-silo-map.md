# Beyond Passports — comprehensive SEO silo & service map

> **⚠️ ACTIVE PIVOT (2026-06-24): SCHENGEN-ONLY.** The public site now focuses solely on Schengen
> visas. Non-Schengen content is being **unpublished (404/redirect)**: all non-Schengen destinations
> (Turkey, Egypt, India, USA/ESTA, Australia, Thailand, UAE, Vietnam), the IDP / driving-abroad silo,
> and other travel authorisations (ETIAS/ETA/ESTA/e-visas). `/destinations` becomes the **Schengen
> visa page**. This is a deliberate temporary narrowing — the silos/destinations below are retained
> in this doc and in code (config/seeder) so we can restore other destinations later on request.
> Silos still in scope: Schengen destinations, visa types, appointments, documents, refusals,
> tools, guides, nationalities, embassy-rule, pricing, B2B. Out of scope for now: #6 travel
> authorisations, #7 driving/IDP, #8 travel essentials (non-Schengen parts).

**Compiled:** 2026-06-24 · Maps every service surfaced in `competitors.md` (yours + 12 rivals) into a hub-and-spoke information architecture.
**Model:** prevention-led, Schengen-led, UK outbound facilitation (NOT UK immigration advice). Each silo = one pillar/hub page + topical spokes that interlink only within the silo, all linking up to the hub; hubs cross-link sparingly.
**Legend:** ✅ live · 🟡 partial/exists in part · 🔴 to build · ⭐ Beyond Passports differentiator vs rivals.

---

## Silo overview (14 silos)
| # | Silo | Hub URL | Primary intent | Status |
|---|---|---|---|---|
| 1 | Destinations (money) | `/destinations` | transactional — "X visa from UK" | ✅ |
| 2 | Visa types | `/schengen-visa/{type}` | type-led — "tourist/business/study Schengen visa" | 🔴 |
| 3 | Appointments | `/appointments` | "Schengen appointment UK / express / near me" | 🟡 |
| 4 | Documents | `/documents` | "what documents / checklist / cover letter" | 🟡 |
| 5 | Refusals & prevention | `/visa-refused` | "refused / why refused / avoid refusal" ⭐ | 🔴 (spec'd) |
| 6 | Other travel authorisations | `/travel-authorisation` | ETA/ETIAS/eVisa | 🟡 |
| 7 | Driving abroad (IDP) | `/driving-abroad` | IDP / driving permit | ✅ |
| 8 | Travel essentials | `/travel-essentials` | insurance, itinerary, passport validity, entry rules | 🔴 |
| 9 | Tools & trust | `/tools` | checker, tracker, calculators, reviews, about, legal | ✅ |
| 10 | Guides & stories | `/guides` | informational top-of-funnel | ✅ |
| 11 | Audiences & nationalities ⭐ | `/schengen-visa/uk-residents` | "Schengen visa for {nationality} in UK" | 🔴 |
| 12 | Apply-through / embassy rule | `/which-country-to-apply` | "which embassy / main destination rule" | 🔴 |
| 13 | Pricing & how-it-works ⭐ | `/pricing` | "cost / fees / is it worth it" (transparency) | 🟡 |
| 14 | B2B / travel agents | `/for-travel-agents` | partner/white-label referrals | 🔴 |

---

## SILO 1 — Destinations (money pages) ✅
**Hub:** `/destinations` (region tabs: Schengen/ETIAS, popular, rest of world)
**Spoke (money):** `/visa/{country}` — e.g. `/visa/france`, `/visa/turkey`
**Sub-spokes (per-country topics):** `/visa/{country}/{topic}` — topics already wired:
do-i-need-a-visa · documents · passport-validity · processing-time · how-to-apply · cost · when-to-apply · children · refused · uk-residents · transit · visa-on-arrival · entries · driving · health
**Maps rivals' services:** per-country Schengen visas (Aviva, SVCn 31, SVA/SVS/FPH 27), worldwide visas (CIBT/VHQ/Scott/Aviva).
**To build:** 🔴 expand beyond 8 destinations (#236); add the 19 missing Schengen + top worldwide (US ESTA, India, UAE, Canada, Australia) as eVisa/authorisation money pages where BP facilitates.

## SILO 2 — Visa types 🔴
**Hub:** `/schengen-visa` (what a Schengen visa is, the 9 sub-types)
**Spokes:** `/schengen-visa/tourist` · `/business` · `/family-visit` · `/study-short` · `/medical` · `/cultural` · `/airport-transit` · `/long-stay` · `/multi-entry`
**Maps rivals:** the 8–9 visa-type lists every small agent publishes (SVA, SVCn, SVS, Scott's C/D, Aviva multi-entry, BOH single/double/multiple).
**Cross-link:** each type page → relevant country money pages + documents silo.
**⭐ Angle:** each type page leads with the *specific refusal risks* for that type (prevention).

## SILO 3 — Appointments 🟡
**Hub:** `/appointments` (how Schengen appointments work, VFS/TLS, timing, express reality)
**Spokes:**
- `/find-a-centre` ✅ (postcode → nearest centre)
- `/appointments/express` 🔴 — honest "express speeds handling not the decision" ⭐
- `/appointments/{city}` 🔴 — London / Manchester / Edinburgh / Birmingham (rivals name these)
- `/visa/{country}/appointment` 🟡 (per-country appointment availability chips)
**Maps rivals:** appointment booking (all), express booking (IAS/CIBT/Scott/Aviva/SVA/SVCn/SVCy), VFS (SVA/SVCn), multi-city centres (SVCn), accompaniment (Scott).
**⭐ Angle:** live availability chips + "grab a slot before it's gone".

## SILO 4 — Documents 🟡
**Hub:** `/documents` (the documents that actually get applications refused)
**Spokes:**
- `/document-checklist` ✅ (interactive tool → token result)
- `/documents/cover-letter` 🔴 (cover-letter service — TL has it) ⭐
- `/documents/financial-evidence` 🔴 (funds — top refusal cause)
- `/documents/photo-requirements` 🔴
- `/documents/travel-insurance-proof` 🔴 (€30k min — TL/SVS reference)
- `/documents/accommodation-itinerary` 🔴 (flight/hotel itineraries — SVS/SVCy)
- `/documents/review-service` 🔴 (paid document review/pre-check — SVS/CIBT/FPH/BOH) ⭐
- `/documents/authentication-legalisation` 🔴 (VHQ/CIBT/Scott FCO)
**Maps rivals:** document review (IAS/CIBT/SVS/FPH/BOH/TL), pre-check (SVS), cover letter (TL), authentication (VHQ/CIBT/Scott), guide-for-documents (SVA).
**⭐ Angle:** AI + human document review = the prevention core; "Four checks before you submit".

## SILO 5 — Refusals & prevention 🔴 (spec'd: #323–328)
**Hub:** `/visa-refused` (refused or worried about refusal — what to do)
**Spokes (reason taxonomy #73):** `/reasons/{reason}` —
insufficient-funds · unclear-purpose · weak-ties · incomplete-documents · invalid-insurance · passport-validity · previous-refusal · overstay-risk · inconsistent-info · wrong-embassy · itinerary-issues …
**Per-country:** `/visa/{country}/refused` (×8 live, extend to all)
**Evergreen:** `/visa-refused/reapply-after-refusal` · `/visa-refused/appeal-vs-reapply`
**Maps rivals:** refusal review/recovery (IAS legal, BOH, TL, SVCy "high approval"), refusal-risk assessment (BOH).
**⭐ Angle:** THE silo nobody owns. Prevention-first; honest (no "100% approval"); positions BP as the refusal-prevention specialist. Highest strategic value.

## SILO 6 — Other travel authorisations 🟡
**Hub:** `/travel-authorisation`
**Spokes:** `/etias` 🔴 (EU ETIAS when live) · `/eta-uk-travellers` 🟡 (other countries' ETAs) · `/evisa/{country}` 🟡 (e-visa facilitation — Turkey, India, Egypt…) · `/esta-usa` 🔴 (Aviva offers) · `/eta-australia` 🔴 (CIBT offers)
**Maps rivals:** ESTA (Aviva), e-visa/online visa (Aviva), ETA Australia (CIBT), ETIAS guidance (Aviva).
**⭐ Angle:** clarity on "ETA has no document / it's an authorisation not a visa" (honesty memo).

## SILO 7 — Driving abroad / IDP ✅
**Hub:** `/driving-abroad`
**Spokes:** IDP types (1926/1949/1968), by-country driving rules, licence-type aware checker.
**Maps rivals:** none of the visa agents offer this — ⭐ unique adjacency / extra revenue.

## SILO 8 — Travel essentials 🔴
**Hub:** `/travel-essentials` (everything beyond the visa that affects entry)
**Spokes:**
- `/travel-essentials/insurance` 🔴 (FCA-safe introducer signpost — NOT BP-charged) ⭐ compliant
- `/travel-essentials/passport-validity` 🟡 (exists as guide topic)
- `/travel-essentials/entry-requirements/{country}` 🔴 (vaccinations/transit/onward-ticket — gap-audit #141)
- `/travel-essentials/itinerary-planning` 🔴 (flight/hotel itineraries — SVS/SVCy)
**Maps rivals:** travel insurance (BOH/SVS), itineraries (SVS/SVCy), entry guidance (FPH).
**⭐ Angle:** prevention extends past the visa — "don't get turned away at the border".

## SILO 9 — Tools & trust ✅
**Hub:** `/tools`
**Spokes:** visa checker ✅ · document-checklist tool ✅ · `/track` status tracker ✅ · `/reviews` (Trustpilot) ✅ · `/about` (UK team) ✅ · `/contact` ✅ · `/compare` (apply yourself vs us) ✅ · `/legal` ✅
**Maps rivals:** quick-check (CIBT/VHQ), order status (CIBT/Scott), eligibility check (FPH/BOH/TL), refund/guarantees (SVCy — BP intentionally does NOT claim), 24/7 + WhatsApp (SVCy/BOH/SVS — BP has WhatsApp float ✅).
**⭐ Angle:** real Trustpilot (consent-gated), live tracker, free checker — transparency over claims.

## SILO 10 — Guides & stories ✅
**Hub:** `/guides` (informational top-of-funnel feeding all silos)
**Spokes:** per-country guide clusters, evergreen how-tos, traveller stories, comparisons/hubs.
**Cross-link:** every guide → its silo's hub + relevant money page (informational → funnel).

---

## SILO 11 — Audiences & nationalities 🔴 ⭐ (highest untapped SEO)
**Hub:** `/schengen-visa/uk-residents` (Schengen from the UK — for British + non-British residents)
**Spokes (by passport/nationality resident in UK — the real BP audience):**
`/schengen-visa/indian-passport-uk` · `/pakistani-passport-uk` · `/nigerian-passport-uk` · `/bangladeshi-…` · `/south-african-…` · `/filipino-…` · `/ghanaian-…` (top UK-resident visa-national groups)
**Sub-angles each page:** BRP/eVisa/pre-settled-status evidence, residence-permit rules, refusal risks specific to that nationality.
**Maps rivals:** none target this segment by nationality — pure white space. Matches BP eligibility router (nationality/residence) already built.
**⭐ Angle:** "Schengen visa for [nationality] living in the UK" — high-intent, low-competition, exactly who BP serves.

## SILO 12 — Apply-through / embassy rule 🔴
**Hub:** `/which-country-to-apply` (the "main destination" rule — which embassy handles your application)
**Spokes:** `/which-country-to-apply/multiple-countries` · `/equal-stay-rule` · `/first-entry-rule` · per-country "apply via {country}" where it's a common hub (France/Spain/Italy/Germany)
**Maps rivals:** TL "embassy selection guidance"; SVA "guide for embassy".
**⭐ Angle:** removes a top cause of wrong-embassy refusals → prevention.

## SILO 13 — Pricing & how-it-works 🟡 ⭐
**Hub:** `/pricing` (transparent fixed service fees + what's included vs the government fee)
**Spokes:** `/how-it-works` 🟡 · `/pricing/whats-included` · `/government-fees-explained` (disbursement vs service fee) · `/compare` (apply yourself vs us) ✅
**Maps rivals:** only Scott's (£250+VAT) and Aviva (£350–435) show price; most hide it.
**⭐ Angle:** transparency = trust + conversion edge; clearly separates service fee from govt fee (compliance).

## SILO 14 — B2B / travel agents 🔴
**Hub:** `/for-travel-agents` (white-label / referral for agencies + corporates)
**Spokes:** `/for-travel-agents/refer` · `/for-business` (corporate travel desks) · `/partners`
**Maps rivals:** SVS "trusted by travel agents"; CIBT/IAS corporate; Aviva business consultancy.
**⭐ Angle:** B2B referral channel competitors under-serve; recurring volume.

## Additions folded into existing silos
- **Silo 9 Tools** — add: `/tools/schengen-calculator` 🔴 (90/180-day calculator — strong linkable asset, no rival has it) · `/tools/fee-estimator` 🔴 · `/tools/processing-time` 🔴 · `/tools/photo-check` 🔴
- **Silo 10 Guides** — add `/glossary` 🔴 + `/faq` hub 🔴 (GEO / AI-citation surface) · visa-rule-change news feed (#138)
- **Silo 1/3 Group & family** — `/group-applications` 🔴 (family/group — BP group orders #82) · `/family-visa` cross-links from Silo 2
- **Silo 4 Documents** — add `/documents/bank-statement-requirements` + `/documents/sponsorship-letter` (top funds-evidence queries)

## Service → silo coverage check (nothing dropped)
| Competitor service (from audit) | Silo |
|---|---|
| Eligibility / requirements check | 9 (checker) + 1 |
| Form completion / "on your behalf" | 1/2 (core delivery, not a page — process) |
| Appointment booking / express / VFS / city / accompaniment | 3 |
| Document review / pre-check / cover letter / authentication / guide | 4 |
| Delivery: courier / collect / tracked return / Royal Mail | 3 (return) + process |
| Refusal review / recovery / risk / appeal | 5 |
| Refund assurance / high approval (claims) | 9 (BP omits — honesty) |
| Consultation / expert advice / 24-7 / WhatsApp | 9 |
| Travel insurance / itineraries / post-approval | 8 |
| Worldwide / non-Schengen / passport / A1 / corporate | 1 + 6 (BP: facilitation scope only) |
| ESTA / e-visa / ETIAS / ETA | 6 |
| Visa types (tourist…long-stay…multi-entry) | 2 |
| IDP / driving | 7 |
| Tools: quick-check, order status, sample visas, FAQs, info | 9 + 10 |

## Internal-linking rules
1. Spoke → its hub (every page links up).
2. Spoke ↔ sibling spokes in same silo only (topical cluster).
3. Hub → top money pages (silo 1) + the checker/checklist tools (silo 9).
4. Guides (silo 10) → relevant silo hub + one money page (funnel down).
5. Refusal silo (5) cross-links into Documents (4) + the per-country `refused` topic.
6. No deep cross-silo spoke-to-spoke links (keeps authority focused).

## Build priority (impact × effort)
1. **Silo 5 Refusals/prevention** 🔴 — owns the white space, brand-defining (#323–328 spec'd).
2. **Silo 11 Audiences/nationalities** 🔴 ⭐ — highest untapped SEO, matches BP's real audience + existing eligibility router; low competition.
3. **Silo 2 Visa types** 🔴 — matches every rival's type pages, easy templated build.
4. **Silo 13 Pricing/how-it-works** 🟡 — transparency = conversion + trust; small build.
5. **Silo 4 Documents** expansion 🔴 — document-review + cover-letter = revenue + prevention.
6. **Silo 9 Tools** — Schengen 90/180 calculator 🔴 (linkable asset / backlinks).
7. **Silo 12 Apply-through/embassy rule** 🔴 — prevention + niche queries.
8. **Silo 1 destinations** expand (#236).
9. **Silo 3 Appointments** spokes · **Silo 8 essentials** · **Silo 6 authorisations** · **Silo 14 B2B**.

## Compliance guardrails (apply across all silos)
- No "100% approval / refund of govt fee / faster decision" — express speeds *handling* only.
- "Facilitation/admin", not UK immigration *advice* (OISC scope #130).
- Insurance = FCA-safe introducer signpost, BP takes no charge.
- ETA/authorisation ≠ visa; no physical document.

---

## Changelog
- **v2 (2026-06-24):** added 4 silos — 11 Audiences/nationalities ⭐, 12 Apply-through/embassy rule, 13 Pricing/how-it-works ⭐, 14 B2B/travel-agents. Folded in: Schengen 90/180 calculator + fee/processing/photo tools, glossary + FAQ hub, group/family applications, bank-statement/sponsorship doc pages. Re-ranked build priority (Audiences silo → #2).
- **v1 (2026-06-24):** initial 10-silo map from competitor service audit.
