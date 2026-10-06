# Portal requirements mined from Beyond Passports lead and client material

**Written 2026-10-06 by the lp-painpoint-analyst agent.** Part of the customer-portal research set (see README). Grounded only in repo material; file paths cited per claim.

**Corpus note (read first).** ROOT = the project root (`UK VIsa/`). The only verbatim real transcript in the repo is `ROOT\chat-brain\examples\erol-close.md`. Real-lead evidence otherwise lives as distilled, dated lessons in `ROOT\chat-brain\lessons.md` and `ROOT\chat-brain\facts.md`. `ROOT\ukv-app\docs\example-chats-quick-services.md` is SYNTHETIC training material ("Realistic end-to-end conversations", L3) using prices that contradict facts.md; marked [synthetic] and never counted as a real client. The raw Lead Chats corpus is in the Google Sheet, not the repo (`memory\lead-chats-log.md`). `ROOT\3 reviews Chats.pdf` could not be rendered. `ROOT\ukv-app\storage\app\private\leads.jsonl` holds only test rows. Counts below are files-in-repo where the theme appears.

## 1. The 15 questions, by stage

**Before paying**

1. "Is it possible in X days?" (5 files: lessons L21 Sandeep "Belgium 14 Sep offered against a 22 Sep flight"; edge-cases L19 Bandita "7 days vs 15 working days"; intake-close L26; engagement-flow F2/E2; landing-copy FAQ2). Human answer: "honest arithmetic: 15-20 day minimum processing + appointment lead time vs their date" (intake-close L26). Portal: a feasibility screen can show the maths; the verdict and date-reset stay human.
2. "Which documents / what do I bring?" (6 files). Real quote, Advella Humbani: "what is it that I have to take with me to the Embassy?" Owner-locked reply: "I shall call you during working hours tomorrow to guide you on the documents" (lessons L277). Portal: a post-payment, tailored, STAGED list ("to BOOK the appointment we only need passport + share code... Financial statements, proof of address and photos are for the appointment DAY", lessons L116). Never a free pre-payment list (lessons L131).
3. "Why charge £40 if I can book the slot myself?" Real quote, Saimul (Italy): "you can book the slot, why charge GBP40?" (lessons L66). Human: deposit = "document review, application form, accommodation, cover letter, hourly slot tracking, NOT the appointment booking" (L66). Portal: a "what your deposit buys" screen plus the Italy rule "the CLIENT books it themselves from their own account" (facts L126). Fully portal-answerable.
4. "Can you guarantee it?" (4 files). Human: "No one honestly can, the consulate decides" (lead-conversion-playbook L124). Portal: static FAQ.
5. "Which country do I apply to for two trips?" (4 files, lessons L21/L33/L97). Human: "country of MOST days (tie -> first entry)" (facts L48). Portal: explain the rule; the routing decision is human (escalation trigger, engagement-flow L177).

**After paying**

6. "Your account is suspended / my transfer is in review" (3 files). Human: "client's bank new-payee security check, not our account... read them from the CURRENT invoice (payee can rotate)" (facts L147). Portal: a payment screen showing live payee details and received/pending status removes this entirely.
7. "Where are we with my file?" Human: proactive milestone messages; "Silence kills trust and invites chargebacks" (lead-conversion-playbook L144). Portal: status timeline, fully answerable.

**Documents**

8. "Which share code?" Real pattern: "a bare 'send your share code' reliably gets the wrong type back, costing a round trip" (facts L44). Portal: upload step naming "View and prove your IMMIGRATION STATUS... not the right-to-work one". Answerable.
9. "Is my balance enough / should I add money?" (Abel, lessons L9, L20: "balance top-up advice AGAIN"). Human only: "NEVER advise topping up" (style-guide L87). Portal must not auto-answer.
10. "Do I buy flights now?" (facts L58, L74-77). Human: "say 'flight RESERVATION'... never 'buy tickets'". Portal: static explainer plus the two-option add-on choice (facts L95).

**Appointment**

