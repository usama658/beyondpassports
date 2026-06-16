# Professional sign-off request emails — pre-launch (fill in and send)

> **Do not go live until all three are signed off (#124 / #125 / #130).**
> Each carries criminal, tax, or regulatory liability that cannot be undone after the fact. Book
> these reviews before taking the first paying customer.

**About UKVisaCo (paste into any email if the recipient asks):** UKVisaCo is an independent,
UK-based **outbound** travel-document **facilitation** service for British/UK travellers going
abroad — foreign tourist visas, eVisas, ETAs, and IDP (International Driving Permit) guidance. We are
**not** a government body and **not** UK immigration advisers. We charge a **service fee** that is
separate from, and on top of, the actual government fees we pay on the customer's behalf. "Express"
options speed **our handling**, not the government's decision; we do not guarantee approval. IDP is a
**guided self-service** product completed in person at PayPoint. Solo operator at launch.
Third-party sub-processors: Stripe (payments), HubSpot (CRM), Anthropic (AI, US), plus hosting and
email providers.

Fill in `[bracketed]` fields before sending.

---

## Email 1 — Data-protection solicitor / DPO (#124 — GDPR cross-border transfers)

**To:** [solicitor / DPO name] — Data-protection solicitor / outsourced DPO
**From:** [your name], UKVisaCo
**Subject:** Sign-off needed: UK GDPR cross-border transfers (passport data to US processors) before launch

Dear [name],

I run UKVisaCo, an independent UK outbound travel-document facilitation service (not a government
body, not UK immigration advice). Before we launch I need a professional sign-off on our
international data-transfer position. Customers give us passport scans, dates of birth, nationality,
and travel plans; some of that data is processed by US-based providers (Stripe for payments, HubSpot
for CRM, and Anthropic for AI guidance), plus our hosting and email providers.

Could you confirm or correct the following:

1. **Lawful basis + transfer mechanism (UK GDPR Art. 44+).** For sending customer personal data to
   our US processors, what is the correct transfer mechanism for each? Our working assumption is the
   **UK-US Data Bridge / DPF UK Extension** where the recipient is certified (we plan to verify each
   on dataprivacyframework.gov and screenshot the listing — e.g. Stripe, HubSpot), and the
   **IDTA / UK Addendum to the EU SCCs** as the contractual fallback where the recipient is not on
   the Data Bridge (e.g. Anthropic's commercial DPA). Is that layered approach sound?
2. **Transfer Risk Assessment + IDTA/SCCs.** Do we need a TRA for each non-adequacy transfer, and is
   the IDTA or UK Addendum required for any of these processors? Is the ICO TRA template sufficient
   for our scale?
3. **Special-category / Article 9.** Does a passport image (revealing nationality, and via photo /
   place of birth potentially more) trigger an Article 9 condition or a **DPIA** at our volume?
4. **Privacy-policy disclosure.** What must our privacy policy say about international transfers —
   naming the US, the specific recipients, and the mechanism for each?
5. **Retention.** We currently plan to delete passport scans [90] days after the application is
   completed. Is that defensible, and should it be stated in the policy?

We also intend to **minimise** what reaches the AI provider — no passport image, no full passport
number, no full DOB to Anthropic; only the non-identifying fields needed for the guidance task.
Please flag if you'd tighten that further.

A 30–45 minute review or a short written note would be ideal. Please let me know your availability
and fee.

Kind regards,
[your name]
UKVisaCo — [phone] / [email]

**What to attach:** our **privacy-policy draft** and our **sub-processor list** (processor, role,
data categories, DPA location, intended transfer mechanism for Stripe, HubSpot, Anthropic, hosting,
email).

---

## Email 2 — Accountant / VAT adviser (#125 — VAT treatment of the service fee)

**To:** [accountant name] — Accountant / VAT adviser
**From:** [your name], UKVisaCo
**Subject:** Sign-off needed: VAT treatment of our service fee + government-fee disbursement before launch

Dear [name],

I'm launching UKVisaCo, an independent UK outbound visa/eVisa/ETA/IDP facilitation service. We charge
a **service fee** and separately pass through the actual **government/visa fee** we pay on the
customer's behalf, at exact cost. I need your sign-off on the VAT treatment before we take the first
payment.

Could you confirm or correct the following:

1. **Service fee VAT status.** Is the UKVisaCo service fee a **standard-rated (20%)** supply of
   facilitation/administration services, or could any of it be exempt or outside scope? (We expect
   standard-rated and want to price on that basis even while below the threshold.)
2. **Place of supply.** For a **UK consumer** customer, is the place of supply the UK, and does that
   change if the customer is a UK business or based abroad?
3. **Disbursement treatment of the government fee.** Can we treat the passed-through government fee as
   a **disbursement (outside scope of VAT)** when passed on at exact cost with **no markup**, on a
   separate invoice line, with the customer being the party liable and us acting as their agent? Does
   that hold for **each fee type** we handle (some bought in our own name may fail the test)?
4. **Registration threshold timing.** Based on **service-fee turnover only** (assuming disbursement
   treatment holds), when must we register given the £90k rolling-12-month threshold, and at what
   run-rate should we start the registration process? Should we register voluntarily at launch, or
   stay unregistered until we approach the threshold (mostly consumer customers who can't reclaim)?
5. **Invoicing / receipt wording.** What exact wording should the two invoice lines use — e.g.
   "UKVisaCo service fee" vs "Government/visa fee (paid on your behalf — disbursement, at cost)" — to
   keep the disbursement treatment clean?

A one-hour review would be ideal. Please let me know your availability and fee.

Kind regards,
[your name]
UKVisaCo — [phone] / [email]

**What to attach:** our **pricing model** (showing the service fee vs government fee split) and a
**sample order/invoice** with the two separated lines and the supplier receipt for the exact
government fee paid.

---

## Email 3 — Immigration-law / OISC (IAA) specialist (#130 — regulatory scope)

**To:** [solicitor name] — Immigration / regulatory solicitor (IAA/OISC scope)
**From:** [your name], UKVisaCo
**Subject:** Sign-off needed: does outbound visa facilitation fall outside OISC/IAA regulation?

Dear [name],

I'm launching UKVisaCo, an independent UK service that helps British/UK travellers obtain
**foreign** travel documents — other countries' tourist visas, eVisas, ETAs — and provides IDP
(International Driving Permit) guidance. We help people **travel abroad**; we do **not** advise on UK
leave to enter/remain, UK visas, asylum, or British nationality. I need your sign-off that we sit
**outside** the regulated space before we launch.

Could you confirm or correct the following:

1. **Regulatory scope.** Does facilitating **outbound foreign** visas for UK travellers fall within
   OISC/IAA-regulated "immigration advice and services" (which, as I understand it, concerns **UK**
   immigration), or **outside** it? Our position is that outbound travel facilitation is not UK
   immigration advice and needs no IAA authorisation — is that correct for our exact offering?
2. **Wording to avoid.** What language must we avoid so we don't drift into regulated territory? We
   plan to position strictly as **facilitation / administration** and avoid the word "advice", and to
   avoid any wording implying UK-visa or government authority. Are there specific phrases you'd
   prohibit?
3. **Edge cases.** How should we handle customer questions that touch UK immigration (e.g. "can I
   re-enter the UK after?" / "will this affect my UK status?")? Our plan is a refusal script that
   declines and refers to an IAA-regulated adviser or gov.uk. Is that the right line?
4. **Registration.** Is any IAA registration or other authorisation needed for our model as
   described, or none?

A 30-minute review of our service description and disclaimer would confirm the outbound-only
carve-out. Please let me know your availability and fee.

Kind regards,
[your name]
UKVisaCo — [phone] / [email]

**What to attach:** our **site disclaimer** (e.g. "UKVisaCo provides outbound travel-document
facilitation for travel to other countries. We are not immigration advisers and do not provide UK
immigration advice. We are not a government body.") and our **service description** (what we do and
explicitly do not do).

---

> Reminder: all three (#124 / #125 / #130) must be signed off by the named professional before launch.
