# /apply Funnel + Payments (#4) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A 6-step visa-only `/apply` funnel that prices from the shared visa JSON, collects documents before payment, charges via Stripe Checkout (guest), and emits an order payload + GA4 purchase on success.

**Architecture:** The two logic-heavy pieces — the **pricing calculator** and the **order-payload builder** — are pure JS modules tested under Node (no Stripe, no WP). The funnel UI is a multi-step form holding state in `sessionStorage`; Stripe Checkout is created server-side by a small serverless function (also holds the success webhook that builds the payload + fires GA4 + hands off to #5). Document upload writes to a private bucket pre-pay; only references travel in the payload. IDP is a cross-sell link only (revised spec).

**Tech Stack:** Vanilla JS funnel, Node serverless function (Stripe SDK), Stripe Checkout, WordPress page `/apply` (noindex, from Foundation), shared visa JSON (#2), GA4 Measurement Protocol.

**Prerequisite:** #1 Foundation (`/apply` noindex stub + GA4), #2 (`data/visas/*.json`). Stripe account (test keys). `node -v` ≥18. Paths relative to repo root.

---

## File structure

- `wordpress/hello-child/assets/js/pricing-core.js` — pure pricing calculator
- `wordpress/hello-child/assets/js/payload-core.js` — pure order-payload builder
- `wordpress/hello-child/assets/js/apply-funnel.js` — 6-step UI + sessionStorage state
- `serverless/create-checkout.js` — creates a Stripe Checkout Session
- `serverless/stripe-webhook.js` — verifies event, builds payload, fires GA4, posts to #5
- `wordpress/hello-child/tests/js/run.mjs` — extend the Node runner (from #3)

---

### Task 1: Pricing calculator (test-first)

**Files:**
- Create: `wordpress/hello-child/assets/js/pricing-core.js`
- Modify: `wordpress/hello-child/tests/js/run.mjs`

- [ ] **Step 1: Add failing tests**

Append to `wordpress/hello-child/tests/js/run.mjs`:
```js
const { priceOrder } = await import('../../assets/js/pricing-core.js');

check('Turkey express price = service 49 + govt 0', () => {
  const p = priceOrder(turkey, 'express');
  assert.deepEqual(p.lineItems, [
    { label: 'Express visa service', amount_gbp: 49 },
    { label: 'Turkey government fee', amount_gbp: 0 },
  ]);
  assert.equal(p.total_gbp, 49);
});
check('India standard = 29 + 22', () => {
  const india = JSON.parse(readFileSync(join(here, '../../data/visas/india.json'), 'utf8'));
  const p = priceOrder(india, 'standard');
  assert.equal(p.total_gbp, 51);
});
check('visa-free destination is non-orderable', () => {
  const p = priceOrder(morocco, 'standard');
  assert.equal(p.orderable, false);
  assert.equal(p.total_gbp, 0);
});
check('unknown tier throws', () => {
  assert.throws(() => priceOrder(turkey, 'platinum'));
});
```

- [ ] **Step 2: Run — expect FAIL**

Run: `node wordpress/hello-child/tests/js/run.mjs`
Expected: cannot import `pricing-core.js`.

- [ ] **Step 3: Implement**

`wordpress/hello-child/assets/js/pricing-core.js`:
```js
// Pure pricing. service tier (from JSON tiers) + government fee at cost. Visa-free = non-orderable.
const TIER_LABEL = { standard: 'Standard', express: 'Express', premium: 'Premium' };

export function priceOrder(data, tier) {
  if (!TIER_LABEL[tier]) throw new Error('unknown tier: ' + tier);
  const v = data.visa || {};
  if (!v.required_for_uk) {
    return { orderable: false, lineItems: [], total_gbp: 0 };
  }
  const service = Number(data.tiers[tier + '_gbp'] || 0);
  const govt = Number(v.govt_fee_gbp || 0);
  const lineItems = [
    { label: `${TIER_LABEL[tier]} visa service`, amount_gbp: service },
    { label: `${data.name} government fee`, amount_gbp: govt },
  ];
  return { orderable: true, lineItems, total_gbp: service + govt };
}

if (typeof window !== 'undefined') { window.UKVPricing = { priceOrder }; }
```

- [ ] **Step 4: Run — expect PASS**

Run: `node wordpress/hello-child/tests/js/run.mjs`
Expected: all PASS, exit 0.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/assets/js/pricing-core.js wordpress/hello-child/tests/js/run.mjs
git commit -m "feat(funnel): pricing calculator (service + govt-at-cost, visa-free non-orderable)"
```

---

### Task 2: Order-payload builder (test-first)

**Files:**
- Create: `wordpress/hello-child/assets/js/payload-core.js`
- Modify: `wordpress/hello-child/tests/js/run.mjs`

- [ ] **Step 1: Add failing tests**

Append to `tests/js/run.mjs`:
```js
const { buildOrderPayload } = await import('../../assets/js/payload-core.js');

check('payload has all required fields', () => {
  const pricing = { lineItems: [{ label: 'Express visa service', amount_gbp: 49 }, { label: 'Turkey government fee', amount_gbp: 0 }], total_gbp: 49 };
  const p = buildOrderPayload({
    orderRef: 'UKV-2026-000123', data: turkey, tier: 'express', pricing,
    applicant: { name: 'A B', email: 'a@b.com', phone: '+44' },
    passportNumber: '123456789',
    documents: ['https://bucket/x.jpg'], stripeSessionId: 'cs_test_1',
  });
  assert.equal(p.product, 'visa');
  assert.equal(p.dest, 'turkey');
  assert.equal(p.tier, 'express');
  assert.equal(p.amount_gbp, 49);
  assert.equal(p.passport_number, '123456789');
  assert.equal(p.line_items.length, 2);
  assert.ok(p.applicant.email && p.documents.length === 1 && p.stripe_session_id);
});
```

- [ ] **Step 2: Run — expect FAIL**

Run: `node wordpress/hello-child/tests/js/run.mjs`
Expected: cannot import `payload-core.js`.

- [ ] **Step 3: Implement**

`wordpress/hello-child/assets/js/payload-core.js`:
```js
// Pure: assemble the order payload handed to #5 CRM. Shape must match crm-ops spec.
export function buildOrderPayload({ orderRef, data, tier, pricing, applicant, passportNumber, documents, stripeSessionId }) {
  return {
    order_ref: orderRef,
    product: 'visa',
    dest: data.slug,
    tier: tier,
    amount_gbp: pricing.total_gbp,
    line_items: pricing.lineItems,
    applicant: { name: applicant.name, email: applicant.email, phone: applicant.phone },
    passport_number: passportNumber,
    documents: documents || [],
    stripe_session_id: stripeSessionId,
  };
}

if (typeof window !== 'undefined') { window.UKVPayload = { buildOrderPayload }; }
```

- [ ] **Step 4: Run — expect PASS**

Run: `node wordpress/hello-child/tests/js/run.mjs`
Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/assets/js/payload-core.js wordpress/hello-child/tests/js/run.mjs
git commit -m "feat(funnel): order-payload builder matching #5 CRM shape"
```

---

### Task 3: Funnel UI + sessionStorage state machine

**Files:**
- Create: `wordpress/hello-child/assets/js/apply-funnel.js`

- [ ] **Step 1: Implement the 6-step controller**

`wordpress/hello-child/assets/js/apply-funnel.js`:
```js
// 6-step funnel. State in sessionStorage; nothing server-side until pay.
(function () {
  const root = document.getElementById('ukv-apply');
  if (!root || !window.UKVPricing) return;
  const dataDir = root.getAttribute('data-dir');
  const KEY = 'ukv_apply';
  const state = JSON.parse(sessionStorage.getItem(KEY) || '{}');
  const save = () => sessionStorage.setItem(KEY, JSON.stringify(state));

  // prefill from ?dest=
  const params = new URLSearchParams(location.search);
  if (params.get('dest')) state.dest = params.get('dest');

  const steps = [...root.querySelectorAll('[data-step]')];
  let current = 0;
  const show = i => steps.forEach((s, n) => s.hidden = n !== i);

  async function loadDest() {
    const res = await fetch(`${dataDir}/${state.dest}.json`, { cache: 'force-cache' });
    if (!res.ok) throw new Error('dest');
    return res.json();
  }

  root.addEventListener('click', async (e) => {
    if (e.target.matches('.ukv-next')) {
      // collect this step's fields into state
      steps[current].querySelectorAll('[name]').forEach(f => { state[f.name] = f.value; });
      save();
      // guard: step 1 -> need a valid orderable dest
      if (current === 0) {
        try {
          const data = await loadDest();
          const pricing = window.UKVPricing.priceOrder(data, state.tier || 'standard');
          if (!pricing.orderable) { alert('This destination is visa-free — no application needed.'); return; }
          state._name = data.name; save();
        } catch { alert('Destination not available.'); return; }
      }
      current = Math.min(current + 1, steps.length - 1);
      show(current);
    }
    if (e.target.matches('.ukv-back')) { current = Math.max(0, current - 1); show(current); }
    if (e.target.matches('.ukv-pay')) { await startCheckout(); }
  });

  async function startCheckout() {
    const data = await loadDest();
    const pricing = window.UKVPricing.priceOrder(data, state.tier || 'standard');
    const res = await fetch('/api/create-checkout', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ dest: state.dest, tier: state.tier, total_gbp: pricing.total_gbp, applicant: { name: state.name, email: state.email } }),
    });
    const { url } = await res.json();
    location.href = url; // Stripe-hosted Checkout
  }

  show(0);
})();
```

- [ ] **Step 2: Lint-check**

Run: `node --check wordpress/hello-child/assets/js/apply-funnel.js; echo "exit=$?"`
Expected: `exit=0`.

- [ ] **Step 3: Commit**

```bash
git add wordpress/hello-child/assets/js/apply-funnel.js
git commit -m "feat(funnel): 6-step sessionStorage controller with dest guard + prefill"
```

---

### Task 4: Stripe Checkout creation (serverless)

**Files:**
- Create: `serverless/create-checkout.js`
- Create: `serverless/package.json`

- [ ] **Step 1: Declare deps**

`serverless/package.json`:
```json
{ "name": "ukv-serverless", "type": "module", "private": true, "dependencies": { "stripe": "^16.0.0" } }
```
Run: `cd serverless && npm install`
Expected: `stripe` installed.

- [ ] **Step 2: Implement the endpoint**

`serverless/create-checkout.js`:
```js
import Stripe from 'stripe';
const stripe = new Stripe(process.env.STRIPE_SECRET_KEY);

// POST { dest, tier, total_gbp, applicant }. Returns { url } to Stripe-hosted Checkout.
export default async function handler(req, res) {
  if (req.method !== 'POST') return res.status(405).end();
  const { dest, tier, total_gbp, applicant } = req.body;
  const amount = Math.round(Number(total_gbp) * 100); // pence
  if (!dest || !tier || !(amount >= 0)) return res.status(400).json({ error: 'bad input' });

  const session = await stripe.checkout.sessions.create({
    mode: 'payment',
    customer_email: applicant?.email,
    line_items: [{
      price_data: { currency: 'gbp', unit_amount: amount, product_data: { name: `${tier} visa service — ${dest}` } },
      quantity: 1,
    }],
    metadata: { dest, tier },
    success_url: `${process.env.SITE_URL}/apply?step=confirm&session_id={CHECKOUT_SESSION_ID}`,
    cancel_url: `${process.env.SITE_URL}/apply?step=pay&cancelled=1`,
  });
  return res.status(200).json({ url: session.url });
}
```

- [ ] **Step 3: Smoke-test with Stripe test keys**

Set `STRIPE_SECRET_KEY` (test) + `SITE_URL` env; invoke the function locally (host's dev runner, e.g. `vercel dev` / `netlify dev` / node adapter) and POST:
```bash
curl -s -X POST http://localhost:3000/api/create-checkout -H "Content-Type: application/json" \
  -d '{"dest":"turkey","tier":"express","total_gbp":49,"applicant":{"email":"a@b.com"}}'
```
Expected: JSON `{ "url": "https://checkout.stripe.com/..." }`.

- [ ] **Step 4: Commit**

```bash
git add serverless/create-checkout.js serverless/package.json
git commit -m "feat(funnel): serverless Stripe Checkout session creation (guest, GBP)"
```

---

### Task 5: Stripe success webhook → payload + GA4 + #5 handoff (serverless)

**Files:**
- Create: `serverless/stripe-webhook.js`

- [ ] **Step 1: Implement the webhook**

`serverless/stripe-webhook.js`:
```js
import Stripe from 'stripe';
const stripe = new Stripe(process.env.STRIPE_SECRET_KEY);

// Stripe calls this on checkout.session.completed. Verifies signature, builds payload, fires GA4, posts to #5.
export default async function handler(req, res) {
  const sig = req.headers['stripe-signature'];
  let event;
  try {
    event = stripe.webhooks.constructEvent(req.rawBody, sig, process.env.STRIPE_WEBHOOK_SECRET);
  } catch (e) {
    return res.status(400).send(`Webhook Error: ${e.message}`);
  }
  if (event.type !== 'checkout.session.completed') return res.status(200).json({ ignored: true });

  const s = event.data.object;
  const orderRef = 'UKV-' + new Date().getFullYear() + '-' + String(s.created).slice(-6);
  const payload = {
    order_ref: orderRef,
    product: 'visa',
    dest: s.metadata.dest,
    tier: s.metadata.tier,
    amount_gbp: s.amount_total / 100,
    applicant: { email: s.customer_email },
    stripe_session_id: s.id,
  };

  // GA4 purchase (Measurement Protocol)
  await fetch(`https://www.google-analytics.com/mp/collect?measurement_id=${process.env.GA4_ID}&api_secret=${process.env.GA4_API_SECRET}`, {
    method: 'POST',
    body: JSON.stringify({
      client_id: s.id,
      events: [{ name: 'purchase', params: { currency: 'GBP', value: payload.amount_gbp, transaction_id: orderRef } }],
    }),
  });

  // Hand off to #5 (Zapier catch-hook / Pipedrive)
  await fetch(process.env.CRM_WEBHOOK_URL, {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
  });

  return res.status(200).json({ received: true });
}
```

- [ ] **Step 2: Test with Stripe CLI**

```bash
stripe listen --forward-to localhost:3000/api/stripe-webhook
stripe trigger checkout.session.completed
```
Expected: webhook returns 200; logs show GA4 + CRM POSTs attempted (use a https://webhook.site URL for `CRM_WEBHOOK_URL` to confirm payload shape includes order_ref/product/dest/tier/amount_gbp).

- [ ] **Step 3: Commit**

```bash
git add serverless/stripe-webhook.js
git commit -m "feat(funnel): Stripe webhook -> order payload + GA4 purchase + #5 handoff"
```

---

### Task 6: Build the /apply page UI (config-and-verify)

**Files:** none tracked (Elementor page, already `noindex` from Foundation).

- [ ] **Step 1: Replace the `/apply` placeholder** with the 6-step markup (HTML widget). Skeleton:
```html
<div id="ukv-apply" data-dir="/wp-content/themes/hello-child/data/visas">
  <section data-step><!-- 1 destination + IDP cross-sell card --><select name="dest">…8 options…</select><button class="ukv-next">Continue</button></section>
  <section data-step hidden><!-- 2 eligibility + tier (Standard/Express/Premium cards) + passport captured ONCE -->…<button class="ukv-back">Back</button><button class="ukv-next">Continue</button></section>
  <section data-step hidden><!-- 3 applicant details (prefilled from step 2) -->…</section>
  <section data-step hidden><!-- 4 document upload (writes to bucket, returns refs) -->…</section>
  <section data-step hidden><!-- 5 review & pay (line items from pricing-core) --><button class="ukv-back">Back</button><button class="ukv-pay">Pay</button></section>
  <section data-step hidden><!-- 6 confirmation (rendered after success_url redirect) --></section>
</div>
```

- [ ] **Step 2: Enqueue funnel scripts on `/apply`** — extend `inc/tools-enqueue.php` (#3):
```php
if (is_page('apply')) {
    $dir = get_stylesheet_directory_uri() . '/assets/js';
    wp_enqueue_script('ukv-pricing', "$dir/pricing-core.js", [], '1.0', true);
    wp_enqueue_script('ukv-payload', "$dir/payload-core.js", [], '1.0', true);
    wp_enqueue_script('ukv-apply', "$dir/apply-funnel.js", ['ukv-pricing','ukv-payload'], '1.0', true);
}
```
(Add `ukv-pricing`/`ukv-payload` to the module-tag filter list.)

- [ ] **Step 3: Wire passport capture at step 2 only** — passport-number field lives in step 2; step 3 shows it read-only/prefilled (no re-ask). The `passport_number` collected client-side is sent with the upload/applicant data, not via Stripe metadata.

- [ ] **Step 4: Document upload (step 4)** — MetForm (or custom) uploader posts files to the private bucket endpoint; returns URLs stored in `state.documents`. Block "Continue" until required docs present.

- [ ] **Step 5: Confirmation step** — on `?step=confirm&session_id=…` redirect, render step 6 and clear `sessionStorage` `ukv_apply`.

- [ ] **Step 6: Verify funnel flow (Stripe test mode)**

In a browser: open `/apply?dest=turkey` → step 1 preselects Turkey + shows IDP cross-sell card linking `/driving-in-turkey/`; advance through tiers (passport asked once) → upload a test doc → step 5 shows "Express visa service £49 + Turkey government fee £0 = £49" → Pay → Stripe test card `4242 4242 4242 4242` → redirect to confirmation.
```bash
curl -sI "https://$STAGING/apply/" | grep -i "noindex"   # still noindex
```
Expected: full path completes; webhook (Task 5) creates the payload; `/apply` remains noindex.

---

### Task 7: Error handling + acceptance

**Files:** none tracked.

- [ ] **Step 1: Verify error paths**
  - Stripe cancel → returns to `/apply?step=pay&cancelled=1` with sessionStorage intact (re-open shows entered data).
  - `/apply?dest=atlantis` → step-1 guard alerts "Destination not available," no advance.
  - Visa-free `/apply?dest=morocco` → guard alerts visa-free, blocks order.
  - Upload failure → cannot advance past step 4.

- [ ] **Step 2: Run all JS unit tests**

Run: `node wordpress/hello-child/tests/js/run.mjs`
Expected: `ALL PASS` (checker, photo, pricing, payload).

- [ ] **Step 3: Acceptance (per spec)** — confirm:
  - Visa path completes on Stripe test mode with correct line items + total from JSON.
  - Prefill `?dest=` lands on the right destination.
  - Passport captured once at step 2, not re-asked at step 3.
  - Docs upload pre-pay, referenced (not embedded) in payload, stored encrypted.
  - On success: GA4 `purchase` fires + payload (incl. `passport_number`) emitted to CRM webhook.
  - IDP cross-sell card links to `/driving-in-<dest>/`, never a cart line.
  - `/apply` is `noindex`.

- [ ] **Step 4: Tag**

```bash
git add -A && git commit -m "chore(funnel): apply funnel #4 acceptance passed" || true
git tag funnel-live
```

---

## Notes
- Payload shape (`order_ref/product/dest/tier/amount_gbp/line_items/applicant/passport_number/documents/stripe_session_id`) is the contract consumed by #5 CRM — keep identical.
- `passport_number` is included because ETA destinations (USA/Australia) issue no document; #5 uses it as the fulfilment key.
- Visa-free destinations are non-orderable here (matches #2); the only IDP touchpoint is the cross-sell link to #6.
