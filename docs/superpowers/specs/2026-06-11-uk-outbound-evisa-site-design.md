# UK Outbound eVisa Site — Design Spec

**Date:** 2026-06-11
**Status:** Approved (design); pending spec review

## 1. Summary

SEO-led website for a UK-based **outbound eVisa facilitation service** — helping British/UK-resident travellers obtain visas, eVisas and travel authorisations (ETA/ESTA/ETIAS) for overseas destinations. Monetised by the operator's **own visa service / lead capture** (not ads, not affiliate).

Model follows the **Atlys playbook**: evergreen country-visa pages + free tools + supporting travel guides, organised into tight topical silos. Deliberately **not** the VisaHQ news-at-scale model.

Derived from competitor organic-keyword analysis of `visahq.com` and `atlys.com` (UK market).

## 2. Decisions (locked)

| Decision | Choice |
|---|---|
| Market | UK only (English, no hreflang at launch) |
| Service direction | Outbound — Brits → world (eVisa/ETA facilitation) |
| Content model | Service + tools + evergreen guides (Atlys-style) |
| Monetisation | Own visa service / lead capture |
| Launch approach | Depth-first on top destination clusters |
| Platform | WordPress core + embedded JS tool widgets |

## 3. Architecture — country silos

Each destination is a self-contained silo. Support guides link **up** to the money page; the money page links **down** to its guides + relevant tool + `/apply`; tools and the UK hub link **out** to money pages.

```
HOMEPAGE → 8 destination hubs + 2 tools + /apply

/turkey/                     MONEY
  /turkey/plugs/
  /turkey/weather-november/
  /turkey/weather-february/
  /turkey/things-to-do-side/
  /turkey/best-time-to-visit/
  /turkey/tourist-tax/
/egypt/                      MONEY
  /egypt/visa-on-arrival/
  /egypt/rules/
/india/                      MONEY
  /india/e-arrival-card/
  /india/duty-free-allowance/
/morocco/                    MONEY
  /morocco/weather-november/
  /morocco/things-to-do-agadir/
  /morocco/vaccinations/
  /morocco/entry-requirements/
/uae/                        MONEY
  /uae/what-to-buy-dubai/
  /uae/work-visa/
/australia/                  MONEY
  /australia/eta-uk-citizens/
  /australia/processing-time/
/usa/                        MONEY
  /usa/esta-processing-time/
  /usa/tourist-visa-requirements/
/europe/schengen/            HUB
  /europe/france/
  /europe/spain/
  /europe/italy/
  /europe/greece/
  /europe/etias/
  /europe/ees-entry-exit/
  /europe/non-schengen-countries/
/uk/                         TRAVELLER INFO HUB
  /uk/does-schengen-cover-uk/
  /uk/eta/
/tools/
  /tools/do-i-need-a-visa/   HERO checker
  /tools/visa-photo/
/apply/                      FUNNEL  (+ /pricing /how-it-works /refunds /terms /about)
```

~30 pages at launch across 8 silos + UK hub + 2 tools + funnel/trust pages.

### Full structure (all phases)

`[P1]` launch · `[P2]` expansion · `[P3]` optional-later. Parked keyword buckets fold in as new silos that do not disturb the launch silos.