11. "You said this week?" Real quote, Kulvir Gill: "you said this week" (lessons L205). Human: explain drip-release and 24h Cover-Letter-ID sync honestly. Portal: an operator-specific state ("reference submitted, waiting for calendar/allocation", engagement-flow C1/C3), never a date.
12. "Can I change the date or centre?" (facts L88-92). Human: fee carries over, "~2-3 changes", centre is locked to the reference. Portal: static rules plus a change counter.

**After appointment**

13. "How long till a decision, can I track it?" (5 files). Human: "~15 calendar days standard, up to 30-45" and "VFS 'Track Your Application' (reference number + surname)" (facts L143-144). Portal: range plus external tracker link; status fixed at "with consulate".
14. "The centre wants my old passport" (client-queries section 5). Human: bring original, quote reference, get a receipt. Human.

**Decision / refusal**

15. "Did I get it? If refused, what now, do I get money back?" Human: "we don't decide... never say 'approved' until in hand" (engagement-flow I5); refund default "NO approval/refund guarantee... BUT the team has told some clients 'we refund 100%'... honour it" (lessons L109). Human, with per-client terms stored.

## 2. Ten quiet or anxious moments

1. **Invoice sent, silence.** Erol: invoice 21:07, nudge 23:24 (erol-close.md). Rule: "T+45-60 min: ONE nudge. Frame = work already in motion" (chase-nurture L4). Portal: "payment pending" state with one scheduled reminder in that exact frame.
2. **High-intent lead dropped at intake.** "Adakafa dropped 47 min" after the 3-part opener (memory lead-chats-log.md). Portal: capture dates/status via form before chat so the agent never re-intakes.
3. **Gone quiet mid-intake** (chase-nurture L10). Portal: saved progress plus resume link.
4. **Ghosted mid-docs.** "soft continuation of the exact outstanding item" (engagement-flow L146); ops wants "auto-chase at 24h then escalating... near travel date keep a 2-day cadence" (delivery-runbook L45). Portal: missing-items list with that cadence.
5. **Direct question buried under a payment chase.** Abel's "Minimum 90 days / Visa duration" "was answered with a payment chase" (lessons L20). Portal: always-visible FAQ so humans never trade an answer for a chase.
6. **Payment "in review" panic** (facts L147). Portal: payee details plus "received" state.
7. **Slot expectation broken** (Kulvir, lessons L205) and **print deadline pressure**: "office printer closes at 5pm... deliver what is ready immediately" (L208). Portal: pack-ready notification with download.
8. **Centre cancels the appointment.** "VFS bulk-cancels appointments... application number survives cancellation" (facts L18). Portal: "cancelled by centre, rebooking" state.
9. **Pack delivery promise.** "If a delivery day was promised (e.g. 'ready Monday'), honour it" (client-queries L50); "full file/folder access is granted AFTER the balance is paid" (facts L101). Portal: gated downloads unlocking on balance.
10. **Data-erasure request** (Saimul, lessons L68: the real concern was "the PORTAL application started under OUR account"). Portal: delete-my-data request that includes operator-portal records.

## 3. What clients asked to SEE or HAVE

- Invoice with number as reference, then "share the screenshot once settled" (style-guide L20-21): receipt/confirmation screen.
- A collective invoice "combining service fee + visa/VFS fee... single reference" (Amal, lessons L42).
- "ONE categorised combined PDF (cover letter -> application form + registration receipt -> passport -> ...)" to print (facts L100); Drive folder as reader after balance (L101).
- Pinned "Meeting Notes summary" after a call (Abel, lessons L10, L19).
- A checklist: lead asked, got "book a call" only, "friction" (lessons L3), later locked to guided call (L277).
- Refusal letter decode by box number (lead-refusal-recovery-playbook section 2).
- Appointment confirmation: "appointment is assigned + confirmed by email" (facts L123).
- Receipt when handing in the old passport (client-queries L36).
- [synthetic] "is there any way to see where my application is?" and "pls where are we with my spain file? been quiet" (example-chats L474, L487).

