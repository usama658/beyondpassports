# 02 — Domain logic (port reference)

Extracted verbatim from the live WordPress mu-plugins so the Laravel services reproduce
**identical** behaviour. Each section gives: the source file, the load-bearing code, and a
plain-rules translation. WordPress storage idioms (`get_post_meta`, transients, journey
arrays) are noted but are NOT part of the rule — they map to the Laravel order model /
audit log.

> Meta-key convention (carried from MEMORY): order meta is `ukv_`-prefixed; `destination`
> is stored as a **display name** on the order (`ukv_destination`) but resolved to a **slug**
> for Pods/destination lookups. Pricing/validity/required-docs are **per-destination** Pods
> fields, not global constants.

---

## 1. Eligibility router

**Source:** `wp-content/mu-plugins/ukv-eligibility.php`

### 1.1 Inputs (intake axes)

Stored on the order (each as `ukv_<key>`). The router reads six of them:

| Axis | Meta key | Type | Used by router? |
|---|---|---|---|
| nationality | `ukv_nationality` | string (free-text country) | **yes** |
| residence_country | `ukv_residence_country` | string | **yes** |
| residency_status | `ukv_residency_status` | enum `citizen\|permanent\|visa_holder\|other` | **yes** |
| trip_purpose | `ukv_trip_purpose` | enum `tourist\|business\|transit\|study\|other` (default `tourist`) | **yes** |
| prior_refusal | `ukv_prior_refusal` | bool (`'1'`/`''`) | **yes** |
| is_minor | `ukv_is_minor` | bool | **yes** |
| visa_entries | `ukv_visa_entries` | string | captured only, **not** in router |
| dual_nationality | `ukv_dual_nationality` | string | captured + shown in metabox, **not** in router |
| residency_visa_expiry | `ukv_residency_visa_expiry` | string | captured only |
| insurance_required | `ukv_insurance_required` | bool | captured only |
| applicant_name / guardian_name | `ukv_applicant_name` / `ukv_guardian_name` | string | captured only |

> **Flag:** the goal mentions `visa_entries` and `dual_nationality` as router inputs. They
> are **captured and displayed but do NOT affect the lane decision** in code. Don't let them
> influence the Laravel routing rule unless that is an intentional behaviour change.

### 1.2 The routing rule (decision D6)

```php
function ukv_eligibility_evaluate( array $a ) {
    $nat     = $a['nationality'] ?? '';
    $res     = $a['residence_country'] ?? '';
    $status  = $a['residency_status'] ?? '';
    $purpose = $a['trip_purpose'] ?? 'tourist';
    $refusal = ! empty( $a['prior_refusal'] );
    $minor   = ! empty( $a['is_minor'] );
    if ( ukv_is_uk( $nat ) && ukv_is_uk( $res ) && 'citizen' === $status
         && 'tourist' === $purpose && ! $refusal && ! $minor ) {
        return 'standard';
    }
    return 'manual_review';
}
```

`ukv_is_uk()` slug-normalises a country string and matches case-insensitively against:
`uk, gb, gbr, united kingdom, great britain, britain, england, scotland, wales, northern ireland`.

**Pseudocode:**
```
lane(axes):
    isUK(s): lowercase(trim(s)) ∈ {uk, gb, gbr, united kingdom, great britain,
                                   britain, england, scotland, wales, northern ireland}
    return STANDARD  iff  isUK(nationality)
                     AND  isUK(residence_country)
                     AND  residency_status == 'citizen'
                     AND  trip_purpose == 'tourist'   (missing ⇒ default 'tourist')
                     AND  NOT prior_refusal
                     AND  NOT is_minor
    else MANUAL_REVIEW
```
The standard lane = UK passport + UK residence + citizen + tourist + not minor + no prior
refusal — **confirmed against code**. Everything else ⇒ `manual_review`.

### 1.3 Lane states & agent override (Clear / Refer)

Four lane values live in `ukv_eligibility`:
- `standard` — auto, self-serve.
- `manual_review` — auto, needs agent.
- `cleared` — agent decision: allow through pipeline.
- `referred` — agent decision: escalate / hold.

