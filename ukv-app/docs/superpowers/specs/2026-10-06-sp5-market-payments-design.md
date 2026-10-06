# Per-market payments (local-currency Stripe, two instalments) design spec

**Status:** draft for owner review. Written 2026-10-06. Sub-project 5 of 7 in the international + tours programme.
**Decision basis:** `docs/product-goals-2026-10.md` (G2, launch gates) and `docs/superpowers/research/2026-10-06-README-decision-basis.md`.
**Depends on:** SP1 (Market object, `market_url`, `partials.market-price` null-safe block, `/{market}` route group). Uses SP4 only for the hub link in the cancel URL.
**Extends:** `StripeService` (currency hard-coded `gbp` at three sites), `Order`, `OrderResource`, `EmailService`.

## How to use this document
1. Read sections 1-3. Flag any wrong assumption now.
2. For each decision in sections 4-9 ask: does it move G2 ("price visible above the fold on every money page in every market; zero 'Get a quote' placeholders on .com") or the launch gate ("one live local-currency charge end to end"), and is the evidence cited?
3. Approve by section; approval unlocks `2026-10-06-sp5-market-payments.md`.
4. Section 11 is the acceptance checklist. Section 12 is the launch gate the owner signs before any market's checkout opens.

## 1. Goal
Let a `.com` market take the first instalment of the service fee in its own currency (ZAR, AED, USD, CAD) through the existing UK Stripe account, charge the second instalment only when ops confirm an appointment on the official site, and show the fee, tax treatment and the "government and visa centre fees paid by you directly" line honestly in every price block and receipt. UK GBP behaviour is unchanged.
Scoreboard: G2 target; launch gate (iii) "one live local-currency charge end to end".

## 2. Scope
In: `orders.market` + currency + instalment columns; `Market` checkout gate methods; `StripeService` currency from `Market`, market Checkout Session payload, live-mode guard, remainder webhook branch; market apply / checkout / confirmation routes behind per-market flags; fee block tax line and CTA; market receipts and remainder-due email; `AppointmentConfirmationService` + Filament "Confirm appointment" action; health endpoint; runbook launch gate; tests.
Out: Ozow / PayShap / Interac / Tabby (need a local entity or extra PSP: SA deep dive "Payments", UAE "BNPL only once a UAE entity exists", US+CA "Interac UNVERIFIED"); tax registration itself (accountant memo, SP6); UK pricing display changes; refunds UI beyond the existing `refund` action; invoices PDF (existing `docs/bp-invoice-template.html` process continues manually).

## 3. Assumptions
- A1 Prices stay `null` in config until the owner sets `price_total`, `price_upfront`, `price_remainder` per market (SP1 §4.1; service brief §3 recommendations are not locks). Null = checkout closed, WhatsApp only, never a placeholder.
- A2 The accountant memo per market arrives as a dated reference string the owner puts in `.env` (`UKV_MARKET_ZA_TAX_MEMO="2026-11-12 Smith & Co VAT memo"`). Null memo = checkout closed, whatever the other flags say.
- A3 Production Stripe is in TEST mode (memory stripe-prod-test-mode). Market checkout in production refuses to create a session unless the secret starts with `sk_live_`; UK behaviour (test-mode rehearsal) is untouched.
- A4 Second instalment trigger is ops marking the appointment confirmed with centre, official reference and date. This is the objectively verifiable event the US chargeback culture demands (US+CA deep dive: "second trigger must be objectively verifiable").
- A5 Currency minor unit is 100 for GBP, ZAR, AED, USD, CAD (all two-decimal in Stripe).
- A6 (added from the UAE licensing memo 2026-10-06): the AE `tax_mode = inclusive` default below presupposes UAE VAT registration. If the accountant/attorney rule that the place of supply is the UK, AE switches to `tax_mode = none` with a note "No UAE VAT charged; Beyond Passports Ltd is a UK company". The memo reference in `.env` is what unlocks either display.