## 4. Ayla playbook: portal job vs must stay human

**Portal can own (repetitive):** structured first-touch capture (engagement-flow A4); invoice issue and payment confirmation (G1-G2); staged document asks (H1); share-code type guidance (facts L44); nudge cadence L1-L6; £90 trigger on appointment confirmation (G5); status/tracking FAQ (I1-I2); payment-in-review FAQ (G3); consent ask at delivery (K1); review invite with Trustpilot BCC (facts L102); deposit-explainer (E3/F8); reschedule rules (N8).

**Must stay human (trust or risk):** the "trust moment" affirming profile strengths by name (engagement-flow stage 4); the 5-step risk-flag structure ending "Name whose call it is" (style-guide L42-49); pending UK extension / 3C (B3); every refusal scenario D1-D8; consultation-call close for slot-anxiety leads (E4); service recovery and refunds (K6, G4); main-destination routing (C12); funds advice (H3); the whole escalation list (engagement-flow section 4, including "Portal actions we don't perform: creating accounts, entering credentials").

## 5. Competitor, app, login, upload mentions

- No real lead in the repo asked "do you have an app" or "can I log in". Closest real friction: Italy slots "open in the VFS Global app... the website often shows nothing, which confuses clients" (facts L126); Denmark status "visible in the applicant's portal account" (client-queries L17); "Can't log in" scenario I3 (engagement-flow L123).
- Internal gap: "no structured upload yet (#68, manual reply/WhatsApp)" (delivery-runbook L44); `/track` page exists with upload (lead-funnel-analysis L40).
- Competitors: Atlys "add travellers, upload docs, Atlys handles final booking; 'Slots reserved via Atlys are held during your application'" (deepdive-appointment-lane L31); Atlys "What you get" deliverables carousel (competitor-swot L34); CIBT nav "Check Order Status · View Invoice · Upload Documents" (`ROOT\docs\competitors.md` L146); iVisa "24/7 chat/email/WhatsApp; '14 languages'" (deepdive-document-consultancy-lane L30); Visard "No web dashboard, no WhatsApp" (appointment-lane L8); "Nobody pairs a board with a named human" (L69).

## 6. Risks: what the portal must NOT do

1. Show "slot secured" or any date for gated operators: "NEVER promise 'slot secured' for gated operators (TLS, GVCW)" (style-guide L88); "never promise dates" (facts L18).
2. Imply a consulate decision: "never imply we control the decision or say 'approved' until in hand" (I5); "Do NOT say 'delivery' unless a courier return was actually booked" (I4).
3. Imply speed: "Express/priority speeds OUR handling, never the government decision" (style-guide L91).
4. Auto-mark documents "reviewed": the Abel anti-pattern, "NEVER claim a document is reviewed before it exists" (lessons L19). No auto-trust of OCR either: "AI663514 (letter I) was wrongly written A1663514" (facts L140).
5. Auto-advise on funds (lessons L9) or hand over the deliverable free: "flows SERVE... never give the deliverable (doc lists/answers) free on-page" (memory flows-serve-not-inform; lessons L131, L277).
6. Expose internal notes: case notes carried a wrong "£3,559" figure already relayed to a client (lessons L283); owner "risk noted+accepted" decisions (L33); "without exposing an internal slip" (L106); ad "[ref: ...]" lines "never mention it to the client" (facts L51).
7. Auto-file uploads into the pack: a statement showed "TOURLOOM LIMITED... Reference: BP-26-95250" and must be scrubbed before filing (lessons L287).
8. Show simulated availability: the "simulated 70/week dynamic pool... contradicts G1's honesty wedge" (appointment-lane L98).
9. Carry two price ladders: "PRICING MISMATCH: France LP ladder... vs facts.md locked £130" (conversion-playbook L48); refund-on-refusal copy (landing-copy L136) conflicts with lessons L109. One source of truth before launch.
10. Unlock the full pack before balance (facts L101), or ignore erasure that must reach VFS/France-Visas records (lessons L68) and the GDPR purge (delivery-runbook L143).
