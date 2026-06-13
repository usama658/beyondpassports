# Research sources — checking visa requirements + spotting appointment scenarios

Where to find the authoritative answer to "what does a UK passport holder need for country X, and is it online or
appointment-based?" — so you can add destinations and prepare appointment scenarios correctly.

## Primary sources (in order)
1. **gov.uk Foreign travel advice** — `https://www.gov.uk/foreign-travel-advice` → pick the country → **"Entry
   requirements"**. THE official UK government source for what British citizens need. **Start here every time.**
2. **The destination's official embassy / consulate / visa portal** — the definitive word on that country's
   process + whether it's online or in-person. (Confirm the real official domain — beware copycats.)
3. **VFS Global** (`vfsglobal.com`) + **TLScontact** (`tlscontact.com`) — if a country's UK visa is processed
   here, it's an **appointment / biometric** route. Search "VFS [country] UK" or "TLScontact [country] UK" — that
   instantly confirms appointment-based + the centres/cities.
4. **IATA Travel Centre / Timatic** — the per-nationality entry-rule database airlines use; most comprehensive
   (often via an airline's "travel requirements" page).

## Decision tree (per destination, for UK citizens)
- gov.uk says **visa-free** → no application, no centre. (Just passport validity + entry conditions.)
- gov.uk says **eVisa / ETA / ESTA / eTA** → **online**, no centre. Deliver the e-visa/authorisation.
- gov.uk says **visa required** AND **not** an eVisa → likely **appointment / sticker visa** → confirm via
  "VFS/TLS [country] UK" → that's a Phase-2 destination: research centre, jurisdiction, slots, fees, biometrics.

## Key planning fact
The **UK passport is very strong** — for British citizens, **most countries are visa-free or eVisa**.
Appointment/sticker visas for UK travellers are the **minority**. Typical appointment-route countries for UK
citizens: **China, Russia, India (full-visa route, though an eVisa also exists), Nigeria, Ghana, and some African
+ Central-Asian states.** These are your **Phase-2 candidates** — research each before selling it.

## Your current catalogue (all online/visa-free — NO centres needed)
Visa-free: Brazil, Morocco, Schengen, Thailand, Turkey, UAE, Vietnam · eVisa: Egypt, India, Tanzania ·
ETA: Australia, Kenya, Sri Lanka, USA. See `exports/supply-chain-capture.csv` (the only [VERIFY] items are the 7
official online-portal URLs).

## When adding a NEW destination
1. gov.uk Entry requirements → classify the route (visa-free / eVisa / ETA / appointment).
2. If online → find + verify the official portal; set the destination's required-docs + validity in Pods.
3. If appointment → research VFS/TLS centre(s), **jurisdiction rule**, slot-release window, fees, biometrics; add
   a Supply-chain node; ensure your insurance covers passport custody + set up the courier.
4. Always re-verify fees + rules vs gov.uk — they change.

## Your own checker
The site's visa checker (`/do-i-need-a-visa/`) + `[ukv_visa_table]` show this for your live destinations. For
researching NEW countries across the world, gov.uk is the source of truth.
