# Phase 0 — decision brief (settle these to unblock the build)

Six decisions that shape the data model, pricing, and copy. Each: the question · options · **my recommendation +
why** · what it affects. Items marked **(confirm with a professional)** need an accountant/solicitor sign-off, but
the recommended default lets you proceed.

---

## D1. VAT on the service fee  **(confirm with an accountant)**
**Question:** Is your **service fee** subject to UK VAT, and are/should you be VAT-registered?
- **A. Not registered yet (under £90k threshold)** — no VAT charged; tiers stay as-is; show fees as "no VAT" .
- **B. Voluntarily register** — add 20% VAT on the service fee; tiers become VAT-inclusive; issue VAT invoices.
- **C. Register later at threshold** — start with A, switch to B when turnover nears £90k.
**Recommendation: A now → C at threshold.** Most lean startups stay unregistered until the £90k threshold; it
keeps prices lower + admin simpler. **Note:** the **government fee is a disbursement** (paid to the authority on
the customer's behalf) — typically outside VAT; confirm treatment with the accountant.
**Affects:** tier prices, receipts/invoices, the reports module.

## D2. FX rule on government fees
**Question:** Govt fees are often in USD/local currency; you collect GBP. How do you set the GBP govt-fee figure so
rate moves don't make you under-collect?
- **A. Buffer %** — store the govt fee in GBP with a built-in buffer (e.g. +5–8%) over the typical converted cost.
- **B. Quote-at-time** — convert the live rate at order time (more accurate, more complex).
- **C. Round up to a safe band** — fixed GBP bands set above worst-case.
**Recommendation: A (buffer %).** Simplest, protects margin, customers see one clean GBP number. Set the buffer in
the destination data; review quarterly. Move to B only if fees get large/volatile.
**Affects:** destination fee data, pricing, margin.

## D3. OISC/IAA regulatory scope  **(confirm with a solicitor)**
**Question:** Does your service need IAA (ex-OISC) regulation?
- **A. Outbound-only, no regulation needed** — you facilitate *British citizens travelling abroad*; IAA regulates
  *UK* immigration advice (inbound). Position firmly as outbound facilitation + admin, **not** UK immigration advice.
- **B. Get an opinion / register** — if you'll ever advise on UK immigration.
**Recommendation: A, confirmed by a quick solicitor check.** Almost certainly outside IAA scope, **but** you must
**never drift into UK-immigration advice** + your copy must say "facilitation/admin service," not "advice."
**Affects:** copy boundaries, disclaimers, what staff may say.

## D4. Consumer Contracts — 14-day cancellation right  **(confirm wording with a solicitor)**
**Question:** How do you handle the statutory 14-day online cancellation right vs starting work immediately?
- **A. Start-now + waiver** — at checkout the customer **consents to start immediately + waives** the 14-day right
  (standard for services begun at once); refunds then follow your service-fee policy.
- **B. Honour full 14 days** — don't start until day 14 or refund fully if cancelled (impractical for time-sensitive
  visas).
**Recommendation: A.** Visas are time-sensitive — customers want you to start now. The checkout tick "I want the
service to start now and I understand I waive my 14-day cancellation right once work begins" is the compliant +
practical path. Pair with your "refund our service fee if we haven't started / on refusal" policy.
**Affects:** checkout consent, ToS, refund policy.

## D5. GDPR cross-border transfer + sub-processors  **(confirm with a DPO/solicitor)**
**Question:** Lawful basis + safeguards for passport/personal data going to HubSpot (US) + Anthropic (US)?
- **A. Proceed with safeguards** — rely on each provider's UK/EU **SCCs / UK IDTA + adequacy**, sign their DPAs,
  **minimise PII** sent (already: passport number withheld from AI), disclose transfers in the privacy policy.
- **B. Keep data in-UK/EU only** — avoid US processors (drop HubSpot/Anthropic or use EU-region equivalents).
**Recommendation: A.** HubSpot + Anthropic both offer DPAs + standard transfer safeguards; sign them, minimise PII,
disclose. Cheapest path that stays compliant. **Tighten:** never send passport *numbers* or scans to AI (already
the rule); consider EU data-residency options if available.
**Affects:** privacy policy, DPAs, what PII each integration may send.

## D6. Which intake dimensions to capture (build once)
**Question:** Which fields go into the ONE apply-form + order-schema change (with residency)?
- **Recommendation — capture all of these now** (cheap to add together, expensive to add later):
  nationality · country of residence · residency status (+ visa expiry) · trip purpose · visa type/entries ·
  applicant name (vs payer) · is-minor (+ guardian) · previous-refusal flag · dual-nationality · insurance-required.
  Use most to **route** (standard vs manual_review) + inform the quote; not all need to gate.
**Affects:** the apply form (#299), order schema, eligibility router, pricing lane.

---

## Decision summary (tick to lock)
| # | Decision | My recommendation |
|---|---|---|
| D1 | VAT | Unregistered now → register at £90k; govt fee = disbursement |
| D2 | FX | Buffer % in the GBP govt fee |
| D3 | OISC/IAA | Outbound-only, outside scope (solicitor confirm) |
| D4 | 14-day right | Start-now + waiver at checkout |
| D5 | GDPR transfer | Proceed with DPAs + SCCs + PII minimisation |
| D6 | Intake fields | Capture all listed, route on the key ones |

**Once you accept/adjust these, Phase 1 builds against them — no rework.** The three "confirm with a professional"
items (D1/D3/D4/D5) can proceed on the recommended default while you get sign-off in parallel.
