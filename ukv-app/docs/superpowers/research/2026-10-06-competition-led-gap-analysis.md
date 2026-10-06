# Competition-led gap analysis for beyondpassports.com + tours (2026-10-06)

> **LOCKED 2026-10-06 (owner): decision basis.** See `2026-10-06-README-decision-basis.md` for the full set and rules of use.

Owner's locked main goal: beat the competitor set. Locked rule: every decision is competition-led. This file consolidates five expert-agent reports run 2026-10-06 and lists the gaps the UK-derived plan missed. Each design decision in the international spec must cite a line here.

## Source reports (same folder)
1. `2026-10-06-competitor-visa-tour-bundling.md`: FlyFast, Breakout, Tourloom, Panelx/VisaD, SVC, IAS (holiday-side UK set).
2. `2026-10-06-competitor-intl-architecture.md`: Atlys, iVisa, Visard, GoVisa (multi-market architecture, pricing, hreflang).
3. `2026-10-06-competitor-local-markets-za-ae-us-ca.md`: who actually competes in each new market.
4. `2026-10-06-four-market-feasibility.md`: scaling-expert feasibility, launch order, top-10 gaps, owners.
5. `2026-10-06-competitor-seo-footprint.md`: sitemaps, taxonomy, schema, depth, AI-search readiness.
6. `2026-10-06-deepdive-appointment-lane.md`: G1 mechanics, pricing, refund terms, UX, pain, blueprint.
7. `2026-10-06-deepdive-document-consultancy-lane.md`: G2 tier anatomy, deliverables inventory, refund text, FAQs.
8. `2026-10-06-deepdive-tours-lane.md`: G4 package anatomy, price construction, PTR/seller-of-travel/TICO exposure.
9. `2026-10-06-deepdive-market-south-africa.md`, `-uae.md`, `-usa-canada.md`: applicant pain quotes, VAC reality per destination, document norms, payment/trust, blueprints.
10. `2026-10-06-competitive-service-brief.md`: SYNTHESIS of 6-9 into per-lane + per-market service specs with price recommendations.
Prior: `../../competitor-swot.md` (16 Aug 2026, UK copy layer, approved France section stack).

