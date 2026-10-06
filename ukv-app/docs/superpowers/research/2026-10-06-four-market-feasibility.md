# Four-market feasibility: ZA, AE, US, CA for beyondpassports.com (2026-10-06)

Agent: scaling-expert. Grounded in: SA spec + plan + SEMrush + legal memo (2026-09-05), competitor-swot.md, chat-brain, appointment-availability-playbook.md. Owner committed to full build; this is full scope in gated milestones. Part of the 2026-10-06 competition-led research set.

## Two premise checks
1. **SA phase-1 foundation is not on master.** Lives on branch `feat/sa-market-phase1` (6 commits, 0 conflicts). master has no `markets` config, no `lp-south-africa.blade.php`, no `market` migration; `StripeService` hardcodes `'currency' => 'gbp'` at lines 97, 204, 361. Four-market build starts from zero multi-market code on master.
2. **"Ops can check availability in all four markets today" contradicts every ops record.** Playbook §0b: no SA VFS account exists, gated on compliance. SA research: every `visa.vfsglobal.com/zaf/` path 403, SA tenant unconfirmed. No AE/US/CA account anywhere. Status UNCHECKED, contradicted by docs. Resolves with one logged row per market in Portal Accounts tab (operator, country, centre, date, error codes). Until then a per-market board renders "enquire" x29 = visibly empty, not a wedge vs Atlys widget.

**Tourloom decision needed:** chat-brain/lessons.md (28 Sep) records a client statement showing "TOURLOOM LIMITED, Reference: BP-26-95250" as payee for a BP invoice; CRM is shared Tourloom/BP. If related entity, Tourloom is not a competitor and ~13-19% of the UK auction is self-bidding.

## South Africa (/za/)
- **Demand** (verified, only market with data): "schengen visa" 5,400; "application" 2,400; "schengen visa south africa" 1,300 (72% of global); cost/price ~880 each; "appointment" 70 (34x smaller than UK). Intent = application/cost-led. UK "we watch the calendar" hero is the wrong lead; "complete file, right consulate, real price in rand" fits. Inbound SA leads: none found. Pull owed: production `orders.nationality LIKE '%south africa%'` + +27 grep of Lead Chats.
- **Appointment network** (UK map does not transfer): Germany = TLScontact 5 VACs (verified, gated per-client); Spain = BLS 3 VACs (verified; sweepability unknown); France = Capago (`verify`, likely gated behind France-Visas ref); Italy = Prenot@Mi / VFS (`verify`); Netherlands unconfirmed; VFS SA tenant 403 to automation. Gated share likely higher than UK. Accounts: ZA VFS per country (OTP +27?), Capago/TLS per client, BLS per applicant. 429 behaviour UNCHECKED.
- **Competition**: za.iasservices (thin), visalogistics, travelstart, apostil, pentravel. Atlys no /en-za. None has appointment infrastructure. Wedge: ops layer + stated rand price. CH/ICO mean little to a Johannesburg buyer; translate to "UK-registered, data-protection registered, look us up" + POPIA statement.
- **Compliance**: ASATA voluntary (verified); FICA n/a; LPA s33 reads as not reaching booking + checklist review but s33(3) + Immigration Regulations open. One attorney consult closes it. Tours: Consumer Protection Act applies to packages sold to SA consumers; taking money = supplier of record. Insurance: FSCA/FAIS from scratch. Data: POPIA s72 cross-border.
- **Pricing/payment**: ~21.58 ZAR/GBP (2026-09-05), £130 ≈ ZAR 2,805 (863/1,942), drifts. Stripe ZAR presentment verified; native SA Stripe impossible. Card-only likely underperforms (Ozow/PayFast/SnapScan norms). SA e-services VAT for foreign suppliers: `verify` with accountant.
- **Ops**: UTC+2, English, WhatsApp dominant. Bank-stamped statements expected (`verify`). SA passports print "ZAF" as place of birth (facts.md). Breaks first: one shared ops identity/phone.
- **Tours**: SA buyers expect long-haul flight included; rand makes Europe expensive; no-flights packages unusual. Moderate fit, referral to SA-licensed operator in phase 1.

