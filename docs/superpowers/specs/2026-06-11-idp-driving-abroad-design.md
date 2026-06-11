# IDP + Driving Abroad — Sub-project Design Spec

**Date:** 2026-06-11 · **Parent:** `2026-06-11-uk-outbound-evisa-site-design.md` (subsystem #6 of 6)
**Depends on:** Foundation (#1), photo maker (#3 — reused). **Cross-sells:** visa money pages (#2) + funnel (#4).
**Supersedes** the parent spec's original IDP model (done-for-you postal). See memory `idp-paypoint-self-service`.

## Goal
An SEO content silo + free tools that capture "IDP / driving abroad" search intent for UK travellers, give honest accurate guidance, and cross-sell the visa service. **Guided self-service — no fulfilment, no payment, no CRM deals.**

## Why the pivot (research-grounded)
- UK IDPs are issued **in person only at PayPoint** (since 1 Apr 2024); no postal/online channel. Done-for-you would require taking custody of customers' original licence + passport → friction + liability + doesn't scale.
- **Accuracy/trust rule (gov.uk):** an IDP is **NOT** needed to drive in the EU/EEA, Switzerland, Norway, Iceland, Liechtenstein **if you hold a UK photocard licence**. It's required there only for **paper-licence / Crown Dependency** holders. IDP is genuinely required (any licence) only for non-EEA 1926/1949/1968 countries (USA, Japan, Turkey, Morocco, UAE, India, Egypt, etc.).
- **Search demand (Ahrefs, UK):** "do you need IDP for X" is dead (~10/mo). Real demand = the **"driving in [country]"** cluster (driving in France 4,900 + "what do I need to drive in France" 2,600 + requirements 2,100 + checklist 1,300). Hub head term = "international driving permit uk" 2,200 (KD43). Lead-gen cluster = how/where/online/cost/1949-vs-1968 (CPC to £1.10). Low-KD wins: Thailand KD2, France KD12.

## Decisions (locked)
| Item | Choice |
|---|---|
| Model | Guided self-service (tools + guides). No issuance, no Stripe, no CRM. |
| Silo shape | Hub & spoke: hub + how-to/service spoke + destination spoke. |
| Destination angle | Merge "IDP [country]" + the bigger "driving in [country] requirements/checklist". |
| Checker input | Destination **+ licence type (photocard / paper)** — required for accuracy. |
| Data | `data/idp-conventions.json` per country, encodes the photocard rule. |
| Schema | Hub + how-to: FAQPage + HowTo. Destination pages: Article + HowTo (+ FAQ). |
| Photo | Reuse #3 photo maker, UK 35×45 preset. |

## Data model — `data/idp-conventions.json`
Keyed by country slug; covers far more countries than the 8 visa silos:
```jsonc
{
  "france": {
    "name": "France", "convention": "1968",
    "idp_required_for_photocard": false,   // EU/EEA photocard rule
    "idp_required_for_paper": true,
    "validity": "3 years or until your licence expires",
    "drive_notes": "Photocard holders don't need an IDP. Car-hire firms may still ask. You also need a UK sticker and to drive on the right.",
    "idp_notes": "Required only if you hold an old-style paper or Crown Dependency licence."
  },
  "usa": {
    "name": "USA", "convention": "1949",
    "idp_required_for_photocard": true,
    "idp_required_for_paper": true,
    "validity": "12 months",
    "drive_notes": "IDP recommended/required alongside your UK licence; rules vary by state.",
    "idp_notes": "1949 permit, valid 12 months."
  }
}
```
EU/EEA + CH/NO/IS/LI entries set `idp_required_for_photocard:false`. Non-EEA 1926/1949/1968 set `true`. Validity per convention: 1926 = 12mo, 1949 = 12mo, 1968 = 3yr/until licence expires.

## Components
1. **IDP / driving checker** (`/international-driving-permit/check/`):
   - Inputs: destination dropdown + **licence type** (photocard / paper).
   - Output: needs-IDP yes/no (accurate per licence type), which convention, validity, £5.50 cost, **honest how-to**: "apply in person at a PayPoint store with your original full licence + a passport-standard photo (+ passport if paper licence); issued on the spot; apply up to 3 months before travel."
   - Reads `data/idp-conventions.json`. Client-side, same pattern as #3 tools (server-rendered table + hydrate).
2. **Nearest-PayPoint locator** — link/embed to the PayPoint IDP store finder (`https://www.paypoint.com/instore-services/international-driving-permits`).
3. **Document checklist** + **compliant photo** (reuse #3 photo maker, UK preset).

## Silo structure
- **Hub** `/international-driving-permit/` — targets "international driving permit uk" + aliases ("international driving licence/license"); explains what an IDP is, the 3 conventions, the photocard rule, how/where/cost; embeds the checker. FAQPage + HowTo schema.
- **Spoke A — how-to / service (lead capture)** `/international-driving-permit/how-to-get/`, `/.../cost/`, `/.../1949-vs-1968/`, `/.../online/` (honest: "no online issuance in the UK — here's how to apply / how we can help with your visa"). Captures the transactional/online-intent traffic; CTA cross-sells the visa service.
- **Spoke B — destinations** `/driving-in-<country>/` — merges "IDP [country]" + "driving in [country] requirements/checklist". Renders convention/validity/IDP-need from `idp-conventions.json` + driving prose (licence, IDP-if-paper, UK sticker, side of road, insurance). Cross-links to the visa money page for that country and to the hub. **Priority order: France/Europe first, then low-KD wins (Thailand), then Spain/Italy/Japan/India/Australia/USA.**

## Internal linking
- Destination pages → up-link to hub (partial anchor) + sibling destinations + cross-link to matching visa money page (#2).
- Hub → checker + how-to spoke + top destinations.
- Visa money pages' `[idp_crosssell]` card (#2) links into the matching `/driving-in-<country>/` page.
- Same silo discipline as #2: no spammy cross-silo; ≤1 exact-match anchor per page.

## Out of scope
IDP issuance/fulfilment, payment, CRM deals (none). Concierge/in-person custody tier — deferred. Non-UK-resident IDP advice (we serve GB/NI residents).

## Acceptance criteria
- `data/idp-conventions.json` validates; EU/EEA+CH/NO/IS/LI entries correctly set `idp_required_for_photocard:false`; non-EEA set `true`.
- Checker returns the **accurate** answer for (a) France + photocard = "no IDP needed", (b) France + paper = "1968 needed", (c) USA + photocard = "1949 needed".
- Checker always shows the honest in-person-at-PayPoint how-to (never implies postal/online issuance).
- Hub + ≥3 destination pages live with correct schema (Rich Results Test passes), each cross-linked to hub + a visa money page.
- Destination pages target the "driving in [country]" cluster, not the dead "do you need IDP for [country]" phrasing.
- No payment/cart/CRM path exists in this subsystem.

## Open items
- Complete the country list in `idp-conventions.json` (research covered the popular set; fill the long tail).
- Confirm PayPoint store-finder embed vs outbound link.
- Decide lead-capture mechanism on the how-to/online pages (email capture vs straight visa-service CTA).
