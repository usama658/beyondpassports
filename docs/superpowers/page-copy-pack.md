# Page copy pack — money / apply / track (paste-ready, clear+trustworthy, compliant)

Compliance on every page: independent service, not a government website; service fee separate from the government
fee; express speeds our handling, not the government's decision; no approval guarantees.

---

## Money / destination page (`/<destination>/`)
Rendered by the Pods `destination` template — most fields are dynamic. Use `{Destination}` = the country name.

1. **Hero**
   - Eyebrow: Independent UK visa service
   - H1: {Destination} visa for UK travellers
   - Sub: Everything you need to apply for your {Destination} visa — clear requirements, fixed fees, and our team checking your application before it's submitted.
   - Button: Start my {Destination} application → `/apply/?dest=<slug>`
   - Trust line: Not a government website · Fees shown include the government fee + our service fee.

2. **Fees & tiers** — H2: {Destination} visa fees → `[ukv_visa_table]` (or styled like Pricing #187 cards: Standard / Express / Premium). Note: "Express speeds our handling, not the government's decision."

3. **Requirements** — H2: What you'll need → bullet list (passport valid 6+ months, photo, travel dates…) via `[ukv_dest_field field="requirements"]`.

4. **How to apply** — H2: How it works → 3 steps: Apply online → We check & prepare → We submit & you track.

5. **FAQ** — H2: {Destination} visa FAQs → renders automatically per destination (accordion).

6. **CTA band** — H2: Ready to apply for your {Destination} visa? → button → `/apply/?dest=<slug>` + `[ukv_whatsapp]`.

**SEO:** Title `{Destination} Visa for UK Citizens | Fees, Requirements & Apply` · focus kw `{destination} visa uk`.

---

## Apply page (`/apply/`)
1. **Hero** — H1: Apply for your visa — Sub: A few simple questions and a secure payment. We take it from there.
2. **The funnel** — `[forminator_form id="300"]`
3. **Reassurance strip** — `[ukv_trust_bar]` + line: "Your details are secure. We check everything before submission."
4. **CTA / help** — H2: Prefer to talk first? → `[ukv_whatsapp]` + call button.
**SEO:** Title `Apply for Your Visa | UK Visa Service` · focus kw `apply for visa uk`.

---

## Track page (`/track/`)
1. **Hero** — H1: Track your application — Sub: Enter your reference and email to see your latest status.
2. **Tracker** — `[ukv_tracker]`
3. **Help CTA** — H2: Need an update? → "Our UK team is here." + `[ukv_whatsapp]` + call button.
**SEO:** Title `Track Your Visa Application | UK Visa Service` · focus kw `track visa application`.

---

## Build note
Clone the same kit blocks per `kit-section-map.md`: hero (Home #202 [0]), fees (Pricing #187 [1]), steps
(Visa #229 [4]), CTA band (Home #202 [3]). Drop the shortcodes above into Shortcode widgets. Check mobile.
