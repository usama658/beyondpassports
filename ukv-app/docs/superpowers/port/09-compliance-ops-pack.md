# 09 — Compliance + Operations DECISION PACK (advisory draft)

Scope: a practical pre-launch decision pack for **Beyond Passports** — an independent, UK-based, outbound
visa / eVisa / ETA + IDP-guidance **facilitation** business. NOT a government body, NOT regulated UK
immigration advice. Charges a **service fee** that is separate from, and on top of, the actual
government fees it pays on the customer's behalf. Small/solo operator at launch. US/third-party
sub-processors: Stripe (payments), HubSpot (CRM), Anthropic (AI, US), hosting, email.

Date: 2026-06-16. Maps to open tasks #124, #125, #130, #132, #133, #134, #135, #136, #138, #139,
#140, #141.

> **Status of this document:** ADVISORY DRAFT prepared by an AI assistant from public sources. It is
> not legal, tax, or regulatory advice. Items marked **CONFIRM** require a named professional
> (accountant / solicitor / ICO) before you rely on them. Sources and access date are listed at the
> end; access date for all web sources is **2026-06-16**.

---

## 0. The three hard launch-blockers (read this first)

Everything in this pack is either a **hard blocker** (legal/tax — get it wrong and you are exposed to
criminal liability, tax debt, or an unlawful-transfer fine) or **advisory** (operational hygiene you
can refine after launch). The three hard blockers:

1. **#130 Stay inside the OISC/IAA line.** Giving *UK* immigration advice without authorisation is a
   **criminal offence**. Your whole "not regulated" position depends on never advising on UK leave to
   enter/remain, UK visas, asylum, or British nationality. This is a positioning + copy + staff-script
   blocker, not a paperwork one. (See §3.)
2. **#125 VAT treatment of the service fee and the government fee.** Get the disbursement test wrong
   and HMRC can treat the *gross* amount (service fee + passed-through government fee) as your taxable
   supply, dragging you over the £90k threshold far faster and creating a VAT debt you never collected.
   Decide the invoicing structure **before** you take the first payment. (See §2.)
3. **#124 Lawful basis for sending passport data to US processors.** Passport/biographic data flowing
   to HubSpot and Anthropic in the US needs a valid UK-GDPR transfer mechanism **in place from day
   one**. Transferring without one is an unlawful transfer (ICO enforcement risk) and you cannot
   retro-fit consent for data already sent. (See §1.)

