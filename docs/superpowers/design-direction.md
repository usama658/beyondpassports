# UKV visual direction (frontend-design)

**Subject:** an independent UK service that gets British travellers their visas/eVisas/ETAs. **Audience:** stressed
travellers who want reassurance + speed. **Page's one job:** make them confident enough to start an application.
The design language is drawn from the subject's own world — **the travel document itself**: passports, security
paper, stamps, the machine-readable zone, boarding passes.

## The problem with the current look
The Travisa kit + generic UK-visa sites cluster on the same template: a stock plane/globe hero photo, flat
corporate blue, "10,000+ applications" big-number stats, and Inter everywhere. It's trustworthy but **forgettable**
— it could be any agency. We'll keep the kit's bones (responsive, fast to build) but give it an unmistakable
document-world identity.

## Token system

### Colour (6 named values — document materiality, NOT the AI-cream default)
- `--ink` **#14202E** — near-black navy ink (text).
- `--navy` **#0A2540** — authority / headers / the passport cover.
- `--paper` **#EEF2F4** — pale blue-grey **passport security paper** (page background). Deliberately cool, not the
  default warm cream — it's the actual colour of a visa page, grounded in the subject.
- `--gold` **#C8A24A** — passport-crest gold (accent, used sparingly).
- `--stamp` **#0E6E6E** — stamp-ink teal (secondary accent / "approved" states).
- `--cta` **#1456B8** — the one action blue (buttons only — scarcity makes it read as "the thing to click").

### Type (deliberate pairing — not Inter-everywhere)
- **Display: "Fraunces"** — a warm modern serif with optical sizing; reads like an official document with a human
  voice. Used with restraint (hero, section titles) at large sizes.
- **Body: "Inter"** — keep; clean, legible, neutral delivery.
- **Utility/data: "Space Mono"** — for order refs, dates, fees, and the signature MRZ strip. Monospace = the
  machine-readable, official register of a passport.
- Scale: hero 56/64px Fraunces 400; H2 32px Fraunces; body 17px Inter; data/caption 13px Space Mono, +0.04em tracking.

### Signature — the **MRZ strip**
The machine-readable zone of a passport (`P<GBR<SMITH<<JAMES<<<<<<<<<<<<<<<<<<`) rendered in Space Mono as a
recurring brand device: a thin band under the hero, the footer, and the way every **order reference** is displayed.
It is instantly, unmistakably "passport," it's true to the subject, and it costs nothing. **This is the one bold
element — everything else stays quiet.** Order refs become `UKV<2026<004821<<<` not a plain string.

### Structure devices (encode meaning, don't decorate)
- **Boarding-pass cards** for destinations: a card with a perforated divider + a stub holding the fee — because a
  destination *is* a ticket to somewhere. Not a plain rounded box.
- **Stamp motif** for completed/approved states (a faint circular stamp), used only where something is genuinely done.
- **01 / 02 / 03** numbering ONLY on the real 3-step process (it *is* a sequence) — nowhere else.

## Hero (thesis, not a big-number template)
```
┌──────────────────────────────────────────────┐
│ eyebrow:  INDEPENDENT UK VISA & eVISA SERVICE │
│                                               │
│  UK visas, sorted —          [ Where are you  │
│  without the stress.           going? ▼ ]     │  ← the checker IS the hero
│  (Fraunces, 56px)            [ Check what I   │     (most useful action, inline)
│                                need → ]        │
│  one-line sub (Inter)        not a govt site  │
│                                               │
│  P<GBR<TRAVELLER<<READY<<<<<<<<<<<<<<<<<<<<<   │  ← MRZ signature strip
└──────────────────────────────────────────────┘
```
Background = `--paper`. The hero leads with the **checker** (the genuinely useful first action), not a stock photo
or a stat. The MRZ strip anchors the brand immediately.

## Section blueprints (map onto the kit)
1. **Hero** — paper bg, Fraunces headline + inline checker + MRZ strip.
2. **Three steps** — 01/02/03 (real sequence): Tell us your trip → We check & submit → We track & deliver.
3. **Destinations** — boarding-pass cards (`[ukv_dest_grid]` restyled), fee on the stub.
4. **Why us / reassurance** — stamp-motif ticks; quiet.
5. **Testimonials** — `[ukv_testimonials]`, set in Inter with a Space-Mono attribution line.
6. **CTA band** — `--navy` bg, gold rule, one `--cta` button + WhatsApp; MRZ strip footer.

## Motion (restrained)
- CTA hover: a subtle "stamp" press (2px down + faint ink ring). 
- Steps: scroll-reveal in sequence. Reduced-motion: all off. Nothing else moves.

## Critique vs the generic (frontend-design pass)
A generic prompt would give: cream bg + serif + terracotta (AI default #1), or stock-photo hero + corporate blue +
big stats. This direction rejects both: **cool passport-security paper** (not cream), **document serif + MRZ
monospace signature** (not Inter-only), **boarding-pass cards + stamp states** (not rounded boxes), **checker-as-
hero** (not a stat). Every choice is derived from the travel-document world, so it can't be mistaken for a generic
agency. The one risk — the MRZ strip — is contained to two or three places so it stays a signature, not a gimmick.

## How to apply in Elementor / the kit (no React)
- **Kit #156 globals:** set Heading font = Fraunces, Body = Inter; add the 6 colours as global colours; add a
  Space-Mono utility class.
- **MRZ strip:** a reusable section = a full-width `--navy` band with Space-Mono text + the `<` chevrons; drop in
  hero base + footer; render order refs via a small filter (`UKV<YEAR<NNNN<<<`).
- **Boarding-pass cards:** the kit's image-box / a column with a left perforation border (dashed) + a fee stub.
- Keep everything else the kit's defaults — spend the boldness only on the signature.

## Build order
1. Kit globals (fonts + colours) — one change, site-wide. 
2. MRZ strip reusable section + order-ref formatter.
3. Hero (checker-led) on the homepage.
4. Boarding-pass destination cards.
5. Apply to money/apply/track per the page-composition blueprint.
