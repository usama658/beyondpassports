# Beyond Passports - Landing Page Copy Draft

12-section order. Hero fused with diagnostic. Compliance-safe. No em-dashes, no AI tells.
WhatsApp: wa.me/447882747584 · `[INSERT…]` = supply real data.

---

## 1. NAV (sticky)
Brand: Beyond Passports · dot "Accepting applications"
Links: How it works · Pricing · Reviews
Buttons: Free check · WhatsApp

## 2. HERO = DIAGNOSTIC
Eyebrow: SCHENGEN VISA APPLICATION HELP · FROM THE UK · COMPANIES HOUSE 17331903
H1: **We prepare your Schengen visa application and secure your appointment.**
Sub: For anyone applying from the UK. Consulate-spec documents, VFS or TLS booking, and a full page-by-page check before you submit. Start free, refunded if we can't secure your appointment.

Multi-step form. One question per screen, progress bar ("Step X of N"), back arrow on every step, answers autosave. Contact is asked LAST so people invest before giving details. A/B TEST two variants below.

### Variant A (4 steps) more qualified
Step 1 of 4: **Which country are you applying for?** (searchable, 29 Schengen · flag + name). Helper: Not sure yet? Pick your main destination, where you'll spend the most days. Auto-advances.
Step 2 of 4: **Where are you stuck?** Tiles: Can't find an appointment · Worried I'll be refused · Refused before · Just starting, need it done properly. Auto-advances.
Step 3 of 4: **When do you need to travel?** Tiles: Within 3 weeks · 3 to 6 weeks · 6+ weeks · No fixed date yet. Auto-advances.
Step 4 of 4: **Where should we send your result?** First name · WhatsApp (country-code selector) · consent tick "I agree to be contacted about my visa. No spam." Button: See my result. Micro: Free. No payment now.

### Variant B (3 steps) higher completion
Step 1 of 3: Which country are you applying for?
Step 2 of 3: Where are you stuck? (same 4 tiles)
Step 3 of 3: Where should we send your result? (name + WhatsApp + consent). Timing is asked later on WhatsApp instead.

### Result step (both variants, adapts to Step 2)
Headline adapts:
- Can't find an appointment: "Slots for [Spain] open in small batches. We watch and grab yours."
- Worried I'll be refused: "[Spain] refusals usually come from small, fixable things. We catch them first."
- Refused before: "A [Spain] refusal is often recoverable, once the file is rebuilt around the exact reason." Plus line: "Send us your refusal letter and we'll name the exact reason, free."
- Just starting: "Here's the clean path to your [Spain] visa, start to finish."
Then "Your next move" line (Variant A adapts to timing; e.g. Within 3 weeks: "It's tight but often doable. Message us now and we'll tell you straight if there's time." 6+ weeks / no date: "You've got time in hand. We can start whenever you're ready.").
CTA: Send my result to a consultant, free
Micro: Free · reply in about 30 min · £40 only when you go ahead · refunded if we can't secure your appointment.
WhatsApp deep-link pre-fills country + situation (+ timing in A) + name.

### A/B test plan
Split traffic 50/50 A vs B. Primary metric: form completion to lead (submitted contact). Secondary: lead quality (lead to £40 paid, by variant) and reply-worthiness. Run to significance (target ~300+ completions/variant). Hypothesis: A yields fewer but more-qualified leads; B yields more leads, slightly less qualified. Keep the winner on completions-that-convert, not raw completions.

## 3. TRUST BAR (reassure + point down, true only)
Job: de-risk in one glance right after the diagnostic, then pull the eye to the appointment board below. A/B/C test three directions. Only use a number if it is real. End every variant with a downward scent cue.

### Variant A: Live activity strip (use only if the "secured this month" figure is real)
Watching VFS and TLS across 29 countries right now · Appointments secured this month: [real N] · Rechecked every 3 hours. See what's available for your country below.

