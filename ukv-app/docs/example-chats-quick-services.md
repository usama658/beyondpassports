# Quick-services — example WhatsApp chats (lead → delivery)

Internal training + canned-reply reference. Realistic end-to-end conversations for each
small/quick service, from multiple customer personas. Shows the agent's judgement calls,
the quote → payment → delivery arc, and compliant framing.

**Compliance rules baked into every chat**
- Never promise or imply a visa approval. The decision is always the consulate's.
- "Express" speeds **our** handling, not the government's decision or the centre's slots.
- Our **service fee is separate** from any government / embassy / third-party fee.
- Say honestly when we cannot help (and don't charge for that).
- No made-up figures shown to clients; quote before taking payment.

## Standard pricing used here (indicative market rates — service fee only)

| Service | Fee | Notes |
|---|---|---|
| Visa checker | No charge | Lead-gen self-serve |
| IDP checker | No charge | Lead-gen self-serve |
| Document checklist | No charge | Lead-gen self-serve |
| 90/180-day calculator | No charge | Lead-gen self-serve |
| Status tracker | No charge | Existing-order touch |
| Find nearest centre | No charge | Lead-gen self-serve |
| Refusal-risk check | £29 | Pre-application review |
| Refusal letter decode | £39 | Written decode + fix list |
| Single document check | £25 | One document reviewed |
| Cover letter | £45 write / £29 review | |
| Proof-of-funds guidance | £29 | |
| Document legalisation / apostille | £49 + apostille fee | FCDO apostille (~£45) paid separately |
| Express appointment booking | £49 | Excludes the centre's own slot/lounge fee |
| Entry-requirements check | £19 | Beyond the visa |
| Travel insurance | No charge | Introducer to authorised partner (FCA) |

## Time spans (SLA map — lead → delivery)

All timings are **business hours, Mon–Sat 9–6**. The delivery clock **starts when the customer
has paid AND sent the required documents**, and **pauses** whenever we're waiting on them. First
reply is the brand promise (site says "within 24 hours"; WhatsApp is usually much faster).

| Service | First reply | Delivery turnaround (after docs in) | Typical total span |
|---|---|---|---|
| Visa checker | ≤ 2 hrs | Instant (answer in chat) | Minutes |
| IDP checker | ≤ 2 hrs | Instant (answer in chat) | Minutes |
| Document checklist | ≤ 2 hrs | Instant (link in chat) | Minutes |
| 90/180-day calculator | ≤ 2 hrs | Instant once dates given | Minutes |
| Status tracker | ≤ 2 hrs | Instant (lookup) | Minutes |
| Find nearest centre | ≤ 2 hrs | Instant (answer in chat) | Minutes |
| Refusal-risk check | ≤ 2 hrs | ≤ 24 hrs | ~1 business day |
| Refusal letter decode | ≤ 2 hrs | Same day – 24 hrs | Same day – 1 day |
| Single document check | ≤ 2 hrs | Same business day (≤ 24 hrs) | Same day – 1 day |
| Cover letter (write) | ≤ 2 hrs | 24–48 hrs + one revision | 1–2 days |
| Cover letter (review) | ≤ 2 hrs | ≤ 24 hrs | ~1 day |
| Proof-of-funds guidance | ≤ 2 hrs | ≤ 24 hrs | ~1 day |
| Document legalisation / apostille | ≤ 2 hrs | 3–7 working days (FCDO ~2–5 + our handling; translation adds 2–3) | ~1 week+ |
| Express appointment booking | ≤ 30 min | Slot secured 1–5 days (availability-driven; monitoring is continuous) | A few days |
| Entry-requirements check | ≤ 2 hrs | Same day (≤ 24 hrs) | Same day – 1 day |
| Travel insurance | ≤ 2 hrs | Instant (partner link in chat) | Minutes |

**Notes**
- **Apostille and express booking are the only two we can't fully control the clock on** — the FCDO's turnaround and the centre's slot availability are external. Always frame these as ranges, never a fixed promise.
- **Express booking ≠ faster government decision.** The £49 buys faster *slot-hunting*, and the total span above is "time to secure the appointment," not "time to visa."
- If a customer hasn't sent docs, the delivery clock hasn't started — chase, don't over-promise.

---

# Transcripts — 7 customer personas per service

## 1. Visa checker — No charge

> ⏱ First reply ≤ 2 hrs · Delivery Instant (answer in chat) · Typical span Minutes

**Customer A — British citizen, salaried, weekend trip to France**
> **[Mon 10:02] Customer:** Hi, I'm British, planning a long weekend in Paris next month. Do I need a Schengen visa?
> **[Mon 10:41] BP:** Hi, thanks for messaging Beyond Passports. Quick check: is this a UK (British citizen) passport you'll be travelling on?
> **[Mon 10:48] Customer:** Yes, full UK passport, red one.
> **[Mon 10:55] BP:** Then you do not need a Schengen visa for a short trip. British citizens can enter France and the rest of the Schengen area for up to 90 days in any 180 for tourism, no visa needed. Two things to have ready: your passport must be issued within the last 10 years and valid for at least 3 months beyond the date you plan to leave.
> **[Mon 10:57] Customer:** Mine was issued 2016. That's fine then?
> **[Mon 11:03] BP:** Issued 2016 means it passes the 10-year rule as long as your travel is before the 2026 anniversary of the issue date, so double-check that exact date on the photo page. Expiry also needs 3 months clearance after you return. If both are good, you're set. Nothing for us to do here and no charge for the check.
> **[Mon 11:04] Customer:** Perfect, thank you.
> **[Mon 11:06] BP:** Safe travels. One heads-up, the EU's new ETIAS travel authorisation is expected to start later, so if you travel further out it's worth a re-check. We're happy to look again any time, no charge.

_Elapsed lead → delivery: about 1 hour._

**Customer B — Indian national, skilled-worker visa, business trip to Germany**
> **[Tue 09:12] Customer:** Hello. I'm Indian, living in London on a Skilled Worker visa. Company wants to send me to Munich for a 4-day conference. Do I need a visa and what type?
> **[Tue 09:29] BP:** Hi, welcome to Beyond Passports. Yes, as an Indian passport holder you'll need a Schengen visa. For a conference the correct category is a short-stay Schengen visa, business purpose, applied through Germany since that's your destination. Can I check two things: how long is your UK BRP/visa valid, and do you have the return part of your trip fixed?
> **[Tue 09:41] Customer:** BRP valid till 2027. Conference is 12–15 next month, flights not booked yet.
> **[Tue 09:52] BP:** Good, your UK status comfortably covers it. Core documents Germany will want: passport, UK residence proof, an invitation or registration letter from the conference, employer letter confirming your role and that they cover costs, travel insurance, and proof of accommodation and travel. You apply at the German visa centre (TLScontact/VFS depending on region) and book a biometrics appointment.
> **[Tue 09:54] Customer:** How long does it take? Conference is in 5 weeks.
> **[Tue 10:03] BP:** Five weeks is workable but not generous, since the consulate's processing time is their call and can run 10–15 working days once you're seen, plus appointment wait. To be clear, we can speed up our side, the paperwork prep and hunting an earlier biometrics slot, but we can't speed the consulate's decision or guarantee a slot exists.
> **[Tue 10:05] Customer:** Understood. What would you charge?
> **[Tue 10:12] BP:** The guidance you've had here is no charge. If you'd like a full document check before you submit so nothing gets bounced, that's our single-document check at £25, or the full refusal-risk review at £29. Our fee is separate from Germany's visa fee and the visa-centre service fee, which you pay them directly. Want me to send the document checklist to start?
> **[Tue 10:14] Customer:** Yes please send the checklist.
> **[Tue 10:18] BP:** Sent to your WhatsApp now. Reply here once your employer letter is drafted and I'll point you to the £29 review if you want a second set of eyes.

_Elapsed lead → delivery: about 1 hour._

**Customer C — Nigerian national, ILR holder, family trip to Spain**
> **[Wed 13:20] Customer:** Hi, me my wife and 2 kids want to go Spain in the summer. I have ILR, Nigerian passport. Do we all need visas?
> **[Wed 13:44] BP:** Hi, thanks for reaching out to Beyond Passports. It depends on each person's nationality, not your UK status. ILR is your UK residence, it doesn't remove the Schengen visa requirement. Are all four of you on Nigerian passports, or do any of you hold British or EU citizenship?
> **[Wed 13:51] Customer:** All 4 Nigerian. Kids born in Nigeria too.
> **[Wed 13:58] BP:** Then all four need a short-stay Schengen visa for Spain, including the children. You'd apply together as a family through Spain's visa centre (BLS). Each person needs their own application and biometrics, though under-12s are usually exempt from fingerprints. How old are the children?
> **[Wed 14:10] Customer:** 6 and 9.
> **[Wed 14:19] BP:** Good, both under 12 so no fingerprints for them, but they still need photos, forms and documents. For minors you'll also need birth certificates and, since both parents are travelling, that part is simpler. Key papers: ILR proof for you and your wife, passports, bank statements showing you can fund the trip, accommodation and return flights, and travel insurance for all four.
> **[Wed 14:22] Customer:** Ok that's a lot. Summer is far so we have time.
> **[Wed 14:31] BP:** You do, which is the ideal position. This check is no charge. When you're ready to prepare, a lot of family refusals come from thin or mismatched financial evidence, so our refusal-risk review at £29 covers the whole family's file in one go. Our fee is separate from Spain's visa fees and the BLS service charge. No rush, message any time.

_Elapsed lead → delivery: about 1 hour._

**Customer D — Filipino national, student visa, recent overstay concern (edge case: manage expectations)**
> **[Thu 11:05] Customer:** Hi I need a schengen visa for Italy. But I have to tell you something. I overstayed in Germany by 3 weeks last year before I came to UK on my student visa. Will that be a problem?
> **[Thu 11:33] BP:** Thanks for being upfront, that genuinely helps. Honest answer: a recorded overstay in the Schengen area is a real factor. It can sit on the SIS/entry system and consulates do see it, so it can lead to extra scrutiny or a refusal. I won't tell you it's fine when it may not be.
> **[Thu 11:38] Customer:** So should I not bother applying?
> **[Thu 11:49] BP:** I'm not saying don't apply, I'm saying go in with eyes open. A single short overstay that has since ended, with a clean UK student record and strong ties, can still be approved, but the decision is entirely Italy's consulate and no one can promise it. What matters is a clean, well-explained file: proof the overstay is resolved, strong reasons to return to the UK, and full funds.
> **[Thu 11:52] Customer:** Can you guarantee it if I pay?
> **[Thu 11:58] BP:** No, and I'd steer you away from anyone who says they can. We can't influence the consulate's decision, only help you present honestly and completely. Given the overstay, I'd be doing you a disservice selling you an express booking as if speed fixes this. It doesn't.
> **[Thu 12:03] Customer:** Ok. What do you suggest then.
> **[Thu 12:12] BP:** The right next step for your situation is the refusal-risk review at £29, where we assess the overstay honestly against your current profile and tell you plainly if it's worth applying now or waiting. If we think the risk is too high to justify the effort and cost, we'll say so, no upsell. This checker chat stays no charge. Want me to open that review?
> **[Thu 12:15] Customer:** Yes let's do the review.
> **[Thu 12:20] BP:** Good call. I'll send the review intake now, and I'll flag the overstay at the top so it's assessed first, before you spend on any consulate fee.

_Elapsed lead → delivery: about 1 hour 15 min._

**Customer E — Egyptian national, self-employed, trip to Greece (edge case: service not needed / DIY)**
> **[Fri 15:40] Customer:** Hi, Egyptian passport, self employed in UK on a Global Talent visa. I want to visit Greece for 8 days. Honestly I've done Schengen visas before myself, I just want to confirm the visa type. Do I really need to pay an agency?
> **[Fri 16:02] BP:** Hi, and thanks for the honesty. Straight answer: no, you don't need to pay anyone if you've done this before and you're comfortable. For 8 days of tourism in Greece you need a short-stay Schengen visa, type C, applied through Greece's visa centre. That's the whole answer and it's no charge.
> **[Fri 16:06] Customer:** Great, that's what I thought. Anything different for self-employed?
> **[Fri 16:15] BP:** Yes, the financial evidence differs. Instead of employer letters, Greece will typically want your business registration, recent business bank statements, personal bank statements, and often your latest tax return (SA302 or equivalent) to show income and ties. Everything else is standard: passport, UK residence proof, insurance, accommodation, return flights.
> **[Fri 16:18] Customer:** Perfect. I have all that.
> **[Fri 16:24] BP:** Then you're well set and I won't invent a reason for you to buy anything. If you ever want one last look before submitting, our single-document check is £25 for a specific worrying document, but from what you've said you may not need it. Our fee would always be separate from the Greek visa and visa-centre fees. Good luck.
> **[Fri 16:26] Customer:** Appreciate the honesty, will keep you in mind.

_Elapsed lead → delivery: about 45 min._

**Customer F — Pakistani national, dependant visa, elderly parent visiting, tight deadline (edge case: deadline impossible)**
> **[Sat 09:08] Customer:** URGENT. My mother is Pakistani, wants to come with us to Netherlands. We fly in 9 days. Can you get her a Schengen visa in time?
> **[Sat 09:22] BP:** Morning, thanks for messaging. I want to be straight with you because time matters here. Nine days is almost certainly not enough for a Netherlands Schengen visa from scratch. Does your mother already have a biometrics appointment booked at the visa centre, or an existing valid visa?
> **[Sat 09:26] Customer:** No appointment yet. Nothing booked.
> **[Sat 09:35] BP:** Then honestly, 9 days is not realistic. Without an appointment you first have to get a biometrics slot, and Netherlands processing after that is the consulate's own timeline, commonly around 15 calendar days and legally up to 45. Even our express slot-hunting speeds our side, not their queue or their decision. I don't want to take money for something the calendar won't allow.
> **[Sat 09:38] Customer:** So there's no way at all?
> **[Sat 09:47] BP:** The realistic options are: apply now aiming for a later trip for your mother, or if your dates are movable, push the travel out to give a proper window. I'd rather tell you this now than sell an express booking that can't beat the consulate's clock. Is the travel date fixed, or is your mother's leg of it flexible?
> **[Sat 09:51] Customer:** The date is fixed for us. Maybe she comes later separately.
> **[Sat 09:58] BP:** That's the sensible route. When you're ready to plan her later trip, this checker stays no charge, and our express booking at £49 can help her secure the earliest realistic biometrics slot, with no false promises on the decision. Our fee is separate from the Dutch visa and centre fees. Message any time and we'll move fast on her side.

_Elapsed lead → delivery: about 50 min._

**Customer G — Brazilian national, retired, long stay concern to Portugal (edge case: wrong visa / redirect)**
> **[Mon 14:15] Customer:** Hello, I'm Brazilian, retired, I live in the UK on ILR. I want to spend 5 months in Portugal next year, mostly resting in the Algarve. Which Schengen visa do I get?
> **[Mon 14:39] BP:** Hi, thanks for reaching out. Important flag before anything else: 5 months is beyond what a short-stay Schengen visa allows. The Schengen short-stay limit is 90 days in any 180-day period, so a standard tourist visa can't cover a continuous 5-month stay. What you're describing is a national long-stay matter, not the short-stay visas we handle.
> **[Mon 14:44] Customer:** Oh. So what do I actually need?
> **[Mon 14:52] BP:** For 5 continuous months in Portugal you'd be looking at a Portuguese national long-stay visa or residence route (for example their retirement/passive-income D7 type), which is applied for directly through the Portuguese authorities and is a different process from Schengen short-stay. That's outside what we do, so I won't take you down our path pretending it fits.
> **[Mon 14:55] Customer:** Could I just do 90 days instead?
> **[Mon 15:04] BP:** You could. As a Brazilian passport holder with ILR, a Portugal short-stay visit within 90 days in any 180 is the kind of trip we can help with, and note Brazilians often benefit from short-stay arrangements, so we'd confirm the exact requirement for your passport. If you keep it to 90 days or under, this becomes a straightforward short-stay check.
> **[Mon 15:07] Customer:** I think I'll do 90 days first and see.
> **[Mon 15:13] BP:** Sensible. When you fix dates, message here for the no-charge check, and if you want your funds and retirement-income evidence reviewed before applying, the refusal-risk review is £29. For the full 5-month plan I'd point you to a Portuguese immigration specialist for the long-stay route, our fee wouldn't apply there. Happy to help on the 90-day version whenever you're ready.

_Elapsed lead → delivery: about 1 hour._

## 2. IDP checker — No charge

> ⏱ First reply ≤ 2 hrs · Delivery Instant (answer in chat) · Typical span Minutes

**Customer A — British citizen, retired, driving holiday in France**
> **[Mon 09:14] Customer:** Hi, we're driving down through France to Spain in August in a hire car. Do I need an International Driving Permit?
> **[Mon 09:31] BP:** Morning! For a full UK photocard licence, you don't need an IDP to drive in France or Spain for a tourist trip. Your GB licence is accepted across the EU/Schengen area. Quick check: is your licence the plastic photocard, or an older paper one?
> **[Mon 09:48] Customer:** Photocard, issued in Birmingham.
> **[Mon 09:52] BP:** Then you're fine, no IDP needed for France or Spain. Two things worth doing: carry the physical licence (not just a photo), and confirm the hire company's own rules, as some ask for a licence "check code" from the DVLA. That code is generated on gov.uk at no charge.
> **[Mon 10:03] Customer:** That's great, thought I'd have to queue at PayPoint for one. Thank you!
> **[Mon 10:06] BP:** Not this time, save yourself the trip. Enjoy the drive. If you later add a non-Schengen leg (say Morocco from southern Spain), the answer changes, so just ping us if the route grows.
_Elapsed lead → delivery: ~50 minutes._

**Customer B — Indian national on Skilled Worker visa, road trip in Italy**
> **[Tue 13:20] Customer:** Hello. I have an Indian driving licence and I live in the UK on a work visa. Renting a car in Italy next month. IDP yes or no?
> **[Tue 13:41] BP:** Hi! Key point: Italy requires an International Driving Permit alongside an Indian licence. Since you're resident in the UK, though, there's a wrinkle. India issues IDPs only to people currently in India through their RTO. The UK PayPoint IDP scheme is for holders of a UK licence, so it won't cover your Indian one.
> **[Tue 13:44] Customer:** So I can't get one here at all?
> **[Tue 13:47] BP:** Not against an Indian licence from the UK, no. Two honest routes: (1) exchange your Indian licence for a UK one if you're eligible (DVLA process), then a UK IDP or your UK licence works; or (2) arrange an Indian IDP on your next trip home. I can't sell you a shortcut that doesn't exist here.
> **[Tue 13:52] Customer:** Ah. Timeline is too tight to exchange before the trip.
> **[Tue 13:55] BP:** Then realistically, for this trip, a car hire in your name may not be feasible with the documents you hold. Safer option: have a travel companion with a valid accepted licence drive, or use trains/transfers in Italy. I'd rather flag that now than have you stuck at the rental desk.
> **[Tue 14:02] Customer:** Frustrating but I appreciate the straight answer.
> **[Tue 14:05] BP:** Understood. No charge for the check, and nothing to sell you here. If you do start the DVLA licence exchange, our single-document check (£25, separate from any DVLA fee) can sanity-check your application pack before you post it.
_Elapsed lead → delivery: ~45 minutes._

**Customer C — Nigerian national, ILR, self-employed, Germany for a trade fair**
> **[Wed 10:05] Customer:** Good morning. I have ILR and a full UK licence. Going to Germany for a trade fair, hiring a van. Do I need an IDP?
> **[Wed 10:22] BP:** Morning! Good news: with a full UK photocard licence, no IDP is needed for Germany. Your UK licence is accepted for both car and van categories you're licensed for. Just confirm the van is within your licence category (standard cars/vans up to 3.5t sit under category B).
> **[Wed 10:30] Customer:** It's a Sprinter-type van, should be under 3.5 tonnes.
> **[Wed 10:33] BP:** Then category B covers it, no IDP required. Bring the physical licence and your passport. If the rental firm asks for a DVLA check code, generate it on gov.uk (no charge) close to travel, as the codes expire after 21 days.
> **[Wed 10:38] Customer:** Perfect. And insurance for the trip?
> **[Wed 10:41] BP:** Vehicle cover usually comes via the rental firm; your own travel insurance is separate and worth having. That's outside the IDP question, but happy to point you the right way. Anything else on documents?
> **[Wed 10:44] Customer:** No, that's clear. Thanks a lot.
> **[Wed 10:46] BP:** Enjoy the fair. If you're also applying for a Schengen business visa for the trip, our refusal-risk check (£29) reviews the pack before you submit, separate from any embassy fee.
_Elapsed lead → delivery: ~40 minutes._

**Customer D — Pakistani national, student visa, wants IDP for Greece**
> **[Thu 15:10] Customer:** Salaam. I'm a student here in the UK. I have a Pakistani licence and want to rent a scooter in Greece. Can you get me a UK IDP?
> **[Thu 16:52] BP:** Wa alaikum salaam! Honest answer first: the UK IDP (issued via PayPoint) is only for holders of a valid GB licence. It can't be issued against a Pakistani licence, so we can't get you a UK IDP for this.
> **[Thu 17:00] Customer:** Oh. Someone told me any student could get one here.
> **[Thu 17:04] BP:** That's a common myth, but it isn't accurate. For Greece with a Pakistani licence you'd need a Pakistani-issued IDP, obtained in Pakistan before you travel. Also note: scooter/moped hire in Greece often needs the correct motorcycle category on your licence and IDP, not just a car entitlement. Worth checking your licence class.
> **[Thu 17:09] Customer:** My licence is car only.
> **[Thu 17:12] BP:** Then even with an IDP, a scooter above 50cc may not be legal for you to ride, and hire firms can refuse. I'd steer you toward buses, ferries and taxis in Greece rather than risk an uninsured ride. Rather be upfront than have you fined abroad.
> **[Thu 17:15] Customer:** Okay, thank you for being clear.
> **[Thu 17:17] BP:** No charge for the guidance. Nothing to sell you here. If your study plans later mean exchanging to a UK licence, ping us and we'll point you at the DVLA route.
_Elapsed lead → delivery: ~2 hours (within same-day SLA)._

**Customer E — Filipino national, dependant visa, family trip to Austria and Croatia**
> **[Fri 09:25] Customer:** Hi! Filipino here, on a dependant visa. I hold a UK provisional and also a full Philippine licence. Family driving trip Austria then Croatia. IDP needed?
> **[Fri 09:44] BP:** Hi! Let's untangle the two licences. A UK provisional does NOT let you drive abroad, and no IDP is issued against a provisional. So the UK one doesn't help here yet.
> **[Fri 09:49] Customer:** And the Philippine full licence?
> **[Fri 09:53] BP:** A Philippine licence is accepted in many EU countries for short visits, but Austria and Croatia both expect an accompanying International Driving Permit for a non-EU licence. That IDP must be issued in the Philippines (by the LTO/AAP), not here, as the UK PayPoint IDP is GB-licence only.
> **[Fri 09:58] Customer:** I don't have a Philippine IDP and can't get back home before the trip.
> **[Fri 10:02] BP:** Then honestly, driving yourself on this trip is a no. Options: have a family member with an accepted licence + valid IDP do the driving, or lean on trains between the cities, which are excellent on that route. I won't pretend the UK can plug this gap.
> **[Fri 10:07] Customer:** My brother-in-law has an Irish licence, he'll drive.
> **[Fri 10:10] BP:** An EU/Irish licence needs no IDP for Austria or Croatia, so he's your driver. Sorted, and no charge for the check. When your UK provisional becomes a full licence, future trips get much simpler.
_Elapsed lead → delivery: ~45 minutes._

**Customer F — Egyptian national, skilled worker, express query, Portugal on Saturday**
> **[Sat 09:05] Customer:** URGENT. Full UK licence (Egyptian originally, exchanged last year). Flying to Portugal TOMORROW, rental booked. Do I need an IDP and can you rush one??
> **[Sat 09:18] BP:** Morning, and breathe, this one's likely fine. You said you EXCHANGED to a full UK photocard licence, correct? If so, no IDP is needed for Portugal at all. Your UK licence is accepted.
> **[Sat 09:21] Customer:** Yes fully exchanged, DVLA photocard in my hand.
> **[Sat 09:24] BP:** Then you need nothing extra, no IDP, no rush service, no fee. Bring the physical licence and passport. Generate a DVLA check code on gov.uk today if the rental firm asks for one (no charge, valid 21 days).
> **[Sat 09:27] Customer:** So I panicked for nothing 😅
> **[Sat 09:29] BP:** Happily, yes. A note on "express" generally: even when an IDP IS needed, our express only speeds OUR handling and slot-hunting, it can't speed a government counter or guarantee same-day. But here you simply don't need one.
> **[Sat 09:33] Customer:** Legend. Thank you!
> **[Sat 09:35] BP:** Safe travels to Portugal. If a future trip needs a Schengen visa on tight timing, our express booking (£49, separate from any embassy/centre fee) hunts the earliest real slot for you.
_Elapsed lead → delivery: ~30 minutes._

**Customer G — Ghanaian national, recently naturalised British, USA-plus-France muddle**
> **[Mon 11:40] Customer:** Hello. Just became British, have a UK licence. Planning France in spring and also California later. Do I sort one IDP for both?
> **[Mon 11:58] BP:** Congratulations on the citizenship! Important distinction: France and the USA are two different answers. For France (Schengen), your UK photocard licence alone is fine, no IDP.
> **[Mon 12:04] Customer:** And California?
> **[Mon 12:08] BP:** Different world. The USA isn't Schengen, and while many US states accept a UK licence for short car hire, some rental firms and states prefer or require an IDP as a recognised translation. That IDP is the 1949-convention type, issued via PayPoint in person, separate question from your France trip.
> **[Mon 12:13] Customer:** So maybe one for the US, none for France?
> **[Mon 12:16] BP:** Exactly. If you get a UK IDP, it's for the US leg, and you'd apply in person at a PayPoint branch (bring UK licence, passport photo, small fee paid there, not to us). It's guided self-service; there's no done-for-you IDP. For France you carry nothing extra.
> **[Mon 12:21] Customer:** Clear. I'll grab the IDP nearer the US trip then.
> **[Mon 12:24] BP:** Right call, as the 1949 IDP only lasts 12 months, so timing it near travel is smart. No charge for today's check. Our focus is Schengen visas, so if the France trip ever needs a visa (it won't for a British citizen), you know where we are.
_Elapsed lead → delivery: ~45 minutes._