## A. Findings that change the plan
| # | Finding | Evidence | Consequence |
|---|---|---|---|
| A1 | Tourloom is a related entity, not a rival | chat-brain/lessons.md 28 Sep: client paid "TOURLOOM LIMITED, Ref BP-26-95250" for a BP invoice; CRM shared Tourloom/BP | tourloom.co.uk at 18.8% IS with 100% position-above = self-bidding. Combined ~31% IS, second to Atlys. Split keywords/geos or consolidate before any new-market spend |
| A2 | Real rivals abroad are ~80% off the UK list | Report 3 | GoVisa, FlyFast, Breakout, Panelx, SVC absent in all four markets. Only IAS + Visard travel. Lock a per-market competitor set (section D) |
| A3 | South Africa is unoccupied by every multi-market player | Reports 2, 3: Atlys no en-ZA (serves India template), iVisa no France/ZA, Visard no /za | First-mover market. Launch first |
| A4 | "Ops can check all four markets" is contradicted by docs | Report 4: playbook §0b no SA VFS account; SA tenant 403; no AE/US/CA accounts anywhere | Board per market renders "enquire" x29 until one logged real check per market exists. Gate, not assumption |
| A5 | FlyFast + Breakout are one operation | Report 1: same template, same Great Portland St address, same form | Treat as one competitor in UK bidding and copy |
| A6 | Hidden UK rival already in US + CA | Report 3: WE ARE LONDONERS LTD (OISC F202200007), 5 lookalike domains, "$140 appointment", "7 Working Days" promises | Your intended playbook, already live in two of your markets. Study and out-trust |
| A7 | Visible fee split is the norm among winners | Report 2: Atlys AED 108 govt + AED 499 service; Visard £35/£100, AED 200, $40; iVisa $399.99 all-in | UK site hides pricing. .com must server-render govt fee vs our fee in local currency |
| A8 | Nobody does hreflang on destination pages | Reports 2, 5: Atlys/iVisa/Visard hub-level only; Atlys/iVisa no x-default | Cheap uncontested edge: full reciprocal hreflang on every page incl .co.uk, x-default to neutral chooser |
| A9 | Atlys forces IP 307 redirects; non-core locales noindexed | Reports 2, 5 | Confirms SA spec rule: market by URL, never Host/IP. Add manual switcher + cookie + dismissible banner |
| A10 | Atlys owns AI-search readiness | Report 5: explicit Allow for 13 AI crawlers, llms.txt 250+ links, Markdown twin per product page `/docs/{dest}.md` | Match: AI-crawler Allow, sectioned llms.txt, per-page .md twin from same Blade data. Nobody else does it |
| A11 | Visa-led tours on the visa page = empty lane everywhere | Reports 1, 2, 3: iVisa sells EU-2015/2302 packages on a separate catalogue; Musafir (AE) bundles tours with "guaranteed appointment"; nobody sells Schengen trips to ZA/US/CA residents; nobody itemises ground transport | Tours block ON the market x destination page, named transport line, no flights, no ATOL |
| A12 | PTR 2018 exposure exists without flights | Report 1: Breakout opts out explicitly; Tourloom ignores it | Compliance memo before tours launch; Breakout-style "not the organiser of a package holiday" line if memo supports it |
| A13 | Trust marks differ per market | Report 3: AE = DET/DED licence + IATA TIDS; US = BBB; ZA/CA = ASATA/TICO untapped; CH/ICO mean little abroad | Per-market trust strip + POPIA/PDPL/CCPA/PIPEDA statements; translate CH/ICO into "UK-registered, data-protection registered, look us up" |
| A14 | Review platforms differ | Report 3: Google reviews (ZA 1,280; AE 1,500+), Hellopeter ZA, BBB US; Trustpilot UK-centric | Per-market review collection plan |
| A15 | Citizens of AE/US/CA are visa-exempt | Report 4 | Market = non-citizen residents. ETIAS confusion = ad waste. Per-market qualification tiles + negatives |
| A16 | 30-min promise + WhatsApp call close break abroad | Report 4: US/CA evening = UK 01:00-04:00; UAE restricts VoIP voice | Market-specific hours; US SMS/phone; UAE Google Meet close |
| A17 | Tax unassessed | Report 4: SA e-services VAT, UAE non-resident VAT, CA GST/HST, UK place of supply | Accountant memo before local-currency checkout |
| A18 | Stripe not live even in GBP; currency hardcoded x3 | Report 4 + memory stripe-prod-test-mode | Payments sub-project depends on going live in GBP first |
| A19 | Clone pages cannot scale | Report 4 + memory country-page-clone-bugs; 29x4 operator matrix = 116 cells, 2 verified | New data-driven templates (owner decision 2026-10-06); operator matrix sheet tab |
| A20 | Slot-alert SaaS is a competitor class | Report 3: Visard, Visa Catcher, Telegram bots in US/CA/AE | Gap nobody fills: human service + slot monitoring. BP's board + named consultants = the hybrid |

## B. Gaps nobody fills (BP can own)
1. Human service that also runs slot monitoring (bots have no humans, agencies no bots).
2. Per-country VAC routing clarity per market (ZA has five operators: VFS (incl. Switzerland), TLScontact (DE/BE), BLS (ES), Capago (FR), GVCW (GR); Visalink stale per 2026-10-06 SA deep dive; no agency names them).
3. Transparent all-in pricing in ZA, US, CA (only AE does it).
4. Refusal-aware consulting (only two AE agencies mention appeals).
5. hreflang + schema (Service, Offer, FAQPage, AggregateRating, dateModified, named reviewer) on destination pages.
6. Server-rendered fee + VAC tables (VisaHQ JS-empty; Atlys/iVisa no tables).
7. Live availability as crawlable text with "last checked" stamps.
8. Visa-led, no-flights, enquiry-only tours on the visa page itself.
9. French-language Quebec PR market: zero agencies.
10. Honest anti-fraud positioning aligned with VFS's own "no fast-track" messaging (Gulf News, Toronto consulates).
11. ICO/data-protection registration published (no competitor publishes one).
12. Refund rule on the service page itself, not buried in T&Cs.