### Variant B: Reassure + scent (safe, no numbers)
Registered UK consultancy · Companies House 17331903 · ICO ZC197159 · Refunded if we can't secure your appointment. See live appointment availability for your country below.

### Variant C: Answer the hero's promise
The hero said we secure your appointment. Prove it and point down: Real slots open in small batches all day. Here's what's available right now, below.

### A/B/C test plan
Split traffic evenly. Primary metric: scroll-past rate to the appointment board (and onward engagement with the board), i.e. does the strip keep momentum. Secondary: overall lead rate. Hypothesis: A (live, specific) pulls hardest but needs real data; B is the safe control; C leans on the hero promise. If the "secured this month" number is not verifiable, drop A and test B vs C only. Winner = the one that best moves people into the board without hurting trust.

Note: momentum should mostly come from the diagnostic RESULT, not this strip. Add to the result step: "See live availability for [Spain] below." so people arrive at the board already curious; the trust bar then only has to not break that momentum.

## 4. APPOINTMENT BOARD
Highest engagement AND highest leak, per Clarity + ads data. REASONING: a board that shows availability is a lookup, and a lookup ends the visit the moment it delivers the fact (or fails to show their date). Fix: turn it from a lookup into a WATCH-REQUEST. Prove slots exist and are scarce and fast, never reveal the specific answer on the page, and make them ask about their own situation. No closure on the page equals a message. Money is stripped from this section entirely (free only); price belongs in §9.

Compliance: NO per-centre slot counts (ban risk + honesty). Aggregate featured total only, and only if it is a real number from ops. Never guarantee a slot. Keep "we confirm live availability with the centre before you pay."

Shared elements (both variants):
- Eyebrow: LIVE APPOINTMENT WATCH · 29 SCHENGEN COUNTRIES
- Aggregate proof line (real number): "This week we've spotted [real total N] appointments across Schengen."
- Volatility line (true, does the persuading): "Slots open in small batches and vanish within minutes."
- Tiles: [flag · country · Watching]. No dates, no per-centre numbers, no "Secure now".
- Days-to-go, NOT a date picker (lower friction, urgency, feasibility gate). "How soon do you travel?" tap buckets: This week · Within 3 weeks · 3 to 6 weeks · Flexible. Honest reaction per bucket: This week: "Very tight. Message us now and we'll tell you straight if it's still possible." Within 3 weeks: "Cutting it close but often doable." 3 to 6 weeks: "Good window, we can secure this comfortably." Flexible: "Plenty of time, we start when you're ready." Optional: show a subtle "X days to go" line on the result (urgency, no fake countdown).
- Footer: Rechecked every 3 hours. We confirm live availability with the centre before you pay.
- Tile click behaviour: clicking a country tile opens the SAME multi-step form from §2, prefilled with that country (skips Step 1), defaults the situation to "Can't find an appointment", then asks days-to-go, then contact. Result: "For [Spain] in that window, slots are tight and move fast. We'll watch the centre and message you the moment one opens." CTA: Watch my dates for me, free (WhatsApp deep-link prefilled: country + days-to-go bucket + situation). The board never books and never shows a slot; it always opens the form.

### A/B test
- Variant A: interactive board. Days-to-go buckets on the board itself, tiles open the multi-step form. Higher engagement, more captured intent.
- Variant B: aggregate-count board. The [real N] count + volatility line + featured "Watching" tiles + one primary CTA "Watch my dates for me, free" (opens the form). Simpler, faster, less on-page interaction.
- Split evenly. Primary metric: leads captured from this section (watch-request submissions). Secondary: scroll-past to the next section (does it keep momentum vs dead-end). Hypothesis: A captures more of the high-intent appointment-seekers who currently leave; B is the lighter control. Winner = most watch-requests without hurting downstream flow.

