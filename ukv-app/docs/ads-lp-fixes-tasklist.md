# Ads Paused — LP Fixes & New Variations (Trello task list)

Triggered when a Google Ad gets paused/disapproved. Visa services are a sensitive category —
pauses are usually **misleading claims / unsupported guarantees / trust & transparency**.

## 1. Diagnose (first)
- [ ] Read the exact pause reason in Google Ads (policy vs quality vs disapproved). Screenshot.
- [ ] Map which LP + ad got flagged.
- [ ] Note the policy category.

## 2. LP compliance fixes (likely cause)
- [ ] Strip fabricated claims on `lp-trust` — "Frankfurt office", "UK & Europe Registered", fake
      approval / "applications submitted" stats.
- [ ] Remove/verify guarantee wording — no "get you the visa", no guaranteed multiple-entry.
- [ ] Fix fake `lp-appt-board` — hardcoded "Updated daily" wait-times → real data or remove.
- [ ] Trust signals every LP needs: "not the government" disclaimer above fold, real company name +
      no. (17331903), address, working legal/privacy/contact links.
- [ ] Testimonials — mark fabricated off or replace with consented anonymised.
- [ ] Match ad ↔ LP relevance (headline delivers the ad's promise).

## 3. New LP variations (rotation stock)
- [ ] A — "Refusal recovery" angle → `/schengen-visa-refused`.
- [ ] B — "Speed / appointment" angle → nearest-slot + honest timeline.
- [ ] C — "Done-for-you prep" angle → checklist + cover letter + booking.
- [ ] Each: unique H1, single CTA, disclaimer strip, mobile-first, no fabricated claims, own URL.
- [ ] Reuse `lp-chrome` partials + Outfit fonts (no CDN).

## 4. QA + relaunch
- [ ] Compliance grep (guarantee, em-dash, fabricated stats, Google Fonts CDN).
- [ ] Mobile render + Core Web Vitals.
- [ ] Legal/privacy/contact links resolve.
- [ ] Submit for Google review / appeal with the fixed LP.
- [ ] Wire Clarity + conversion events on each variation.
- [ ] A/B rotation once approved.

**Labels:** Blocker · Compliance · New-LP · QA.
**Golden rule:** every LP = not-the-government + no guaranteed outcome + real company details.