## C. Patterns to copy (cited) and to avoid (cited)
Copy: Visard market-first path + hreflang cluster + x-default root; Atlys/Visard fee split; Atlys llms.txt + .md twins + "How we reviewed"; iVisa schema set; Visard documents table with "common mistake" column + visible "Updated"; Tourloom package card anatomy; Breakout PTR opt-out + 14-day/refund-before-work policy + "no special or priority appointment access"; VisaD "What we don't do" + outcome refund; xVisa "payable now / after" laddering; GoVisa/iVisa "you may apply directly via official sites" beside the price; IAS itemised refund table.
Avoid: Atlys IP 307 + "for Indians" fallback + on-time guarantees; GoVisa machine-translated hreflang sprawl + 0+ counters; Tourloom price-less "transparent pricing" + dead flights page; FlyFast/Breakout holiday cards priced at visa fees + uniform 4.8/5; SVC fabricated counters + anonymous entity; VisaHQ JS-only fee cells; iVisa nationality pages for visa-free pairs; Visard "agency £400-1,000" comparison; USD-only pricing.

## D. Per-market competitor sets (lock these alongside the UK set)
- UK (auction): Atlys, GoVisa, Visard, FlyFast+Breakout (one), Panelx/VisaD, SVC, IAS, iVisa; Flypass (copy twin); Tourloom = sister brand, not rival.
- ZA: One Visa World, Visa Logistics, Visa24HR, The Visa Agent (R1,590), Visa Box (Travelstart partner), Global Visa Services, Visas Unlimited, za.iasservices; Easy Visas (lead-gen). Global absent.
- AE: Visa Guy, Green Apple (AED 850), Regal (AED 850), GlobalVisaShop (AED 899), AFC Holidays (AED 650), Akira (AED 450), Musafir (tours + guaranteed appointment), The Visa Services, Lets Visa, Zabeel, schengenvisa.ae; Atlys en-AE; typing centres.
- US: Atlys (home turf), CIBT, VisaHQ, Visard, Visa Catcher, WE ARE LONDONERS LTD network, Dokument USA ($750), us.iasservices, diaspora tour ops (Akshar, Global Holidays).
- CA: WE ARE LONDONERS LTD network, Toronto Visa Express / Passport Visa Toronto (CAD 150-180), schengenvisapro, visahq.ca, Y-Axis, Visa Catcher, diaspora agencies (Spring Travels, Journey Asia).

## D2. VAC corrections from the deep dives (2026-10-06)
- SA: Switzerland = VFS not TLS; Visalink stale. CA: BLS Spain gone (Spain = consulate email queue); no TLS; VFS only FR/NL/CH/AT (+ fronting DE/AT/ES in Western Canada); DE Toronto/IT/PT/BE/GR direct. US: France TLS has no state jurisdiction; Belgium no VAC; Germany = BLS. AE: France operator contested (TLS vs VFS), verify. Refusal rates: SA ≈ 5.7%, UAE 23.8% (2024).

## E. Launch order (competition-led) + stage gates
1. South Africa. 2. UAE. 3+4. USA + Canada as a pair after US-hours reply capacity.
Gate per market: (i) one real logged VAC check per core destination, (ii) compliance memo reviewed by a local professional, (iii) one live local-currency charge end to end.

## F. Decisions only the owner can make (blocking the spec)
1. Tourloom: confirm sister brand; decide UK auction split/consolidation.
2. Ops reality: which markets have a portal account today (evidence row per market).
3. Second ops identity for non-UK per-client VAC work, and its time zone.
4. Tours phase 1: referral-only to licensed local operators vs BP-arranged enquiry-only (compliance memos first).
5. Reply-hours policy per market; channel for US/CA.