Design note: a "request a callback" (15 min, not a booked slot) is a SECONDARY option, and it belongs in §9 Pricing and §10 Refusal recovery only, never here. Reasoning: hero, diagnostic, and board are low-friction, loop-open, panic-buyer moments; a scheduled-call ask adds calendar and commitment friction that would tank top-of-funnel conversion. WhatsApp-first stays primary everywhere. Note: "reply in about 30 min" is a WhatsApp response-time promise, not a call, keep it.

## 5. CONSEQUENCE (dark break)
REASONING: fear (loss aversion) is a top conversion lever, and placement here is right (the board built appointment-fear; this bridges into refusal-fear, then "we handle both"). But abstract doom is word salad and kills momentum. Rule: fear must be concrete, personal, and immediately resolved, or it just causes a bounce. So this section pairs the stake with the fix. Variant B can also ABSORB the later "documents / x-ray" job, so we don't build a separate section that risks not converting. A/B test the two.

### Variant A: The loss math (dark break, concrete)
Eyebrow: WHY GETTING IT RIGHT MATTERS
H2: **A refusal costs you more than the visa.**
Body: One refused application means your consulate fee gone, your booked flights at risk, and a refusal on your record for years that you have to declare every time after. And it is rarely a big thing. It is a photo a fraction off spec, a bank balance on the wrong date, a cover letter that says the wrong thing.
Line: **Those are exactly the things we check before you submit.**
CTA (back into the funnel): See where you stand, free.

### Variant B: What actually gets people refused (fuse fear + the fix, can absorb the documents section)
Eyebrow: THE REAL REASONS PEOPLE GET REFUSED
H2: **It's almost never the big things.**
List (each a "you would not catch this" beat, real causes):
- A photo that fails biometric spec.
- A closing balance on the wrong day.
- A cover letter that reads like a template.
- Applying through the wrong country.
- One missing piece of evidence.
Close: **We catch every one of these before you submit. That is the whole job.**
CTA: Get your file checked, free.

### A/B test plan
Split evenly. Primary metric: click-through from this section back into the funnel (diagnostic / free check). Secondary: overall page conversion and scroll-depth past this section (does it keep momentum vs bounce). Hypothesis: B converts better because it makes the fear concrete and personal (reader scans the list: "is my photo one of these?"), answers "why pay you" in the same breath, and doubles as the documents section. If B wins, drop the standalone documents section (one section, three jobs, nothing to cut at go-live).

Compliance: no invented percentages. If a real refusal figure can be sourced live to EC DG HOME or the Home Office, it can support Variant A, otherwise keep it qualitative. No guarantee of approval.

## 6. SOCIAL PROOF
Eyebrow: THEY NEARLY GAVE UP TOO
H2: **From "there are no slots" to boarding the flight.**
[INSERT 3 to 5 REAL, CONSENTED REVIEWS. First name or initial · country · one honest line, e.g. "Refused once, approved with their help. Spain." No aggregate star rating unless a real Google or Trustpilot profile backs it.]

## 7. TEAM
Eyebrow: WHO YOU'RE TALKING TO
H2: **A named team, not a call centre.**
Sub: You deal with a real person from your first message to your visa in hand.
[INSERT REAL people: Sarah · James · Chloe, with REAL photos and one-line roles. Must be genuine, consented staff. If no consented photo, use first name and an initials avatar only.]

## 8. PROCESS
Eyebrow: HOW IT WORKS
H2: **The visa's in your passport. Your trip is real.**
Sub: You send a few documents and get on with your life. We carry the hard parts, the ones that get people refused.
- Step 1: **Tell us where you're going.** One message. We tell you free where you stand. `2 min · free`
- Step 2: **We get it ready and get your appointment.** Cover letter, bank check, photo, form, VFS or TLS slot, all handled. `about 2 weeks · we do it`
- Step 3: **Walk in. Walk out with it done.** The relief of knowing it's handled. `done`
CTA: Start free, message us