`ukv_eligibility_apply()` recomputes `standard`/`manual_review` on save **but never
overwrites an agent decision**:
```php
if ( ! in_array( $existing, [ 'cleared', 'referred' ], true ) ) {
    update_post_meta( $order_id, 'ukv_eligibility', ukv_eligibility_evaluate( $axes ) );
}
```
**Rule:** recompute the auto lane only when current lane ∉ {cleared, referred}.

Agent action sets the lane to `cleared` or `referred`, stores a free-text note
(`ukv_eligibility_note`), and **on change** appends an audit entry
`"Eligibility cleared|referred: {note}"`.

### 1.4 Clearance / gate (blocks past `paid`)

```php
function ukv_order_is_cleared( $order_id ) {
    $e = get_post_meta( $order_id, 'ukv_eligibility', true );
    return in_array( $e, [ 'standard', 'cleared' ], true );
}
```
"Cleared" = lane is `standard` OR `cleared`. (`manual_review` and `referred` are NOT cleared.)

**Eligibility gate** (`ukv_eligibility_gate_enforce`): on a status transition,
```
if ukv_order_is_cleared(order):         allow   (standard & cleared never blocked)
elif attempted_status == 'paid':        allow   ('paid' is the entry stage; order may sit there)
else:                                    BLOCK -> revert ukv_status to ukv_status_last
                                                 (fallback 'paid'); audit "Blocked: eligibility not cleared."
```
So a non-cleared (`manual_review`/`referred`) order **cannot advance past `paid`**. It runs at
hook priority 10 and only on a real transition (`new != last`, `new != ''`, `new != 'paid'`).

---

## 2. Pricing / quote

### 2.1 Fixed tiers (standard self-serve lane)

**Source:** `ukv-hubspot.php` (charge hook) + per-destination Pods fields in `ukv-forminator-glue.php`.

There is **no single global tier table**. Service-fee amounts are **per-destination** Pods
number fields: `tier_standard_gbp`, `tier_express_gbp`, `tier_premium_gbp`. The checkout
form posts the chosen tier's **price** as `radio-1` (a number, not a label).

The tier **name** is reverse-mapped from that posted price:
```php
$tierP = (float) $prepared_data['radio-1'];          // service fee chosen
$govt  = (float) ukv_dest_value( $dest, 'govt_fee_gbp' );
$total = $tierP + $govt;
$tierName = [ 29=>'Standard', 49=>'Express', 79=>'Premium',
              25=>'Standard', 39=>'Express', 59=>'Premium',
              35=>'Standard', 55=>'Express', 85=>'Premium' ][ (int) $tierP ] ?? (string) $tierP;
```
**Total = service fee (tier) + government fee (per destination).** Order is created with
`ukv_tier` (name), `ukv_service_fee` (=tierP), `ukv_govt_fee` (=govt), `ukv_total`, status `paid`.

> **Flag — contradictory tier amounts.** The price→name map hardcodes **three** disjoint
> price sets (29/49/79, 25/39/59, 35/55/85) for the three destination price bands. Any
> destination whose Pods tier prices fall outside these nine numbers gets `$tierName` =
> the raw number string. For the port, do NOT hardcode these numbers: derive the tier
> **name** by comparing the chosen service fee against the destination's own
> `tier_standard_gbp / tier_express_gbp / tier_premium_gbp`, which is the source of truth.
> The 9-number map is a fragile launch shortcut.

**SLA per tier** (`ukv-orders.php::ukv_order_sla_hours`) — uses substring match on the tier
NAME, independent of the price map:
```
express  -> 24h   |   premium -> 12h   |   else (standard) -> 72h
```
> **Flag — naming inconsistency.** "Premium" gets the *fastest* SLA (12h) yet is the
> *highest-priced* tier; "Express" (24h) sits between. Confirm this is intended before porting
> — it's the reverse of the usual standard < express < premium speed ordering.

### 2.2 Bespoke-quote path (manual_review / cleared lane)

**Source:** `ukv-quote.php`

