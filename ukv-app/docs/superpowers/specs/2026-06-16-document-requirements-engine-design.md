# Document Requirements Engine — design spec

**Goal:** customer clarity. Show each traveller the exact documents to prepare for THEIR case, before & after applying. Cut chase emails + abandonment.

**Approach (B):** a data-driven conditional rules engine. Requirements are DATA (admin-editable rows), not code. A service evaluates active rules against an order → a tailored checklist surfaced on the destination/apply preview, confirmation page+email, and tracker/upload page.

## Data model

### New table `document_requirements` (one row = one rule)
- `document_key` string — stable slug (`passport`, `photo`, `employer_letter`)
- `label` string — customer-facing name
- `note` text nullable — guidance
- `category` string — display grouping (`identity`,`funding`,`logistics`,`health`,`core`)
- `conditions` json — match spec (below)
- `mandatory` bool default true
- `active` bool default true
- `sort_order` int default 0
- timestamps

### `conditions` JSON — all keys optional; AND across keys, OR within an array; `{}` = applies to all
```json
{
  "destinations": ["turkey","india"],
  "trip_purpose": ["business"],
  "is_minor": true,
  "residency_status": ["visa_holder"],
  "visa_entries": ["multiple"],
  "prior_refusal": true,
  "employment_status": ["self_employed"],
  "accommodation_type": ["host"],
  "funding_source": ["sponsored"],
  "payer_is_applicant": false,
  "min_stay_days": 30,
  "max_stay_days": 90,
  "passport_validity_short": true
}
```
`passport_validity_short`, `min_stay_days`, `max_stay_days` match against COMPUTED values (service derives from `passport_expiry`, `travel_date`, `return_date`, destination `passport_validity_months`).

### New `orders` fields (post-pay detail step — keep apply funnel lean)
- `employment_status` string nullable — one of: employed, self_employed, student, retired, unemployed, other
- `accommodation_type` string nullable — one of: hotel, host, own_property, other
- `funding_source` string nullable — one of: self, sponsored
- `return_date` date nullable
- `payer_is_applicant` boolean nullable default true

Pre-pay inputs (already captured) drive the PREVIEW; post-pay fields refine the FINAL checklist.

## Service contract — `App\Services\RequirementService`
```php
/** @return list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}> */
public function for(Order $order): array;       // personalised, ordered by sort_order then label
/** @return same shape */
public function preview(Destination $destination, array $ctx = []): array; // no order; ctx overrides assumptions
```
Matching: each condition key compared to the order/ctx value. String keys → value ∈ array. Booleans → equals. Numeric stay → compare computed `stay_days` (return_date − travel_date); rule excluded if the value it needs is unknown. Empty conditions → always matches.

## Presentational contract — `resources/views/partials/doc-checklist.blade.php`
Expects `$items` (service shape) + optional `$personalised` bool. Pure presentational: groups by `category`, splits mandatory vs recommended, shows `note`. No DB access.

## Surfaces
- `/visa/{slug}` + `/apply` — "Documents you'll likely need" PREVIEW (preview()).
- `/confirmation/{order}` + awaiting-docs email — personalised checklist (for()).
- `/track` + `/documents` — personalised checklist; `/documents` also collects the 5 post-pay fields.

## Existing data migration
Convert each destination's current `destinations.required_docs` strings into baseline mandatory rules scoped to that destination (no other conditions). Idempotent. Day-one parity.

## Testing
- RequirementService: each condition type, AND/OR semantics, destination scope, computed passport-validity + stay-length, empty=all.
- Feature: business+minor order returns expected docs; tourist adult baseline; non-matching rules excluded.
- Detail-form: post-pay fields persist + validate.

## Out of scope (later phase C)
AI per-doc verification + typed QA-gate enforcement. The checklist slots become the targets it verifies against — no rework.
