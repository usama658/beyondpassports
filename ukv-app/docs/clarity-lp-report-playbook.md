# Clarity LP Report Playbook — daily issues analysis & optimization (Beyond Passports)

Daily Microsoft Clarity review of the landing pages: find friction, log it, propose fixes, verify.
Clarity is live (consent-gated, task #369). Target LPs:
`/schengen-visa-agent`, `/schengen-visa-help`, `/honest-schengen-visa-service`,
`/schengen-visa-refused`, `/schengen-visa-refusal-risk`, `/schengen-visa-appointment`, home.

---

## Daily checks (per key LP)

- **Dead clicks** — taps on elements that do nothing (fake buttons, non-links). Log each.
- **Rage clicks** — repeated frustrated clicks = broken/confusing element. Log location.
- **Quick-backs** — landed → left in <10s (wrong-fit traffic or weak above-fold). Note the source.
- **Scroll depth** — % reaching the CTA / pricing / checklist band. Flag drop-off before the CTA.
- **Excessive scrolling** — hunting = poor hierarchy. Note the section.
- **Session recordings** — watch 3–5 (filter: exits, rage clicks, mobile). One-line observation each.
- **Top drop-off point** — where sessions end most, per LP.
- **Device split** — mobile vs desktop friction (most traffic is mobile).

## Metrics to pull

Sessions · avg scroll depth · avg time · quick-back % · rage-click count + top element ·
dead-click count + top element · CTA-reach % (scroll to WhatsApp / checklist CTA).

## Daily optimization report (one per day)

- **Top 3 issues** — element · page · Clarity signal · # sessions affected
- **Hypothesis** per issue (why)
- **Proposed fix** (copy tweak / move CTA / fix dead element / mobile spacing)
- **Priority** (High = blocks conversion / Med / Low)
- **Status** (Observed → Fix proposed → Shipped → Verified next-day)

Row format:
`Date · Page · Issue · Clarity signal · Sessions · Hypothesis · Fix · Priority · Status`

## Weekly rollup (Fri)

Recurring issues · fixes shipped · before/after scroll + CTA-reach · next week's test list.

## Guardrails

- Ship one change at a time so Clarity shows a clean before/after.
- Don't chase a single weird session — look for the pattern across sessions.
- Keep every copy fix compliant (not the government, no guaranteed outcome).