## 3. Document checklist — No charge

> ⏱ First reply ≤ 2 hrs · Delivery Instant (link in chat) · Typical span Minutes

**Customer A — British citizen, salaried, first Schengen trip (France)**
> **[Mon 09:14] Customer:** Hi, going to Paris in October with my partner. British passport. What documents do I actually need?
> **[Mon 09:41] BP:** Morning! Good news first: as a British citizen you don't need a Schengen visa for a short France trip. Up to 90 days in any 180 you can travel visa-free.
> **[Mon 09:43] Customer:** Oh! So I don't need anything from you?
> **[Mon 09:44] BP:** Correct, no visa and no checklist needed. Just carry a passport valid for at least 3 months beyond your return date and with under 10 years since issue. Return/onward ticket and proof of accommodation are worth having in case border staff ask.
> **[Mon 09:46] Customer:** Brilliant, that's a relief. Thank you for being straight with me.
> **[Mon 09:47] BP:** Any time. When ETIAS goes live you'll just need a quick online travel authorisation, no appointment. I'll not sell you something you don't need. Safe travels to Paris!

_Elapsed lead → delivery: about 33 minutes._

**Customer B — Indian national, skilled-worker visa, salaried (Germany, business)**
> **[Mon 11:02] Customer:** Hello. I'm Indian, on a UK Skilled Worker visa. Need to visit our Munich office for a week in September. Can you send the document list for a German business visa?
> **[Mon 11:20] BP:** Hi, happy to. Quick check first: is your UK BRP/eVisa valid for at least 3 months beyond your return, and is the trip employer-sponsored?
> **[Mon 11:26] Customer:** Yes valid till 2027. Employer is sending me, they'll cover costs.
> **[Mon 11:34] BP:** Perfect, that shapes the list. Here's your Germany short-stay business checklist: 👉 beyondpassports.co.uk/checklist/de-business
> **[Mon 11:35] BP:** Core items: application form, 2 biometric photos, passport (plus old ones), UK visa/eVisa proof, cover letter, invitation letter from the Munich office, your UK employer letter confirming role + trip purpose, 3 months' bank statements + payslips, travel insurance min €30,000 cover, flights and hotel booking.
> **[Mon 11:37] Customer:** That invitation letter, what should it say?
> **[Mon 11:39] BP:** It should state who you are, purpose and dates, who bears costs, and the host's contact details. The German mission fee (currently around €90) and the VFS/appointment-centre service fee are separate and paid to them, not to us.
> **[Mon 11:41] Customer:** Understood. Tight on time as the slot dates look far out.
> **[Mon 11:43] BP:** If the deadline gets tight, our Express slot-hunting (£49) watches for earlier VFS openings. It speeds our search only, it can't create slots or affect Germany's decision. No obligation, the checklist above is yours at no charge.

_Elapsed lead → delivery: about 40 minutes._

**Customer C — Nigerian national, self-employed, student-in-UK dependant (Italy)**
> **[Mon 14:08] Customer:** Good afternoon. Nigerian passport, I'm in the UK as a dependant on my wife's student visa. Want to visit Rome for 10 days. Checklist please.
> **[Mon 14:31] BP:** Good afternoon! I can send the Italy tourist checklist. One thing to flag: as a dependant your UK status and ties matter to the consulate, so we'll make sure that section is solid.
> **[Mon 14:33] Customer:** Okay. I'm self-employed, run a small logistics business back home and remotely.
> **[Mon 14:40] BP:** Noted, self-employed changes the financial evidence. Here's the tailored list: 👉 beyondpassports.co.uk/checklist/it-tourist-selfemp
> **[Mon 14:41] BP:** Key items: form, 2 photos, passport + UK dependant BRP/eVisa, wife's student proof (CAS/enrolment + her visa), marriage certificate, 6 months' personal + business bank statements, CAC business registration, flights, Rome accommodation, and travel insurance €30,000+.
> **[Mon 14:44] Customer:** My bank statements are a bit up and down month to month. Worried about that.
> **[Mon 14:46] BP:** That's a common concern with self-employed income. It doesn't disqualify you, but consistency and a clear closing balance help. I can't promise an outcome, the decision is always Italy's consulate.
> **[Mon 14:47] Customer:** Makes sense. How do I know if my finances look risky?
> **[Mon 14:49] BP:** That's exactly what our Refusal-risk check (£29) is for: we review your specific docs and flag weak spots before you submit. The checklist itself stays at no charge. Want the link?
> **[Mon 14:52] Customer:** Yes send it, I'll think about the review.
> **[Mon 14:53] BP:** Done above. Take your time, no pressure.

_Elapsed lead → delivery: about 45 minutes._

**Customer D — Filipino national, ILR, retired (Spain)**
> **[Tue 16:40] Customer:** Hi there. Filipina, I have UK ILR, retired now. Planning a month in Spain over winter. What papers do I need?
> **[Wed 09:12] BP:** Good morning, and thanks for your patience overnight! With UK ILR you're a settled resident, so this is a straightforward Spain short-stay. A month is within the 90/180 limit, all good.
> **[Wed 09:15] Customer:** Yes just a long holiday. I get a pension, no job now.
> **[Wed 09:22] BP:** Retired is fine, pension counts as income. Here's your Spain tourist checklist: 👉 beyondpassports.co.uk/checklist/es-tourist-retired
> **[Wed 09:23] BP:** Items: form, 2 photos, passport + UK ILR proof (BRP/eVisa), pension statements + 3-6 months' bank statements, accommodation for the full month (hotel/rental/host), flights both ways, and travel insurance €30,000+ covering the whole stay.
> **[Wed 09:25] Customer:** The insurance for a whole month sounds pricey.
> **[Wed 09:27] BP:** It varies by provider and age, and you buy it directly from the insurer, that cost is separate from anything we do. Shop around, annual multi-trip can work out cheaper if you travel often.
> **[Wed 09:29] Customer:** Good tip. That's everything I need then?
> **[Wed 09:31] BP:** That's the full list. If you'd like a second pair of eyes on one item before booking your appointment, our Single-document check (£25) covers that. Otherwise you're set, enjoy the Spanish winter sun!

_Elapsed lead → delivery: next morning (overnight lead), then minutes._

**Customer E — Pakistani national, recent overstay concern (Netherlands) — expectation-managed**
> **[Thu 10:05] Customer:** Salaam. Pakistani passport, living in UK on a work visa. I want the checklist for Netherlands. But I have to be honest, I overstayed a Schengen trip by 3 weeks last year before I sorted my dates.
> **[Thu 10:26] BP:** Wa alaikum salaam, and thank you for being upfront, that really helps. I'll be equally honest with you.
> **[Thu 10:28] BP:** A recent overstay is a material issue. The consulate can see prior Schengen history, and an overstay raises questions about compliance. A checklist alone won't address that.
> **[Thu 10:30] Customer:** So should I just not bother applying?
> **[Thu 10:33] BP:** I'm not saying don't apply, that's your call. But I won't hand you a generic list and imply it'll be smooth, because it may not be, and the decision is entirely the Dutch consulate's.
> **[Thu 10:35] Customer:** What would you actually recommend?
> **[Thu 10:38] BP:** Before spending on fees and an appointment, get the overstay assessed properly. Our Refusal-risk check (£29) looks at your specific history and tells you honestly whether it's worth applying now or waiting. That's more useful to you than a checklist today.
> **[Thu 10:40] Customer:** Fair. I'd rather know before I waste the embassy fee.
> **[Thu 10:42] BP:** Exactly the right instinct. The mission and centre fees are non-refundable and separate from us. Shall I send the risk-check link?
> **[Thu 10:44] Customer:** Yes please.
> **[Thu 10:45] BP:** Sent. Whatever the outcome, you'll get a straight answer, not a sales pitch.

_Elapsed lead → delivery: about 40 minutes (risk-check advised, no checklist issued)._

**Customer F — Brazilian national, student in UK, family group (Greece)**
> **[Fri 12:50] Customer:** Oi! Brazilian, studying in the UK on a student visa. Travelling to Greece in the summer with my mum and younger brother who are visiting from Brazil. Do we all need the same documents?
> **[Fri 13:15] BP:** Oi, olá! Great question. You're in different situations, so the docs differ a bit. You apply as a UK resident student; your mum and brother, visiting from Brazil, would normally apply from Brazil unless they're lawfully here long enough to apply in the UK. Are they in the UK now on visitor visas?
> **[Fri 13:22] Customer:** Yes they're here for 2 months on visit visas, arrived last week.
> **[Fri 13:30] BP:** Thanks. That's a nuance: UK visitor status doesn't always let non-residents apply for Schengen from the UK, missions usually want you to apply where you're normally resident. So your brother and mum may need to apply from Brazil.
> **[Fri 13:33] Customer:** Ah I didn't realise. So maybe just me from here?
> **[Fri 13:36] BP:** Likely, yes. Here's your Greece student checklist for the UK-based applicant: 👉 beyondpassports.co.uk/checklist/gr-tourist-student
> **[Fri 13:37] BP:** Yours: form, 2 photos, passport + UK student eVisa/BRP, enrolment letter + CAS, bank statements/sponsor proof, flights, Greece accommodation, insurance €30,000+. For your family the requirement list is similar but filed in Brazil.
> **[Fri 13:40] Customer:** This is confusing for a group. Can someone check we're doing it right?
> **[Fri 13:42] BP:** For mixed-residency families it's worth it. Our Refusal-risk check (£29 per applicant) can review each person's situation so nobody books an appointment in the wrong country. The checklist above stays at no charge.
> **[Fri 13:45] Customer:** Obrigada, I'll discuss with my mum.
> **[Fri 13:46] BP:** De nada! Take your time, links are yours to keep.

_Elapsed lead → delivery: about 55 minutes._

**Customer G — Egyptian national, self-employed business owner, urgent (Belgium) — deadline managed**
> **[Sat 09:03] Customer:** Hello. Egyptian, UK ILR. I have a trade conference in Brussels and I need to travel in 6 days. Send me the checklist, I want to apply Monday.
> **[Sat 09:19] BP:** Morning! I can send the Belgium business checklist right away, but I have to be straight with you about the timeline before you spend anything.
> **[Sat 09:21] Customer:** Go on.
> **[Sat 09:24] BP:** Six days is very tight. Even with an appointment, Belgium's processing can take around 15 calendar days and sometimes longer, and that's fully the consulate's timeframe, not something we or Express can shorten. Express only speeds our slot-hunting, not the decision.
> **[Sat 09:26] Customer:** So realistically I won't get it in time?
> **[Sat 09:28] BP:** Honestly, for a fixed date 6 days out, it's high-risk to impossible. I won't take money implying we can make it happen when the decision timeline sits with Belgium.
> **[Sat 09:30] Customer:** Damn. Is there any version of this that works?
> **[Sat 09:33] BP:** Two honest options: try for a later conference date if the organiser allows, or apply now accepting you may miss this one but be ready for the next trip. Here's the checklist either way: 👉 beyondpassports.co.uk/checklist/be-business
> **[Sat 09:34] BP:** Items: form, 2 photos, passport + UK ILR proof, company registration + trade licence, 6 months' business + personal statements, conference invitation/registration, cover letter, flights, hotel, insurance €30,000+.
> **[Sat 09:36] Customer:** Appreciate you not just taking my money.
> **[Sat 09:38] BP:** That's how we work. If you do apply and want the file pressure-tested fast, the Refusal-risk check (£29) is there, but no charge for the checklist or this advice. The mission and VFS fees are separate and non-refundable, so go in with eyes open.

_Elapsed lead → delivery: about 35 minutes (checklist issued with deadline warning)._

## 4. 90/180-day calculator — No charge

> ⏱ First reply ≤ 2 hrs · Delivery Instant once dates given · Typical span Minutes

**Customer A — British retiree, second home in Spain, salaried pension**
> **[Mon 09:11] Customer:** Hi, I've got a place near Alicante. I keep losing track of how many days I've used in Schengen this year. Can you check for me?
> **[Mon 09:26] BP:** Happy to. The 90/180 rule is a rolling window, not a calendar year, so on any given day we count backward 180 days and add up your Schengen days. Could you list your entry and exit dates for the last 6 months?
> **[Mon 10:02] Customer:** In this year: 12 Jan to 20 Feb, then 3 Apr to 28 Apr. Nothing since.
> **[Mon 10:14] BP:** Counting from today (Mon 12 Jul), the 180-day window opens on 14 Jan. So 14–20 Feb counts as 7 days, plus 3–28 Apr is 26 days. That is 33 days used, 57 remaining as of today. Your Jan entry days before the 14th have already rolled off.
> **[Mon 10:17] Customer:** Brilliant. And if I fly back out 1 Aug?
> **[Mon 10:23] BP:** By 1 Aug the window slides forward, so the Feb block drops off entirely and only the Apr days count. You'd have roughly 64 days free from 1 Aug. Do keep your entry/exit stamps as proof, the border officer does the final count.
> **[Mon 10:25] Customer:** Perfect, thank you.
> **[Mon 10:31] BP:** You're welcome. If you ever want a documented day-by-day ledger you can show at the border, our single-document check is £25 and gives you a dated printout. No charge for the quick count today. Safe travels.

_Elapsed lead → delivery: about 80 minutes, mostly waiting on the customer._

**Customer B — Indian skilled-worker, business trips to Germany**
> **[Tue 14:03] Customer:** I'm on a Skilled Worker visa in the UK. My company sends me to Frankfurt a lot. Does the 90/180 apply to me too?
> **[Tue 14:12] BP:** It does. Your UK visa lets you live and work here, but it gives you no Schengen rights, so for Germany you're on the same 90-in-180 short-stay allowance as any visitor. Want me to tally your trips?
> **[Tue 14:20] Customer:** Sure. 4–8 Mar, 6–10 May, 2–6 Jun, 30 Jun–4 Jul.
> **[Tue 14:31] BP:** Each is 5 days, four trips, 20 days used. Counting back 180 days from today (Tue 13 Jul) all four fall inside the window, so 20 used, 70 remaining. Plenty of headroom.
> **[Tue 14:34] Customer:** Good. I might get sent for a 3-month project in autumn though.
> **[Tue 14:45] BP:** That's the one to watch. A single 90-day stint would use your whole allowance, and any earlier day still inside the window would tip you over. For a stay that long, Germany likely expects a national (long-stay) visa or an ICT permit, not the visa-free short stay. That's an embassy process with its own government fees, separate from anything we'd charge.
> **[Tue 14:47] Customer:** Understood, I'll flag it to HR.
> **[Tue 14:52] BP:** Good plan. When dates firm up, our refusal-risk check is £29 if you want the long-stay route sanity-checked. No charge for today's count.

_Elapsed lead → delivery: under an hour._

**Customer C — Nigerian student, wants to add trips before booking**
> **[Wed 11:40] Customer:** hello, I'm a student in Manchester (Nigerian passport). planning to visit France in December. how do I know I won't overstay?
> **[Wed 11:51] BP:** As long as you hold a valid short-stay Schengen visa for the trip, we just need to make sure the days fit the 90/180 limit. Have you been to any Schengen country in the last 6 months?
> **[Wed 12:15] Customer:** yes, Italy 10–24 Sep with my course.
> **[Wed 12:24] BP:** So 15 days used from Italy. If your France trip is, say, 18–30 Dec, that's 13 days. By December the Sep days may have rolled out of the 180-day window depending on exact dates, but even worst case 15 + 13 = 28 days, well inside 90. You're safe on the day count.
> **[Wed 12:27] Customer:** great. one thing, my current visa was single-entry for Italy.
> **[Wed 12:33] BP:** That's the real blocker, then, not the day count. A single-entry visa was used up when you left Italy, so you'd need a fresh Schengen visa for France. The day maths is fine, the visa itself is what you'd apply for. Embassy/centre fees on that are separate from our service.
> **[Wed 12:35] Customer:** ok that makes sense, thanks for being straight with me.
> **[Wed 12:40] BP:** Anytime. If you'd like your France application pre-checked before submission, the single-document check is £25 per doc. No charge for the calculator.

_Elapsed lead → delivery: about an hour._

**Customer D — Filipino dependant, family of four, tight itinerary**
> **[Thu 16:20] Customer:** Hi we're a family of 4 (I'm on a dependant visa). Did Greece in summer, now planning Austria at Christmas. Want to be sure none of us overstays.
> **[Thu 16:31] BP:** Let's map it. Same entry/exit dates for all four, or did anyone travel separately?
> **[Thu 16:38] Customer:** All together. Greece 5–26 Jul. Austria we're eyeing 20 Dec–2 Jan.
> **[Thu 17:02] BP:** Greece is 22 days. From 20 Dec, counting back 180 days lands around 23 Jun, so the full Greek block still sits inside the window. Austria 20 Dec–2 Jan is 14 days. Running total on your exit day 2 Jan: 22 + 14 = 36 days for each of you, 54 remaining. All four are clear.
> **[Thu 17:05] Customer:** What a relief. The kids are on the same count?
> **[Thu 17:11] BP:** Yes, each traveller has their own 90/180 tally, but since you all move together your numbers are identical. Keep everyone's passport stamps together as evidence.
> **[Thu 17:13] Customer:** Thank you so much.
> **[Thu 17:19] BP:** Pleasure. If you'd like the four applications reviewed as a set before you file, we can bundle a single-document check at £25 each. No charge for the family count today. Happy Christmas travels.

_Elapsed lead → delivery: about an hour._

**Customer E — Egyptian self-employed consultant, recent overstay (edge case)**
> **[Fri 10:05] Customer:** I need to work out my remaining days. I think I might have gone slightly over on my last trip to the Netherlands.
> **[Fri 10:16] BP:** Let's look carefully. Give me your entry and exit dates for every Schengen trip in the last 180 days.
> **[Fri 10:41] Customer:** 1 Feb–15 May Netherlands. Long one, I was setting up a client.
> **[Fri 10:52] BP:** That single stay is 104 days, which is already 14 over the 90-day short-stay limit. I have to be honest with you: that's a recorded overstay, and no calculator can undo it. Your remaining balance right now is zero, and you'll be in a deficit until those days roll off.
> **[Fri 10:55] Customer:** That's bad. What do I do?
> **[Fri 11:08] BP:** Straight answer: don't attempt another Schengen entry until your day count is clearly back under 90, and be aware an overstay can affect future entries and visa applications. This isn't something we can paper over, and I won't sell you a booking service that might walk you into a refusal at the border. Best next step is to sit out the window and, if you have a legitimate reason for long stays, look at a national long-stay visa for the country involved.
> **[Fri 11:10] Customer:** I appreciate the honesty, even if it's not what I hoped.
> **[Fri 11:17] BP:** I'd rather tell you now than after a border refusal. No charge for this, and I won't push a paid service where it wouldn't help. If you later pursue a long-stay visa, our £29 refusal-risk check can flag the overstay disclosure for you. Take care.

