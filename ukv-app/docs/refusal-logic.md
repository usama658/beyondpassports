# Refusal verdict logic (REFLOGIC) — LOCKED

Drives the verdict shown on the refusal flow's **details** step in
`ukv-app/public/lp-v2-preview.html` (config: `REFBASE`, `REFMOD`, `REFCOPY`,
`REFLEAN`, resolver `refTier()`; applied in `mDetailsFill()`).

Verdict = `base(reason) + modifier(country difficulty)`, clamped to 3 tiers.
`Not sure` = always **Assess**. Overstay / Documents-in-strict never rise above
their cap. Non-refusal (eligibility) flows are unaffected.

## Reason base score

| Reason (tile) | Base | Real refusal ground (Art. 32) |
|---|---|---|
| Funds | 3 | Insufficient means of subsistence |
| Travel plan | 3 | Purpose/conditions not justified or unreliable |
| Insurance | 3 | Travel medical insurance not valid/sufficient |
| Return | 2 | Intention to leave could not be ascertained |
| Documents | 2 | Doc reliability doubted (not fraud) |
| Overstay | 1 | Prior overstay / SIS alert |
| Not sure | — | Assess after reading the letter |
| *(False/forged docs)* | decline | Not taken on — fraud ground |

## Country difficulty modifier (from `config ukv.slots` / `window.CTRY` difficulty enum)

| Tier | Modifier | Countries |
|---|---|---|
| straightforward | +1 | France, Greece, Portugal |
| standard | 0 | Spain, Germany, Austria |
| strict | −1 | Italy, Netherlands, Switzerland |
| high | −2 | Belgium |

## Final score → verdict tier

`score = clamp(base + mod, 1..3)` → 3 = 🟢 green, 2 = 🟡 amber, 1 = 🔴 red.

| Score | Badge | Heading | Reason chip |
|---|---|---|---|
| 🟢 3 | Recoverable | "Good news. This looks recoverable." | Workable |
| 🟡 2 | Fixable with work | "Your case needs a proper look." | Needs work |
| 🔴 1 | High risk | "This one is harder. We will be honest." | High risk |
| ⚪ Not sure | Assessment needed | "Send us the letter and we will tell you." | Read letter first |
| ⛔ False docs | We cannot help | "We cannot take fraud-based cases." | (no CTA — honest stop) |

## Verdict matrix (reason × country tier)

| Reason | Straightforward | Standard | Strict | High |
|---|---|---|---|---|
| Insurance | 🟢 | 🟢 | 🟢 | 🟡 |
| Funds | 🟢 | 🟢 | 🟡 | 🟡 |
| Travel plan | 🟢 | 🟢 | 🟡 | 🟡 |
| Return | 🟢 | 🟡 | 🟡 | 🔴 |
| Documents | 🟢 | 🟡 | 🔴 | 🔴 |
| Overstay | 🟡 | 🔴 | 🔴 | 🔴 |

## Country lean notes (`REFLEAN`, shown on amber/red)

| ISO | Note |
|---|---|
| it | Italy leans on document and funds proof. |
| nl | Netherlands leans on ties and purpose. |
| be | Belgium applies high scrutiny across the board. |
| ch | Switzerland is exact on funds and insurance. |
| fr | France leans on a coherent, evidenced itinerary. |
| de | Germany leans on ties and financial pattern. |

## Compliance
- All verdicts are **provisional** (stated once in the snapshot subhead + fine print).
- No guaranteed outcome; high-risk/decline copy is deliberately honest.
- Reason tiles map to real Schengen Visa Code Art. 32 grounds so copy speaks
  the embassy's language. See also `country-logic.md`, `hero-urgency-config.md`.