## UAE (/ae/)
- **Demand**: AE 1.0K/mo "schengen visa application" AND 1.0K/mo "schengen visa appointment" (appointment-led profile, unlike SA); UAE 5th globally for "schengen visa". UAE citizens visa-exempt: market = expat residents (Indian, Pakistani, Filipino, Egyptian passports), closest analogue to BP UK base. Pulls: SEMrush AE db ("schengen visa from dubai", "for uae residents", "dubai cost", "vfs global dubai schengen", "france visa appointment dubai", "spain visa bls dubai" ...) + Domain Overview atlys.com, musafir.com, raynatours.com, vfsglobal.com; Keyword Planner UAE/English.
- **Appointment network** (`verify`): VFS very large in Dubai (IT, NL, AT, PT, maybe DE, CH), TLScontact France (Dubai + Abu Dhabi), BLS Spain. If true, most VFS-heavy = most sweepable of four. Accounts: AE VFS per country (UAE phone OTP?), BLS per applicant, TLS per client.
- **Competition**: heavier than SA. Atlys markets hard to UAE; Musafir, Rayna, dnata bundle visa + Europe packages; thousands of typing centres/Instagram agencies. Wedge: transparent single fee with £40/£90 split (locals bundle or quote on enquiry), real board if ops delivers, WhatsApp-native. UK numbers carry some weight with UK-brand-trusting expats.
- **Compliance** (biggest unknown): visa-facilitation/typing/travel agency = licensed activities (DET Dubai, per-emirate), generally need local establishment; whether a foreign online seller is caught UNCHECKED, memo before any dirham of spend. UAE advertising + cybercrime law on misleading promotions. Tours: tourism licence. Insurance: CBUAE. Data: PDPL.
- **Pricing/payment**: AED USD-pegged, stable price. Typing centres low fees, agencies bundle: £130-equivalent needs "what's included" line. Stripe AED `verify`. UAE 5% VAT: non-resident suppliers may have registration obligation with no threshold (`verify`). BNPL (Tabby/Tamara) norm, not phase 1.
- **Ops**: UTC+4, UAE evening = UK afternoon, 30-min workable. Sat-Sun weekend. WhatsApp dominant BUT VoIP voice calls restricted in UAE (`verify`): breaks the locked "WhatsApp call close"; Google Meet/regular call instead. Docs: Emirates ID, residence visa, employer NOC, salary certificate, 3-6 months stamped statements, residence validity beyond return. Ramadan hours. Arabic/Hindi/Urdu enquiries.
- **Tours**: BEST FIT of four. Visa + Europe package is an existing UAE product; short-haul flights cheap so land-only plausible. Local inclusions: family rooms, halal, Eid departures, Switzerland/Paris/Italy, private transfers. Dense licensed competition; phase 1 referral to DET-licensed operator, enquiry-only.