```
HOMEPAGE                                                   [P1]

DESTINATION SILOS
/turkey/ 💰  + plugs, weather-november, weather-february,
             things-to-do-side, best-time-to-visit, tourist-tax   [P1]
/egypt/ 💰   + visa-on-arrival, rules                      [P1]
/india/ 💰   + e-arrival-card, duty-free-allowance         [P1]
/morocco/ 💰 + weather-november, things-to-do-agadir,
             vaccinations, entry-requirements              [P1]
/uae/ 💰     + what-to-buy-dubai, work-visa                [P1]
/australia/ 💰 + eta-uk-citizens, processing-time          [P1]
/usa/ 💰     + esta-processing-time, tourist-visa-requirements [P1]
             + cover-letter                                [P2]
/canada/ /new-zealand/ /china/ /saudi-arabia/ /vietnam/
/thailand/ /indonesia-bali/ /kenya/ /cambodia/
/sri-lanka/ /jordan/   (same template)                     [P2]

EUROPE / SCHENGEN HUB
/europe/schengen/ 🏛️
  + france, spain, italy, greece                           [P1]
  + etias, ees-entry-exit, non-schengen-countries          [P1]
  + cover-letter, invitation-letter, employment-letter,
    appointment-guides                                     [P2]

UK TRAVELLER HUB
/uk/  + does-schengen-cover-uk, eta                        [P1]
      + /uk/passport/ (fees, renewal, child-passport,
        photo→tool)                                        [P2]

EMBASSIES (programmatic nav silo)                          [P2]
/embassies/[country]/[city]/  → links to destination money page

TOOLS
/tools/  + do-i-need-a-visa, visa-photo                    [P1]
         + status-checker, ds-160-filler,
           cover-letter-generator, appointment-tracker,
           passport-index                                  [P2]

FUNNEL & TRUST                                             [P1]
/apply, /pricing, /how-it-works, /refunds, /terms, /about

OPTIONAL LATER                                             [P3]
/driving-abroad/  (IDP — only if serviced)
/travel-updates/  (news layer — only with ads + authority)

EXCLUDED (no silo — dead)
competitor brands · typos/noise · non-UK locales ·
UK inbound visas (separate brand) · pure flights/airline
```

## 4. Internal-linking rules (silo discipline)

1. Support guide → links **up** only, to its own money page (not other silos).
2. Money page → links to its sibling guides, the relevant tool, and `/apply`.
3. Tools → deep-link to the matching destination money page.
4. UK hub → links **out** to destination money pages ("travelling? get your [X] visa").
5. Cross-silo links sparing + contextual only.
6. Homepage = hub to all 8 destinations + 2 tools + apply.

## 5. Keyword map (primary + volume → secondaries)

Volumes from competitor keyword export. 💰 = money page, 🏛️ = hub.

### Turkey
- `/turkey/` 💰 — **turkey visa (4,400)** · do i need a visa for turkey (2,900); turkey visa from uk (1,900); visa for turkey from uk (1,300); turkey visa application (1,000); turkey tourist visa (1,000); turkey e-visa; is turkey schengen (480)
- `/turkey/plugs/` — **turkey plug type (2,900)** · power adapters for turkey (6,600); what plugs are used in turkey (1,600); turkey plug (1,600)
- `/turkey/weather-november/` — **turkey weather november (5,400)** · weather in turkey in november (2,900); istanbul weather november (1,600)
- `/turkey/weather-february/` — **weather in turkey in february (2,400)** · istanbul weather february (1,300); turkey weather february (880)
- `/turkey/things-to-do-side/` — **side turkey (9,900)** · things to do in side turkey (1,300)
- `/turkey/best-time-to-visit/` — **best time to visit turkey (1,600)** · best time to visit istanbul (720)
- `/turkey/tourist-tax/` — **turkey tourist tax (320)**

### Egypt
- `/egypt/` 💰 — **egypt visa (8,100)** · travel visa to egypt (3,600); visa for egypt (2,900); egypt visa uk (2,400); egypt visa online (880); how much is a visa for egypt (480)
- `/egypt/visa-on-arrival/` — **egypt visa on arrival (1,600)**
- `/egypt/rules/` — **rules to follow in egypt (90)**

### India
- `/india/` 💰 — **visa for india (1,900)** · india visa; india tourist visa; do you need visa for india from uk; how much is a tourist visa to india
- `/india/e-arrival-card/` — **india arrival card (1,300)** · e arrival card (720)
- `/india/duty-free-allowance/` — **india duty free limit (110)**