## 4. Data model
`orders` gains `market` string(5) default `uk` (indexed), `currency` string(3) default `GBP`, `upfront_fee` decimal, `remainder_fee` decimal, `remainder_paid_at` timestamp, `remainder_checkout_url` string(500), `appointment_confirmed_at` timestamp. Existing rows default to UK/GBP. `Order::resolveMarket(): Market`, `Order::isUkMarket(): bool`. `service_fee` and `total` hold the all-in fee for market orders; `govt_fee` stays null (never collected: document lane 6.2 item 3, risky practice 5 "Holding government fees").

## 5. Config and gate
Per market in `config/ukv.php`: `checkout_enabled => env('UKV_MARKET_<CODE>_CHECKOUT', false)`, `tax_mode` (`none|inclusive|exclusive`), `tax_note` (string|null), `tax_memo => env('UKV_MARKET_<CODE>_TAX_MEMO')`.
`Market::canCheckout()`: UK returns `config('ukv.apply.enabled')` (unchanged UK switch); non-UK returns `enabled && checkout_enabled && all three prices set && upfront + remainder == total (to the cent) && tax_memo !== null`. So a market can open checkout while the UK funnel stays flag-off, and the UK can never be re-opened by a market flag (G6).
Tax display rule (locked by market, text editable): AE `inclusive`, "Prices include 5% UAE VAT." (UAE deep dive; VFS fee itself quoted "incl VAT"; see A6); CA `exclusive`, "Plus applicable taxes." until GST/HST registration (US+CA deep dive: "prices tax-exclusive", "non-resident security minimum $5,000"); US `none` (services untaxed except HI/NM, accountant memo decides); ZA `none` (SA e-services VAT `verify`, memo decides).

## 6. Stripe
`StripeService::currencyFor(Order)` = `Order::resolveMarket()->currencyLower()`; replaces the three `'gbp'` literals (order session ~line 97, checklist session ~line 204 via `Market::uk()`, bespoke link ~line 361). UK yields `gbp` exactly as before.
`marketSessionPayload(Order, 'upfront'|'remainder', array $appointment = [])` is a pure builder (tested without Stripe): `price_data.currency` = market currency, `unit_amount` = fee x 100, product name "Schengen visa preparation ({market}): First instalment, to start" / "Second instalment, due on confirmed appointment", description carrying the government-fee line, `metadata` `{order_id, order_ref, market, instalment, appointment_reference, appointment_centre, appointment_date, confirmed_by, confirmed_at}`, same metadata on `payment_intent_data` (shows on the charge, dispute evidence), `success_url` `{intl}/{code}/confirmation/{ref}?session_id=...`, `cancel_url` `{intl}/{code}/schengen-visa`.
Presentment: the UK Stripe account creates sessions in ZAR/AED/USD/CAD; Stripe converts to GBP at settlement (feasibility: "Stripe ZAR presentment verified"; UAE deep dive: "AED via Stripe (UK entity, AED presentment)"). FX and cross-border fees are a cost line for the accountant memo, not a customer surcharge (no "6% card fee": document lane, Aviva).
Live-mode guard: `liveModeOk(secret, isProduction, isUkMarket)`; false => `createMarketCheckoutSession` throws `RuntimeException`; the market checkout controller catches it and redirects to the hub with "Checkout is not open yet. Message us on WhatsApp and we will send a payment link." Never a 500, never a test-mode charge that looks real.
Webhook: `handleWebhook` routes `checkout.session.completed` to `markSessionPaid(Order, Session)`: `metadata.instalment = remainder` => `markRemainderPaid` (idempotent on `remainder_paid_at`, event, receipt); otherwise existing `markOrderPaid` (UK receipt for UK orders, market receipt for market orders).

