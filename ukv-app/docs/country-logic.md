# Country Logic — master table (LOCKED)

Single source of truth for all per-country logic. Feeds: form config (Slot status / Difficulty / Qualification enums), the urgency strip + difficulty chip, the eligibility gate, and ops. **One table, never split.** Enum axes (C/D/E) drive the live form; reference columns (F–O) inform ops + copy.

Locked: 2026-08-25. Related: `hero-urgency-config.md` (form-side enums), preview `storage/previews/lp-v2-states.html`.

**LIVE COPY:** Google Sheet "Beyond Passports Master Sheet" (`1YdBQuUNejo177bwWtc9CpIGDyU1_YmlQ7bhw87oYhr4`), tab **"Country Logic"**, range A1:O30 (written 2026-08-25). Edit there; this MD is the frozen mirror. Sheets API write path: refresh `token_usama_theden.json` (has `spreadsheets` scope) and call Sheets API **without** the `x-goog-user-project` header (that quota project has Sheets API disabled).

## Columns
| Col | Field | Type | Drives |
|-----|-------|------|--------|
| A | Country | text | display |
| B | ISO | text | join key |
| C | Slot status | enum | urgency strip |
| D | Difficulty | enum | difficulty chip |
| E | Qualification | enum | eligibility gate |
| F | Refusal % (+year) | number | copy (sourced only) |
| G | Approval time | text | copy |
| H | Confirmed flight required | Y/N/Sometimes | doc checklist |
| I | Key refusal documents | text | doc checklist / prep |
| J | Prime slots available | Yes/Paid/Limited/— | upsell |
| K | Portal verification | text | ops / applicant prep |
| L | VAC operator | text | slot-sweep feasibility |
| M | Biometric reuse | text | skip in-person (59-mo) |
| N | Funds threshold | text | doc checklist |
| O | Source · reviewed | date | governance / auto-fallback |

## Enums
- **C Slot status:** `open` · `filling` · `limited` · `check` · `waitlist`
- **D Difficulty:** `straightforward` · `standard` · `strict` · `high`
- **E Qualification:** `eligible` · `residence` · `main-dest` · `restricted`
- **H Flight:** `N` (reservation ok) · `Sometimes` · `Y`
- **J Prime:** `Yes` · `Yes (paid)` · `Limited` · `—`
- **K Verification:** `In-person biometrics` · `Video` · `None`
- **F / N / O:** `verify` = placeholder; fill from real source before it drives any public claim (ASA).

## Data (29 rows)
| Country | ISO | Slot | Difficulty | Qualification | Refusal% | Approval | Flight | Key refusal docs | Prime | Verification | Operator | Biometric | Funds | Source·reviewed |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Spain | es | filling | standard | eligible | verify | <=15d (up to 45) | Required | funds, accommodation, itinerary | Yes (paid) | Online + In-person biometrics | BLS | 59-mo reuse | verify | verify |
| France | fr | open | straightforward | eligible | verify | <=15d (up to 45) | Required | funds, cover letter, insurance | Yes (paid) | In-person biometrics | TLS | 59-mo reuse | verify | verify |
| Italy | it | limited | strict | main-dest | verify | <=15d (up to 45) | N | funds, ties-to-UK, itinerary | Limited | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Germany | de | open | standard | eligible | verify | <=15d (up to 45) | N | insurance, funds, purpose | Yes | In-person biometrics | VFS/iData | 59-mo reuse | verify | verify |
| Greece | gr | open | straightforward | eligible | verify | <=15d (up to 45) | N | funds, accommodation | Yes | In-person biometrics | Embassy/VFS | 59-mo reuse | verify | verify |
| Netherlands | nl | filling | strict | residence | verify | <=15d (up to 45) | N | funds, sponsor letter, purpose | Limited | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Portugal | pt | open | straightforward | eligible | verify | <=15d (up to 45) | N | funds, accommodation | Yes | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Belgium | be | limited | high | main-dest | verify | <=15d (up to 45) | Sometimes | funds, ties-to-UK, cover letter | Limited | In-person biometrics | TLS | 59-mo reuse | verify | verify |
| Austria | at | check | standard | residence | verify | <=15d (up to 45) | N | funds, insurance | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Switzerland | ch | waitlist | strict | restricted | verify | <=15d (up to 45) | N | funds, purpose, ties | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Sweden | se | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Norway | no | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Denmark | dk | check | standard | eligible | verify | <=15d (up to 45) | N | funds, purpose | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Finland | fi | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Iceland | is | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Poland | pl | check | standard | eligible | verify | <=15d (up to 45) | N | funds, purpose | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Czechia | cz | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Hungary | hu | check | standard | eligible | verify | <=15d (up to 45) | N | funds, purpose | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Slovakia | sk | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Slovenia | si | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Croatia | hr | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Estonia | ee | check | standard | eligible | verify | <=15d (up to 45) | N | funds, purpose | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Latvia | lv | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Lithuania | lt | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Luxembourg | lu | check | standard | eligible | verify | <=15d (up to 45) | N | funds, purpose | — | In-person biometrics | TLS/VFS | 59-mo reuse | verify | verify |
| Malta | mt | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS/OSC | 59-mo reuse | verify | verify |
| Liechtenstein | li | check | standard | restricted | verify | <=15d (up to 45) | N | funds, purpose (via CH) | — | In-person biometrics | via Switzerland | 59-mo reuse | verify | verify |
| Bulgaria | bg | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |
| Romania | ro | check | standard | eligible | verify | <=15d (up to 45) | N | funds, accommodation | — | In-person biometrics | VFS | 59-mo reuse | verify | verify |

## Governance / compliance
- `verify` fields never drive a public claim until sourced (refusal %, funds, source date). ASA CAP 3.28.
- Scarcity (`filling`/`limited`) + refusal % need evidence; stale > 7 days → auto-fall back Slot=`check`, keep others.
- Operators marked generic `VFS` for non-core countries — confirm before slot-sweeping.
- Biometric reuse = Schengen-wide 59-month rule; note per-row exceptions only.
- Same object feeds tiles, search, urgency strip, difficulty chip, eligibility gate — one source, never contradict.
