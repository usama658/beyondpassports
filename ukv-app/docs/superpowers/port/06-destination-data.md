# 06 — Destination Data (UK-citizen outbound visa baseline)

**Purpose:** Authoritative, current government/authority data for the 8 launch destinations, for UK
(full British citizen) passport holders travelling for **tourism**. Replaces the placeholder figures
in `DestinationSeeder.php` (launch blocker #129 / H-5).

**Scope note:** Figures below are the **GOVERNMENT / issuing-authority fee only**. The facilitation
service's own tier fees (`tier_standard_gbp`, etc.) are a separate commercial decision and are NOT
researched here — they are left untouched.

> **DO NOT auto-apply.** Visa rules and fees change frequently and several values are seasonal or
> were taken from secondary sources. **Every value needs human confirmation against the live source
> immediately before go-live.** Research access date: **16 June 2026**.
> FX used for GBP conversions (mid-June 2026, indicative): **USD 1 ≈ £0.745**, **AUD 1 ≈ £0.525**.
> GBP figures are rounded to whole pounds for a pricing baseline; treat as approximate.

---

## Per-destination summary

| # | Destination | Requirement (UK citizen) | Govt/authority fee | Approx GBP | Processing | Max stay | Passport validity | Confidence |
|---|-------------|--------------------------|--------------------|-----------|-----------|----------|-------------------|-----------|
| 1 | **Turkey** | **Visa-free** (e-visa no longer required for UK) | none | £0 | n/a | 90 days / any 180 | **≥150 days** after arrival + 1 blank page | **High** (gov.uk) |
| 2 | **Egypt** | Visa required — eVisa **or** visa-on-arrival | VOA **US$30** (cash); eVisa similar | ~£22 | VOA instant; eVisa ~3–7 days | 30 days | 6 months after arrival + 1 blank page | **High** (gov.uk fee) |
| 3 | **India** | **eVisa** (e-Tourist) required | **US$25** Jul–Mar / **US$10** Apr–Jun (30-day) **+ 2.5% bank fee** | ~£19 (Jul–Mar) | up to 72 h | 30 days (double entry) | 6 months after arrival + 2 blank pages | **Medium** (seasonal; official-portal fee UNVERIFIED — see notes) |
| 4 | **USA (ESTA)** | **ESTA** (Visa Waiver Program) required | **US$40.27** | ~£30 | up to 72 h (apply ≥72 h ahead) | 90 days | valid for length of stay (6 mo recommended) | **Medium-High** (multiple secondary; gov.uk doesn't quote fee) |
| 5 | **Australia (eTA)** | **ETA (subclass 601)** required | **AUD$20** service charge (no visa charge) | ~£11 | usually minutes | 90 days per entry (12-mo validity) | valid for length of stay | **Medium-High** (gov.uk confirms "service fee"; AUD20 secondary) |
| 6 | **Thailand** | **Visa-free** (visa exemption) | none (TDAC is **free**) | £0 | TDAC instant | 60 days | 6 months after arrival + 1 blank page | **High** (gov.uk) |
| 7 | **UAE** | **Free visa-on-arrival** (no advance application) | none | £0 | on arrival | 90 days / any 180 | 6 months after arrival | **High** (gov.uk) |
| 8 | **Vietnam** | **Visa-free 45 days**, OR **eVisa** for longer | eVisa **US$25** single / US$50 multiple | ~£19 | ~3–5 working days | 45 days visa-free; **90 days** on eVisa | 6 months after arrival + 2 blank pages | **High** (gov.uk) / fee **Medium** (secondary) |

---

## Detail & sources

### 1. Turkey — Visa-free
- **Requirement:** No visa. The Turkey e-visa is **no longer required** for British citizen passport
  holders for tourism. "You can visit Turkey without a visa for up to 90 days in any 180-day period,
  for business or tourism."
- **Fee:** none.
- **Max stay:** 90 days in any rolling 180-day period.
- **Passport validity:** at least **150 days** after arrival date + at least 1 blank page (NOT the
  usual 6-month rule).
- **Source:** gov.uk Turkey entry requirements — https://www.gov.uk/foreign-travel-advice/turkey/entry-requirements (accessed 16 Jun 2026).
- **Confidence: High.** ⚠️ This overrides the seeder's current `eVisa` / 6-month assumption.

### 2. Egypt — Visa required (eVisa or visa-on-arrival)
- **Requirement:** Visa required. Options: e-visa online, visa-on-arrival at approved airports, or
  consulate. Narrow exception: visa-free <15 days at Sharm El-Sheikh/Dahab/Nuweiba/Taba resorts (air arrival).
- **Fee:** Visa-on-arrival **US$30 (cash only)** per gov.uk. eVisa priced similarly (single-entry).
- **Approx GBP:** ~£22.
- **Processing:** VOA instant; eVisa typically ~3–7 business days (secondary sources).
- **Max stay:** 30 days (standard). eVisa "valid for up to 3 months" refers to validity, not stay length.
- **Passport validity:** 6 months after arrival + 1 blank page.
- **Sources:** gov.uk Egypt entry requirements — https://www.gov.uk/foreign-travel-advice/egypt/entry-requirements (accessed 16 Jun 2026).
- **Confidence: High** for VOA fee (gov.uk quotes $30). Note the seeder placeholder said $25 → correct to $30.

### 3. India — e-Tourist Visa (eVisa)
- **Requirement:** e-Tourist Visa required, applied online before travel.
- **Fee (30-day):** **US$25** Jul–Mar; **US$10** Apr–Jun (off-season reduction), **plus 2.5% bank
  transaction fee**. (1-year ≈ US$40, 5-year ≈ US$80.)
- **Approx GBP:** ~£19 (Jul–Mar standard rate); ~£8 Apr–Jun.
- **Processing:** typically within 72 hours.
- **Max stay:** 30 days, double entry.
- **Passport validity:** 6 months after arrival + 2 blank pages.
- **Sources:** Official portal indianvisaonline.gov.in/evisa/tvoa.html (cited; **direct fetch was
  blocked in this session — live fee UNVERIFIED against the official portal**). Fee figures
  corroborated by Indian Embassy press release and multiple 2026 secondary guides:
  https://visasnews.com/en/india-why-the-off-season-tourist-e-visa-rate-is-more-attractive-in-2026/ ;
  https://www.indianembassyusa.gov.in/News?id=24889 (accessed 16 Jun 2026).
- **Confidence: Medium.** Fee is **seasonal** — a single static `govt_fee_gbp` cannot capture both
  bands. Recommend storing the **Jul–Mar standard (£19)** as baseline and flagging seasonality.
  Human must confirm current fee on the official portal before go-live.

### 4. USA — ESTA (Visa Waiver Program)
- **Requirement:** Approved **ESTA** required for VWP travellers (air/sea/land).
- **Fee:** **US$40.27** (US$4 processing + US$36.27 authorisation surcharge). Raised from $21 under
  HR-1; in force since ~Sep 2025, with a 27¢ inflation adjustment to $40.27 for 2026.
- **Approx GBP:** ~£30.
- **Processing:** apply at least 72 hours before travel; often quicker.
- **Max stay:** up to 90 days.
- **Passport validity:** must be valid for the length of planned stay (gov.uk). 6-month rule not
  imposed by US for VWP, but recommended.
- **Sources:** gov.uk USA entry requirements (confirms ESTA mandatory; does **not** quote fee) —
  https://www.gov.uk/foreign-travel-advice/usa/entry-requirements ; fee corroborated by Fragomen,
  VisaHQ, NBAA (HR-1 / Sep-2025 implementation), e.g.
  https://www.visahq.com/news/2026-05-08/us/esta-fee-now-4027-what-the-2026-price-means-for-business-and-leisure-travelers/
  (accessed 16 Jun 2026).
- **Confidence: Medium-High.** Fee is from consistent secondary sources, not the official CBP page
  directly — confirm on esta.cbp.dhs.gov before go-live. Seeder placeholder ($21/£17) is **stale**.

### 5. Australia — ETA (subclass 601)
- **Requirement:** Electronic Travel Authority (ETA, subclass 601), applied via the official
  **Australian ETA app**. (An alternative free eVisitor 651 also exists for UK passport holders.)
- **Fee:** **no visa application charge**, but an **AUD$20 service charge** to use the ETA app.
- **Approx GBP:** ~£11.
- **Processing:** usually granted within minutes.
- **Max stay:** up to 3 months (≈90 days) per entry; multiple entries over 12-month validity.
- **Passport validity:** valid for the length of planned stay (gov.uk).
- **Sources:** gov.uk Australia entry requirements (confirms "no visa application charge, but there
  may be a service fee") — https://www.gov.uk/foreign-travel-advice/australia/entry-requirements ;
  AUD$20 figure from multiple 2026 subclass-601 guides (accessed 16 Jun 2026).
- **Confidence: Medium-High.** Confirm AUD$20 on the official Australian ETA app/immi.gov.au before go-live.

### 6. Thailand — Visa-free (visa exemption)
- **Requirement:** No visa. Visa exemption for tourism. **TDAC (Thailand Digital Arrival Card)**
  mandatory from 1 May 2025 for all arrivals — completed online within 3 days before arrival, **free**.
- **Fee:** none (TDAC free).
- **Max stay:** 60 days.
- **Passport validity:** 6 months after arrival + 1 blank page.
- **Source:** gov.uk Thailand entry requirements — https://www.gov.uk/foreign-travel-advice/thailand/entry-requirements (accessed 16 Jun 2026).
- **Confidence: High.** Note required_docs should mention the mandatory free TDAC.

### 7. UAE — Free visa-on-arrival
- **Requirement:** No advance visa. Visitor/tourist visa issued **free of charge on arrival**.
- **Fee:** none.
- **Max stay:** up to 90 days over a 180-day period (begins at first entry). (Upgraded from 30 days
  in Aug 2024 — seeder's 30-day placeholder is **stale**.)
- **Passport validity:** 6 months after arrival.
- **Source:** gov.uk UAE entry requirements — https://www.gov.uk/foreign-travel-advice/united-arab-emirates/entry-requirements (accessed 16 Jun 2026).
- **Confidence: High.** Note: this is visa-on-arrival, not a pre-applied eVisa — `visa_type` and the
  service proposition for UAE should reflect that.

### 8. Vietnam — Visa-free 45 days, or eVisa for longer
- **Requirement:** Visa exemption for up to **45 days**. For longer/multiple entries, an **eVisa**
  (90-day, multiple entry) is available.
- **Fee:** eVisa **US$25 single-entry** / US$50 multiple-entry (official portal evisa.gov.vn).
- **Approx GBP:** ~£19 (single).
- **Processing:** ~3–5 working days.
- **Max stay:** 45 days visa-free; 90 days on eVisa.
- **Passport validity:** 6 months after arrival + 2 blank pages, no damage.
- **Sources:** gov.uk Vietnam entry requirements (confirms 45-day exemption + 90-day eVisa) —
  https://www.gov.uk/foreign-travel-advice/vietnam/entry-requirements ; fee from evisa.gov.vn via
  secondary 2026 guides (accessed 16 Jun 2026).
- **Confidence: High** for requirement (gov.uk); **Medium** for fee (secondary). Note many UK tourists
  need **no** visa at all (≤45 days) — the service proposition is the >45-day / multiple-entry eVisa.

---

## Material corrections vs current seeder placeholders

| Destination | Seeder placeholder | Verified | Action |
|-------------|--------------------|----------|--------|
| Turkey | `eVisa`, 6-mo passport | **Visa-free**, **150-day** passport rule | change visa_type + validity |
| Egypt | govt_fee $25/£20 | **$30 / ~£22** | raise fee |
| India | £30, 30 days | **~£19 std (seasonal $10–$25) + 2.5%** | lower & flag seasonality |
| USA | $21 / £17 | **$40.27 / ~£30** | nearly double — stale |
| Australia | £11 (AUD20) | AUD20 / **~£11** | confirms (recompute GBP) |
| Thailand | visa-free, 60 d | confirmed; add **free TDAC** | add doc |
| UAE | 30 days | **90 days**; it's **VOA** not eVisa | raise stay; fix type |
| Vietnam | eVisa $25, 90 d | **45-d visa-free** OR 90-d eVisa $25 | clarify dual path |

---

## Ready-to-paste PHP (DestinationSeeder `$destinations` field values)

> Apply **only after human verification**. Mirrors DestinationSeeder fields:
> `visa_type, govt_fee_gbp, max_stay_days, passport_validity_months, required_docs[]`.
> `passport_validity_months` is an integer-months field; Turkey's 150-day rule ≈ 5 months
> (encoded as `5` with a comment — confirm whether the field should instead store days).
> Service-tier fees and `required_for_uk` are NOT changed here.

```php
// === VERIFIED GOVT DATA (access date 2026-06-16) — HUMAN-CONFIRM BEFORE GO-LIVE ===
// FX used: USD 1 ≈ £0.745, AUD 1 ≈ £0.525. govt_fee_gbp rounded to whole £.

// Turkey — VISA-FREE now (e-visa no longer required for UK)
'visa_type' => 'Visa-free',
'govt_fee_gbp' => 0.00,
'max_stay_days' => 90,            // 90 in any 180-day period
'passport_validity_months' => 5, // actually ≥150 days after arrival + 1 blank page
'required_docs' => [
    'Passport valid 150+ days beyond arrival, with 1 blank page',
    'Onward / return travel details',
    'Proof of accommodation',
],

// Egypt — visa required (eVisa or visa-on-arrival US$30 cash)
'visa_type' => 'eVisa',
'govt_fee_gbp' => 22.00,          // VOA US$30 ≈ £22
'max_stay_days' => 30,
'passport_validity_months' => 6,
'required_docs' => $eVisaDocs,

// India — e-Tourist Visa (SEASONAL: US$25 Jul-Mar / US$10 Apr-Jun, +2.5% bank fee)
'visa_type' => 'eVisa',
'govt_fee_gbp' => 19.00,          // Jul-Mar standard US$25 ≈ £19 (Apr-Jun ≈ £8) — seasonal, verify
'max_stay_days' => 30,
'passport_validity_months' => 6,
'required_docs' => $eVisaDocs,

// USA (ESTA) — US$40.27
'visa_type' => 'ETA',
'govt_fee_gbp' => 30.00,          // US$40.27 ≈ £30
'max_stay_days' => 90,
'passport_validity_months' => 6, // US requires valid for stay; 6 mo recommended
'required_docs' => $etaDocs,

// Australia (eTA subclass 601) — AUD$20 service charge
'visa_type' => 'eTA',
'govt_fee_gbp' => 11.00,          // AUD$20 ≈ £10.50 → £11
'max_stay_days' => 90,            // up to 3 months per entry
'passport_validity_months' => 6, // AU requires valid for stay; 6 mo recommended
'required_docs' => $etaDocs,

// Thailand — visa-free 60 days (mandatory free TDAC)
'visa_type' => 'Visa-free',
'govt_fee_gbp' => 0.00,
'max_stay_days' => 60,
'passport_validity_months' => 6,
'required_docs' => [
    'Passport valid 6+ months, 1 blank page',
    'Thailand Digital Arrival Card (TDAC) — free, within 3 days of arrival',
    'Onward / return travel details',
    'Proof of accommodation',
],

// UAE — FREE visa-on-arrival, 90 days
'visa_type' => 'Visa on arrival',
'govt_fee_gbp' => 0.00,
'max_stay_days' => 90,            // 90 in any 180-day period (upgraded Aug 2024)
'passport_validity_months' => 6,
'required_docs' => [
    'Passport valid 6+ months',
    'Onward / return travel details',
    'Proof of accommodation',
],

// Vietnam — visa-free 45 days OR eVisa 90 days (US$25 single)
'visa_type' => 'eVisa',
'govt_fee_gbp' => 19.00,          // eVisa single US$25 ≈ £19 (≤45 days needs no visa)
'max_stay_days' => 90,            // eVisa; 45 visa-free
'passport_validity_months' => 6,
'required_docs' => $eVisaDocs,    // note: passport needs 2 blank pages
```

---

## UNVERIFIED / flags for human review
- **India fee** — official portal (indianvisaonline.gov.in) direct fetch was **blocked this session**;
  fee figures are from embassy press release + secondary guides. Also **seasonal** ($10 Apr–Jun /
  $25 Jul–Mar) so a single static value is lossy. **Confirm live.**
- **USA ESTA $40.27** — not quoted on gov.uk; corroborated across multiple reputable secondary sources
  (HR-1 / Sep-2025 rollout). Confirm on esta.cbp.dhs.gov.
- **Australia AUD$20** — gov.uk confirms a "service fee" exists but doesn't state the amount; AUD$20
  from secondary 2026 guides. Confirm in the official Australian ETA app.
- **Egypt / Vietnam eVisa fees** — gov.uk confirms Egypt VOA $30 directly; Vietnam eVisa $25 is from
  evisa.gov.vn via secondary guides. Confirm on official portals.
- **passport_validity_months type** — Turkey's rule is **150 days / 1 blank page**, not a clean month
  count; encoded as `5` with a comment. Decide whether the schema should store days for accuracy.
- All values: **rules change frequently — re-verify immediately before go-live.**
```