_Elapsed lead → delivery: same morning, roughly 70 minutes._

**Customer F — Brazilian ILR holder, back-to-back trips, cutting it fine**
> **[Sat 09:30] Customer:** Morning! I have ILR here. I do a lot of short Schengen hops for photography. Worried I'm close to the limit. Can you run my numbers?
> **[Sat 09:38] BP:** Of course. ILR is your UK status and doesn't change the Schengen rule, so let's count. List every trip in the last 6 months with dates.
> **[Sat 09:52] Customer:** 8–22 Jan, 5–19 Feb, 10–24 Mar, 14–28 Apr, 12–26 May, 9–23 Jun.
> **[Sat 10:14] BP:** Each block is 15 days, six trips. Counting back 180 days from today (Sat 12 Jul) the window opens around 14 Jan, so your 8–13 Jan days have dropped off; that trip counts as 9 days, the other five as 15 each. Total: 9 + 75 = 84 days used, 6 remaining as of today.
> **[Sat 10:17] Customer:** Yikes. So I basically can't travel again this month?
> **[Sat 10:26] BP:** Only 6 days of headroom right now, and it moves daily as old days roll off. If you wait until, say, early August, the Jan and Feb blocks age out and you reopen roughly 30 days. I'd hold off on any July trip. Want me to tell you the exact date your balance crosses back above 14 days?
> **[Sat 10:28] Customer:** Yes please, that would really help.
> **[Sat 10:37] BP:** From your dates, you'll be back above ~30 free days by around 6 Aug, once both the Jan and Feb stays leave the window. Plan new bookings from then. If you'd like a live ledger you can update per trip, our single-document check gives you a dated printout for £25. No charge for today's run.

_Elapsed lead → delivery: about an hour, within Saturday hours._

**Customer G — Pakistani business owner, urgent, deadline impossible (expectation management)**
> **[Sat 15:40] Customer:** URGENT. I need to be in Milan for a trade fair on Wednesday. I have a UK visa but no Schengen visa yet. Can your express service get me the days sorted in time?
> **[Sat 15:47] BP:** I want to help, so let me be precise about what's what. The 90/180 calculator only counts days, it doesn't grant entry. To go to Italy you need a Schengen visa, and a UK visa on its own doesn't cover you.
> **[Sat 15:49] Customer:** So how fast can you get the visa? I'll pay for express.
> **[Sat 15:58] BP:** Here's the honest position. Our express service speeds up our handling and slot-hunting, it cannot speed up the consulate's decision or conjure an appointment that doesn't exist. A short-stay Schengen visa typically needs an in-person biometrics appointment and processing that runs well beyond a few days. For a Wednesday fair, that timeline isn't realistic, and I won't take a booking fee knowing it can't land in time.
> **[Sat 16:01] Customer:** That's frustrating but I understand. Is there any option?
> **[Sat 16:12] BP:** Two honest ones. First, check whether a colleague who already holds a valid Schengen visa can attend. Second, if the fair repeats or you have future EU travel, start a proper application now for the next one, we can hunt the earliest genuine appointment. Any visa is the consulate's decision, never ours to promise.
> **[Sat 16:14] Customer:** Ok. Let's plan for the next trip then.
> **[Sat 16:22] BP:** Sensible. When you have target dates, our express booking is £49 for priority slot-hunting, separate from the embassy and appointment-centre fees. No charge for straightening this out today. I'll be here when you're ready.

_Elapsed lead → delivery: same afternoon, under an hour._

## 5. Status tracker — No charge

> ⏱ First reply ≤ 2 hrs · Delivery Instant (lookup) · Typical span Minutes

**Customer A — British retiree, salaried pension, France (Nice)**
> **[Mon 10:02] Customer:** Hi, I applied for my France short-stay visa last Tuesday through you lot. Any idea where it's at? Ref BP-FR-4471.
> **[Mon 10:19] BP:** Morning David. Pulling it up now. BP-FR-4471 shows: biometrics captured Thu, application forwarded to the France consulate Fri. Status on our side is "with consulate, awaiting decision."
> **[Mon 10:21] Customer:** Ah good. So how long till it's approved?
> **[Mon 10:26] BP:** Honest answer: we can't say it will be approved, and we can't set the timing. The decision and the clock are entirely the consulate's. Typical processing for France short-stay is around 10–15 working days from lodging, but it varies.
> **[Mon 10:27] Customer:** Fair enough. Will you tell me when it moves?
> **[Mon 10:31] BP:** Yes. We poll the centre's tracking and message you the moment the status changes to "ready for collection/return." Nothing needed from you right now.
> **[Mon 10:33] Customer:** Lovely, thanks.
> **[Mon 10:35] BP:** Anytime. If you'd like us to sanity-check your travel dates against the likely return window before you book flights, our single-document/date check is £25 and separate from any consulate fee. No pressure though. Enjoy Nice.

_Elapsed lead → delivery: ~30 minutes._

**Customer B — Indian skilled-worker, salaried, Germany (Munich), business trip**
> **[Tue 09:08] Customer:** Need an urgent update. Germany business visa. Flying next Monday. Ref BP-DE-3120. Is it done??
> **[Tue 09:41] BP:** Hello Arun. Looking now. BP-DE-3120: lodged at the centre Mon last week, status is still "in process at consulate." No decision posted yet.
> **[Tue 09:42] Customer:** That's cutting it close. Can you speed the decision up?
> **[Tue 09:48] BP:** I have to be straight with you: we cannot speed a government decision, and nobody legitimately can. What we CAN do is chase the centre for a status refresh and, where the consulate offers it, request expedited handling on their side. That speeds our end and their queueing, not the outcome or its certainty.
> **[Tue 09:50] Customer:** Please do whatever you can.
> **[Tue 09:57] BP:** Doing it now. I've flagged the reference for a priority status pull and I'll message you the instant it changes. One caution: if there's no decision by Fri, I would not book non-refundable travel for Monday. I'd rather tell you that now than have you lose money.
> **[Tue 09:58] Customer:** Appreciate the honesty. Hold the flights, noted.
> **[Tue 10:03] BP:** Good call. If it helps, our express slot-hunting service (£49, separate from consulate/centre fees) is for finding earlier appointments, not decisions, so it wouldn't help here. I'll just keep tracking. Sit tight.

_Elapsed lead → delivery: ~1 hour (tracking ongoing)._

**Customer C — Nigerian student, dependant status, Netherlands (Amsterdam)**
> **[Wed 11:20] Customer:** hello please check my status, i think something is wrong. i havent heard anything in 3 weeks. ref BP-NL-2087
> **[Wed 11:44] BP:** Hi Chidi. Let me look. BP-NL-2087: this one shows "additional documents requested by consulate" flagged 4 days ago. That's likely why it's quiet. Did you receive an email from the centre?
> **[Wed 11:46] Customer:** no nothing. what documents??
> **[Wed 11:52] BP:** The centre note lists proof of accommodation for the full stay. Please check spam and the portal inbox. This is a request from the consulate, not a refusal. Your file simply pauses until they receive it.
> **[Wed 11:54] Customer:** ok found it in spam!! so if i send it will i get approved?
> **[Wed 11:59] BP:** Sending it keeps your application alive and moving, which is the right step. But I can't promise approval. The consulate makes that call once they review the complete file. Get the accommodation proof in as soon as you can.
> **[Wed 12:03] Customer:** ok uploading now. thank you so much
> **[Wed 12:07] BP:** Well done. Once it's uploaded the status should flip back to "in process" within a day or two and I'll keep watching it. If you want a second pair of eyes on the accommodation doc before you submit, our single-document check is £25 and separate from any centre fee. Optional.

_Elapsed lead → delivery: ~45 minutes._

**Customer D — Pakistani self-employed trader, Italy (Milan), overstay history (edge case: manage expectations)**
> **[Thu 14:10] Customer:** Salaam. Checking my Italy visa status, ref BP-IT-1904. Also I want to know my chances honestly.
> **[Thu 14:33] BP:** Wa alaikum salaam, Bilal. Status first: BP-IT-1904 is "with consulate, awaiting decision," lodged 6 working days ago. Nothing has changed today.
> **[Thu 14:35] Customer:** Ok. On chances, I should tell you I overstayed a Schengen visa in 2023 by about 5 weeks. Left voluntarily. Will that block me?
> **[Thu 14:44] BP:** Thank you for being upfront, that genuinely helps. I won't sugar-coat it: a recent overstay is a factor the consulate weighs, and it can lead to refusal or extra scrutiny. I can't predict their decision either way. What I can say is your honesty and the voluntary departure are the right footing.
> **[Thu 14:46] Customer:** So maybe I wasted the fees?
> **[Thu 14:52] BP:** The application is already lodged, so the consulate fee is spent regardless of us. Our status tracking is at no charge. I'd manage expectations rather than raise hopes. If it's refused, you'll get written reasons, and that shapes any future application.
> **[Thu 14:54] Customer:** Understood. I prefer the truth.
> **[Thu 14:59] BP:** That's the right mindset. If a refusal does land, our refusal-risk review (£29, separate from consulate fees) reads the reasons and tells you honestly whether reapplying is worth it or not. For now I'll just track and message you on any change.

_Elapsed lead → delivery: ~50 minutes._