## USA (/us/)
- **Demand**: US 3.6K/mo "schengen visa application", 4th globally. US citizens visa-exempt: market = green card/H-1B/F-1/L-1/EAD holders with visa-required passports. ETIAS confusion (late 2026, `verify`) pollutes searches, same ad-waste pattern as UK-citizen Portugal lesson at larger scale. Pulls: "for green card holders", "for h1b", "for indian citizens in usa", "for f1 students", city variants (NY, Houston, SF, Chicago, LA); negatives "etias", "do us citizens need a visa for europe"; Domain Overview atlys, ivisa, visahq, cibtvisas.
- **Appointment network** (`verify`): VFS for IT, NL, PT, maybe FR/AT; BLS Spain; DE, GR, CH direct consulate systems. Gated share high. Consular jurisdiction by state strict: UK "switch centre" fallback may not exist. Accounts: US VFS per country (US phone OTP?), BLS per applicant, consulates per client.
- **Competition**: Atlys (US HQ, home turf), iVisa, VisaHQ, CIBT, diaspora agencies. GoVisa's US phone suggests US operator. UK small players absent. Wedge: narrow; resident-non-citizen specialist (status-validity, I-797/EAD nuances) at transparent price. A bet, not a signal.
- **Compliance**: no federal licence known; seller-of-travel registration in CA, FL, WA, HI (`verify`) can reach out-of-state sellers; California most likely to catch tours. FTC refund/guarantee rules. Insurance = state-licensed producers. CCPA/CPRA. Memo: does a UK company with no US presence selling visa assistance + enquiry-only tours trigger seller-of-travel registration/bonding, state by state.
- **Pricing/payment**: USD native Stripe. Compare live vs CIBT/VisaHQ/Atlys per-application fees. Sales tax on services mostly n/a (`verify`). Round USD pricing.
- **Ops**: UTC-5 to -8: US evening = UK 01:00-04:00. 30-min promise breaks without night shift or honest hours. WhatsApp penetration low; SMS/iMessage/phone dominate = channel-fit risk. Spanish second language. Docs: status proof (green card, I-797, I-20 travel signature, EAD), pay stubs, statements, status valid 3+ months beyond return (`verify`). Breaks first: time zone.
- **Tours**: weakest fit. Land-only Europe tours mature (Trafalgar, Globus, Costsaver); UK visa consultancy not natural seller to a Texan H-1B holder. Seller-of-travel exposure thins "enquiry-only" shield. Not in phase 1, or pure referral.

## Canada (/ca/)
- **Demand**: 1.0K/mo "schengen visa application". Citizens exempt: market = PRs, work/study permit holders (Indian, Filipino, Nigerian, Chinese cohorts). ETIAS confusion. Pulls: "canada pr", "work permit holders canada", "international students canada", Toronto/Vancouver, "italy visa vfs toronto", "spain visa bls toronto"; Domain Overview visahq.ca, cibtvisas.ca, ivisa, atlys; Keyword Planner English + French.
- **Appointment network** (`verify`): VFS for IT, NL, PT, probably FR; BLS Spain; DE, GR direct. Jurisdiction by province. CA VFS per country (Canadian phone OTP?).
- **Competition**: VisaHQ, CIBT, iVisa, Atlys + diaspora agencies Toronto/Vancouver/Brampton. CICC regulates Canadian immigration advice only, not Schengen (`verify`): no licence barrier for locals either. Wedge = US wedge, narrower.
- **Compliance**: Tours: Ontario TICO, BC Consumer Protection BC, Quebec OPC permit can reach out-of-province sellers (`verify`). Quebec French-language rules. PIPEDA. Insurance provincial. Visa assistance: no regime found, UNCHECKED.
- **Pricing/payment**: CAD native Stripe. Non-resident GST/HST simplified registration for services to Canadian consumers above threshold (`verify`). Interac norm; cards fine.
- **Ops**: UTC-3.5 to -8, same break as US. English + French. Docs: PR card/permit, Notice of Assessment, statements, employer letter. WhatsApp higher among diaspora than general population.
- **Tours**: weak fit + heavier provincial licensing. Referral only or not in phase 1.

## (a) Ranked launch order
1. **South Africa**: verified demand, citizen market, compliance one attorney consult from closed, trivial time zone, ZAR presentment verified, no competitor with an ops layer.
2. **UAE**: appointment-led signal, WhatsApp-native, friendly time zone, likely most sweepable network, visa-led tours already a local product. Held by licensing unknown = first memo.
3. **USA**: biggest volume, worst fit (time zone, channel, ETIAS pollution, incumbents at home, seller-of-travel). Launch as North America pair with CA only after US-hours reply capability exists.
4. **Canada**: smallest volume, every US problem + provincial tour licensing + French. Ride on US build; never first.
Stage gate between markets: (i) one real logged VAC check per core destination, (ii) compliance memo reviewed by a local professional, (iii) one live local-currency charge end to end.