The other nine items (#132, #133, #134, #135, #136, #138, #139, #140, #141) are **advisory** —
strongly recommended, but they make the business resilient/efficient rather than lawful. The
"decide-before vs decide-soon-after" split is in §13.

---

## 1. #124 — GDPR cross-border transfer (passport/customer data to US processors)

**(a) The issue.** Customers hand you passport scans, dates of birth, nationality, and travel plans —
this is personal data, and a passport image is arguably special-category-adjacent (it reveals
nationality and, via place of birth/photo, can imply other attributes). HubSpot and Anthropic process
that data in the US. Under UK GDPR you may only send personal data outside the UK if you have a valid
**transfer mechanism** and you have done a transfer risk assessment. The UK transfer landscape has
moved recently: the **Data (Use and Access) Act 2025** (Royal Assent 19 Jun 2025) introduced a "data
protection test" for transfers, the **UK-US Data Bridge** (live since Oct 2023) gives an
adequacy-style route for US recipients that have self-certified, and the **ICO updated its
international-transfers guidance on 15 Jan 2026**.

**(b) Recommended position.** Use a **layered** basis, strongest-available-first, per processor:

- **First choice — UK-US Data Bridge (adequacy route).** If the US recipient is on the Data Privacy
  Framework list *with the UK Extension active*, the transfer is treated like a transfer to an
  adequate country — no IDTA needed. **You must verify each recipient on the official DPF list, not
  assume it.** Stripe and HubSpot are commonly DPF/UK-Extension certified — **CONFIRM each on
  dataprivacyframework.gov before launch and screenshot the listing**.
- **Fallback — IDTA or the UK Addendum to the EU SCCs.** Where the recipient is *not* on the Data
  Bridge (Anthropic's commercial DPA uses EU SCCs Module 2/3 + the **UK Addendum** rather than the
  Data Bridge — **CONFIRM current status**), rely on the contractual route already in their DPA and
  keep a copy. Do a short **Transfer Risk Assessment (TRA)** using the ICO TRA tool for each.
- **Data minimisation to AI (this is the cheap, high-impact control).** Do **not** send raw passport
  images or full passport numbers to Anthropic. Use the AI for *guidance/classification on
  non-identifying fields* (destination, visa type, eligibility logic). Strip or tokenise PII before it
  reaches the model. This shrinks the #124 risk surface to near-zero for the AI leg regardless of the
  transfer mechanism.

**(c) Actions / checklist.**
- [ ] For each US processor: record the transfer mechanism (Data Bridge / IDTA / UK Addendum) and
      keep evidence (DPF listing screenshot OR signed DPA with SCCs+UK Addendum).
- [ ] Complete one TRA per non-adequacy transfer (ICO template).
- [ ] In the privacy policy, add an **International transfers** section naming the US, the recipients
      (Stripe, HubSpot, Anthropic, hosting, email), and the mechanism for each; link the DPF list.
- [ ] Add a data-minimisation rule to the AI integration: no passport image / passport number / full
      DOB to Anthropic; pass only the fields needed for the guidance task.
- [ ] Set passport-document retention (e.g. delete scans X days after the application is completed)
      and state it in the policy.
- **CONFIRM with ICO guidance / a data-protection solicitor:** whether a passport image triggers a
  **DPIA** for your volume, and whether your processing of nationality data needs an Article 9
  condition.

---

## 2. #125 — VAT on the service fee

**(a) The issue.** The UK VAT registration threshold from 1 Apr 2026 is **£90,000** of taxable
turnover on a **rolling 12-month** basis (deregistration £88,000); standard rate is **20%**. Two
questions: (i) when must Beyond Passports register, and (ii) is the *government fee* you collect part of your
taxable turnover or a non-VATable **disbursement**? This is the difference between your turnover being
"service fees only" (slow to hit £90k) vs "service fees + every government fee passed through" (fast to
hit £90k, and a 20% liability on money you never kept).

**(b) Recommended position.**
- **The service fee is a standard-rated (20%) supply** of facilitation/administration services. Even
  while you are *below* the threshold and not charging VAT, price and document on the assumption it
  becomes 20% later, so you can absorb or pass it on cleanly.
- **Treat the government/visa fee as a disbursement, not part of your supply** — but ONLY if you meet
  **all eight HMRC disbursement conditions**: you act as agent for the client; the client requested
  it; the client is the one liable for the fee; you paid the supplier (the government) on the client's
  behalf; the client knew the cost was theirs; it is shown **separately** on your invoice; you pass on
  the **exact** amount with **no markup**; and the service was supplied to the client, not consumed by
  you. If you mark up the government fee by even a penny, HMRC can treat the whole gross amount as your
  supply → VATable. So: **make all your margin in the named service fee; pass the government fee
  through at exact cost on a separate invoice line.**
- **Do not register voluntarily at launch** unless input-VAT recovery clearly beats the admin + the
  20% your (mostly consumer) customers can't reclaim — for a solo launch, stay unregistered until you
  approach the threshold.

**(c) Actions / checklist.**
- [ ] Structure the invoice with two clearly separated lines: **"Beyond Passports service fee"** (your supply)
      and **"Government/visa fee (paid on your behalf — disbursement)"** at exact cost.
- [ ] Build a rolling-12-month turnover tracker on **service fees only** (assuming disbursement
      treatment holds); set an alert at ~£75k to start the registration process before £90k.
- [ ] Keep the supplier receipt (proof of exact government fee paid) attached to every order.
- [ ] Write the customer terms so the client is contractually the person liable for the government fee
      and you are their agent in paying it (this underpins both the disbursement test and §5/§35).
- **CONFIRM with an accountant:** that the disbursement treatment holds for *each* fee type you handle
  (some "fees" bought in your own name fail condition 8), and the exact registration timing for your
  forecast.

---

## 3. #130 — OISC / IAA regulatory scope

**(a) The issue.** UK immigration advice/services are regulated under the Immigration and Asylum Act
1999; the regulator is the **Immigration Advice Authority (IAA)** — the rebranded OISC since 16 Jan
2025. Regulated "immigration advice and services" means advice on applications to the **UK**
authorities for leave to enter/remain, asylum, UK immigration bail, and **British** nationality. Doing
this unregulated is a **criminal offence**. Your business does the opposite direction: it helps people
**leave** the UK / travel to **other countries** (foreign tourist/eVisa/ETA, IDP guidance). That is
**outbound travel facilitation, not UK immigration advice**, and falls **outside** the IAA's remit.

**(b) Recommended position.** Beyond Passports is **not** providing UK regulated immigration advice and does
**not** need IAA authorisation — **provided it stays strictly on the outbound side of the line**. The
position is sound but fragile: it survives only if your copy, scripts, and AI outputs never drift into
UK immigration.

**(c) Where the line is — what you may and may NOT do.**
- **OK (outbound, unregulated):** explain another country's tourist-visa/eVisa/ETA process; complete
  and submit *foreign* government application forms on the customer's behalf; pay foreign government
  fees as agent; signpost gov.uk/FCDO for entry requirements; guide on IDP (an International Driving
  Permit — a PayPoint-issued document, per memory, not immigration at all).
- **NOT OK (UK immigration — regulated, criminal if unregulated):** advising whether someone can get/
  extend a **UK** visa or stay in the UK; advising on UK asylum, settlement, or **British**
  citizenship; advising a foreign national in the UK on their UK status; "fixing" UK refusals.
- **Watch the edge:** "Can I come back to the UK after?" / "will this affect my UK status?" questions
  from customers are UK-immigration questions — your script must **decline and refer to an IAA-
  regulated adviser or gov.uk**, not answer.

**(d) Actions / checklist.**
- [ ] Add a prominent disclaimer site-wide: *"Beyond Passports provides outbound travel-document facilitation
      for travel to other countries. We are not immigration advisers and do not provide UK immigration
      advice. We are not a government body."*
- [ ] Write a one-page **refusal script** for any UK-immigration question (decline + refer to IAA
      register / gov.uk).
- [ ] Constrain the AI system prompt to **refuse UK-immigration questions** and stay outbound-only.
- [ ] Avoid brand/marketing language implying UK-visa or government authority (see also §10).
- **CONFIRM with a solicitor (immigration/regulatory):** a 30-minute review of your service
  description and scripts to confirm the outbound-only carve-out for your exact offering.

---

## 4. #132 — Sub-processor DPAs and ROPA

**(a) The issue.** As a controller you must have a **Data Processing Agreement (Art. 28)** with every
processor, and you must keep a **Record of Processing Activities (ROPA, Art. 30)**. Most vendors
provide a standard DPA you accept online — you must actually accept/sign and **keep a copy**.

**(b) Recommended position.** Accept each vendor's standard DPA, store a dated copy, and maintain a
one-page ROPA. No bespoke negotiation needed at your scale.

**(c) Processor register — accept DPA + file evidence.**

| Processor | Role | Data | DPA — where | Transfer basis (verify) |
|-----------|------|------|-------------|--------------------------|
| Stripe | Payment processing | Name, card token, email, amount | stripe.com/legal/dpa | Data Bridge/SCCs — **CONFIRM on DPF list** |
| HubSpot | CRM / pipeline | Name, email, phone, enquiry, status | legal.hubspot.com/dpa | Data Bridge/SCCs — **CONFIRM on DPF list** |
| Anthropic | AI guidance | Minimised, **no passport image/number** | anthropic.com/legal (commercial DPA) | EU SCCs Mod 2/3 + UK Addendum — **CONFIRM** |
| Hosting provider | App + DB hosting | All app data incl. passport scans | provider's DPA page | depends on data location — **CONFIRM** |
| Email provider | Transactional/marketing email | Name, email, content | provider's DPA page | **CONFIRM** |

**(d) Actions / checklist.**
- [ ] Accept and download each DPA; store in `/legal/dpas/` with the date accepted.
- [ ] Confirm the actual hosting + email vendors (placeholders above) and add their DPA links + data
      location (prefer UK/EU hosting for passport scans to shrink #124).
- [ ] Build the ROPA: purposes, categories of data, categories of recipients (the table above),
      transfers + safeguards, retention periods, security measures.
- [ ] Re-review the list whenever you add a tool.
- **CONFIRM with ICO/DP solicitor:** whether your scale needs a registered **DPO** (likely no for a
  solo operator, but document the reasoning) and whether ICO **data-protection-fee registration** is
  due (most controllers pay the ICO fee — **likely yes, register and pay**).

---

## 5. #135 — Cash-flow / float (and safeguarding) — read with §2 and §34

**(a) The issue.** Between taking the customer's money and paying the foreign government fee, you are
**holding the customer's money**. If you hold "relevant funds" as a payment service you could fall
into the **Payment Services Regulations 2017 / EMRs** and **safeguarding** obligations — but those
rules apply to payment/e-money *institutions*, not to a trader who collects its own fees and pays a
supplier as part of providing a service.

**(b) Recommended position.** Structure the deal so you are **not** providing a regulated payment
service: the customer buys **a facilitation service from you** (one price = your service fee +
disbursement), and paying the government fee is **incidental to that service**, not a standalone
money-transfer/agency-collection business. Under that structure you are an ordinary trader, not a
payment institution, and the FCA safeguarding regime (new rules from **7 May 2026**, £100k audit
threshold) does **not** bite. Keep float **short** regardless: collect, then pay the government within
a day or two — don't sit on customer money.

**(c) Actions / checklist.**
- [ ] In terms: customer purchases a **service**; the government fee is paid by you as part of
      delivering that service (not "we hold your money in trust until you tell us to pay").
- [ ] Pay the government fee promptly after collection to minimise float and refund exposure.
- [ ] Keep a buffer of working capital so you can always front the government fee even if a Stripe
      payout is delayed (see §35).
- [ ] Clear refund policy for "paid us but application not yet submitted" vs "government fee already
      paid" (the latter usually non-refundable — the money has left).
- **CONFIRM with a solicitor:** that your model does **not** constitute a regulated payment service /
  money remittance; this is the one place the "facilitation, not payments" framing must be airtight.

---

## 6. #134 — Supplier payment mechanics (paying the actual government fees)

**(a) The issue.** You need a reliable way to pay foreign government/visa portals on the customer's
behalf — often by card, often in USD or local currency, sometimes with per-transaction or daily
limits and fraud blocks on government portals.

**(b) Recommended position.** Use a **dedicated business card** (ideally a multi-currency card, e.g. a
business account that issues virtual cards in USD/EUR) **separate from operating spend**, with limits
sized to your daily volume. Keep the operator's personal cards out of it entirely (clean books +
disbursement evidence per §2).

**(c) Actions / checklist.**
- [ ] Open a dedicated business bank/card; enable multi-currency or a USD virtual card to cut FX cost
      and reduce decline/fraud flags on US-billed portals.
- [ ] Note each portal's quirks: card-type restrictions, 3-D Secure, per-day caps; keep a runbook.
- [ ] Capture the **exact amount charged in GBP** (including the card's FX) as the disbursement proof.
- [ ] Set a sensible card limit + transaction alerts; never store card details in HubSpot.
- [ ] Have a **backup payment method** for portals that decline the primary (see §36 SPOF).
- **CONFIRM with accountant:** whether the card-issuer FX spread can be passed through as part of the
  disbursement or must be absorbed in the service fee (ties to the no-markup disbursement rule, §2).

---

## 7. #133 — FX risk (GBP service fee vs USD/local government fees)

**(a) The issue.** You quote the customer a **GBP** total, but some government fees are denominated in
**USD or local currency** and settle days later at a different rate. If GBP weakens between quote and
payment, the government-fee leg costs you more and eats your margin (or you under-recover the
disbursement).

**(b) Recommended position.** Don't fix a GBP government-fee figure far ahead of payment. Use one or
more of: (i) **quote the disbursement with a small FX buffer** (and refund any over-collection to keep
the no-markup disbursement rule clean — buffer must not become margin); (ii) **collect and pay
same-day** so the window is tiny; (iii) **hold a USD balance** in the multi-currency account to pre-buy
currency when rates are good; (iv) **build FX volatility into the service-fee margin**, which is yours
to set, rather than into the disbursement.

**(c) Actions / checklist.**
- [ ] Decide the FX policy: same-day settlement + USD float for the common destinations.
- [ ] If you add an FX buffer to the disbursement, **refund overage** (or it breaks §2's exact-cost
      rule); cleaner to carry FX risk in the service fee.
- [ ] Re-pull the live government fee at checkout (don't rely on the cached per-destination figure,
      which is flagged unverified — see #138/§9).
- [ ] Review FX exposure monthly against your USD spend.
- **CONFIRM with accountant:** the cleanest VAT-safe way to recover FX cost (almost certainly via the
  service fee, not the disbursement).

---

## 8. #136 — Business continuity / single point of failure

**(a) The issue.** Solo operator = the business stops if the operator is ill, loses access, or is
unreachable. Customers mid-application, government fees paid but not submitted, and inbox/CRM all
depend on one person.

**(b) Recommended position.** Reduce key-person risk with documentation, redundancy, and a
"break-glass" handover so a trusted second person (spouse/accountant/contractor) can keep customers
whole even if they can't run the whole business.

**(c) Actions / checklist.**
- [ ] **Runbook** (paper + encrypted file): how to submit each destination's application, refund a
      customer, and reach support for each vendor.
- [ ] **Credential vault** (password manager) with an **emergency-access/legacy contact** set, so a
      trusted person can recover access; store recovery codes offline.
- [ ] **Off-site, automated backups** of the app DB and passport documents (see §39), tested restore.
- [ ] A short **"if I'm unavailable" plan**: an auto-responder + a trusted contact who can pause new
      orders, finish in-flight ones, and issue refunds.
- [ ] Domain, hosting, email auto-renew on (a lapsed domain is an instant outage).
- **CONFIRM:** none required — operational.

---

## 9. #138 — Visa-rule-change monitoring (keeping per-destination data current)

**(a) The issue.** The per-destination data (fees, eligibility, processing times, ETA/eVisa rules) is
currently **flagged unverified** (see `08-audit-pass3.md` H-5). Visa rules and fees change without
notice; stale data = wrong quotes (FX/VAT/refund knock-ons) and reputational/consumer-law risk.

**(b) Recommended position.** Treat the destination dataset as a **living record with a named owner, a
cadence, and a source of truth per field**. At launch, **re-pull the government fee live at checkout**
for the destinations you actually sell, and don't display a destination you haven't verified.

**(c) Actions / checklist.**
- [ ] Assign an **owner** (the operator at launch) and a **review cadence**: monthly for active
      destinations, quarterly for the long tail; immediate on any known rule change.
- [ ] Record **per-field source + last-verified date** (official government portal first, FCDO/gov.uk
      second). Show "last checked" to build trust.
- [ ] Subscribe to official change feeds where available (gov.uk foreign-travel-advice email alerts
      per country; destination government portals).
- [ ] Gate publishing: a destination stays hidden/"contact us" until verified.
- [ ] Reconcile the cached figure vs the live checkout figure; alert on drift.
- **CONFIRM:** none — operational, but accuracy underpins the §2 disbursement and §41 signposting.

---

## 10. #140 — Acquisition channels (launch marketing) — brief

**(a) The issue.** Realistic, low-budget channels for a niche outbound-visa facilitation service at
solo-launch scale, without tripping the §3 positioning rules or paid-platform policy on
immigration/government-adjacent ads.

**(b) Recommended position (priority order).**
1. **SEO / content** — already built; lead channel. Per-destination "how to get a [country]
   eVisa/ETA from the UK" pages, kept current via §9. Highest ROI for this niche.
2. **Paid search (Google Ads)** — high-intent ("[country] visa from UK") converts well, but
   government-services advertising has **policy/verification requirements** and platforms may demand
   proof you're not impersonating a government service. Lead with clear "independent / not a government
   body" copy (also satisfies §3). Start small, exact-match, branded + high-intent only.
3. **Partnerships** — travel agents, relocation/expat services, language-test/IDP (PayPoint)
   adjacencies, student-travel orgs; referral arrangements.
4. **Comparison/marketplace + reviews** — Trustpilot from day one (trust is the whole game in this
   category, given scam-adjacent competitors).
5. **Email** — capture + nurture (HubSpot) with the entry-requirements signposting content (§41) as
   the lead magnet.

**(c) Actions / checklist.**
- [ ] Audit ad copy and landing pages for §3 compliance (no government impersonation).
- [ ] Set up Trustpilot + review-request flow.
- [ ] Build a partnership one-pager and target 5 adjacent referrers.
- **CONFIRM:** Google Ads/Microsoft Ads **government-services advertiser verification** requirements
  before spending (platform policy, not law).

---

## 11. #141 — Entry requirements beyond the visa (vaccinations / transit / onward ticket / passport validity)

**(a) The issue.** A visa alone doesn't guarantee entry. Travellers also need valid passport validity
(often 6 months), vaccinations, transit visas, onward/return tickets, and proof of funds. If you
**advise** on these (especially medical/vaccination) you take on liability and stray toward advice you
aren't qualified to give; if you **ignore** them, customers get turned away and blame you.

**(b) Recommended position.** **Signpost, don't advise.** Surface the relevant authoritative source
(gov.uk foreign-travel-advice for the destination, NHS Fit for Travel / TravelHealthPro for health)
and make the customer confirm they've checked. Never give medical advice; never guarantee entry.

**(c) Actions / checklist.**
- [ ] On each destination page and in the post-purchase flow, link the **gov.uk foreign-travel-advice
      "Entry requirements"** page for that country and the NHS travel-health source.
- [ ] Add a **checklist + acknowledgement**: "I confirm I have checked passport validity, vaccinations,
      transit, and onward-ticket requirements on gov.uk/NHS." Log the acknowledgement.
- [ ] Use the FCDO-style disclaimer: general signposting for convenience, not a substitute for
      official sources / professional advice; no liability for reliance.
- [ ] AI guidance: may **summarise and link** official requirements; must **not** give medical advice
      or guarantee entry, and must cite/link the source.
- **CONFIRM:** none required — but the disclaimer wording is worth a solicitor's eye alongside §3.

---

## 12. #139 — Admin security checklist

**(a) The issue.** A single admin account holding passport scans, customer PII, and payment context is
a high-value target. 2FA is now built (Breezy, per `08-audit-pass3.md`); the rest is hardening.

**(b) Recommended position.** Apply standard least-privilege + defence-in-depth; for a solo operator
the highest-value items are 2FA (done), off-site backups, patching, and a recovery plan.

**(c) Checklist.**
- [x] **2FA** on admin (Breezy — built; confirm enforced for all admin roles).
- [ ] **Brute-force / rate-limiting** on the login route; lockout + alert on repeated failures.
- [ ] **Patching cadence**: framework, plugins, OS, dependencies — monthly + on security advisories.
- [ ] **Off-site, encrypted backups** (DB + passport documents), automated, with a **tested restore**;
      retention aligned to §1 retention policy (don't keep passport scans longer than stated).
- [ ] **Least-privilege roles**: admin vs staff vs read-only; per `08-audit-pass3.md` role policies are
      on all 10 Filament resources — confirm no over-privileged accounts.
- [ ] **Uptime/monitoring** + alerting; TLS/HSTS; CSP already hardened (per pass-3 audit).
- [ ] **Secrets**: no secrets in repo; rotate keys; unique strong passwords in a vault.
- [ ] **Access review**: quarterly review of who can log in; remove stale accounts/contractors.
- **CONFIRM:** none — operational; ties to §1 (retention) and §8 (recovery).

---

## 13. Prioritised split — decide-before-launch vs decide-soon-after

### Decide BEFORE launch (do not take a paying customer without these)
- **#130 OISC/IAA positioning** — site disclaimer + UK-immigration refusal script + AI guardrail.
  *(Hard blocker — criminal exposure.)*
- **#125 VAT invoice structure** — two-line invoice (service fee vs disbursement), turnover tracker,
  no markup on the government fee. *(Hard blocker — tax exposure.)*
- **#124 Transfer mechanism + AI data-minimisation** — DPF-list check per US processor, IDTA/UK
  Addendum fallback, privacy-policy transfers section, no passport image/number to Anthropic.
  *(Hard blocker — unlawful-transfer exposure.)*
- **#132 DPAs accepted + ROPA + ICO fee** — accept each vendor DPA, build ROPA, register/pay the ICO
  data-protection fee. *(Strongly advisable before launch; ICO fee is a legal duty for most
  controllers.)*
- **#135 + #134 "facilitation not payments" framing + a working supplier-payment card** — so you can
  actually pay government fees and stay outside the payment-services regime.

### Decide SOON AFTER launch (advisory; refine in the first weeks)
- **#133 FX policy** (same-day settlement / USD float / margin buffer).
- **#138 destination-data monitoring cadence + live-fee re-pull** (re-pull at checkout from day one;
  formalise the cadence shortly after).
- **#136 continuity / SPOF** (runbook, credential legacy contact, tested off-site restore).
- **#139 admin hardening** beyond the already-built 2FA (brute-force, patching, backups).
- **#141 entry-requirements signposting** (links + acknowledgement checklist + disclaimer).
- **#140 acquisition channels** (Trustpilot, paid-search verification, partnerships).

### The path
**Lock the three hard blockers first** — OISC/IAA positioning (#130), VAT invoice structure (#125),
and the transfer mechanism + AI minimisation (#124) — because each carries criminal, tax, or
regulatory liability that you cannot undo after the fact. In parallel, accept the vendor DPAs, build
the ROPA, pay the ICO fee, and confirm the "facilitation not payments" framing with a solicitor.
Everything else (#133, #134 mechanics detail, #135 float discipline, #136, #138 cadence, #139
hardening, #140, #141) is advisory hygiene you can stand up in week one and tighten over the first
month. Get a **one-hour solicitor review (OISC line + payments framing)** and a **one-hour accountant
review (VAT disbursement + registration timing)** booked before the first sale — those two
conversations de-risk all three hard blockers.

---

## Sources (all accessed 2026-06-16)

- VAT threshold £90k / 20% / rolling 12-month (2026): THP Chartered Accountants —
  https://www.thp.co.uk/uk-vat-threshold-2026-a-complete-guide-for-growing-businesses/ ;
  Numeric Accounting — https://www.numericaccounting.co.uk/blog/when-should-you-register-for-vat-a-practical-guide-for-small-businesses-2026-to-2027/ ;
  HMRC/HoC Library — https://commonslibrary.parliament.uk/research-briefings/sn00963/
- Disbursement vs supply — 8 HMRC conditions: Law Society —
  https://www.lawsociety.org.uk/topics/business-management/vat-treatment-of-disbursements-and-expenses ;
  ACCA — https://www.accaglobal.com/uk/en/technical-activities/uk-tech/in-practice/2022/march/vat-on-disbursements-and-recharges.html ;
  Accounts for Lawyers — https://www.accountsforlawyers.co.uk/blog/vat-compliance/disbursements-vat-treatment-uk-law-firms
- OISC → IAA scope (UK immigration only; criminal if unregulated): Free Movement —
  https://freemovement.org.uk/what-is-the-oisc/ ; LawWorks —
  https://www.lawworks.org.uk/solicitors-and-volunteers/resources/oisc-regulation-and-clinics ;
  LexisNexis — https://www.lexisnexis.com/en-gb/legal/guidance/giving-immigration-advice-in-the-uk
- UK GDPR transfers — IDTA / UK Addendum / Data Bridge / DUAA 2025 / ICO Jan-2026 guidance:
  DPO Centre — https://www.dpocentre.com/blog/international-data-transfers-explaining-eu-sccs-uk-addendum-and-uk-idta/ ;
  Harper James — https://harperjames.co.uk/article/idta-and-uk-addendum-explained/ ;
  ICO (UK Extension to DPF) — https://ico.org.uk/for-organisations/uk-gdpr-guidance-and-resources/international-transfers/adequacy-regulations/how-does-the-uk-extension-to-the-eu-us-data-privacy-framework-work/
- UK-US Data Bridge + DPF list (verify each recipient): White & Case —
  https://www.whitecase.com/insight-alert/uk-us-data-bridge-practical-considerations-uk-organisations ;
  DPF list — https://www.dataprivacyframework.gov/
- Anthropic DPA / SCCs + UK Addendum: Anthropic Privacy Center —
  https://privacy.claude.com/en/articles/10458704-how-does-anthropic-protect-the-personal-data-of-claude-users ;
  vendor summary — https://companyscope.io/vendors/anthropic
- Payment-services safeguarding (PSRs 2017 / EMRs / new rules 7 May 2026 / £100k audit threshold):
  FCA PS25/12 — https://www.fca.org.uk/publications/policy-statements/ps25-12-changes-safeguarding-regime-payments-and-e-money-firms ;
  Ashurst — https://www.ashurst.com/en/insights/uk-emoney-and-payment-institutions-must-comply-with-new-safeguarding-rules-from-7-may-2026/
- FCDO / gov.uk entry-requirements signposting + disclaimer: GOV.UK foreign travel advice —
  https://www.gov.uk/foreign-travel-advice ; About FCDO travel advice —
  https://www.gov.uk/guidance/about-foreign-commonwealth-development-office-travel-advice

> Reminder: items marked **CONFIRM** require a named accountant, solicitor, or the ICO before you rely
> on them. This pack is an advisory draft, not professional advice.