Manual-review orders are **NOT priced on the fixed tiers** — an agent sets a bespoke amount
and sends a Stripe Payment Link. Applicability:
```php
function ukv_quote_applies( $order_id ) {
    $e = get_post_meta($order_id,'ukv_eligibility',true);
    if ( 'manual_review' === $e || 'cleared' === $e ) return true;     // bespoke lane
    if ( ukv_order_is_cleared($order_id) && 'standard' !== $e ) return true;  // guarded fallback
    return false;                                                       // 'standard' => fixed tiers
}
```
Quote status (`ukv_quote_status`, default `none`): `none → sent → paid`.

- `ukv_set_quote(order, amount)` — stores `ukv_quote_amount`; sets status to `none` only if
  unset. Does **not** send.
- `ukv_quote_send(order)`:
  ```
  amount = ukv_quote_amount(order)
  if amount <= 0: return false            // nothing to send
  status = 'sent'; ukv_quote_sent_at = now()
  ukv_quote_link = PLACEHOLDER ('https://buy.stripe.com/PLACEHOLDER')
  audit "Quote sent: GBP{amount, 2dp}"
  return true
  ```

**Stripe Payment Link behaviour:** the link is a **placeholder constant** today
(`UKV_QUOTE_PLACEHOLDER_LINK`). The real per-order Stripe Payment Link is to be generated via
the live Stripe key at launch and written into `ukv_quote_link`. In the port, `ukv_quote_send`
should call the Stripe Payment Links API to create a link for `amount` and store the returned
URL; the `paid` status is set when payment confirms (no webhook wired here yet).

> **Flag — dead-ish today:** the standard self-serve checkout charges via the Forminator
> Stripe field on the Apply form (not Payment Links). Payment Links are only the bespoke lane,
> and currently a placeholder string. The "paid" quote status has no setter in these files.

---

## 3. Stage gates

**Source:** `ukv-stage-gates.php` (general flow), `ukv-orders.php` (canonical stage list),
`ukv-qa-gate.php` (the `submitted` transition only).

### 3.1 Ordered stages

`UKV_ORDER_STATUSES` (canonical pipeline, in order):
```
paid → awaiting_docs → doc_review → submitted → awaiting_decision → delivered → won
                                                                            ↘ rejected
                                                                            ↘ refunded
```
`paid` is the entry stage (set at order creation). `won / rejected / refunded` are terminal.
`ukv_status_last` records the previous stage; the gates revert to it on a blocked move.

### 3.2 Entry requirements per target stage

`ukv_stage_entry_requirements()` — a target stage **absent** from this map has **no** entry
criteria (always enterable). `submitted` is **delegated to the QA gate** (section 4) and is
never blocked by this engine.