## (b) Top 10 gaps a UK-derived plan missed
1. SA phase-1 code not on master; multi-market architecture unbuilt.
2. No non-UK VAC account exists; "ops can check all four markets" unevidenced; board would render "enquire" x29 per market.
3. Stripe prod in TEST mode; currency hardcoded GBP in three places; "local-currency checkout x4" sits on a payment path not live even in GBP.
4. Country pages = seven static clones with known clone bugs; 116 pages cannot be cloned; country-logic.md UK-only. 29x4 operator matrix = 116 cells, 2 verified.
5. US/CA/UAE citizens are visa-exempt; market = non-citizen residents; ETIAS noise burns budget unless copy, negatives and qualification step (local equivalent of BRP time-left tile) rebuilt per market.
6. 30-min promise fails in North American hours; WhatsApp weak in US; WhatsApp voice restricted in UAE (breaks locked consultation-call close).
7. Trust assets don't travel: CH/ICO mean little abroad; need local proof, local numbers, POPIA/PDPL/CCPA/PIPEDA statements; refund wording per market vs local consumer law.
8. Tours = regulated travel-seller activity (US states, CA provinces, UAE DET, SA CPA); insurance bundling multiplies regimes; "enquiry-only" stops protecting once BP takes money.
9. Tax exposure (UK VAT place of supply, SA e-services VAT, UAE non-resident VAT, CA GST/HST) unassessed; FX + cross-border card fees eat a slice of £130.
10. Competitor frame wrong twice: real international set = Atlys/iVisa/VisaHQ/CIBT + locals, not FlyFast/Breakout; Tourloom appears to be a related entity so UK auction economics may be partly self-inflicted. Cheapest competition-led win today = UK budget + the France/Flypass copy race in competitor-swot.md, not four markets.
Bonus: single shared ops identity; gated per-client work in four markets lands on one person.

## (c) Next action + owner per gap
| # | Action | Owner |
|---|---|---|
| 1 | Merge `feat/sa-market-phase1` onto master; extend `markets` config to za/ae/us/ca before any page work | Dev workflow; owner approves merge |
| 2 | One real browser session per market on the VFS tenant for one low-stakes country; log result + error codes in Portal Accounts (playbook §0b); confirm OTP phone | Ops; evidence to owner |
| 3 | Decide: Stripe live keys in GBP first; then market-aware currency/amount from `config('ukv.markets.<code>')` in StripeService | Owner (Stripe); dev |
| 4 | Build 29x4 operator matrix tab beside "Country Logic" (operator, cities, sweepable/gated, source URL, reviewed date) from each embassy's site; replace clones with data-driven template | scaling-expert drafts structure; ops/VA fills; dev templates |
| 5 | Run SEMrush/Keyword Planner pulls incl. ETIAS negatives; define per-market qualification tiles | Owner (SEMrush); lp-painpoint-analyst; lp-copy-expert |
| 6 | Reply-hours policy per market; WhatsApp vs SMS/phone for US/CA; test UAE Google Meet close | Owner; lead-chat-expert updates playbooks |
| 7 | Per-market trust strip + privacy addendum; local review collection plan | lp-copy-expert; compliance-research-agent (data-law memo); owner (local number) |
| 8 | Four memos: UAE visa-services licensing; US seller-of-travel reach; CA provincial travel licensing; SA CPA for packages. Decision: referral-only tours phase 1 | compliance-research-agent, then local professional; owner decides tours model |
| 9 | Accountant memo: VAT/GST per market + Stripe FX/settlement on £130 | Owner's accountant; scaling-expert facts pack |
| 10 | Confirm Tourloom relationship; re-cut UK auction; evaluate UK budget increase vs four-market spend | Owner; clarity-log-expert + Ads data |

## Architecture note
Reusable once merged: `supply_nodes.market` + `AvailabilityService($market)`; `config('ukv.markets.<code>')` for currency/price/WhatsApp/hours; LpAssembler/SlotTiles injection; disclaimer-strip; Country Logic sheet pattern. New: `/za|ae|us|ca` route group, market from URL (never Host); hreflang + per-market sitemap across .co.uk/.com; .com root market chooser (no geo redirect); market-aware StripeService; country x market operator dataset; per-market lead tagging; templated country page replacing clones.

## Open question
Who is the second operational identity (person, phone, login set) running non-UK per-client VAC work, and in which time zone? Every market's board, promise and launch gate depends on that before any code.