### Morocco
- `/morocco/` 💰 — **morocco visa (2,900)** · morocco visa from uk (2,400); morocco e-visa (1,900); do i need a visa for morocco (1,000); visa for morocco (590); morocco visa online (480)
- `/morocco/weather-november/` — **agadir weather november (1,900)**
- `/morocco/things-to-do-agadir/` — **things to do in agadir morocco (1,600)**
- `/morocco/vaccinations/` — **vaccinations for morocco (720)** · shots for morocco (880); injections for morocco (720)
- `/morocco/entry-requirements/` — **morocco entry requirements (320)**

### UAE / Dubai
- `/uae/` 💰 — **dubai visa (2,900)** · dubai visa from uk (2,400); do you need a visa for dubai (1,900); uae visa (1,600); do you need a visa to visit dubai (880); united arab emirates visa (720); dubai visa for indians (720)
- `/uae/what-to-buy-dubai/` — **what to buy in dubai (tail cluster)**
- `/uae/work-visa/` — **dubai work visa (90)**

### Australia
- `/australia/` 💰 — **australian visa (8,100)** · australia visa (6,600); australia visa application (3,600); australia tourist visa (2,900); electronic travel authority eta visa australia (1,600); do uk citizens need a visa for australia (1,000)
- `/australia/eta-uk-citizens/` — **do uk citizens need a visa for australia (1,000)**
- `/australia/processing-time/` — **australia visa processing time (260)**

### USA
- `/usa/` 💰 — **us visa (8,100)** · visa for america (2,900); usa visa (2,900); apply for us visa from uk (1,600); us visa application uk (1,600); us visa uk (1,300); us visa from uk (1,000)
- `/usa/esta-processing-time/` — **how long do estas take (1,000)**
- `/usa/tourist-visa-requirements/` — **required bank balance for us tourist visa**

### Europe / Schengen
- `/europe/schengen/` 🏛️ — **schengen visa (33,100)** · schengen visa fee (2,900); schengen visa cost (2,400); schengen visa appointment (2,400); schengen visa photo requirements (880); easiest country to get schengen visa
- `/europe/france/` — **france visa (6,600)** · france visa application (5,400); france visa appointment (2,900); france visa from uk (1,300)
- `/europe/spain/` — **spain visa (5,400)** · spain visa appointment (2,400); visa for spain from uk (1,600)
- `/europe/italy/` — **italy visa (1,900)** · italy visa appointment (1,600); italy schengen visa (1,300)
- `/europe/greece/` — **greek travel visa (1,900)** · greece visa appointment (2,400); greece visa (880)
- `/europe/etias/` — **etias application (6,600)** · etias; eu etias delay
- `/europe/ees-entry-exit/` — **eu entry exit system problems (2,400)** · entry-exit system (390); new eu border checks
- `/europe/non-schengen-countries/` — **non schengen european countries (1,300)**

### UK traveller hub
- `/uk/does-schengen-cover-uk/` — **schengen visa united kingdom (4,400)** · does schengen visa cover uk; uk visa and schengen visa (1,600)
- `/uk/eta/` — **uk eta cluster (~660)** · uk eta price; uk eta cost; uk eta questions

### Tools
- `/tools/visa-photo/` — **passport size photo (4,400)** · passport size photo uk dimensions (2,900); visa photo maker; schengen visa photo size
- `/tools/do-i-need-a-visa/` — link-magnet; generic `visa requirements` + per-country `do i need a visa for [country]`

### Dedup notes
- EES lives **only** at `/europe/ees-entry-exit/` (no `/uk/ees`) to avoid cannibalisation.
- `/europe/schengen/` (transactional) vs `/uk/does-schengen-cover-uk/` (UK-specific info) = distinct intent, no clash.

## 6. Page templates

- **Destination money page** — sections: do you need a visa · visa types · cost · processing time · requirements/docs · how to apply · FAQ. Primary CTA → `/apply`. Schema: FAQPage + Service + BreadcrumbList.
- **Support guide** — top-funnel info; internal-links to its money page; soft CTA.
- **UK info hub** — informational; builds EEAT trust; links out to money pages.
- **Tool page** — embedded JS widget + supporting copy + links to relevant destinations.

## 7. Lead / service funnel