| Target stage | Entry requirement (must be TRUE to advance) |
|---|---|
| `doc_review` | `≥ 1` document uploaded — `count(array_filter(ukv_documents)) >= 1` |
| `awaiting_decision` | `ukv_govt_ref` non-empty (order was actually submitted to govt) |
| `delivered` | `ukv_govt_ref` non-empty (can't deliver what was never submitted) |
| `submitted` | **delegated** — always `ok=true` here; QA gate owns it |
| any other (`awaiting_docs`, `won`, `rejected`, `refunded`, `paid`) | none — always enterable |

### 3.3 Enforcement

`ukv_stage_gate_enforce(order, attempted)`:
```
if attempted == 'submitted':            return false   (QA gate owns it)
prev = ukv_status_last
if attempted == prev:                   return false   (no real transition)
check = can_enter(order, attempted)
if check.ok:                            return false   (allow)
else:                                                   # BLOCK
    revert_to = (prev != '' && prev != attempted) ? prev : 'paid'
    ukv_status = revert_to
    set admin-notice transient with reasons
    audit "Stage move to {attempted} blocked: {reasons}"
    return true
```
Hook order matters: **QA gate priority 9 → stage gate priority 10 → email hook priority 12**,
so a reverted status never fires a stage-change email. (`ukv-required-docs.php` syncs
`ukv_required_docs` count at priority 7, before the gates.)

---

## 4. QA gate (pre-submission)

**Source:** `ukv-qa-gate.php`. Sole authority on the `→ submitted` transition.

`ukv_qa_can_submit(order)` — order may be submitted iff ALL true:
```
1. post_type == 'ukv_order'                                  (else fail "Not a valid order.")
2. document completeness:
     have     = count(array_filter(ukv_documents))
     required = (int) ukv_required_docs            // per-destination count, synced by ukv-required-docs.php
     if required > 0:  have >= required            else: have >= 1   (floor = at least one doc)
3. human sign-off:   ukv_qa_signed_off === '1'
```
Returns `{ ok, reasons[] }`. Enforcement (`ukv_qa_gate_enforce`) only acts when
`attempted == 'submitted'`; on failure it reverts `ukv_status` to `ukv_status_last`
(fallback `doc_review` if last is empty or itself `submitted`), audits
`"Submission blocked by QA gate: {reasons}"`, sets an admin notice. Sign-off save runs at
priority 8 (before the gate at 9) so a fresh tick counts in the same save.

**Required-docs source** (`ukv-required-docs.php`): per-destination Pods `required_docs`
(comma/newline list). Default when unset: `["Passport bio page", "Passport photo"]` (count 2).
`ukv_sync_required_count()` mirrors that count onto `ukv_required_docs` order meta on every
save (priority 7). `ukv_order_docs_complete()` = uploaded `>=` destination required count.

**Pseudocode:**
```
canSubmit(order):
    if not order: fail
    have = countUploadedDocs(order)
    need = order.requiredDocsCount   (>0 ? compare to need : floor 1)
    if have < need: reason "Only {have} of {need} required document(s) attached."  (or "No documents attached")
    if not order.qaSignedOff: reason "QA sign-off not recorded…"
    ok = no reasons
```

---

## 5. Passport-validity rule

**Source of the value:** `ukv-passport-validity.php` (stores expiry + seeds the requirement).
**Where the rule fires:** `ukv-barriers.php` Rule 2.

- Order meta `ukv_passport_expiry` = `Y-m-d` (validated by regex `^\d{4}-\d{2}-\d{2}$`;
  empty clears it). Captured via the Passport-expiry meta box or from the Apply-form Stripe
  charge (`ukv-apply-intake.php`, `ukv-passport-validity.php`).
- Per-destination requirement: Pods number field `passport_validity_months`, **seeded to 6**
  on every destination that has no value (6 months is the common requirement). Idempotent
  one-time seed guarded by option `ukv_passport_validity_seeded`.

**The rule (barriers Rule 2):**
```php
$expiry     = get_post_meta($oid,'ukv_passport_expiry',true);              // Y-m-d
$req_months = (int) ukv_dest_value($slug,'passport_validity_months');     // e.g. 6
$travel     = get_post_meta($oid,'ukv_travel_date',true);                  // Y-m-d
if ( $expiry && $req_months && $travel ) {
    $need = strtotime($travel) + $req_months * 2629800;   // ~month = 2,629,800 s (30.44 days)
    if ( strtotime($expiry) < $need ) {
        // create a PERMANENT, case-scoped barrier:
        // "Your passport may not have the {req_months} months' validity this destination
        //  requires beyond your travel date…"
    }
}
```

**Pseudocode:**
```
passportValidityBarrier(order, destination):
    if not (passport_expiry AND req_months AND travel_date): no-op   # all three required
    requiredUntil = travel_date + req_months months   (1 month ≈ 30.44 days / 2,629,800 s)
    if passport_expiry < requiredUntil:
        raise barrier(nature=permanent, scope=case, rule_key="{order_ref}:passport_validity")
```

> **Notes / flags:**
> - The rule needs **all three** data points (expiry, requirement, travel date). Missing any
>   ⇒ silently no barrier. In the port, decide whether a missing travel date should instead
>   compare against "today" — current code does not.
> - "Months" is approximated as `2629800 s` (30.44-day average), not calendar months. A
>   calendar-accurate port (`->addMonths()`) will differ by up to a couple of days at the
>   boundary. Match the average-second behaviour only if exact parity is required.
> - The requirement is measured **beyond travel date**, not beyond submission/today.