## 7. Funnel routes (per-market, flag-gated)
Inside the SP1 `/{market}` group, behind new middleware `EnsureMarketCheckoutOpen` (404 unless `Market::current()->canCheckout()`): `GET|POST /apply` (`MarketApplyController`), `GET /checkout/{order:order_ref}` (`MarketCheckoutController`). `GET /confirmation/{order:order_ref}` (`MarketConfirmationController`) is not behind the gate so a paid client can always return; it 404s when the order's market differs from the URL.
`MarketOrderService::create(Market, data)` creates the order with market, currency, fees from config, status `paid` (pipeline entry stage, `paid_at` null until the webhook), eligibility `standard`, consent timestamp, one `OrderEvent`. The existing UK `CheckoutController` and `/checkout/{ref}` keep working for market orders because `createCheckoutSession` branches on `isUkMarket()`.
Apply form fields: name, email, WhatsApp number, destination (29 names from `MarketHubController::SCHENGEN`), travel date, begin-now consent. No UK-only fields (postcode, BRP).

## 8. Display
`partials.market-price` (SP1) gains: the tax line from `Market::taxNote()`; when `canCheckout()` a "Start for {upfront}" link to `/{code}/apply`; otherwise the existing WhatsApp CTA line "Message us on WhatsApp to start" with no price placeholder. The block still renders nothing when `price_total` is null. The `config('ukv.pricing.placeholder')` string ("Get a quote") never appears on `.com` (test).
Fee block wording keeps SP1's lines (all-in fee, split, "Government visa fee and visa centre fee are paid by you directly") and adds "Pay only to Beyond Passports Ltd, never to an individual" (UAE deep dive "Payment"; fraud-association risk 3).
Confirmation page: shows "Payment received" only when `paid_at` is set; otherwise "Payment pending" (webhook may lag). Purchase analytics event only when paid, with the order's currency, not GBP.

## 9. Receipts and emails
`MarketReceipt` mailable (markdown `emails.market-receipt`) for market orders, for both instalments: amount with symbol and ISO code, instalment label, remaining amount and its trigger, tax note, "Government and visa centre fees are paid by you directly to the official provider and are not included in this receipt.", "Paid to Beyond Passports Ltd (Companies House 17331903). We never ask for payment to an individual.", mandatory compliance footer. For the remainder: "This second instalment became due when your appointment {ref} at {centre} on {date} was confirmed by our team." UK orders keep `OrderPaid` byte-for-byte.
`RemainderDue` mailable: sent when ops confirm the appointment on a market order with an unpaid remainder; carries the Checkout URL and the same appointment facts.

## 10. Ops: appointment confirmation
`AppointmentConfirmationService::confirm(Order, centre, reference, scheduledAt, confirmedBy)`: creates an `Appointment` (`booked`), stamps `appointment_confirmed_at` once, writes an `OrderEvent` with the facts and the confirmer's name, and, for a market order with `remainder_fee > 0` and no `remainder_paid_at`, creates the remainder Checkout Session with appointment metadata, stores the URL, and sends `RemainderDue`. Re-confirming (reschedule) never creates a second session. UK orders get the appointment + event + existing `appointment_booked` email only.
Filament `OrderResource` table action "Confirm appointment" (centre, official reference, date) calls the service with `auth()->user()->name`. `OrderResource` also gains a `market` badge column, a `market` Select on the form, currency-aware fee prefixes and `->money($record->currency)`.