Money-page + tool CTAs → `/apply`:
1. Eligibility — destination, nationality (UK), travel date.
2. Contact — name, email.
3. Passport details.
4. Submit → operator fulfils the eVisa on the traveller's behalf / lead qualified.

Capture → CRM + email. Every money page and tool routes here.

## 8. Tech stack

- WordPress + RankMath (SEO/schema) + Fluent Forms (funnel → CRM) + lightweight fast theme.
- Tools = embedded JS widgets mounted in WP pages.
- Schema: FAQPage, HowTo, BreadcrumbList, Service.

## 9. SEO / trust / compliance

- Silo internal linking (guides → money page → `/apply`).
- EEAT: named visa-expert author, "last updated" dates, cited sources.
- **Compliance** (critical for outbound eVisa facilitation + payment/ad approval): prominent disclaimer (independent service, not a government website); transparent pricing; refund + terms pages.
- UK-only; no hreflang at launch.

## 10. Measurement

- Google Search Console + rank tracking on the 8 destination clusters.
- Success metric = application/lead submits per destination.

## 11. Scope boundaries (explicitly excluded at launch)

Parked / cut, with reason:

| Excluded | Reason |
|---|---|
| News / travel-disruption keywords | VisaHQ news model — rejected; decays, needs Google-News trust |
| Embassy/consulate navigational | No service intent; VisaHQ-owned DB pages |
| UK passport admin (fees/renewal) | Domestic info/ad-intent, not lead-intent |
| UK inbound visas (visit/spouse/ILR/work) | Inbound regulated model — explicitly out of scope |
| Destinations beyond MVP-8 (Vietnam, Thailand, Bali, China, Saudi, Canada, NZ, Kenya, etc.) | Depth-first launch — **Phase 2 expansion** |
| Per-country status checkers, DS-160, cover-letter, appointment trackers, passport index | Only 2 hero tools at launch — **Phase 2 tool backlog** |
| Competitor brand terms (atlys, visahq, scorevisa) | Can't/shouldn't target |
| Non-English / non-UK locale keywords | UK-only English site, no hreflang |
| Flights / airline / airport terms | Flight intent, not visa |
| App-support guides (cover/invitation/employment letters, bank balance) | Low commercial value — Phase-2 Schengen-silo candidates |
| Driving licence / IDP | Adjacent travel-doc, off-core |
| Typos / ultra-low-volume noise | Canonical term already mapped |

## 12. Phase 2 / 3 — parked buckets → silo homes

Each excluded bucket from §11 has a designated home so expansion bolts on without disturbing launch silos.

| Parked bucket | Silo home | Phase |
|---|---|---|
| Embassy / consulate navigational | **`/embassies/[country]/[city]/`** programmatic silo → links to destination money pages | P2 (highest-value parked) |
| UK passport admin (fees, renewal, child passport, photo) | **`/uk/passport/`** sub-silo under UK hub → links to `/tools/visa-photo` | P2 |
| App-support guides (cover/invitation/employment letter, bank balance) | Inside existing silos — Schengen + USA support guides | P2 |
| Off-MVP destinations (Canada, NZ, China, Saudi, Vietnam, Thailand, Bali, Kenya, Cambodia, Sri Lanka, Jordan) | **New destination silos**, same template | P2 |
| Per-country tools (status checker, DS-160, cover-letter gen, appointment tracker, passport index) | **`/tools/`** expansion, cross-linked to destinations | P2 |
| Extra destination guides (weather / what-to-buy / things-to-do / tourist-tax / visa-free) | Inside that destination's silo | ongoing |
| Driving licence / IDP | **`/driving-abroad/`** silo — only if serviced | P3 (optional) |
| News / travel-disruption | **`/travel-updates/`** news layer — only with ads + domain authority | P3 (optional) |

### Dead — no silo
UK inbound visas (separate brand, regulated) · competitor brand terms · non-UK/non-English locale keywords · pure flights/airline/airport terms · typos & ultra-low-volume noise.