**Customer E — Filipino nurse, ILR, Spain (Barcelona), family group of four**
> **[Fri 09:15] Customer:** Hi! Checking on our family Spain visas, four of us. Ref BP-ES-5560. My kids' ones especially.
> **[Fri 09:33] BP:** Morning Grace. BP-ES-5560 covers all four applicants. Current status: two shown as "ready for return" (yours and your husband's), and the two children's are still "in process at consulate." Same lodge date, they sometimes clear in batches.
> **[Fri 09:35] Customer:** Oh no why are the kids behind? Is something wrong with theirs?
> **[Fri 09:41] BP:** Nothing in the notes suggests a problem. It's common for minors' files to be checked separately and take a couple of extra days. No document request is flagged against them. I'd read it as normal batching, not a red flag.
> **[Fri 09:43] Customer:** Ok phew. Should we wait for all four before collecting?
> **[Fri 09:49] BP:** Yes, I'd collect all four passports together in one trip once the children's flip to "ready." I'll ping you the moment they do. And to be clear, I can't guarantee the outcome on any of the four until the passports are physically returned with the decision inside.
> **[Fri 09:51] Customer:** Perfect, thank you so much!
> **[Fri 09:54] BP:** Pleasure. No charge for the tracking. If you'd like me to confirm your travel-date buffer before you book four sets of flights, the date check is £25 and separate from consulate fees. Only if useful.

_Elapsed lead → delivery: ~40 minutes._

**Customer F — Egyptian PhD student, student visa, Austria (Vienna), conference deadline (edge case: service not needed)**
> **[Sat 10:40] Customer:** Hello, I'm tracking an Austria visa. But actually I applied directly at the centre myself, not through you. Can you still check the status? Ref is the centre's, not a BP one.
> **[Sat 11:05] BP:** Hi Karim. Honest answer: our tracker only pulls files we lodged, so I can't look up a reference that isn't in our system. I don't want to charge you or waste your time on something we can't actually do.
> **[Sat 11:07] Customer:** Ah. So how do I check mine?
> **[Sat 11:14] BP:** You can track it directly on the visa centre's own portal using the reference and passport number on your receipt, at no charge. That's the same source we'd be reading from anyway. No middleman needed for a status you already have access to.
> **[Sat 11:16] Customer:** That's really helpful, thank you for not upselling me.
> **[Sat 11:19] BP:** Of course. One thing worth flagging: if your conference date is tight and the portal says still "in process," we can't and shouldn't promise the consulate will decide in time. Only they control that.
> **[Sat 11:21] Customer:** Noted. If it gets refused close to the date, could you help?
> **[Sat 11:25] BP:** Then yes, we could. If a refusal lands, our refusal-risk review is £29 (separate from any centre or consulate fee) and tells you honestly whether a fast reapplication is realistic before your conference. Until then, use the centre portal and save your money.

_Elapsed lead → delivery: ~45 minutes (no lookup performed — referred out)._

**Customer G — Ghanaian entrepreneur, self-employed, Belgium (Brussels), express slot booked**
> **[Mon 16:30] Customer:** Evening. I paid for your express service and got an early Brussels appointment last week. Now I want to track the actual visa. Ref BP-BE-3345.
> **[Mon 16:52] BP:** Good evening, Kwame. BP-BE-3345: biometrics done at the express slot Wed, file forwarded to the Belgium consulate Thu, status now "awaiting decision." So your file is in, on time.
> **[Mon 16:54] Customer:** Great. The express got me in fast, so the visa should come fast too right?
> **[Mon 16:59] BP:** I want to be precise here. Express got you an earlier appointment, which is what it's for. It does not speed or guarantee the consulate's decision. Those are two separate things. Belgium short-stay typically runs about 10–15 working days from lodging, but the consulate owns that timing.
> **[Mon 17:01] Customer:** Understood. So we just wait.
> **[Mon 17:05] BP:** Exactly. I'll monitor and message you the second it changes to "ready for return." Nothing for you to do.
> **[Mon 17:07] Customer:** Perfect. Thanks for being clear.
> **[Mon 17:10] BP:** Anytime. Tracking is at no charge. If you'd like your supporting-business documents reviewed before any future trips, the single-document check is £25 and separate from consulate fees. For now, sit back.

_Elapsed lead → delivery: ~40 minutes._

## 6. Find nearest centre — No charge

> ⏱ First reply ≤ 2 hrs · Delivery Instant (answer in chat) · Typical span Minutes

**Customer A — British retiree, salaried background, planning France trip from Cornwall**
> **[Mon 09:14] Customer:** Hi, I need to give fingerprints for a France visa. Where's my nearest place? I'm in Truro.
> **[Mon 09:31] BP:** Morning! France uses TLScontact for Schengen visa applications in the UK. From Truro your closest options are the London (Wandsworth) centre, or Manchester if you'd rather head north. There isn't one in the South West, so most Cornwall applicants use London.
> **[Mon 09:40] Customer:** London it is then. Do I pay you for this?
> **[Mon 09:44] BP:** No charge for finding the centre, that's just guidance. Two things to keep straight: the appointment-centre service fee and the French visa fee are set by them and paid to them, separate from anything we'd ever charge.
> **[Mon 09:47] Customer:** Understood. Thanks.
> **[Mon 09:52] BP:** You're welcome. When you're ready to book, our express slot-hunting (£49) watches for earlier London openings, that speeds our searching, not the centre's actual availability. Enjoy France!

_Elapsed lead → delivery: ~40 minutes._

**Customer B — Indian skilled-worker in Leeds, salaried, Germany business trip, tight-ish**
> **[Tue 14:02] Customer:** Need nearest German visa centre. I'm in Leeds, travelling in about 5 weeks for work.
> **[Tue 14:20] BP:** Hi! Germany runs UK Schengen appointments through VFS Global. Your nearest is the Manchester VFS centre, roughly an hour from Leeds. There's also Edinburgh and London if those suit better.
> **[Tue 14:25] Customer:** Manchester works. Is 5 weeks enough?
> **[Tue 14:38] BP:** Often yes for a business trip, but it depends on live slot availability at Manchester and the consulate's own processing time, which we don't control. Book your appointment as soon as you have your invitation letter and documents ready.
> **[Tue 14:41] Customer:** Will do. What do you charge for the centre info?
> **[Tue 14:46] BP:** No charge for this. The VFS service fee and the German visa fee are separate and paid to VFS, not us. If the Manchester calendar looks empty, our express slot-hunting (£49) can watch for earlier openings, but I can't promise a slot exists.
> **[Tue 14:49] Customer:** Great, thanks.

_Elapsed lead → delivery: ~45 minutes._

**Customer C — Nigerian student in Cardiff, Netherlands short trip, on a budget**
> **[Wed 10:05] Customer:** hello where do i go for netherlands visa biometrics? im a student in cardiff
> **[Wed 10:22] BP:** Hi! The Netherlands uses VFS Global for UK Schengen applications. There's no centre in Wales, so from Cardiff the nearest is the London VFS centre. Some applicants use Manchester instead depending on train routes.
> **[Wed 10:28] Customer:** ok london. is there any charge from you? im a student, watching money
> **[Wed 10:33] BP:** No charge from us for this answer. Fair warning so nothing surprises you: the VFS service fee and the Dutch visa fee are set by them and paid separately, and student applicants sometimes qualify for reduced visa fees, worth checking on the consulate page.
> **[Wed 10:37] Customer:** thats really helpful thank you
> **[Wed 10:41] BP:** Anytime. If you'd like a second pair of eyes on your bank statements and enrolment letter before you go, our single-document check is £25, but plenty of students file fine on their own. Good luck!

_Elapsed lead → delivery: ~35 minutes._

**Customer D — Pakistani ILR holder in Birmingham, self-employed, Spain family trip**
> **[Thu 16:40] Customer:** Salaam. Nearest Spanish visa centre to Birmingham please. Family of four, I'm self-employed.
> **[Thu 16:58] BP:** Wa alaikum salaam! Spain uses BLS International for UK Schengen visas. Your nearest is the Birmingham BLS centre, so you're in luck, no long trip needed. London and Manchester also have BLS centres.
> **[Thu 17:03] Customer:** Perfect, local. All four of us book together?
> **[Thu 17:11] BP:** Each applicant needs their own appointment and biometrics, including children in most cases, though under-12s are often exempt from fingerprints. Book the four slots together at Birmingham BLS if you can.
> **[Thu 17:15] Customer:** Noted. And your fee for this?
> **[Thu 17:20] BP:** No charge for the centre info. The BLS service fee and the Spanish visa fees are per person, set by them and paid to them, separate from us. Self-employed files sometimes get extra scrutiny on income proof, so if you'd like our refusal-risk check (£29) before booking four fees, it can flag gaps early.
> **[Thu 17:24] Customer:** That's sensible, I'll think about it. Shukriya.

_Elapsed lead → delivery: ~45 minutes._

**Customer E — Filipino dependant in Aberdeen, Italy trip, EDGE CASE: no centre nearby + can't help beyond info**
> **[Fri 11:10] Customer:** Hi po, nearest Italy visa centre? I'm in Aberdeen, my husband is on a work visa here.
> **[Fri 11:29] BP:** Hi! Italy uses iVisa/VFS for UK Schengen applications, and the relevant centres are in London and Edinburgh. From Aberdeen, Edinburgh is your closest by a good margin.
> **[Fri 11:34] Customer:** Edinburgh ok. But actually I heard as a dependant of a work visa I might not even need a Schengen visa?
> **[Fri 11:47] BP:** Good question, and honestly this is where I'd rather be straight with you than book you anything. Whether you need a Schengen visa depends on your own nationality and passport, not your husband's UK visa. Some nationalities travel visa-free short-stay, others don't. Philippines passport holders currently do need a Schengen visa for Italy, so Edinburgh VFS is right for you.
> **[Fri 11:52] Customer:** Ah ok so I do need it. Salamat po. Do I pay you?
> **[Fri 11:56] BP:** No charge for this. The VFS service fee and Italian visa fee are separate and paid to them. Book Edinburgh once your documents are ready. If your paperwork feels uncertain, our single-document check is £25, but no pressure at all.

_Elapsed lead → delivery: ~46 minutes._

**Customer F — Egyptian self-employed in London, Greece trip, EDGE CASE: deadline impossible, we manage expectations**
> **[Sat 09:03] Customer:** Nearest Greece visa centre in London? I fly Thursday. Urgent!!
> **[Sat 09:12] BP:** Morning! Greece uses the Greek visa centre operated via Global/VFS in London, so you're already in the right city. But I have to be honest with you about the Thursday flight.
> **[Sat 09:14] Customer:** What's wrong?
> **[Sat 09:22] BP:** A Schengen visa can't realistically be applied for, biometrics given, and decided by the consulate in under a week from a standing start. Even our express service only speeds our slot-hunting and handling, it can't speed the consulate's decision or make same-week slots exist. I don't want you paying fees for a trip that can't be documented in time.
> **[Sat 09:25] Customer:** So I should not book Thursday?
> **[Sat 09:31] BP:** I'd rebook your travel to give at least 3 to 4 weeks of runway, then apply properly at the London centre. That's the honest call. When you have a realistic date, come back and I'll help you find the fastest available slot at no charge.
> **[Sat 09:34] Customer:** Appreciate the honesty. Will rebook.
> **[Sat 09:38] BP:** Sensible move. No fees wasted, and you'll travel with a valid visa. Talk soon.

_Elapsed lead → delivery: ~35 minutes._

**Customer G — Brazilian citizen visiting UK, Portugal side-trip, EDGE CASE: service not needed, no visa required**
> **[Mon 13:20] Customer:** Oi! I'm Brazilian, staying in Manchester a few weeks, want to pop to Portugal. Where's the nearest visa centre?
> **[Mon 13:42] BP:** Hi! Before I send you anywhere, let me save you a trip. Brazilian passport holders don't need a Schengen visa for short stays, up to 90 days in any 180 for tourism. So for a short Portugal visit you generally won't need to visit a visa centre at all.
> **[Mon 13:47] Customer:** Really? So no appointment needed?
> **[Mon 13:53] BP:** Correct, for a short tourist trip on a Brazilian passport. Just carry your passport, proof of onward/return travel and accommodation, and be ready to show funds at the border. Do double-check the 90/180 day count if you've had recent Schengen visits, since your UK time doesn't count but prior Schengen days do.
> **[Mon 13:57] Customer:** Amazing, that saves me a lot. Obrigado!
> **[Mon 14:01] BP:** You're welcome, no charge and no centre needed. Enjoy Portugal! If you ever plan a longer stay that does need a visa, we're here.

_Elapsed lead → delivery: ~40 minutes._

## 7. Refusal-risk check — £29

> ⏱ First reply ≤ 2 hrs · Delivery ≤ 24 hrs · Typical span ~1 business day

**Customer A — British citizen, salaried, first Schengen trip to France**
> **[Mon 09:14] Customer:** Hi, flying to Paris in 3 weeks for a friend's wedding. First time applying for a Schengen visa and I'm nervous I'll get refused. Can you check my chances?
> **[Mon 09:41] BP:** Morning, and congrats on the wedding invite. Quick thing first: you said British citizen? If you hold a full UK passport you don't need a Schengen visa at all for a short trip to France. You'd enter visa-free (soon under ETIAS, but that's a travel authorisation, not a visa).
> **[Mon 09:48] Customer:** Oh. I'm British but I was born in India, I assumed I'd need one. So I really don't apply for anything?
> **[Mon 09:52] BP:** Correct, if your current passport is a British Citizen passport. Nationality of birth doesn't matter, the passport does. Can you confirm the top line says "British Citizen"?
> **[Mon 10:15] Customer:** Yes it says British Citizen.
> **[Mon 10:19] BP:** Then you're all set, no visa and no refusal risk to check. I won't take £29 off you for something you don't need. Carry the passport (6+ months validity), wedding invite and return ticket, that's it.
> **[Mon 10:22] Customer:** That's honest of you, thank you. Bookmarking you for my mother-in-law's trip, she's on an Indian passport.
> **[Mon 10:24] BP:** Please do, that one we genuinely can help with. Enjoy Paris.
_Elapsed lead → delivery: ~1 hr 10 min (assessment only, no charge)._

**Customer B — Indian national, skilled worker in the UK, Italy tourism**
> **[Tue 11:02] Customer:** Hello. Indian passport, Skilled Worker visa in the UK. Planning 8 days in Italy in September. A colleague got refused last year and it's put me off. Can you do a refusal-risk check before I apply?
> **[Tue 11:20] BP:** Yes, this is exactly what our £29 refusal-risk check is for. I review your profile against the common Italy refusal grounds (funds, ties, itinerary, insurance, prior travel) and give you a written risk rating plus fixes. To be clear, £29 is our assessment fee only; it's separate from the Italy visa fee and the appointment-centre fee you'll pay later.
> **[Tue 11:26] Customer:** Understood. What do you need from me?
> **[Tue 11:31] BP:** Six things: passport bio page, UK BRP/eVisa share code, last 3 months bank statements, employment letter with salary, your rough itinerary/hotel plan, and how many days off HR approved. Photos are fine.
> **[Tue 14:05] Customer:** [file] [file] [file] Sent passport, share code and 3 months statements. Employer letter comes tomorrow.
> **[Tue 14:22] BP:** Got them. Balance and salary credits look healthy, that's the biggest single factor and it's in good shape. I'll hold for the employer letter to confirm your leave and role, then finalise.
> **[Wed 09:40] Customer:** [file] Employer letter attached.
> **[Wed 09:58] BP:** Perfect. Here's the payment link for the £29 check: [link]. Once it clears I'll send the full written assessment within 24 hrs, usually much sooner.
> **[Wed 10:12] Customer:** Paid.
> **[Wed 10:14] BP:** Received, thank you. Working on it now.
> **[Wed 13:36] BP:** [file] Your refusal-risk report is attached. Overall rating: LOW risk. Two tidy-ups: (1) add a covering letter naming your return-to-UK ties (job, tenancy), (2) your travel insurance must show €30,000 medical cover, the draft you mentioned only had €15,000. Fix those two and your file is strong. Remember approval is always the Italian consulate's call, but nothing here should trip you up.
> **[Wed 13:52] Customer:** Really clear, thank you. Can you also book the appointment slot for me?
> **[Wed 13:55] BP:** We can, that's a separate service. I'll send details, no pressure.
_Elapsed lead → delivery: ~1 business day (paid Wed 10:12, delivered Wed 13:36)._

**Customer C — Nigerian national, self-employed, Germany business trip, urgent**
> **[Wed 16:40] Customer:** Need urgent help. Nigerian passport, live in UK on ILR. Germany business conference, I fly in 9 days. Self-employed. Worried my accounts look messy. Refusal check possible today??
> **[Wed 16:58] BP:** We can turn a refusal-risk check around within 24 hrs, so you'd have it tomorrow, well inside your 9 days. Note: our £29 check is separate from the German visa fee and the appointment-centre fee. One honesty point up front, we assess and de-risk your file, we can't speed up or guarantee the consulate's decision or a slot.
> **[Wed 17:10] Customer:** Understood, just want to know where I stand. Sending docs now. [file] [file]
> **[Wed 17:14] BP:** That's passport and ILR. For self-employed I also need: 6 months business bank statements, latest SA302 or tax return, the conference invite/registration, and proof of who's paying (you or the company).
> **[Wed 17:40] Customer:** [file] [file] Statements and conference invite in. Tax return I'll dig out tonight.
> **[Thu 09:12] Customer:** [file] SA302 attached.
> **[Thu 09:30] BP:** Thanks. Honest read before you pay: your income is irregular month to month, which German consulates scrutinise. It's workable but you'll need a strong covering letter and possibly a sponsor letter from the conference organiser. Still want the full written check at £29? It'll spell out exactly what to add.
> **[Thu 09:36] Customer:** Yes please, that's the reassurance I need. [link] paid.
> **[Thu 09:38] BP:** Received. Prioritising it given your timeline.
> **[Thu 12:20] BP:** [file] Report attached. Rating: MEDIUM risk, mainly the irregular income. Three actions: (1) covering letter explaining income pattern with annual total, (2) request a letter from the conference confirming your role and that fees are paid, (3) show a clear closing balance covering the trip plus buffer. Do those and MEDIUM drops toward LOW. Decision stays with Germany, but this is a much safer file.
> **[Thu 12:41] Customer:** Brilliant, exactly what I needed. Thank you.
_Elapsed lead → delivery: ~1 business day (paid Thu 09:36, delivered Thu 12:20)._

**Customer D — Pakistani national, UK student, Spain half-term trip, recent refusal**
> **[Thu 15:05] Customer:** Hi. Pakistani passport, student in the UK. I got REFUSED for a Spain visa 5 months ago (they said insufficient proof of funds). Want to reapply for the Easter break. Can your check tell me if I'll pass this time?
> **[Thu 15:26] BP:** Sorry to hear about the refusal, and thanks for being upfront, that history genuinely helps. Straight answer: no check can tell you if you'll "pass", the decision is always the Spanish consulate's. What our £29 check does is compare your new file against the exact reason you were refused and flag whether you've actually fixed it. Do you still have the refusal letter?
> **[Thu 15:34] Customer:** Yes I have it. [file]
> **[Thu 15:49] BP:** Thank you. This says funds were insufficient and ties not demonstrated. Key question: since then, what's changed on the money side? A refusal on the same ground with the same evidence usually gets refused again, so I want to be sure there's something new before you spend anything.
> **[Thu 16:05] Customer:** My father now sends a fixed monthly allowance and I have 4 months of statements showing it, plus a sponsorship letter from him.
> **[Thu 16:12] BP:** That's a real change, so a fresh check is worthwhile rather than a waste. Send: passport, UK student visa/share code, your 4 months statements, father's sponsorship letter, his proof of funds, and your enrolment letter. £29 is our fee only, separate from the Spanish visa and centre fees.
> **[Thu 16:40] Customer:** [file] [file] [file] All attached. Enrolment letter tomorrow morning.
> **[Fri 09:15] Customer:** [file] Enrolment letter.
> **[Fri 09:33] BP:** Got everything. Here's the link: [link].
> **[Fri 09:52] Customer:** Paid.
> **[Fri 13:10] BP:** [file] Report attached. Rating: MEDIUM, improved from your last attempt. The new allowance evidence directly addresses the refusal ground, that's the important part. Two must-fixes: (1) father's sponsorship letter needs his relationship, income source and a line accepting financial responsibility, yours is missing the last part; (2) add a short letter addressing the previous refusal head-on and what's changed. Reapplying with no acknowledgement of the prior refusal is a common trip-up. Decision remains Spain's, but you're in a materially stronger position than before.
> **[Fri 13:29] Customer:** This is so helpful, I had no idea I should mention the old refusal. Thank you.
_Elapsed lead → delivery: ~1 business day (paid Fri 09:52, delivered Fri 13:10)._

**Customer E — Filipino national, UK dependant visa, Netherlands trip, deadline too tight**
> **[Fri 15:40] Customer:** Hello! Filipino passport, UK dependant visa (husband is a skilled worker). We want to visit Amsterdam. I fly on Tuesday. Can you check my refusal risk today so I can apply Monday?
> **[Fri 15:58] BP:** Thanks for the details. I have to be straight with you before anything else: flying Tuesday and applying Monday isn't realistic for a Netherlands short-stay visa. After you submit at the appointment centre, processing typically takes around 15 calendar days, and you can't legally travel until it's decided and issued. A refusal-risk check won't change that timeline.
> **[Fri 16:10] Customer:** Oh no. So even a rush won't work?
> **[Fri 16:16] BP:** Not for Tuesday, no. "Express" on our side only speeds our own handling, like slot-hunting and file prep, it can't speed the consulate's decision or conjure an earlier appointment. I won't take £29 for a check when the trip can't legally happen on these dates.
> **[Fri 16:24] Customer:** That's disappointing but I appreciate you not just taking my money. What if we move the trip?
> **[Fri 16:31] BP:** That's the right move. Pick dates 4 to 6 weeks out, then a refusal-risk check makes real sense and we'd have time to fix anything it flags. Message me once you've rebooked and I'll prioritise you.
> **[Fri 16:38] Customer:** Will do. We'll rebook for next month. Thank you for being honest.
> **[Mon 09:05] BP:** Morning, following up, hope the rebooking went smoothly. Whenever you have new dates, I'm here.
_Elapsed lead → delivery: no charge (trip not viable on stated dates; advised to rebook)._

**Customer F — Egyptian national, UK ILR, retired, family group to Greece**
> **[Mon 10:20] Customer:** Good morning. Egyptian passport, ILR in the UK, retired. Travelling to Greece with my wife and adult son (he's on a work visa here) in October. Can one refusal-risk check cover all three of us?
> **[Mon 10:44] BP:** Morning. A single £29 check covers one applicant's file. For a family group I'd suggest a check on each traveller since your circumstances differ, retired with pension/savings, your wife likely as your dependant, and your son as a separate working applicant. I can do all three at £29 each, or just the two you're most unsure about. Fees are our assessment only, separate from the Greek visa and centre fees per person.
> **[Mon 10:52] Customer:** Let's do all three to be safe. What do you need?
> **[Mon 10:58] BP:** For you: passport, ILR, pension statements and 3 months bank statements, plus proof of savings. For your wife: passport, ILR, and a note that you're funding her (she can be shown as dependent on your funds). For your son: passport, UK work visa/share code, 3 months statements, employment letter. Plus your shared itinerary and hotel booking.
> **[Mon 14:30] Customer:** [file] [file] [file] Mine and my wife's documents attached, plus our hotel booking.
> **[Tue 09:15] Customer:** [file] [file] My son's documents now attached.
> **[Tue 09:40] BP:** All received for three applicants. Total is £87 (3 × £29). Payment link: [link]. Full written reports for each within 24 hrs of payment.
> **[Tue 10:02] Customer:** Paid £87.
> **[Tue 10:05] BP:** Received, thank you. Working through all three.
> **[Tue 15:50] BP:** [file] [file] [file] Three reports attached. You: LOW, pension plus savings is exactly what Greece likes to see. Your wife: LOW, as long as your covering letter clearly states you cover her costs, which I've templated. Your son: LOW-MEDIUM, his statements are fine but the employment letter doesn't state his approved leave dates, add that. As always the consulate decides, but this is a clean family file.
> **[Tue 16:12] Customer:** Wonderful, thank you for the care. We'll fix my son's letter.
_Elapsed lead → delivery: ~1 business day (paid Tue 10:02, delivered Tue 15:50)._

**Customer G — Brazilian national, UK Global Talent visa, self-employed, Portugal, prior overstay**
> **[Wed 11:10] Customer:** Hi there. Brazilian passport, UK Global Talent visa. I want a refusal-risk check for a 2-week Portugal trip in November. One thing I should mention: I overstayed a Schengen visa by about 3 weeks back in 2021. Does that ruin my chances?
> **[Wed 11:34] BP:** Thanks for disclosing that up front, it's the single most important detail and hiding it would be far worse. Honest position: a prior Schengen overstay is a serious refusal factor and it can also carry an entry-ban period recorded in the Schengen system. Before you pay anything, I need to know whether a ban was ever imposed. Do you have any paperwork from when you exited in 2021, or were you flagged at the border?
> **[Wed 11:50] Customer:** I wasn't stopped at the border when I left, and I never received any ban letter that I know of.
> **[Wed 11:58] BP:** That's a reasonably good sign, but "not that I know of" isn't confirmation. A refusal-risk check is worth doing here because the overstay must be addressed directly in your file, but I want to set expectations plainly: this history raises your risk regardless of how strong the rest looks, and the Portuguese consulate makes the final call. If you're comfortable proceeding on that basis, I'll take it on.
> **[Wed 12:06] Customer:** Yes, I'd rather know exactly how to handle it than walk in blind.
> **[Wed 12:14] BP:** Good. Send: passport, UK Global Talent visa/share code, 6 months business bank statements, proof of self-employed income, itinerary, and anything at all from the 2021 trip (entry/exit stamps, old boarding passes). £29 is our fee only, separate from the Portuguese visa and centre fees.
> **[Wed 15:20] Customer:** [file] [file] [file] Passport, share code, statements and itinerary in. I found my 2021 boarding passes too. [file]
> **[Thu 09:25] BP:** Thanks, the old boarding passes are genuinely useful, they help evidence when you actually left. Payment link for the check: [link].
> **[Thu 09:44] Customer:** Paid.
> **[Thu 09:47] BP:** Received. This one needs care, I'll have it to you within 24 hrs.
> **[Thu 16:30] BP:** [file] Report attached. Rating: HIGH risk, driven entirely by the 2021 overstay, not your finances (which are strong). Key actions: (1) a frank written explanation of the overstay, its cause and that you left voluntarily, with the boarding passes as proof of exit date; (2) evidence of clean travel since; (3) strong UK ties via your Global Talent status. I've also flagged that you may want to verify with the Portuguese consulate whether any alert exists, before applying. I won't sugar-coat it: this is approvable but far from guaranteed, and the decision is solely Portugal's. What you can control is putting the most transparent file possible in front of them, which this now does.
> **[Thu 17:02] Customer:** That's exactly the honesty I wanted. Thank you for taking it seriously instead of just reassuring me.
_Elapsed lead → delivery: ~1 business day (paid Thu 09:44, delivered Thu 16:30)._

## 8. Refusal letter decode — £39

> ⏱ First reply ≤ 2 hrs · Delivery Same day – 24 hrs · Typical span Same day – 1 day

**Customer A — British citizen, salaried, France refusal (2.1 / missing intent to leave)**
> **[Mon 10:12] Customer:** Hi, my France visa got refused last week and honestly the letter makes no sense to me. Can you explain what it actually means?
> **[Mon 10:41] BP:** Hi, yes that's exactly what our Refusal letter decode is for. We read the consulate's letter line by line and tell you in plain English which grounds they ticked, what evidence gap it points to, and what a stronger reapplication would need. It does not overturn the decision, that stays with the consulate, but it removes the guesswork. Could you send a photo of the full letter, both sides?
> **[Mon 10:58] Customer:** [photo] here it is. There are three little boxes ticked.
> **[Mon 11:20] BP:** Got it, thank you. I can see three standard grounds marked. Before I go deep, the fee for the decode is £39, and that is our service fee only. Any later reapplication has a separate embassy visa fee and appointment-centre fee which are not ours. Happy to proceed?
> **[Mon 11:34] Customer:** Yes go ahead.
> **[Mon 11:36] BP:** Here is the secure link: [link]. Once it clears I'll write up the decode.
> **[Mon 12:05] Customer:** Paid.
> **[Mon 12:07] BP:** Received, thank you. Working on it now, you'll have the write-up today.
> **[Mon 15:48] BP:** [file] Decode ready. Summary: the main ground is "your intention to leave before the visa expires could not be established", plus weak proof of funds and unclear trip purpose. In plain terms the consulate was not convinced you would return to the UK. I've listed the exact anchors that usually address this: employer letter with return date, recent payslips, and a clearer day-by-day itinerary. Nothing here is a guarantee, but it targets the ground they raised.
> **[Mon 16:22] Customer:** This is so much clearer than the letter itself. Thank you.
> **[Mon 16:25] BP:** Glad it helped. If you'd like, we also offer a document-check on the reapplication set, quoted separately. No pressure, and take your time before you reapply.

_Elapsed lead → delivery: same day (about 6 hours)._

**Customer B — Indian, skilled-worker visa, Germany refusal, urgent conference**
> **[Tue 09:03] Customer:** Refused for Germany. I have a work conference in 12 days and I'm panicking. What went wrong?
> **[Tue 09:35] BP:** Sorry to hear that, let's find out. Please send a clear photo of the refusal letter and the code or boxes ticked. I'll decode the actual grounds so you know what to fix rather than guessing.
> **[Tue 09:44] Customer:** [photo]
> **[Tue 10:12] BP:** Thanks. The letter cites two grounds: purpose/conditions of stay not sufficiently justified, and doubts over means of subsistence. So they weren't satisfied the conference attendance and your funding were clearly evidenced. The decode fee is £39, our service fee only, separate from any new embassy or appointment-centre fees. One honest note before you pay: even our express handling speeds our work and slot-hunting, it cannot speed the consulate's decision or guarantee a slot inside 12 days. Still want the decode?
> **[Tue 10:20] Customer:** Yes, I still need to understand it. Here's payment coming.
> **[Tue 10:23] BP:** Link: [link].
> **[Tue 10:39] Customer:** Done.
> **[Tue 10:41] BP:** Received. Because of your deadline I'll prioritise this and aim for a few hours.
> **[Tue 13:52] BP:** [file] Decode done. Key fix for ground one: an official conference invitation naming you, plus a letter from your UK employer confirming sponsorship of the trip and your role. For ground two: bank statements showing the funds were already present, not a recent lump deposit. Realistic view on timing: a fresh appointment plus decision inside 12 days is tight and outside anyone's control, so I'd also prepare a plan B for attending remotely. I won't pretend the date is safe.
> **[Tue 14:20] Customer:** Appreciate the straight talk. Better than false hope.
> **[Tue 14:24] BP:** That's the job. If you decide to reapply, we can prep the set, quoted separately.

_Elapsed lead → delivery: same day (about 3.5 hours)._

**Customer C — Nigerian, self-employed, Italy refusal, funds ground**
> **[Wed 11:40] Customer:** good afternoon. my Italy tourist visa was refused and the reason part is very short and confusing. can you help me read it?
> **[Wed 12:38] BP:** Good afternoon, yes we can. Our Refusal letter decode reads the letter and explains each ground plainly, plus what evidence it points to. Please send a photo of the full letter. As you're self-employed, the funds and business-proof section is often where these hinge, so I'll pay close attention there.
> **[Wed 13:15] Customer:** [photo] this is all they sent.
> **[Wed 13:44] BP:** Thank you. Two grounds are marked: justification of purpose and conditions of the intended stay were not provided, and the information submitted regarding justification for the purpose and conditions was not reliable. In plain terms they questioned both your trip rationale and the credibility of your funds documents. Fee is £39, our service fee only, separate from any future embassy or centre fees. Shall I proceed?
> **[Wed 14:20] Customer:** yes please. how do i pay?
> **[Wed 14:22] BP:** Secure link here: [link].
> **[Wed 15:30] Customer:** sent it now.
> **[Wed 15:33] BP:** Received, thank you. I'll have your decode by tomorrow morning at the latest, likely sooner.
> **[Thu 09:26] BP:** [file] Decode ready. The "not reliable" wording is the important one: it usually flags funds that look inconsistent, for example large recent deposits with no matching income trail. For a self-employed applicant the stronger set is 6 months of business account statements, tax or registration documents, and a letter explaining your income pattern. Purpose ground wants a firmer itinerary and accommodation proof. This targets what they raised, though the decision always remains theirs.
> **[Thu 10:05] Customer:** now i understand the reliability part. thank you very much.
> **[Thu 10:09] BP:** You're welcome. Take your time rebuilding the funds trail before reapplying, that's the piece that matters most here.

_Elapsed lead → delivery: overnight (about 18 hours)._

**Customer D — Filipino dependant, Spain refusal, but issue is a simple admin error (manage expectations / minimal help)**
> **[Thu 16:20] Customer:** Hello po. My Spain visa was refused. My husband is on a UK work visa and I'm his dependant. The letter mentions something about the application being inadmissible?
> **[Thu 17:02] BP:** Hello, thanks for reaching out. "Inadmissible" is different from a normal refusal, it often means the file was not formally examined at all, usually a procedural issue like a missing signature, wrong visa type, fee not paid, or applying too early. Could you send a photo of the letter so I can confirm which it is?
> **[Thu 17:30] Customer:** [photo]
> **[Fri 09:14] BP:** Thanks for waiting. I can confirm this is an inadmissibility notice, not a refusal on the merits. The reason marked is that the application was lodged more than 6 months before the trip, which they don't accept. Honestly, this doesn't need a full paid decode. You simply reapply within the correct window with the same documents, no new evidence battle required. I'd rather tell you that than take £39 for a one-line answer.
> **[Fri 09:41] Customer:** Oh! So I just apply again closer to the date? That's a relief. Thank you for being honest po.
> **[Fri 09:44] BP:** Exactly that. Book the appointment so it falls inside 6 months of travel and resubmit. No charge for this, it wouldn't be fair. If a genuine refusal ever comes with real grounds, that's when the decode earns its keep.
> **[Fri 09:52] Customer:** Thank you so much, I'll come back if needed.

_Elapsed lead → delivery: overnight (about 17 hours), no charge._

**Customer E — Pakistani student, Netherlands refusal, previous overstay flagged**
> **[Fri 13:05] Customer:** Hi. Netherlands refused my visa. The letter says something about a previous stay and being a risk. I did overstay in another country in 2023 by a few weeks. Can you decode it?
> **[Fri 13:48] BP:** Hi, yes I can decode exactly what they've written, and I'll be candid about what it means. Please send a photo of the full letter. I'll flag upfront: where a prior overstay is on record, it's one of the harder grounds to move, so I want to set honest expectations before you pay anything.
> **[Fri 14:10] Customer:** [photo] here. I know the overstay is my fault.
> **[Fri 14:41] BP:** Thank you, and I appreciate the honesty. The letter marks two grounds: risk to public policy/security is not excluded, and intention to leave before expiry not established. The first is tied to the immigration history you mentioned. The decode fee is £39, service fee only, separate from any embassy or centre fees. But I won't oversell: no evidence "cancels" a recorded overstay, and time plus a clean record since is what usually helps most. If you still want the full written breakdown and a realistic reapplication view, I'm glad to do it.
> **[Fri 15:20] Customer:** Yes, I'd rather know the full picture than nothing.
> **[Fri 15:22] BP:** Understood. Link: [link].
> **[Fri 15:48] Customer:** Paid.
> **[Fri 15:50] BP:** Received. I'll write this carefully and have it to you tomorrow morning.
> **[Sat 09:30] BP:** [file] Decode ready. Straight version: the "public policy" ground is the anchor and it stems from the 2023 overstay, so no single document overturns it. What realistically helps a future application is a clean travel record since, a strong genuine-student profile, tuition and funds well evidenced, and a frank cover letter acknowledging the past rather than hiding it. I've set out how to frame each, but I want to be clear this improves your case, it does not guarantee approval, and that call is always the consulate's.
> **[Sat 10:12] Customer:** That's fair. At least I know what I'm dealing with now.
> **[Sat 10:15] BP:** That's the aim. Give it time before reapplying, that patience genuinely works in your favour here.

_Elapsed lead → delivery: overnight (about 18 hours)._

**Customer F — Egyptian retiree, Greece refusal, travelling to visit family**
> **[Mon 09:22] Customer:** Good morning. I am retired and applied to visit my daughter in Greece. They refused me. The letter is in formal language I cannot follow. Please help.
> **[Mon 10:10] BP:** Good morning, of course. Our Refusal letter decode translates that formal language into plain terms, ground by ground, and points to the evidence each one concerns. Please send a photo of the whole letter. As a retiree visiting family, the funds source and the host invitation are the usual focus, so I'll look there closely.
> **[Mon 10:38] Customer:** [photo] thank you.
> **[Mon 11:15] BP:** Thank you. The letter cites justification of the purpose of stay not provided, and means of subsistence not sufficiently demonstrated. Since you're retired, they likely wanted a clear source of income shown, pension statements for example, and a proper invitation from your daughter. The decode fee is £39, our service fee only, and it's separate from any embassy visa fee or appointment-centre fee on a reapplication. Would you like to proceed?
> **[Mon 13:40] Customer:** Yes please. My daughter will help me pay.
> **[Mon 13:42] BP:** Here is the secure link: [link].
> **[Mon 15:10] Customer:** She has paid now.
> **[Mon 15:13] BP:** Received with thanks. You'll have the decode by tomorrow morning.
> **[Tue 09:40] BP:** [file] Decode ready. For the subsistence ground: pension statements plus recent bank statements showing steady funds, and if your daughter is sponsoring, a formal invitation with her proof of residence and income in Greece. For the purpose ground: a clear letter stating the family visit, dates, and where you'll stay. This directly addresses what they raised, but the final decision stays with the Greek consulate.
> **[Tue 10:20] Customer:** Now it is clear. Thank you for your patience with me.
> **[Tue 10:24] BP:** A pleasure. If your daughter would like us to check the invitation and funds set before reapplying, that's a separate service and we're happy to help.

_Elapsed lead → delivery: overnight (about 18 hours)._

**Customer G — Brazilian, ILR holder, family group, Austria refusal on travel insurance (quick, clear-cut)**
> **[Wed 14:15] Customer:** Hi! We applied as a family of four for Austria (ski trip) and got refused. I have ILR here. The letter mentions travel insurance? We thought we had it. Confused.
> **[Wed 14:52] BP:** Hi, happy to decode it. Insurance grounds are usually very specific, so send me a photo of the full letter and I'll pinpoint exactly what they objected to across the applications.
> **[Wed 15:20] Customer:** [photo] all four were refused the same way.
> **[Wed 15:49] BP:** Thanks. It's a single, clear ground: the travel medical insurance did not meet requirements, most often because the coverage was under the required 30,000 euro minimum, or it didn't cover the full dates, or it excluded the Schengen area. The decode fee is £39, our service fee only, separate from any embassy or centre fees if you reapply. Given it's one clean ground, want me to confirm the exact defect and the fix?
> **[Wed 16:05] Customer:** Yes please, want to get it right for all four.
> **[Wed 16:07] BP:** Secure link: [link].
> **[Wed 16:31] Customer:** Paid, thanks.
> **[Wed 16:33] BP:** Received. This one's clear-cut so I'll have it back to you this evening.
> **[Wed 18:02] BP:** [file] Decode done. Confirmed: the policy you submitted capped medical cover below the 30,000 euro Schengen minimum, which is the sole reason cited. The fix is straightforward, a compliant Schengen travel medical policy for all four travellers covering the full trip dates, minimum 30,000 euro, valid across the Schengen area. Attach the policy certificate showing those figures clearly. That resolves the exact ground raised, though approval always rests with the consulate.
> **[Wed 18:25] Customer:** Amazing, that's an easy fix. Thank you!
> **[Wed 18:28] BP:** Exactly, this is one of the more fixable ones. Get the compliant policy for all four and you'll have directly answered their objection. Safe skiing when you get there.

_Elapsed lead → delivery: same day (about 3.5 hours)._

## 9. Single document check — £25

> ⏱ First reply ≤ 2 hrs · Delivery Same business day (≤ 24 hrs) · Typical span Same day – 1 day

**Customer A — British citizen, salaried, one bank statement for a France trip**
> **[Mon 09:14] Customer:** Hi, I'm applying for a French Schengen visa (I'm British but my passport's a non-visa-national situation for my wife actually). Can you just look over ONE document, my 3-month bank statement, and tell me if it's fine?
> **[Mon 09:31] BP:** Morning, and yes, that's exactly what our Single document check covers. One document, one clear verdict on whether it meets the French consulate's expectations, with fixes if needed. The fee is £25. That's our review fee only, separate from any consulate, VFS or appointment-centre charges. Who is the applicant, you or your wife?
> **[Mon 09:38] Customer:** My wife. She's Indian, on a spouse visa here.
> **[Mon 09:45] BP:** Understood. Send the bank statement as a PDF (the bank's original download, not a screenshot) and tell me the trip dates and rough daily budget. I'll check balance level, that it's the right 3 months, name and address match, and that it reads as genuinely hers.
> **[Mon 10:02] Customer:** [file] statement.pdf. Trip is 6 nights Paris, mid-August. Balance sits around £3,400.
> **[Mon 10:05] BP:** Got it, reviewing now. To confirm before payment: one document, one written check, same business day. Here's the link: [link]
> **[Mon 10:19] Customer:** Paid.
> **[Mon 10:22] BP:** Payment received, thank you. Working through it now.
> **[Mon 12:40] BP:** [file] check-notes.pdf. Verdict: the statement is fine and well above the rough £55/day benchmark for France. Two small notes: (1) it's an official PDF, good, no screenshots needed; (2) one page shows a large one-off deposit 5 weeks ago. Not a problem, but carry a one-line explanation in case the officer asks. Everything else, name, address, 3-month window, all correct.
> **[Mon 12:51] Customer:** Brilliant, thank you. That deposit was a tax refund.
> **[Mon 12:53] BP:** Perfect, a printed HMRC refund line covers that. Note this is our assessment, not a guarantee. The decision is always the consulate's. Good luck with the application.
_Elapsed lead → delivery: same day (~2.5 hrs)._

**Customer B — Nigerian, skilled-worker, sponsor letter for Germany**
> **[Tue 15:40] Customer:** Do you check invitation letters? I have a sponsor/host letter from my cousin in Berlin for a German visa. Not sure it's worded right.
> **[Tue 16:12] BP:** Yes, a host invitation is a valid single document to check. We'll confirm it has what the German mission looks for: host's full details, your details, relationship, dates, address of stay, and who covers costs. The fee is £25, our review only, separate from any consulate or VFS Global fees. Send it across whenever ready.
> **[Tue 16:20] Customer:** [photo] letter photo
> **[Tue 16:24] BP:** I can read it, but a photo of a letter isn't ideal for a check. Can your cousin send the original as a PDF or a clean scan? I want to be sure I'm judging the real document the consulate will see.
> **[Wed 08:55] Customer:** [file] invitation.pdf here's the scan.
> **[Wed 09:02] BP:** Much better, thank you. Quick payment step so I can start: [link]. One document, verdict back same business day.
> **[Wed 09:20] Customer:** Done.
> **[Wed 09:22] BP:** Received, thanks. Reviewing now.
> **[Wed 11:48] BP:** [file] check-notes.pdf. The letter is mostly strong but has three gaps the German mission often flags: (1) it doesn't state your exact travel dates, (2) no full address of where you'll stay, only the district, (3) it doesn't say clearly whether your cousin is covering accommodation only or full costs. I've drafted the exact three lines to add. Everything else, signature, passport reference, is fine.
> **[Wed 11:59] Customer:** This is really helpful. If he adds those lines, do I resend for another check?
> **[Wed 12:05] BP:** A re-check on the amended letter is a fresh £25, but honestly the three lines are spelled out, so many customers just add them and go. Your call. And remember a strong invitation supports the file, it never guarantees the decision, which stays with the consulate.
_Elapsed lead → delivery: 1 day (submitted Tue, delivered Wed midday)._

**Customer C — Indian student, travel insurance policy for Italy**
> **[Thu 11:05] Customer:** hi can you check if my travel insurance is enough for an italy student trip visa? just the insurance doc
> **[Thu 11:20] BP:** Happy to. For Schengen the insurance must show minimum €30,000 medical cover, valid across all Schengen states, for your full travel dates. That's exactly the kind of single document we check. Fee is £25, our review only, separate from any insurer or consulate cost. Send the policy PDF and your travel dates.
> **[Thu 11:34] Customer:** [file] insurance.pdf. dates 2 Sep to 20 Sep.
> **[Thu 11:41] BP:** Thanks. Payment link to begin: [link]. Same-day written verdict.
> **[Thu 11:52] Customer:** paid
> **[Thu 11:55] BP:** Got it, reviewing.
> **[Thu 14:30] BP:** [file] check-notes.pdf. One real problem: the policy cover amount reads £25,000, which converts below the required €30,000 threshold, so an Italian consulate could reject it. Also the end date is 19 Sep but you travel until 20 Sep, one day short. Recommend a policy showing €30,000+ and covering through 20 Sep (add a buffer day). Everything else, Schengen-wide wording, is correct.
> **[Thu 14:41] Customer:** oh good catch. most insurers let me just bump the cover?
> **[Thu 14:44] BP:** Usually yes, upgrading cover and extending one day is a small change with most Schengen policies. Once you have the new PDF you can self-check against those two points. Note this is guidance, not approval, the consulate decides.
_Elapsed lead → delivery: same day (~3.5 hrs)._

**Customer D — Filipino, dependant, employment letter for Netherlands (edge case: document not needed)**
> **[Fri 09:30] Customer:** I want you to check my employer's NOC letter for a Netherlands tourist visa. I'm a dependant here on my husband's visa but I also work part-time.
> **[Fri 10:05] BP:** Glad to help, and I want to save you money first. A quick question: is your part-time job what you're relying on to show ties and funds, or is your husband sponsoring the trip and covering costs?
> **[Fri 10:14] Customer:** My husband is sponsoring and paying. I just thought the work letter would help.
> **[Fri 10:22] BP:** Then a check on the employment letter alone won't tell you much, the document that actually carries your case is your husband's sponsorship and his financials, plus proof of your relationship. Paying £25 to check a letter that's only a minor supporting item wouldn't be honest value.
> **[Fri 10:29] Customer:** Oh. So which single document is worth checking?
> **[Fri 10:35] BP:** If you want one high-value check, send your husband's sponsorship letter or his 3-month bank statement, that's the spine of a dependant-sponsored file. Same £25, but far more useful. The employment letter you can simply include as-is.
> **[Fri 10:41] Customer:** That makes sense. Let me get his bank statement then.
> **[Fri 10:44] BP:** Good plan. Send it when ready and I'll take payment then, so you're only paying for the check that matters. No charge for today's steer.
_Elapsed lead → delivery: not applicable (redirected before payment)._

**Customer E — Egyptian, self-employed, business registration doc for Greece**
> **[Mon 13:10] Customer:** Salaam. I'm self-employed and applying for a Greek Schengen visa. Can you check my company registration extract? I've heard self-employed files get scrutinised.
> **[Mon 13:38] BP:** Wa alaykum salaam. Yes, and you're right that self-employed applications get a closer look, so a document check is sensible. For the business registration extract I'll confirm it's current, shows your name/ownership, is the officially recognised extract, and, if it's in Arabic, whether it needs certified English translation for the Greek consulate. Fee £25, our review only, separate from any translation, notary or consulate cost. Send it over.
> **[Mon 13:52] Customer:** [file] commercial-register.pdf. It's in Arabic.
> **[Mon 13:58] BP:** Received. Payment link to start: [link]. Same business day.
> **[Mon 14:10] Customer:** Paid, thanks.
> **[Mon 14:13] BP:** Thank you, reviewing now.
> **[Mon 16:50] BP:** [file] check-notes.pdf. The extract itself is valid and current, ownership in your name, good. The gap is language: it's fully in Arabic with no English, and the Greek consulate will expect a certified English translation attached. That translation is a separate third-party cost, not part of our fee. I've noted which fields the translator must carry over. The document is otherwise strong for a self-employed file.
> **[Mon 17:02] Customer:** Understood. Any translator you recommend?
> **[Mon 17:08] BP:** I can't tie you to one, but ask for a certified/sworn translator who states their credentials on the translation, that's what consulates accept. Once translated you can attach both. As always, a solid document supports the case but the consulate makes the decision.
_Elapsed lead → delivery: same day (~3.5 hrs)._

**Customer F — Pakistani, ILR, cover letter for Spain (edge case: expectation management, deadline)**
> **[Wed 16:30] Customer:** URGENT. My Spain appointment is tomorrow morning 9am. Can you check my cover letter tonight? Willing to pay extra.
> **[Wed 16:52] BP:** I can see the pressure, and I'll be straight with you. Our single check is same business day, and it's now late afternoon, so a tonight turnaround isn't something I can promise, our hours are until 18:00. I won't take £25 on a promise I might not meet before your 9am.
> **[Wed 16:58] Customer:** Can express make it faster?
> **[Wed 17:03] BP:** Express speeds our handling and slot-hunting, it doesn't buy after-hours guarantees or change your appointment time. For a cover letter specifically, I can do something useful right now at no charge instead: the three things Spanish consular officers care about are clear trip purpose, dates matching your bookings, and who funds the trip. If your letter states those plainly, it's doing its job.
> **[Wed 17:09] Customer:** It does cover all three, I think.
> **[Wed 17:14] BP:** Then you're likely in reasonable shape for tomorrow. Read it once more against those three points and check dates match your flight and hotel exactly, that's the usual slip. A cover letter rarely makes or breaks a file on its own.
> **[Wed 17:19] Customer:** That actually calms me down. Thank you for being honest.
> **[Wed 17:23] BP:** Of course. If you'd like a proper documented check after tomorrow for any future trip, we're here. Good luck at 9am, the decision rests with the consulate, but a tidy file is the best you can bring.
_Elapsed lead → delivery: not applicable (declined paid check on honesty; free steer given same evening)._

**Customer G — Brazilian, retired, pension proof for Portugal**
> **[Sat 10:15] Customer:** Bom dia. I'm retired and applying for a Portugal Schengen visa. Could you check my pension statement as proof of income? Just that one document.
> **[Sat 10:40] BP:** Bom dia. Yes, a pension statement is a good single document to check for a retiree file. I'll confirm it shows your name, the regular payment amount, the paying body, and that the monthly income comfortably supports the trip length for Portugal. Fee is £25, our review only, separate from any consulate or VFS charge. Send it whenever ready.
> **[Sat 10:58] Customer:** [file] pension.pdf. Trip is 10 days, Lisbon and Porto.
> **[Sat 11:03] BP:** Thank you. Payment link to begin: [link]. Same business day, though as it's Saturday I'll have it back to you well before close at 18:00.
> **[Sat 11:15] Customer:** Paid.
> **[Sat 11:18] BP:** Received, reviewing now.
> **[Sat 13:35] BP:** [file] check-notes.pdf. The pension statement is solid: name matches, monthly amount is clear, and it comfortably covers a 10-day trip on Portugal's benchmark. One suggestion, not a fault: it shows only the latest month. Include the last 2 to 3 payment records or a bank statement showing the pension landing, so the consulate sees the income is regular, not a one-off. The document itself is genuine and clear.
> **[Sat 13:44] Customer:** Makes sense, I can print three months easily.
> **[Sat 13:47] BP:** That's all it needs. With three months shown, this is a strong income proof. Reminder that it supports your case, the final decision is the Portuguese consulate's. Boa viagem.
_Elapsed lead → delivery: same day (~2.5 hrs)._

## 10. Cover letter — £45 write / £29 review

> ⏱ First reply ≤ 2 hrs · Delivery 24–48 hrs write / ≤ 24 hrs review · Typical span 1–2 days

**Customer A — British citizen, salaried, first Schengen trip to France**
> **[Mon 09:14] Customer:** Hi, I'm applying for a French Schengen visa for a week in Paris in September. My friend said I need a cover letter. Do you write these?
> **[Mon 09:31] BP:** Morning, and yes we do. Quick note first: as a British citizen you can visit the Schengen area for up to 90 days without any visa at all. Can I ask what passport you hold? I'd rather not sell you something you don't need.
> **[Mon 10:02] Customer:** Oh! I'm British, full UK passport. So I don't need a visa?
> **[Mon 10:05] BP:** Correct, for tourism up to 90 days you don't. No cover letter, no application, nothing to pay us. Just carry your return ticket and hotel booking in case border staff ask. Enjoy Paris.
> **[Mon 10:07] Customer:** That's saved me a fair bit. Thank you for being straight with me.
> **[Mon 10:09] BP:** Anytime. If your plans ever change to a long stay or work, come back and we'll help properly.
_Elapsed lead → delivery: ~1 hour (advised, no service needed, no charge)._

**Customer B — Indian national, skilled-worker visa, business trip to Germany**
> **[Tue 11:40] Customer:** Need a cover letter for a German business visa. I'm Indian, on a UK Skilled Worker visa. Invitation is from our Munich office. Appointment is next Thursday.
> **[Tue 11:52] BP:** We can do that. To confirm the tier: the write service is £45 and covers a tailored letter explaining your trip purpose, ties to the UK, funding and return intent. That fee is ours only, separate from the VFS Germany appointment fee and the embassy visa fee, which you pay them directly. Sound right?
> **[Tue 12:15] Customer:** Yes. What do you need from me?
> **[Tue 12:18] BP:** Six things: passport bio page, UK BRP/visa, the Munich invitation letter, your dates, employer name plus your job title, and who's funding the trip. Photos are fine.
> **[Tue 13:30] Customer:** [file] passport, BRP, invite, and a note with dates/employer attached.
> **[Tue 13:44] BP:** Got it all. Company-funded trip, invitation on file, clean UK ties. Comfortable slot before your Thursday appointment. Here's the secure payment link for £45: [link]
> **[Tue 14:20] Customer:** Paid.
> **[Tue 14:22] BP:** Received, thank you. First draft with you within 24 hours, likely tomorrow morning.
> **[Wed 10:15] BP:** [file] Draft cover letter. It names the Munich host, your role and salary band, states the company covers costs, and confirms your return to your UK job. Review and tell me if anything needs a tweak.
> **[Wed 12:40] Customer:** Perfect, just fix my job title to "Senior Data Engineer".
> **[Wed 13:05] BP:** [file] Updated. One tip: print it on plain paper, sign and date it, and keep it on top of your document set. Good luck Thursday.
> **[Wed 13:11] Customer:** Great service, thanks.
_Elapsed lead → delivery: ~25 hours._

**Customer C — Nigerian national, self-employed, family trip to Italy**
> **[Wed 16:20] Customer:** Hello. Me and my wife want to apply for Italy visa, tourism, 10 days. I run my own business here in the UK. Can you write two cover letters?
> **[Wed 16:38] BP:** We can. For a couple we do a combined family cover letter plus a short individual note each, still charged as one write at £45. Just so it's clear, that's our writing fee only. The VFS Italy appointment fee and the Italian embassy visa fees are separate and paid to them.
> **[Wed 17:02] Customer:** Okay. I'm self-employed though, is that a problem?
> **[Wed 17:10] BP:** Not at all, it just means the letter should lean on your business as your tie to the UK. Do you have your company registration or self-assessment, plus recent business bank statements? We reference them in the letter, we don't submit them for you.
> **[Thu 09:05] Customer:** [file] Company cert, last SA302, and 3 months business statements. Both passports too.
> **[Thu 09:26] BP:** Clear picture. Self-employed with an active UK business and both of you travelling and returning together reads well. Payment link for the £45 write: [link]
> **[Thu 10:40] Customer:** Done.
> **[Thu 10:43] BP:** Received. Draft within 24 to 48 hours as it's two linked letters. I'll aim for tomorrow.
> **[Fri 15:30] BP:** [file] Family cover letter plus one signed-note template each. It states your itinerary, that your UK business funds the trip, and that you both return to run it. Have a look.
> **[Fri 16:55] Customer:** This is very good. We are happy.
> **[Fri 17:00] BP:** Glad to hear it. Print, sign, date each one. Safe travels to Italy.
_Elapsed lead → delivery: ~2 days._

**Customer D — Pakistani national, student, review of a self-written letter for Spain**
> **[Thu 20:10] Customer:** I already wrote my own cover letter for a Spain student-related short trip. Can you just check it rather than write from scratch? I'm a Pakistani student in the UK.
> **[Fri 09:12] BP:** Yes, the review service is £29. We mark up your draft with fixes on tone, structure, ties and anything that could raise a question, and return a clean version. It's our fee only, separate from the VFS or Spanish consulate fees. Send the draft when you're ready.
> **[Fri 09:40] Customer:** [file] my_letter.docx. Trip is a 6-day study visit, I have a UK student visa, parents fund me.
> **[Fri 10:15] BP:** Read it. It's solid but three things: it doesn't mention your course end date, it's vague on who funds the trip, and it omits your return to studies. Those are the exact points that get queried. Happy to fix and return? Payment link: [link]
> **[Fri 10:48] Customer:** Paid the £29.
> **[Fri 10:50] BP:** Thanks. Reviewed version back within 24 hours, sooner if I can.
> **[Fri 18:20] BP:** [file] Reviewed letter, tracked changes plus a clean copy. Added your course dates, a line that your parents fund the trip with proof referenced, and a clear return-to-studies statement. Read both versions.
> **[Sat 09:30] Customer:** Much stronger now. Thank you.
> **[Sat 09:36] BP:** You're welcome. Enrolment letter and bank proof should sit behind it in your pack. Good luck.
_Elapsed lead → delivery: ~9 working hours._

**Customer E — Filipino national, dependant/ILR, retired parents visiting from abroad**
> **[Fri 13:05] Customer:** Hi, my retired parents in the Philippines want to visit the Netherlands and I'll host part of the trip. I'm on ILR here. Can you write a cover/sponsorship letter?
> **[Fri 13:29] BP:** We can write the applicant cover letter for your parents and a sponsor letter from you as host. That's the £45 write. Our fee only. The Netherlands appointment fee and consulate visa fees are separate. One question: the actual visa application has to be made by your parents in the Philippines, we handle the letters, not the submission. Okay?
> **[Fri 14:00] Customer:** Yes that's fine, they'll submit there. What do you need?
> **[Fri 14:06] BP:** Your parents' passport pages, their pension or funds proof, your ILR/BRP, your address, and how costs are split. If you're covering accommodation say so, we'll word the sponsorship accordingly.
> **[Sat 10:20] Customer:** [file] Both passports, pension statements, my BRP, and my address. I cover their hotel, they cover flights and spending.
> **[Sat 10:41] BP:** Perfect split to show. Retired parents with pension income and a returning-home intent, you sponsoring accommodation. Reads clean. Payment link for £45: [link]
> **[Sat 11:15] Customer:** Paid.
> **[Sat 11:18] BP:** Received. Both letters within 24 to 48 hours. Monday morning likely, as Sunday we're closed.
> **[Mon 11:30] BP:** [file] Two documents: your parents' cover letter and your host sponsorship letter. Each states the cost split, their pensions, and their return to the Philippines. Take a look.
> **[Mon 13:15] Customer:** Excellent, exactly what we needed.
> **[Mon 13:20] BP:** Great. Sign and date the sponsor letter, your parents sign theirs. Send both out to them for the appointment.
_Elapsed lead → delivery: ~3 days (spanning closed Sunday)._

**Customer F — Egyptian national, student, deadline genuinely too tight**
> **[Wed 08:05] Customer:** URGENT. I need a cover letter for a Greece Schengen visa. My appointment is TODAY at 14:00 and I still have nothing written. Egyptian student in the UK. Please help fast.
> **[Wed 08:22] BP:** I understand the pressure. I have to be honest: a proper tailored letter needs your documents and at least a few hours to write and let you review, and we can't guarantee a finished piece before a 14:00 appointment today. Even express speeds our writing, not the clock you're up against.
> **[Wed 08:26] Customer:** So you can't do it?
> **[Wed 08:31] BP:** Not to a standard I'd charge you for by 14:00, no. What I can do at no charge: give you a short structure to type yourself right now. Purpose and dates, your UK student ties and course end, who funds the trip, and a clear line that you return to studies. A signed one-page version in your own words is accepted.
> **[Wed 08:34] Customer:** Okay please send the structure.
> **[Wed 08:39] BP:** [file] One-page skeleton with four labelled paragraphs and prompts. Fill each in, sign, date, print. That covers today.
> **[Wed 08:52] Customer:** Done it, thank you for not just taking my money.
> **[Wed 08:55] BP:** That's the job. If a future trip has breathing room, we'll write you a full one properly. Good luck at 14:00.
_Elapsed lead → delivery: ~50 minutes (declined paid work, guided at no charge)._

**Customer G — Brazilian national, self-employed, wants a letter for a passport-free trip**
> **[Sat 10:40] Customer:** Hi, I want to buy a cover letter for a trip to Portugal in October. I'm Brazilian, self-employed, living in the UK on a partner visa. 12 days, tourism.
> **[Sat 10:58] BP:** Happy to help, but one check first so you don't overpay. Are you travelling on your Brazilian passport as a tourist? Brazilian nationals can enter the Schengen area visa-free for short stays up to 90 days for tourism.
> **[Sat 11:20] Customer:** Yes, Brazilian passport, just a holiday. So I don't need a visa at all?
> **[Sat 11:24] BP:** For tourism up to 90 days, no Schengen visa and therefore no cover letter for an application. There's no application to attach it to. I won't charge you for a document you don't need.
> **[Sat 11:27] Customer:** What should I carry then?
> **[Sat 11:31] BP:** Return flight, accommodation booking, travel insurance, and proof of funds in case a border officer asks. Note the ETIAS travel authorisation is expected to become required for visa-free visitors in future, so check that nearer your date. Your UK partner visa doesn't affect visa-free entry on your Brazilian passport.
> **[Sat 11:36] Customer:** Brilliant, thanks for being honest.
> **[Sat 11:39] BP:** Pleasure. If you ever need a letter for a visa-required country, you know where we are. Enjoy Portugal.
_Elapsed lead → delivery: ~1 hour (advised, no service needed, no charge)._

## 11. Proof-of-funds guidance — £29

> ⏱ First reply ≤ 2 hrs · Delivery ≤ 24 hrs · Typical span ~1 day

**Customer A — British retiree, self-funded, France (long tourist trip)**
> **[Mon 10:02] Customer:** Hi, I'm retired and planning six weeks in France next spring. My income is a pension plus some savings. I keep reading that I need to "prove funds" but nobody explains how much or what to actually show. Can you help?
> **[Mon 10:41] BP:** Hello, happy to help. Quick check first: are you a British passport holder, and is this a standard short-stay Schengen tourist trip (under 90 days in 180)? Six weeks is well within that.
> **[Mon 10:49] Customer:** Yes British, and yes six weeks only, then home.
> **[Mon 11:03] BP:** Perfect. Our Proof-of-funds guidance is £29. You get a written breakdown of what the French consulate typically expects for your trip length, which documents demonstrate it, and how to present pension + savings cleanly so there are no questions. To be clear, our £29 is a service fee only. It is separate from the visa/embassy fee and any appointment-centre charge. And I have to be honest up front: we guide your evidence, we never promise an outcome. The decision is always the consulate's.
> **[Mon 11:10] Customer:** Understood. Go ahead.
> **[Mon 11:12] BP:** Here's the secure payment link: [link]. Once it's through I'll send a short questionnaire so I can tailor the figures to your actual trip.
> **[Mon 11:31] Customer:** Paid.
> **[Mon 11:33] BP:** Received, thank you. Questionnaire here [file] — trip dates, nightly accommodation cost, and whether you're covering your own hotels or staying with family. Send it back whenever suits.
> **[Tue 08:52] Customer:** Filled in and returned [file].
> **[Tue 15:20] BP:** Thanks. Your guidance pack is ready [file]. It covers: the daily-means figure France applies, how six weeks translates to a suggested balance range, using a 3-month pension-credited bank statement as your core proof, and a one-line cover note explaining the savings. Nothing here is a guarantee, it's about presenting your genuine position clearly. Anything unclear, just ask.
> **[Tue 15:44] Customer:** Very clear, thank you.
> **[Tue 15:46] BP:** One optional extra if useful later: we also do a full document-review before you submit, £29 as well. No pressure. Safe travels.

_Elapsed lead → delivery: ~1 day (Mon late-morning enquiry to Tue afternoon delivery)._

**Customer B — Indian skilled-worker, salaried, Germany (business + tourism)**
> **[Tue 13:15] Customer:** Hello. I'm on a Skilled Worker visa in the UK, Indian passport. Travelling to Germany for a conference then a few days sightseeing. I earn a normal salary but my account balance is low right after rent day. Will that sink my funds proof?
> **[Tue 13:40] BP:** Good question, and a common worry. Short answer: consulates look at the whole picture, not one low day. Are you British-resident with a valid BRP/eVisa, and roughly how many total days in Germany?
> **[Tue 13:52] Customer:** Valid until 2027, eVisa. About nine days total.
> **[Tue 14:05] BP:** Great. Our Proof-of-funds guidance is £29 (service fee only, separate from the German visa fee and the appointment-centre charge). For your case I'll show how salary regularity and your employer letter offset a temporary low balance, and how to time your statement so it reflects a healthier picture honestly. I won't fabricate anything and I can't promise approval, that's the consulate's call. Want to proceed?
> **[Tue 14:11] Customer:** Yes please.
> **[Tue 14:12] BP:** Payment link: [link].
> **[Tue 14:29] Customer:** Done.
> **[Tue 14:31] BP:** Thank you, received. Send me: last 3 months' statements (you can redact card numbers), your monthly net salary, and whether the conference organiser or your employer covers any costs [file].
> **[Wed 09:15] Customer:** Sent [file]. Employer covers the hotel for conference nights.
> **[Wed 16:40] BP:** That helps a lot. Guidance pack ready [file]. Key points: your salary credits across 3 months show clear regularity, the employer-paid hotel reduces the balance you personally need to evidence, and I've noted which day's balance to reference plus a short cover note listing salary + covered costs. Present it as your true position, nothing invented. Good luck at the conference.
> **[Wed 16:58] Customer:** Really useful, thanks.

_Elapsed lead → delivery: ~1 day (Tue afternoon to Wed late-afternoon)._

**Customer C — Nigerian student dependant, family group, Spain (holiday)**
> **[Wed 11:20] Customer:** Hi. My husband is a PhD student here, I'm his dependant, Nigerian passports, and we want to take our two kids to Spain for 10 days in the summer. Money is tight on a stipend. I'm scared our funds won't look enough for four people. Can you tell us what's realistic?
> **[Wed 11:52] BP:** Thanks for the detail, and yes this is exactly what the guidance is for. Family-of-four short trips do get scrutinised on funds, so presenting them well matters. Are all four on valid UK visas (student + dependants), and is the stipend your main income?
> **[Wed 12:10] Customer:** All valid, yes. Stipend plus a small part-time job I do.
> **[Wed 12:25] BP:** Okay. Honest note before you pay: Spain applies a per-person daily-means expectation, and for four people over 10 days that adds up. If your genuine balance is well below it, no amount of presentation fixes that, and I'd rather tell you now than take £29 for guidance you can't act on. Would you be able to share a rough total available (savings + stipend buffer) so I can sanity-check before you commit?
> **[Wed 12:38] Customer:** Roughly £2,600 across accounts, and the trip's mostly paid already, flights and apartment booked.
> **[Wed 12:52] BP:** That changes things helpfully. Pre-paid flights and accommodation count strongly in your favour because they lower the "living funds" the consulate expects you to show on top. £2,600 plus proof of prepaid travel is workable to present honestly. On that basis the £29 guidance is worth it. Shall I proceed?
> **[Wed 13:01] Customer:** Yes, thank you for being straight with us.
> **[Wed 13:03] BP:** Payment link: [link]. (Service fee only, separate from Spain's visa fee and the centre charge.)
> **[Wed 13:20] Customer:** Paid.
> **[Wed 13:22] BP:** Received. Please send: booking confirmations for flights + apartment, your 3-month statements, and stipend/part-time evidence [file].
> **[Thu 08:40] Customer:** All attached [file].
> **[Thu 17:10] BP:** Guidance pack ready [file]. It shows how to present the prepaid bookings to reduce the daily-means expected, how to combine both accounts into one clear balance summary, and a short family cover letter listing who's travelling and what's already paid. This is presentation of your real position, not a guarantee, the consulate decides. You're in a more solid spot than you feared.
> **[Thu 17:35] Customer:** This is such a relief. Thank you.

_Elapsed lead → delivery: ~1 day (Wed midday to Thu late-afternoon)._

**Customer D — Pakistani self-employed, Netherlands (short business trip) — DECLINE / manage expectations**
> **[Thu 09:30] Customer:** Salaam. I run my own import business, Pakistani passport, ILR in the UK. I need to be in Amsterdam in FIVE DAYS for a supplier meeting. My accounts are fine but messy. Can you sort my proof of funds so I get the visa in time?
> **[Thu 09:58] BP:** Walaikum salaam. I'll be straight with you because the timing is the issue, not your money. Even with express handling on our side, express only speeds OUR work and slot-hunting. It cannot speed the consulate's decision, and it cannot create appointment slots that aren't there. A Netherlands short-stay application in five days, from first appointment to decision, is realistically not achievable. I don't want to take £29 for guidance you can't use in time.
> **[Thu 10:04] Customer:** So there's nothing?
> **[Thu 10:16] BP:** For this specific meeting, honestly no, I won't pretend otherwise. What I'd suggest: if the supplier can move the meeting or you can send a colleague, we prep your funds properly for a slightly later date. Your ILR and business income are genuinely fine to evidence once we tidy the presentation. But I won't sell you a five-day miracle.
> **[Thu 10:22] Customer:** Appreciate the honesty. Let me see if I can push the meeting.
> **[Thu 10:24] BP:** Do that. If it moves even a couple of weeks out, come back and the £29 guidance (service fee only, separate from the Dutch visa and centre charges) will genuinely help. No charge for this conversation, and nothing taken from you today.
> **[Thu 10:31] Customer:** Will do. Thanks.

_Elapsed lead → delivery: same morning; no sale, expectations managed._

**Customer E — Filipino ILR holder, salaried, Italy (family visit) — service not needed**
> **[Fri 14:05] Customer:** Hi there. Filipino, ILR in UK, going to Italy for two weeks to see my sister who lives in Milan. She's covering everything, accommodation and most costs. Do I need your proof-of-funds service?
> **[Fri 14:33] BP:** Let me check before you spend anything. Is your sister an EU/Italian resident, and will she provide a formal invitation or sponsorship declaration?
> **[Fri 14:41] Customer:** Yes she's an Italian resident, and she said she'll sign whatever invitation letter is needed.
> **[Fri 14:55] BP:** Then honestly you may not need our £29 guidance at all. When a resident host formally sponsors you with a signed declaration plus their own proof of means, that often satisfies the funds requirement directly, and you'd mainly show a modest personal buffer. I'd rather tell you that than sell you something you don't need.
> **[Fri 15:02] Customer:** Oh, that's helpful. So what do I actually show?
> **[Fri 15:14] BP:** Broadly: your sister's invitation/sponsorship, her residency proof, and a light personal statement showing you're not destitute. If you'd like that turned into a tailored checklist for your exact case we can, but for a fully sponsored two-week visit many people manage without paid guidance. Your call, no pressure either way.
> **[Fri 15:22] Customer:** I'll try it myself first. Thank you for being honest.
> **[Fri 15:24] BP:** Sensible. If the consulate's checklist confuses you, come back and we'll help. Enjoy Milan.

_Elapsed lead → delivery: same afternoon; advised out of a purchase._

**Customer F — Egyptian student, part-time income, Greece (island holiday)**
> **[Fri 16:20] Customer:** Hello. Egyptian passport, full-time Master's student in Manchester. Planning a week in Greece after exams. My money comes from part-time work and family transfers into my account. I'm worried the transfers look suspicious. How do I prove funds properly?
> **[Sat 09:12] BP:** Morning, and good question. Family transfers are completely normal for students, the key is documenting them so they read as legitimate support, not unexplained deposits. Valid student visa, and roughly seven days in Greece?
> **[Sat 09:30] Customer:** Yes valid until 2026, and seven days.
> **[Sat 09:44] BP:** Our Proof-of-funds guidance is £29, service fee only, separate from the Greek visa fee and appointment-centre charge. For you I'll cover: how to label recurring family transfers with a short supporting note or sponsor letter, showing your part-time payslips alongside, and the balance range sensible for a student week in Greece. Presentation of genuine funds only, and the decision stays with the consulate. Want to go ahead?
> **[Sat 09:51] Customer:** Yes please.
> **[Sat 09:53] BP:** Payment link: [link].
> **[Sat 10:08] Customer:** Paid.
> **[Sat 10:10] BP:** Thank you. Send me: 3 months' statements showing the transfers, your part-time payslips, and the name/relationship of whoever sends the family money [file].
> **[Sat 12:30] Customer:** Attached [file]. Transfers are from my father.
> **[Mon 11:15] BP:** Thanks, worked through it this morning. Guidance pack ready [file]. It shows how a one-line note from your father plus his ID/relationship proof turns those transfers from "unexplained" into documented support, how to pair them with your payslips, and a sensible balance to hold for the week. All honest presentation, no guarantee of outcome. This should ease the suspicion worry.
> **[Mon 11:40] Customer:** Exactly what I needed, thank you.

_Elapsed lead → delivery: ~1 day of business hours (Fri evening enquiry, Sat prep, Mon delivery over the weekend gap)._

**Customer G — Brazilian dependant, self-employed spouse, Austria (ski trip)**
> **[Mon 15:40] Customer:** Hi. Brazilian passport, I'm the dependant of my husband who's a skilled worker here. He's self-employed as a contractor. We want a week skiing in Austria in January. His income is irregular, some months big, some small. How do we prove funds when it's up and down?
> **[Mon 16:08] BP:** Hello. Irregular self-employed income is very common and consulates are used to it, the trick is showing the pattern over time rather than one month. Are both of you on valid UK visas, and is the trip roughly seven days?
> **[Mon 16:20] Customer:** Both valid, and yes about seven days, two of us.
> **[Mon 16:35] BP:** Good. Our guidance is £29 (service fee only, separate from Austria's visa fee and the centre charge). For a self-employed contractor I'll show how to present 6 months of statements to smooth out the peaks and troughs, how to use average monthly inflow rather than a single figure, and how to cover two people for a ski week where daily costs run higher. Genuine presentation only, and Austria's consulate makes the final decision, not us. Shall I send the link?
> **[Mon 16:42] Customer:** Yes go ahead.
> **[Mon 16:44] BP:** Payment link: [link].
> **[Mon 17:05] Customer:** Paid, thanks.
> **[Mon 17:07] BP:** Received. Please send: 6 months' business/personal statements, any contractor invoices, and your husband's visa details plus yours [file]. No rush tonight, tomorrow's fine.
> **[Tue 09:50] Customer:** All sent this morning [file].
> **[Tue 16:30] BP:** Guidance pack ready [file]. It shows how to present a 6-month average to explain the irregular income, which invoices to attach as backup, a note on the higher daily-means sensible for a ski trip, and a short cover letter tying it together for both travellers. Honest presentation of your real position, the outcome rests with the consulate. Enjoy the slopes.
> **[Tue 16:52] Customer:** Brilliant, thank you so much.

_Elapsed lead → delivery: ~1 day (Mon afternoon to Tue late-afternoon)._

## 12. Document legalisation / apostille — £49 + apostille fee

> ⏱ First reply ≤ 2 hrs · Delivery 3–7 working days (FCDO ~2–5 + handling) · Typical span ~1 week+

**Customer A — British citizen, marrying in Italy, needs birth certificate apostilled**
> **[Mon 09:14] Customer:** Hi, getting married in Lake Como in September. The comune is asking for an apostilled birth certificate. Can you sort that?
> **[Mon 10:41] BP:** Congratulations. Yes, we handle FCDO apostille on UK birth certificates. Quick check first: is your certificate a full certified copy from the GRO (the long one), issued within the last few months? Italian comuni usually want a recent one.
> **[Mon 11:20] Customer:** It's my original from when I was born, 1989.
> **[Mon 11:28] BP:** That still legalises fine, but some Italian town halls reject certificates older than 3–6 months. Safer to order a fresh GRO copy (£12.50 direct from the government, that's separate from us) then apostille that. Want me to guide you on ordering the fresh one, or apostille the 1989 original as-is at your risk?
> **[Mon 13:02] Customer:** Let's do a fresh one to be safe. What's the total?
> **[Mon 13:10] BP:** Our handling fee is £49. The FCDO apostille itself is £30.20 (standard) or £75 premium, paid to the government and separate from our fee. The £12.50 GRO copy is also separate. So worst case standard route: £49 + £30.20 + £12.50. No charge from us for the ordering guidance.
> **[Mon 13:15] Customer:** Standard is fine, September is ages away. Go ahead.
> **[Mon 13:22] BP:** Perfect. Here's the secure link for our £49 handling fee: [link]. Once you've ordered the GRO copy and it arrives, post it to us and we submit to the FCDO. I'll email the order steps now.
> **[Mon 15:47] Customer:** Paid. [file]
> **[Mon 15:52] BP:** Got it, thank you. GRO ordering guide sent to your email. Reply here when the fresh certificate lands and we'll move.
> **[Thu 10:05] Customer:** GRO copy arrived today, posting it to you this afternoon.
> **[Fri 09:30] BP:** Received your certificate this morning. Submitting to FCDO on the standard service today. Typical turnaround is 2–5 working days on their side, then we courier it back same day it returns.
> **[Wed 14:12] BP:** Good news, apostille is back from the FCDO. [photo] Posting the legalised certificate to you by tracked delivery now, tracking number in your email. Note the apostille certifies the signature/seal, not the marriage outcome, that's the comune's call.
> **[Wed 14:40] Customer:** Brilliant, thank you so much!

_Elapsed lead → delivery: ~9 days (fresh GRO order + standard FCDO)._

**Customer B — Indian skilled worker, degree certificate for German employer**
> **[Tue 08:50] Customer:** My new German employer wants my UK master's degree apostilled before I relocate to Munich. How fast can you do it?
> **[Tue 09:15] BP:** We can help. One thing first: a UK degree certificate usually needs a solicitor or notary to certify it before the FCDO will apostille it, because it isn't a UK public document on its own. Do you have a notarised copy already, or just the original degree?
> **[Tue 09:22] Customer:** Just the original from my university.
> **[Tue 09:40] BP:** Then the steps are: notary certifies it, then FCDO apostilles the notary's signature. We can arrange the notary or you can use your own. Notary fees run roughly £40–£75 and are separate from us and from the FCDO fee.
> **[Tue 09:44] Customer:** Please arrange everything, I don't have time. It's urgent, I start in 3 weeks.
> **[Tue 09:58] BP:** Doable. Our handling fee is £49. On top: notary (approx £50, paid to them) and FCDO premium apostille £75 (paid to government) for the fastest route. Premium speeds OUR submission and the FCDO's handling, but I can't guarantee an exact FCDO return date. Total from you roughly £49 + £50 + £75.
> **[Tue 10:05] Customer:** Fine, let's go premium.
> **[Tue 10:11] BP:** Payment link for our £49: [link]. Once paid I'll book the notary appointment and email you the slot.
> **[Tue 10:29] Customer:** Done.
> **[Tue 10:40] BP:** Received, thank you. Notary booked for Thu morning, details in your inbox. Bring the original degree and photo ID.
> **[Thu 12:15] Customer:** Notary done, they've couriered the certified copy to you.
> **[Fri 09:20] BP:** Certified copy received. Submitting to FCDO premium today. Expect their return in roughly 2 working days, then we send straight to you.
> **[Tue 11:30] BP:** Apostille returned. [photo] Tracked delivery on its way to you today, well inside your 3-week window. Reminder: this legalises the document, your employer/German authorities decide on recognition.
> **[Tue 11:52] Customer:** Lifesaver, thank you.

_Elapsed lead → delivery: ~7 working days (notary + FCDO premium)._

**Customer C — Nigerian ILR holder, marriage certificate for Spanish residency**
> **[Wed 12:03] Customer:** Hello, I need my UK marriage certificate legalised for use in Spain. Spain also mentioned something about translation?
> **[Wed 12:44] BP:** Happy to help. Two layers here: 1) FCDO apostille on the marriage certificate, 2) Spain typically requires a sworn/official translation into Spanish. We do the apostille; the sworn translation is a separate specialist and a separate fee. Is your certificate a certified copy from the register office?
> **[Wed 13:10] Customer:** Yes, certified copy from Croydon register office.
> **[Wed 13:18] BP:** Perfect, that apostilles directly, no notary needed. Order matters: apostille FIRST, then translate the apostilled document, so the translator captures the apostille too. Otherwise Spain may bounce it.
> **[Wed 13:25] Customer:** Good to know. What do I owe you?
> **[Wed 13:33] BP:** Our handling fee £49. FCDO standard apostille £30.20 to the government (separate). Sworn translation, if you want us to coordinate it, is quoted by the translator separately, usually £30–£60 per page. No charge from us to point you to a vetted sworn translator.
> **[Wed 13:40] Customer:** Do the apostille now, I'll sort translation after. Standard is fine.
> **[Wed 13:47] BP:** Great. £49 link here: [link]. Post the certified certificate to us once paid and we submit.
> **[Wed 16:20] Customer:** Paid, posting tomorrow.
> **[Fri 09:15] BP:** Certificate arrived. Into the FCDO standard queue today, roughly 2–5 working days their side.
> **[Thu 10:50] BP:** Apostille back. [photo] Sending it to you tracked today, plus I've emailed a vetted sworn-translator contact for the Spanish version. Apostille confirms the document's authenticity; residency approval is the Spanish authorities' decision.
> **[Thu 11:30] Customer:** Thank you, very clear.

_Elapsed lead → delivery: ~8 working days (apostille only; translation follows separately)._

**Customer D — Filipino student, police certificate (ACRO) for Dutch internship**
> **[Thu 15:10] Customer:** My Netherlands internship needs my UK police certificate apostilled. I have my ACRO certificate already.
> **[Thu 16:33] BP:** Great, an ACRO police certificate is a UK public document and apostilles directly, no notary. Quick check: is it the original ACRO with the signature, and issued recently? Some Dutch bodies want it under 3 months old.
> **[Thu 16:40] Customer:** Issued 5 weeks ago, original in hand.
> **[Thu 16:48] BP:** Perfect timing. Our handling fee £49, FCDO standard apostille £30.20 paid to the government (separate from us). When do you need it by?
> **[Thu 16:55] Customer:** Internship starts in about 4 weeks, so standard is okay I think.
> **[Thu 17:02] BP:** Standard is fine for that window. Payment link for £49: [link]. Post the ACRO to us after paying and we'll handle the FCDO submission.
> **[Fri 11:20] Customer:** Paid earlier, posted the ACRO this morning.
> **[Mon 09:40] BP:** ACRO received this morning. Submitting FCDO standard today, approx 2–5 working days their side, then straight back to you.
> **[Fri 13:15] BP:** Apostille returned. [photo] Tracked delivery to you today. Note the apostille legalises the certificate only; the internship/Dutch host decide on acceptance.
> **[Fri 13:44] Customer:** Perfect, thanks for keeping me posted.

_Elapsed lead → delivery: ~6 working days (ACRO apostille, standard)._

**Customer E — Egyptian self-employed, power of attorney impossible deadline (managed expectations)**
> **[Fri 16:20] Customer:** URGENT. I need a power of attorney notarised AND apostilled for a property sale in Greece. The signing is Monday. Can you get it apostilled by then?
> **[Fri 16:38] BP:** I understand the pressure, so let me be straight with you rather than take your money and disappoint you. Today is Friday afternoon. Even FCDO premium runs on working days, and the notary step has to happen first. Monday is not achievable, I won't pretend otherwise.
> **[Fri 16:42] Customer:** Nothing at all? Not even paying extra?
> **[Fri 16:50] BP:** Paying more speeds our handling and the FCDO's queue, but it can't create working days that don't exist over a weekend, and it never speeds the Greek side. Realistic fastest with premium: notary Monday, FCDO submission Monday/Tuesday, return around Wednesday/Thursday. That's the honest floor.
> **[Fri 16:55] Customer:** The buyer might move the signing. If I push it to Thursday, can you do it?
> **[Fri 17:03] BP:** A Thursday signing is workable. Plan: notary Monday am, FCDO premium same day, likely back Wed, courier to reach you Thu. Our fee £49; notary approx £50 and FCDO premium £75 both separate and paid to them/the government. I won't take payment until you've confirmed the buyer will move the date, so you're not paying for an impossible timeline.
> **[Fri 17:20] Customer:** Let me confirm with them over the weekend and message you Monday.
> **[Mon 09:12] Customer:** Buyer agreed Thursday. Let's do it.
> **[Mon 09:20] BP:** Good. Here's the £49 link: [link]. Notary slot held for 11:30 today, address in your email. Bring the draft POA and photo ID.
> **[Mon 09:38] Customer:** Paid, heading to the notary.
> **[Mon 15:10] BP:** Notary copy received from you, submitted to FCDO premium this afternoon.
> **[Wed 12:40] BP:** Apostille back. [photo] Same-day courier booked to reach you Thursday morning, ahead of signing. The apostille legalises signatures/seal; the sale itself is between you, the buyer and Greek notary.
> **[Wed 13:05] Customer:** You saved this deal. Thank you for being honest on Friday.

_Elapsed lead → delivery: ~5 working days (weekend killed the original Monday deadline; rebooked realistically)._

**Customer F — Brazilian dependant visa holder, no legalisation actually needed (declined to sell)**
> **[Sat 10:05] Customer:** Hi, I want to apostille my Brazilian birth certificate so I can use it here in the UK for my child's school registration. Can you do it?
> **[Sat 10:52] BP:** Happy to look, but I want to save you money if I can. Two issues: first, we apostille UK-issued documents through the FCDO. A Brazilian document has to be legalised in Brazil, not by the UK FCDO, so that's not something we can process here.
> **[Sat 11:10] Customer:** Oh. So I do need something though?
> **[Sat 11:18] BP:** Maybe not. UK state schools generally don't require an apostille for enrolment, they usually just want proof of your child's age and address (passport, a translation if needed, utility bill). Have you actually been asked for a legalised/apostilled document in writing, or is this a precaution?
> **[Sat 11:31] Customer:** The school just asked for the birth certificate. They didn't say apostille.
> **[Sat 11:38] BP:** Then you very likely don't need us at all. I'd give the school your child's Brazilian birth certificate plus a simple certified English translation if they ask for one. No apostille, no FCDO, no fee to us. If they later insist on legalisation, that's done via Brazilian authorities and I'll point you to the right route at no charge.
> **[Sat 11:45] Customer:** That's such a relief, I thought this would cost me a fortune. Thank you for being honest.
> **[Sat 11:50] BP:** That's the job. Come back anytime if you ever need a genuinely UK document legalised for use abroad.

_Elapsed lead → delivery: same day (advice only, no service sold, no charge)._

**Customer G — Pakistani business owner, company documents for France (family group, multiple docs)**
> **[Mon 11:02] Customer:** I run a UK Ltd company and I'm setting up a branch in France. Need several docs apostilled: certificate of incorporation, articles of association, and a board resolution. Can you handle a batch?
> **[Mon 11:35] BP:** Yes, we do multi-document batches regularly. Quick sort: the certificate of incorporation from Companies House apostilles directly. Articles and the board resolution usually need a notary or solicitor to certify them first, since they're private company documents. France often also wants a sworn French translation, which is a separate specialist and fee.
> **[Mon 11:48] Customer:** Understood. So notary for two, direct for one. What's the pricing for the batch?
> **[Mon 12:05] BP:** Our handling fee is £49 for the first document and £25 per additional document in the same batch, so £49 + £25 + £25 = £99 handling for the three. Then, separate from us: FCDO apostille £30.20 per document standard (or £75 each premium), paid to the government, plus notary fees for the two private docs (approx £50–£90). Translation quoted separately if you need it.
> **[Mon 12:14] Customer:** Let's do standard on all three. Timeline?
> **[Mon 12:22] BP:** Once the two are notarised, all three go to the FCDO together. Standard is roughly 2–5 working days their side. Realistic full batch: about a week to ten days including the notary step. I'll confirm the notary slot after payment.
> **[Mon 12:30] Customer:** Go ahead.
> **[Mon 12:36] BP:** Payment link for the £99 handling: [link]. Once paid I'll book the notary for the articles and resolution and email you the appointment.
> **[Mon 14:50] Customer:** Paid. [file]
> **[Mon 14:58] BP:** Received, thank you. Notary booked Wed am, bring the original articles and signed board resolution. The incorporation cert I can submit without notarisation.
> **[Wed 13:40] Customer:** Notary done, they're sending the certified copies to you.
> **[Thu 09:30] BP:** All three documents in hand. Submitting the batch to FCDO standard today, approx 2–5 working days their side.
> **[Wed 15:20] BP:** Full batch apostilled and back. [photo] Tracked courier to you today. Reminder: apostilles legalise the documents; French registration and translation acceptance are decided by the French authorities. Want me to introduce the sworn French translator for the next step?
> **[Wed 15:48] Customer:** Yes please, send that over.

_Elapsed lead → delivery: ~9 working days (batch of 3 with notary step, standard FCDO)._

## 13. Express appointment booking — £49

> ⏱ First reply ≤ 30 min · Delivery Slot secured 1–5 days (availability-driven) · Typical span A few days

**Customer A — British citizen, salaried, needs France slot fast for half-term**
> **[Mon 09:14] Customer:** Hi, I need a France Schengen appointment ASAP. Half-term trip on the 28th and the earliest slot I can see online is way too late. Can you get me in sooner?
> **[Mon 09:31] BP:** Morning! We can help. To be clear up front: we hunt and secure a genuine appointment slot faster than most people manage alone, but we can't create availability that isn't released, and we never influence the consulate's decision. Which visa centre city, and how many applicants?
> **[Mon 09:40] Customer:** London, just me. Passport is British so I don't need a visa myself but my wife travels on an Indian passport, she's the applicant.
> **[Mon 09:44] BP:** Understood, one applicant then (your wife). Our fee is £49 for the express slot-hunt. That's our handling charge only. The France visa centre (TLS) appointment fee, the €90 visa fee and any service charges are paid separately, directly to them. Happy to proceed?
> **[Mon 09:52] Customer:** Yes go for it.
> **[Mon 09:55] BP:** Great. Secure payment link here: [link]. Once it clears I'll start watching for London releases today. Can you confirm her full name as on passport and preferred weekday windows?
> **[Mon 10:07] Customer:** Paid. Aisha Rahman. Any weekday morning works, she can do short notice.
> **[Mon 10:09] BP:** Payment received, thank you. Short-notice flexibility helps a lot. I'll monitor cancellations and new releases and message the moment one fits before the 28th.
> **[Tue 08:52] BP:** Morning update: a London slot opened for Thu this week, 11:20. I've held it and started the booking [photo]. Please reply within the hour to lock it.
> **[Tue 09:05] Customer:** Yes lock it!!
> **[Tue 09:16] BP:** Locked and confirmed [file]. She'll get the centre's own email too. Reminder: bring the full document set; the slot secures the appointment, not the outcome. Want me to run a document pre-check for £39 before Thursday?
> **[Tue 09:20] Customer:** Yes please, that'd settle my nerves.

_Elapsed lead → delivery: ~1 day (slot held next morning)._

**Customer B — Indian national, skilled worker, Germany business trip, tight deadline**
> **[Wed 14:02] Customer:** Need a Germany appointment in Manchester. Business trip, my company wants me there in 9 days. VFS shows nothing for 3 weeks. Help?
> **[Wed 14:19] BP:** Hi! We can start hunting straight away. Honest framing first: 9 days is tight. We can often beat the visible calendar via cancellation slots, but I can't promise a date will open in that window, and once booked the consulate still needs standard processing time. Are you flexible on the centre city?
> **[Wed 14:26] Customer:** I can do Manchester or Birmingham. It's urgent.
> **[Wed 14:31] BP:** Good, two cities doubles our chances. Fee is £49 for the express slot-hunt, our charge only. The VFS Germany appointment fee, the €90 visa fee and any optional VFS services are separate and paid to them directly. Shall I set you up?
> **[Wed 14:38] Customer:** Yes. But if you genuinely can't find anything in time, what happens?
> **[Wed 14:44] BP:** Fair question. If we secure no slot inside your usable window, we refund the £49 in full. No slot, no charge kept. Payment link: [link].
> **[Wed 14:59] Customer:** Paid.
> **[Wed 15:01] BP:** Received, thank you. Watching Manchester and Birmingham now. I'll ping you the instant anything usable appears.
> **[Thu 17:40] BP:** Birmingham cancellation just surfaced for next Tue 09:00, that's within your window. Holding it [photo]. Confirm and I'll finalise.
> **[Thu 18:02] Customer:** Confirm, thank you so much.
> **[Fri 09:12] BP:** Finalised this morning, confirmation attached [file]. You're booked Tue 09:00 Birmingham. Please have every document ready; the appointment doesn't guarantee approval. Safe trip.

_Elapsed lead → delivery: ~2 days._

**Customer C — Nigerian national, ILR holder, family of four to Italy, no rush**
> **[Mon 11:20] Customer:** Good morning. Family holiday to Italy in October, four of us. The IVL portal has appointments but they're a bit far out. Can you get something earlier?
> **[Mon 11:35] BP:** Good morning! Yes, though I want to be straight with you: October is a fair way off, so there's a decent chance you can book a suitable slot yourself at no charge. Our express service earns its keep when the calendar is jammed or your deadline is tight. What's the earliest you'd actually want the appointment?
> **[Mon 11:49] Customer:** Ideally August so we're not rushing documents.
> **[Mon 11:58] BP:** Let me check live availability for a family of four in your centre before you pay anything.
> **[Mon 12:14] BP:** There are August slots already visible for four applicants in London IVL, no queue-jumping needed. Honestly you don't need us for this one. I'd book it directly and keep your £49. Want me to walk you through the group-booking screen?
> **[Mon 12:26] Customer:** That's very honest, thank you. Yes please walk me through it.
> **[Mon 12:31] BP:** Happy to. When booking four, add all applicants under one appointment before selecting the time, or the portal splits you across slots. Screenshot each step and I'll confirm you've got it right, no charge for the pointers.
> **[Mon 12:48] Customer:** Booked all four for 12 Aug. Really appreciate you not just taking my money.
> **[Mon 12:52] BP:** That's the way we work. If the document stage feels daunting later, our checklist review is there. Enjoy Italy.

_Elapsed lead → delivery: same day (advised self-serve, no charge)._

**Customer D — Filipino national, student dependant, Netherlands, mid urgency**
> **[Thu 16:10] Customer:** hello, i need appointment for Netherlands visa. i'm a student's dependant here. the slots in london keep disappearing before i can click
> **[Thu 16:33] BP:** Hi! That vanishing-slot problem is exactly what we handle. Quick note on how it works: we watch and grab releases fast, but we never guarantee a specific date and the consulate alone decides the visa. Which centre, and when do you need to travel?
> **[Thu 16:48] Customer:** London. travel is in about 5 weeks, visiting family in Amsterdam
> **[Thu 16:55] BP:** Five weeks gives us a comfortable window. Fee is £49 for the express slot-hunt, our handling only. The VFS Netherlands appointment fee and the €90 visa fee are separate, paid directly to the centre. Want to go ahead?
> **[Thu 17:02] Customer:** yes ok. can you also do the visa form for me?
> **[Thu 17:08] BP:** The £49 covers the slot only. The application form is a different service if you want it, but many dependants complete it fine themselves. Let's lock the slot first. Payment link: [link].
> **[Thu 17:21] Customer:** paid :)
> **[Thu 17:24] BP:** Received, thank you. Hunting London releases now. I'll message the second one holds still long enough to grab.
> **[Sat 10:15] BP:** Got one. London next Wed 13:40, held and booking [photo]. Reply to confirm and I'll finalise.
> **[Sat 10:41] Customer:** yes please confirm!
> **[Sat 10:52] BP:** Confirmed [file]. Booked Wed 13:40 London. Bring your full document set; the slot secures the appointment only. If you'd like help with the form later, just say.

_Elapsed lead → delivery: ~2 days._

**Customer E — Egyptian national, self-employed, Spain, recent overstay concern**
> **[Tue 10:05] Customer:** Hi, I want an express Spain appointment please. Barcelona trip next month. One thing, I overstayed a Schengen visa by about 3 weeks last year, is that a problem for booking?
> **[Tue 10:22] BP:** Hi, thanks for being upfront, that really helps. Booking the appointment itself isn't blocked by history. But I won't gloss over it: a recent overstay is something the consulate weighs, and we can't influence or predict that decision. We can secure the slot; we can't affect the outcome. Still want to proceed knowing that?
> **[Tue 10:34] Customer:** Yes, I understand it's their call. I'd rather apply properly than not at all.
> **[Tue 10:41] BP:** Sensible approach. Honestly, before you pay for express, is the standard calendar workable? Barcelona-bound means BLS/Spain centre. If slots are visible for next month you may not need express at all.
> **[Tue 10:48] Customer:** They're there but only in 4 weeks, my trip is week 5, cutting it fine.
> **[Tue 10:55] BP:** That's genuinely tight given processing time after the appointment. £49 express to pull an earlier slot is justified here. Our fee only; the BLS Spain appointment fee and €90 visa fee are separate. I'd also gently suggest a strong cover letter addressing the overstay, though that's your call. Proceed with the slot?
> **[Tue 11:03] Customer:** Yes, and I'll write a cover letter. Link please.
> **[Tue 11:06] BP:** [link]. Once cleared I'll hunt earlier London slots for Spain today.
> **[Tue 11:19] Customer:** Paid.
> **[Wed 12:30] BP:** Earlier slot secured: Mon next week, 10:00 [file]. That buys you more processing margin. Remember, the earlier appointment helps your timeline, not the decision itself. Good luck with the application.

_Elapsed lead → delivery: ~1 day._

**Customer F — Pakistani national, retired, Greece, deadline genuinely impossible**
> **[Fri 15:40] Customer:** Assalam o alaikum. I need urgent Greece appointment. I fly to Athens in 4 days for a wedding. Please arrange express booking.
> **[Fri 15:58] BP:** Wa alaikum salam, and thank you for reaching out. I have to be completely honest with you rather than take a fee I don't think will help. Four days is not realistic for a Greece Schengen visa. Even if we secured an appointment tomorrow, the consulate needs processing time after it, usually well beyond four days, and we can never rush their decision.
> **[Fri 16:07] Customer:** So there is no way at all?
> **[Fri 16:14] BP:** Not honestly, no, not for travel in four days. Anyone promising you a guaranteed visa in that window would be misleading you. I won't charge you £49 for something I can't reasonably deliver.
> **[Fri 16:20] Customer:** I appreciate your honesty. The wedding is fixed though.
> **[Fri 16:29] BP:** I understand, and I'm sorry. If the family has any future events, come to us with 4 to 6 weeks and we'll have real room to work. For now, if your dates have any flex at all, I'm glad to check availability, but I won't sell you false hope.
> **[Fri 16:38] Customer:** No flex sadly. Thank you for not just taking the money.
> **[Fri 16:42] BP:** Of course. Wishing the couple well, and we're here whenever the timing allows.

_Elapsed lead → delivery: none (declined honestly, no charge)._

**Customer G — Brazilian national, dependant on skilled-worker spouse, Portugal, flexible**
> **[Mon 13:15] Customer:** Oi! Hi. I need a Portugal appointment for a Lisbon trip. My husband is on a skilled worker visa, I'm his dependant. Slots in London are all taken for weeks. Can you help me jump the queue?
> **[Mon 13:38] BP:** Oi, hello! We can help find an earlier slot, though a small clarification: we don't jump any queue or skip anyone, we catch cancellations and new releases faster than manual checking. And the consulate alone decides the visa. When's the trip?
> **[Mon 13:50] Customer:** In about 6 weeks. No fixed date, quite flexible.
> **[Mon 13:56] BP:** Flexibility is ideal, it lets us grab the first good opening. Fee is £49 for the express slot-hunt, our handling only. The VFS Portugal appointment fee and the €90 visa fee are separate and paid to the centre directly. Shall I proceed?
> **[Mon 14:04] Customer:** Yes please. Just me, one applicant.
> **[Mon 14:07] BP:** Lovely. Payment link: [link]. Once it clears I'll start watching London Portugal releases today.
> **[Mon 14:19] Customer:** Done, paid!
> **[Mon 14:22] BP:** Received, thank you. Monitoring now. With your flexibility I expect something soon.
> **[Wed 09:30] BP:** Two options opened: this Fri 09:40 or next Mon 14:10, both London [photo]. Which suits you?
> **[Wed 10:02] Customer:** Friday please, sooner the better!
> **[Wed 10:14] BP:** Booked Fri 09:40, confirmation attached [file]. Bring your full dependant document set; the slot secures the appointment only, not the outcome. Enjoy Lisbon when it's all done!

_Elapsed lead → delivery: ~2 days._

## 14. Entry-requirements check — £19

> ⏱ First reply ≤ 2 hrs · Delivery Same day (≤ 24 hrs) · Typical span Same day – 1 day

**Customer A — British citizen, salaried, long weekend in France**
> **[Mon 10:02] Customer:** Hi, I'm British, flying to Paris on Friday for a 3-day trip. Do I actually need a visa? Getting mixed answers online.
> **[Mon 10:19] BP:** Hi, happy to check this properly for you. Can you confirm three things: your passport nationality, your passport issue and expiry dates, and whether you've had any Schengen stays in the last 180 days?
> **[Mon 10:34] Customer:** British passport, issued March 2019, expires March 2029. No trips to Europe since 2023.
> **[Mon 10:41] BP:** Thanks. On the face of it you likely fall under visa-free short-stay rules, but I want to verify passport validity, the 90/180 rule and the new EU entry checks against your exact dates before you book anything. Our Entry-requirements check is £19. That's our service fee only; there's no government or embassy fee involved on a visa-free trip. You get a written summary today.
> **[Mon 10:47] Customer:** Go on then, that's worth £19 for peace of mind.
> **[Mon 10:49] BP:** Here's the secure payment link: [link]. Once it's through I'll start the check.
> **[Mon 11:12] Customer:** Paid.
> **[Mon 11:14] BP:** Received, thank you. Working on it now, you'll have the summary well within 24 hours.
> **[Mon 15:38] BP:** Done. Summary attached [file]. Headlines: your passport meets the "issued within 10 years / valid 3+ months beyond departure" rule, you're visa-exempt for a 3-day tourist stay, and you have full 90 days available in the current 180-day window. Note the new EU entry system now records entry biometrically, so allow extra time at the border. No visa needed, book with confidence.
> **[Mon 15:46] Customer:** Brilliant, really clear. Thanks!
> **[Mon 15:48] BP:** Any time. Safe travels. If you'd like, we also do a quick travel-document review before departure, but honestly for this trip you're all set.

_Elapsed lead → delivery: same day (≈5.5 hrs)._

**Customer B — Indian national, skilled-worker visa, business trip to Germany**
> **[Tue 08:41] Customer:** Good morning. Indian passport, living in Manchester on a Skilled Worker visa. Need to go to Munich for a work conference next month. What do I need?
> **[Tue 09:06] BP:** Morning. As an Indian national you'll need a Schengen visa for Germany, and a business trip means the Business category rather than tourist. Before I confirm the exact requirements, can you tell me: your BRP/eVisa status and its expiry, and whether the conference organiser or your UK employer is providing an invitation letter?
> **[Tue 09:20] Customer:** Skilled Worker valid until 2027. Employer will write a letter, and the German host company can send an invite too.
> **[Tue 09:31] BP:** Good, that's a strong file. Our Entry-requirements check is £19 and gives you a written list of exactly what Germany's Business route wants for someone in your situation, including the UK-residence documents. To be clear, that £19 is our service fee only. The visa fee itself, plus the appointment-centre fee, are separate and paid to them, not us. I'll note those amounts in the summary so there are no surprises.
> **[Tue 09:38] Customer:** That's fine, please send the link.
> **[Tue 09:39] BP:** Here you go: [link].
> **[Tue 10:55] Customer:** Done.
> **[Tue 10:58] BP:** Received, thank you. I'll have your tailored requirements list back to you today.
> **[Tue 16:20] BP:** Summary ready [file]. It covers: valid passport, two visas including your Skilled Worker proof and recent UK payslips/bank statements, both invitation letters, travel and accommodation bookings, travel insurance with 30,000 EUR medical cover, and the biometrics appointment. I've flagged the current visa fee and centre fee as separate third-party costs. One honest note: apply as early as your dates allow, because I can't influence how quickly the consulate decides.
> **[Tue 16:34] Customer:** Very thorough, thank you. Can you also help me actually book the slot?
> **[Tue 16:41] BP:** Yes, that's a separate service and I'll send details, no pressure. For today, your requirements are confirmed.

_Elapsed lead → delivery: same day (≈7.5 hrs)._

**Customer C — Nigerian national, self-employed, honeymoon in Italy**
> **[Wed 12:10] Customer:** Hello. Me and my wife are Nigerian, we have ILR in the UK. Planning our honeymoon in Italy in October. What are the entry requirements?
> **[Wed 12:33] BP:** Congratulations! With ILR and Nigerian passports you'll each need an Italian Schengen tourist visa. Quick questions: are you both self-employed or salaried, and roughly how many nights and cities in Italy?
> **[Wed 13:05] Customer:** I'm self-employed (I run a small logistics company), my wife is salaried. About 10 nights, Rome and Amalfi.
> **[Wed 13:12] BP:** Perfect, that's very doable. The self-employed side just needs a slightly different set of financial documents, which I'll spell out. Our Entry-requirements check is £19 total for both of you on one itinerary. That's our fee only; the Italian visa fee and the appointment-centre fee are separate and go to them, and I'll list those amounts for you.
> **[Wed 13:40] Customer:** Okay let's do it.
> **[Wed 13:42] BP:** Secure link here: [link]. Once paid I'll build your list.
> **[Wed 14:15] Customer:** Paid, thank you.
> **[Wed 14:18] BP:** Got it. You'll have the summary today.
> **[Thu 09:26] BP:** Morning, apologies it rolled to today. Summary attached [file]. Key items for a couple: valid passports plus ILR proof, your self-employed pack (company registration, business bank statements, accountant's letter, tax records), her employment letter and payslips, joint itinerary, hotel bookings for Rome and Amalfi, return flights, and travel insurance with 30,000 EUR cover each. I've marked the Italian visa fee and centre fee as separate third-party costs. Apply together on one appointment where possible.
> **[Thu 09:52] Customer:** This is exactly what we needed. Thank you so much.
> **[Thu 09:58] BP:** My pleasure, and congratulations again. Whenever you're ready to prepare the documents we can review them for you.

_Elapsed lead → delivery: 1 day (≈21 hrs, overnight)._

**Customer D — Pakistani national, student, recent overstay (edge case: manage expectations)**
> **[Thu 17:44] Customer:** Hi. Pakistani passport, I'm a student in London. I want to go to Spain for Christmas but I have to be honest, I overstayed a Schengen visa by about 3 weeks last year before I came to the UK. Can you still do my entry-requirements check?
> **[Thu 17:52] BP:** Thanks for being upfront, that genuinely helps. I can still do the check, but I won't pretend the overstay isn't a factor. A recorded overstay can lead to extra scrutiny or refusal, and no service, ours included, can promise an approval. The decision is entirely the Spanish consulate's.
> **[Fri 08:30] Customer:** I understand. So is it even worth the £19?
> **[Fri 08:47] BP:** Honest answer: the £19 check will tell you exactly what to prepare and how to address the prior overstay in your file, which is useful. But it cannot remove the overstay from the record or improve your odds beyond honest, well-organised paperwork. If your only goal was a guaranteed yes, I'd tell you to save your money. If a clear, realistic requirements list helps you decide whether to apply, it's worth it.
> **[Fri 09:15] Customer:** That's fair. I'd rather know where I stand. Let's do the check.
> **[Fri 09:17] BP:** Right approach. Here's the link: [link]. Our £19 is service only; the Spanish visa and centre fees are separate and non-refundable by them even if refused, which is exactly why I want you going in eyes open.
> **[Fri 09:40] Customer:** Paid.
> **[Fri 09:43] BP:** Received. I'll build this carefully today.
> **[Fri 15:10] BP:** Summary attached [file]. It lists Spain's tourist requirements as a student (enrolment letter, student finance proof, accommodation, insurance, return flight) and, importantly, a section on how to present the previous overstay honestly with any evidence of its cause. I've stated plainly that this improves clarity, not certainty. Please read the expectations note in bold before you decide to apply.
> **[Fri 15:29] Customer:** Thank you for being straight with me. I'll think about it properly now.
> **[Fri 15:33] BP:** That's the right call. Whatever you decide, apply honestly. If you go ahead, we're here.

_Elapsed lead → delivery: 1 day (overnight gap, ≈21 hrs)._

**Customer E — Filipino national, dependant visa, family group to Netherlands**
> **[Mon 09:30] Customer:** Hello po. We are Filipino, I'm on a dependant visa (husband is a nurse here). We want to take our 2 kids to the Netherlands in spring. What do we need?
> **[Mon 09:48] BP:** Hello, lovely trip to plan. As Filipino nationals you'll each need a Netherlands Schengen tourist visa, children included. Can you confirm: your dependant visa expiry, the children's ages, and whether both parents are travelling?
> **[Mon 10:22] Customer:** Dependant visa valid till 2028. Kids are 6 and 9. Yes both of us are going.
> **[Mon 10:31] BP:** Great, a full-family application. Our Entry-requirements check is £19 for the whole family on one itinerary, and I'll include the specific documents Dutch posts want for minors, which trip people up most. To be clear that £19 is our service fee; the Netherlands visa fees per person and the centre fee are separate third-party costs. Note children under 6 are often exempt from the visa fee, but yours are 6 and 9, so I'll confirm the exact amounts in the summary.
> **[Mon 10:40] Customer:** Yes please, send link.
> **[Mon 10:41] BP:** Here: [link].
> **[Mon 12:05] Customer:** Paid po, salamat.
> **[Mon 12:08] BP:** Received, thank you. Family summary coming today.
> **[Mon 17:05] BP:** Summary attached [file]. For the four of you: valid passports, both parents' dependant-visa proof and the sponsor's employment letter/payslips, the children's birth certificates, school absence letters, and both parents' consent as they're minors, plus family accommodation, return flights and insurance with 30,000 EUR cover each. Visa and centre fees marked separate. I've added a minors checklist so nothing's missed at the appointment.
> **[Mon 17:24] Customer:** This is so helpful, maraming salamat!
> **[Mon 17:27] BP:** You're very welcome. When the time comes we can review the kids' documents too, they're the fiddly part.

_Elapsed lead → delivery: same day (≈7.5 hrs)._

**Customer F — Egyptian national, retired, river cruise (edge case: service not needed)**
> **[Wed 11:15] Customer:** Hi there. I'm Egyptian, retired, living in the UK on ILR. I already hold a valid multiple-entry Schengen visa from a trip in May, expires next February. I've booked a Danube cruise starting in Austria in November. Do I need your entry-requirements check?
> **[Wed 11:29] BP:** Let me be honest before you spend anything. If you already hold a valid multiple-entry Schengen visa that covers your November dates, and you'll stay within its allowed days and the 90/180 limit, you likely don't need a fresh requirements check at all. Can I confirm two things: does the visa clearly say "MULT", and how many days have you already used on it this 180-day period?
> **[Wed 11:52] Customer:** Yes it says MULT. I used 8 days in May, nothing since.
> **[Wed 12:04] BP:** Then you're almost certainly fine and I'm not going to sell you a £19 check you don't need. With a valid MULT visa, only 8 days used, and a November cruise, you're inside both the validity and the 90/180 allowance. Just carry the visa, passport, cruise itinerary and insurance. Austria being your entry point matches good practice since it's your first Schengen stop this trip.
> **[Wed 12:10] Customer:** Well that's refreshing. So no charge at all?
> **[Wed 12:13] BP:** No charge, this was a quick eligibility sanity-check and you passed it. The only thing I'd genuinely watch: don't let total Schengen days across all trips exceed 90 in any 180-day window before your visa expires in February.
> **[Wed 12:19] Customer:** Understood. I'll happily recommend you to my cruise friends.
> **[Wed 12:22] BP:** Much appreciated. If any of them are unsure about their own eligibility, that's exactly when the £19 check earns its keep. Enjoy the Danube.

_Elapsed lead → delivery: same day (≈1 hr, no sale — declined honestly)._

**Customer G — Brazilian national, self-employed, tight deadline to Portugal (edge case: deadline reality-check)**
> **[Sat 09:05] Customer:** Hi! Brazilian passport, self-employed, live in the UK on a Global Talent visa. I need to be in Lisbon in 8 days for a client pitch. Can you sort my entry requirements fast?
> **[Sat 09:16] BP:** I can do the requirements check quickly, but I have to be straight about the timeline. As a Brazilian national resident in the UK you need a Portuguese Schengen visa, and that means a biometrics appointment plus consulate processing. Eight days is extremely tight, and I can't speed up the consulate's decision or guarantee an appointment exists in time.
> **[Sat 09:34] Customer:** Ah. I assumed express would make it faster.
> **[Sat 09:41] BP:** Common assumption, so let me clear it up. Express, where we offer it, speeds our handling and slot-hunting, never the government's decision or whether a slot is physically available. For an 8-day window I won't take your money pretending it's likely. What the £19 check can do today is confirm the exact requirements and realistic appointment lead times, so you can decide whether to push the pitch date or try anyway.
> **[Sat 09:58] Customer:** Okay, that's actually useful. Do the check, and tell me honestly if 8 days is dead.
> **[Sat 10:01] BP:** Deal. Link here: [link]. £19 is our service fee only; Portuguese visa and centre fees are separate. I'll include current appointment lead times so you can judge realistically.
> **[Sat 10:20] Customer:** Paid.
> **[Sat 10:23] BP:** Received. Fast turnaround on this one given your deadline.
> **[Sat 13:15] BP:** Summary attached [file]. Requirements for a Brazilian Global Talent holder are straightforward (passport, UK-residence proof, self-employed financials, invitation from the Lisbon client, insurance, accommodation, return flight). The hard truth is in bold: current appointment lead times for Portugal exceed your 8-day window, so this specific date is very unlikely. My recommendation: if the client can move the pitch by 2 to 3 weeks, you're in good shape; if not, consider a video pitch now and travel later once the visa's secured.
> **[Sat 13:32] Customer:** Gutting but I'd rather know now than waste the visa fee. Thank you for the honesty.
> **[Sat 13:36] BP:** That's exactly why I flagged it early. When you have a firm later date, come back and we'll line up the whole thing properly.

_Elapsed lead → delivery: same day (≈4.5 hrs)._

## 15. Travel insurance — No charge

> ⏱ First reply ≤ 2 hrs · Delivery Instant (partner link) · Typical span Minutes

**Customer A — British citizen, retired, driving trip to France**
> **[Mon 10:02] Customer:** Hi, we're a retired couple driving to France and Belgium next month. Do we actually need travel insurance for a Schengen trip on a British passport?
> **[Mon 10:41] BP:** Hello! For a short stay you enter visa-free on a British passport, so there's no visa requirement to show a policy. That said we'd still strongly recommend cover for medical costs and repatriation abroad, especially driving. It's your call, not a legal must here.
> **[Mon 10:44] Customer:** Makes sense. We'd want something solid then.
> **[Mon 10:52] BP:** We don't sell insurance and take no fee for this. We introduce you to an FCA-authorised partner and you buy directly from them. Ask for cover of at least EUR 30,000 for medical plus repatriation, valid across all Schengen countries for your full travel dates. Here's the partner link: partner.beyondpassports.co.uk/insure
> **[Mon 11:20] Customer:** Perfect, we'll sort it there. Thank you.
> **[Mon 11:24] BP:** Enjoy the drive. If you ever need a visa-required trip later, our single-document check (£25) is there, but you won't need it for France. Safe travels!

_Elapsed lead → delivery: about 1 hour 20 minutes._

**Customer B — Indian national, skilled worker, visa applicant for Germany**
> **[Tue 09:12] Customer:** I'm applying for a German Schengen visa from the UK. The checklist says I need travel insurance. Can you provide it?
> **[Tue 09:29] BP:** Hi! We don't sell the policy ourselves and take no fee for helping. We introduce you to an FCA-authorised partner and you buy the cover directly from them. For a German visa the consulate needs a policy covering minimum EUR 30,000 medical plus repatriation, valid for every Schengen country across your exact travel dates.
> **[Tue 09:33] Customer:** My trip is 14 to 22 August, Germany only. Does it still need to cover all Schengen countries?
> **[Tue 09:40] BP:** Yes, the certificate should state coverage across all Schengen states even if you only visit Germany, and the dates must span 14 to 22 August fully. The partner's certificate is formatted for visa applications: partner.beyondpassports.co.uk/insure
> **[Tue 09:44] Customer:** Great. And this is separate from the visa fee?
> **[Tue 09:47] BP:** Correct. The insurance premium goes to the insurer, the German visa fee and VFS appointment-centre fee are separate charges to the consulate/centre, and our guidance here carries no charge.
> **[Tue 09:51] Customer:** Understood, buying now.
> **[Tue 09:54] BP:** Nice one. If you'd like a second pair of eyes on the whole file before submission, our refusal-risk check is £29. Optional. Good luck with the application!

_Elapsed lead → delivery: about 40 minutes._

**Customer C — Nigerian national, ILR holder, family group to Italy**
> **[Wed 11:30] Customer:** Hello, I need travel insurance for 5 people (me, wife, 3 kids) for an Italian Schengen visa. Can you get us a family policy?
> **[Wed 11:58] BP:** Hi! Happy to point you the right way. We don't sell policies and charge no fee for this. We introduce you to an FCA-authorised partner who can quote a family/group policy, and you buy directly from them.
> **[Wed 12:03] Customer:** What exactly should it say for Italy?
> **[Wed 12:11] BP:** Each traveller must be covered for at least EUR 30,000 medical plus repatriation, valid across all Schengen countries for your full dates. Make sure all five names and dates of birth appear on the certificate, as the consulate checks each applicant. Link: partner.beyondpassports.co.uk/insure
> **[Wed 12:16] Customer:** Do the kids need their own or are they on mine?
> **[Wed 12:22] BP:** A family policy usually lists all members on one certificate with each name shown. Confirm with the partner that every child is named and each meets the EUR 30,000 minimum. That's what the consulate wants to see.
> **[Wed 12:30] Customer:** Perfect, thank you.
> **[Wed 12:34] BP:** Pleasure. Insurance premium is paid to the insurer, and the Italian visa and appointment-centre fees are separate. If you want the family bundle reviewed before you submit, the refusal-risk check is £29. Safe trip!

_Elapsed lead → delivery: about 1 hour._

**Customer D — Pakistani national, student, tight deadline for Netherlands**
> **[Thu 16:40] Customer:** URGENT. My Netherlands appointment is tomorrow morning 9am and I have no travel insurance yet. Can you sort it tonight??
> **[Thu 16:52] BP:** Deep breath, this one is very doable. The insurance itself is instant online, so you can have the certificate within minutes. We don't sell it and take no fee. Here's the FCA-authorised partner: partner.beyondpassports.co.uk/insure
> **[Thu 16:55] Customer:** What do I choose so it's accepted?
> **[Thu 16:59] BP:** Minimum EUR 30,000 medical plus repatriation, valid across all Schengen countries, and dates covering your entire trip. Buy it, then download the PDF certificate and print it for the morning.
> **[Thu 17:06] Customer:** Bought it, certificate downloaded. That was fast.
> **[Thu 17:10] BP:** Well done. To be clear, we can't influence the appointment slot or the consulate's decision, only make sure this piece is right. Print it tonight so you're not relying on the centre's printer.
> **[Thu 17:12] Customer:** Printing now. Thank you so much.
> **[Thu 17:15] BP:** You're set. If you want a quick single-document check (£25) on any other item before 9am, message me a photo. Good luck tomorrow!

_Elapsed lead → delivery: about 35 minutes._

**Customer E — Filipino national, dependant visa holder, already has annual policy for Spain**
> **[Fri 13:05] Customer:** Hi, I already have a yearly worldwide travel policy from my bank. Do I need to buy anything from you for my Spain visa?
> **[Fri 13:33] BP:** Possibly not, which is good news. Can you check three things on your existing certificate: does it cover at least EUR 30,000 for medical plus repatriation, is it valid across all Schengen countries, and does it cover your full travel dates?
> **[Fri 13:44] Customer:** It says EUR 50,000 medical and repatriation, worldwide including Europe, valid all year. Trip is in October.
> **[Fri 13:49] BP:** Then your existing policy already meets the Spanish requirement. You don't need to buy a new one, and we won't push you to. Just print the certificate and the policy wording page for your file.
> **[Fri 13:52] Customer:** Oh great, so no need for the partner link at all?
> **[Fri 13:55] BP:** No need in your case. If the certificate doesn't clearly state repatriation, ask your bank for a confirmation letter that names it, as some consulates are strict on that word.
> **[Fri 13:58] Customer:** Will do. Thanks for being honest!
> **[Fri 14:01] BP:** Always. If you'd like the certificate wording checked against the Spain checklist, single-document check is £25, but from what you've described you're fine. Enjoy Spain!

_Elapsed lead → delivery: about 55 minutes (no purchase needed)._

**Customer F — Egyptian national, self-employed, business trip to Austria**
> **[Sat 09:20] Customer:** Need travel insurance for an Austrian business visa. I'm self-employed. Does that change the insurance side?
> **[Sat 09:38] BP:** Morning! Self-employment affects your financial documents, but not the insurance requirement itself. The policy needs the same thing for anyone: EUR 30,000 minimum medical plus repatriation, all Schengen countries, full dates.
> **[Sat 09:44] Customer:** Can you issue it? I'd rather one company handles everything.
> **[Sat 09:52] BP:** We don't issue or sell insurance and we take no fee here. We introduce you to an FCA-authorised partner and you buy directly, so you deal with a regulated insurer for the cover. Link: partner.beyondpassports.co.uk/insure
> **[Sat 09:57] Customer:** Fine. My dates might shift by a few days, is that a problem?
> **[Sat 10:03] BP:** Buy it once your dates are firm, or pick the widest range you're sure of. If dates move outside the policy window the consulate can reject it, so the certificate must cover the actual entry and exit days.
> **[Sat 10:09] Customer:** Got it, I'll confirm dates first then buy.
> **[Sat 10:12] BP:** Sensible. Remember the premium is separate from the Austrian visa fee and any appointment-centre charge. When your dates lock, our refusal-risk check (£29) can sanity-check the self-employed financials too. Have a good weekend!

_Elapsed lead → delivery: about 50 minutes._

**Customer G — Ghanaian national, applicant with recent policy that falls short for Greece**
> **[Mon 14:15] Customer:** I bought a cheap travel policy online for my Greece visa but the agent at the centre said it might not be accepted. Can you check?
> **[Mon 14:40] BP:** Let's look. Can you send the coverage amount for medical, whether it says repatriation, the countries listed, and the dates?
> **[Mon 14:48] Customer:** It says EUR 15,000 medical, Europe, no mention of repatriation, dates match my trip.
> **[Mon 14:55] BP:** That's the problem: EUR 15,000 is below the EUR 30,000 minimum and there's no repatriation stated. The consulate would likely reject it, so the centre agent was right to flag it.
> **[Mon 14:58] Customer:** So I wasted that money?
> **[Mon 15:04] BP:** Check the seller's cancellation or cooling-off terms, you may be able to get a refund on the shortfall policy. Either way you'll need a compliant one: EUR 30,000 plus medical, repatriation named, all Schengen countries, full dates. FCA-authorised partner here: partner.beyondpassports.co.uk/insure
> **[Mon 15:11] Customer:** Ok, buying the proper one now and I'll chase the old one for a refund.
> **[Mon 15:15] BP:** Good plan. We take no fee for this and the premium goes to the insurer. If you'd like the whole Greece file checked so nothing else gets flagged at the centre, the refusal-risk check is £29. You've got this.

_Elapsed lead → delivery: about 1 hour._

---

*Prices are indicative standard-market service fees and can be changed centrally. Government, embassy, apostille, translation and appointment-centre fees are always separate and paid to those providers. Nothing here promises a visa outcome — the decision is the authority.*