## 11. Testing (acceptance checklist)
`php artisan test --filter='MarketsCheckoutConfig|MarketCheckoutGate|OrdersMarketColumn|StripeMarketCurrency|MarketPriceBlock|MarketApplyFlow|MarketReceiptEmail|ConfirmAppointment'`:
1. Every market has `checkout_enabled`, `tax_mode`, `tax_note`, `tax_memo`; defaults closed.
2. `canCheckout()` matrix: prices null, memo null, flag off, split mismatch => false; all set => true; UK follows `ukv.apply.enabled`; `/health/stripe` lists per-market `checkout_open`.
3. `orders.market` defaults `uk`, `currency` defaults `GBP`; `resolveMarket()`.
4. Payload: `zar`/`aed`/`usd`/`cad` lowercased, `unit_amount` x100, metadata instalment + market + appointment facts, market success/cancel URLs; UK `currencyFor` = `gbp`; `liveModeOk` matrix.
5. `markSessionPaid` remainder path idempotent; upfront path sends `MarketReceipt` for market orders and `OrderPaid` for UK.
6. Price block: tax note per market; checkout CTA only when gate open; never "Get a quote"; renders nothing when price null.
7. Apply/checkout routes 404 when gate closed; POST creates order with market fields and redirects to market checkout; cross-market order 404; Stripe refusal redirects with message; confirmation pending vs paid.
8. Receipt content per market (symbol, ISO, tax note, govt line, company line); remainder receipt names the appointment.
9. Confirm appointment: Appointment row, `appointment_confirmed_at`, event text, remainder URL, `RemainderDue` queued; second confirm creates no second session; UK order never calls Stripe.
Existing suites green: `CheckoutGuardTest`, `StripeWebhookTest`, `ChecklistPaymentTest`, `OrderEventsTest`, `RefundFlowTest`, `Market*`.

## 12. Launch gate per market (owner signs before `UKV_MARKET_<CODE>_CHECKOUT=true`)
1. Accountant memo for the market received and referenced in `UKV_MARKET_<CODE>_TAX_MEMO` (feasibility gap 9; service brief §9 "Tax memos per market before local-currency checkout"; UAE memo Q3 on place of supply).
2. Production Stripe on `sk_live_` + live webhook; `/health/stripe` shows `secret_mode: live` (memory stripe-prod-test-mode).
3. Prices set in config; split sums to total; fee block reviewed on `/{code}`.
4. One live local-currency charge end to end: apply, pay upfront, webhook receipt, ops confirm appointment, remainder link, pay, remainder receipt; then refund the test charge (product goals gate iii).
5. Compliance memo for the market reviewed by a local professional (SP6; product goals gate ii).

## 13. Competitor citation per decision
| Decision | Beats / learns from | Source |
|---|---|---|
| All-in fee, local currency, split shown, govt/VAC fee separate and never collected | Aviva £385 bundle holds client money; iVisa "all-inclusive"; Visa Agent pays fees for clients | document lane §1, §4 item 5, 6.2 |
| Second instalment only on confirmed appointment | Visard "No appointment = no payment"; Flypass break clause | appointment lane §2, §7 fee wording; G1 "steal" |
| Objectively verifiable trigger with appointment facts on the charge | US "72% consider disputes a valid alternative to refunds" | US+CA deep dive "Payment", risk 4 |
| AED shown VAT-inclusive (subject to A6) | VFS UAE fee "AED 146.74 incl VAT"; AED 649 incl 5% recommendation | UAE deep dive "Fees", "Price construction"; UAE memo Q3 |
| CAD "+ applicable taxes" until registered | non-resident GST/HST registration + $5,000 security | US+CA deep dive "Payment", CA price construction |
| Null price = nothing, never "Get a quote" | FlyFast/Breakout/Tourloom hidden prices; G2 target | document lane §4 item 7; G2 |
| Cards via Stripe presentment only in phase 1 | Ozow/PayShap, Tabby, Interac need local entity or PSP | SA deep dive "Payments"; UAE "BNPL"; US+CA "Interac UNVERIFIED" |
| "Pay only to Beyond Passports Ltd" | Dh2,500-4,000 "for a date" scams; fraud-association risk | UAE deep dive "Fair-price anchors", risk 3 |
| Live-mode guard before any market charge | prod Stripe is in test mode today | memory stripe-prod-test-mode; feasibility gap 3 |
| Per-market checkout flags independent of UK | UK funnel flag-off; G6 fix UK auction first | routes/web.php `ukv.apply.enabled`; G6 |

## 14. Open items carried forward
SP6: accountant memos (ZA e-services VAT, UAE non-resident VAT and place of supply, US HI/NM, CA GST/HST + security), compliance memos; SP7: Clarity/GA purchase events per currency, Ads conversion values in local currency; later: local rails (Ozow/PayShap, Interac), BNPL with a UAE entity, PDF invoices per currency, refund action in market currency.