## 9. PRICING
Eyebrow: ONE PRICE. NOTHING HIDDEN.
H2: **£130 for the whole thing. £40 to start.**
Ladder: **Pay now £40** (start your case) then **On appointment £90** (when your appointment is secured) then **Total £130**
Separate: Government or consulate fee is about £77 (around €90), paid by you directly to the consulate. It's not ours and we never add to it.
Included: appointment booking · Schengen-compliant travel insurance · accommodation booking · application form · travel itinerary · cover letter for your consulate · every page checked · guidance throughout.
Risk panel: **Refunded in full if we can't secure your appointment. Your service fee back if your visa is refused.**
CTA: Start for £40 · A named consultant · about 30 min reply · CH 17331903 · ICO ZC197159.

## 10. REFUSAL RECOVERY
Eyebrow: REFUSED BEFORE?
H2: **A refusal is often fixable once you know the real reason.**
Body: Refusals come down to specific grounds, and many are very recoverable when the file is rebuilt around them: the right itinerary, the right evidence, a cover letter that answers exactly what the consulate doubted. Send us your refusal letter and we'll tell you honestly what went wrong and whether it's worth trying again.
CTA: Review my refusal, free

## 11. FAQ
Eyebrow: STRAIGHT ANSWERS
H2: **The questions everyone asks before they message us.**
1. **Can you guarantee I'll get the visa?** No one honestly can. The consulate decides. We guarantee a properly built, submission-ready file and honest advice. Anyone promising approval is a red flag, not a bargain.
2. **Can it be done before my travel date?** Often yes. Processing needs about 15 to 20 working days plus appointment lead time. Tell us your date and we'll tell you straight.
3. **Which documents do I need?** It depends on your consulate and your situation. Do the free check and we build your exact list and review every page.
4. **What if I'm refused? Do I lose my money?** The government fee is separate and paid by you to the consulate. Our service fee comes back if your visa is refused, and in full if we can't secure your appointment.
5. **I was refused before. Can you help?** Often yes. Send the refusal letter and we name the exact reason and show the rebuild plan.
6. **Which country should I apply to?** The one where you spend the most days (a tie goes to first entry). The wrong country is a classic refusal trigger, and we get this right.
7. **Do I even need a visa?** UK citizens travel Schengen visa-free (90 in 180). If you hold a non-UK passport and live in the UK, this service is for you, and we confirm your basis first.
8. **Isn't it cheaper to do it myself?** You can. But a refusal costs your trip, your fee, and a years-long record. £40 hands that risk to someone who does this daily.

## 12. FINAL CTA
H2: **Stop refreshing VFS. Let's start.**
Sub: One message. A named consultant tells you free exactly where you stand, usually within 30 minutes.
CTAs: Let's start · Get my free check
Micro: £40 to start · refunded if we can't secure your appointment · Companies House 17331903 · ICO ZC197159.

---

## FOOTER + DISCLAIMER (legal, don't skip)
Disclaimer strip: *We are a private, independent visa-assistance service. We are not the government, a consulate, or VFS/TLS. Visa decisions are made solely by the relevant authorities.*
Footer: Beyond Passports · Companies House 17331903 · ICO ZC197159 · Terms · Privacy · Complaints · Contact · © 2026.

## OVERLAY: EXIT-INTENT (bottom sheet)
**Before you go, is your file actually ready?** Send us your country and travel date. We'll tell you free whether an appointment is reachable and what your documents need. No payment, no pressure. Get my free check · No thanks

## OVERLAY: STICKY MOBILE BAR
Message us free · micro: £40 to start · refunded if no appointment.

---

## BEFORE PUBLISH: COMPLIANCE
- Real consented reviews (§6) and real team photos and names (§7).
- The §5 number stays qualitative unless sourced live (EC DG HOME or Home Office).
- No ratings or percentages anywhere unless a real profile backs them (no 4.9, 98%, or 340+).
- Lean on the true anchors: Companies House 17331903 · ICO ZC197159 · £130/£40 split · refund.
