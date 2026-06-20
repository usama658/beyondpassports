# Destination image selection criteria

The standard for every destination photo used on cards, the nav mega-menu, and money-page
heroes. Apply it before adding any image. One consistent bar keeps the grid cohesive and
keeps us legally clean.

## 0. Source reliability (lesson learned)
- **Do NOT use random keyword/tag services** (e.g. loremflickr) — they return *any* photo tagged
  with the word, not the iconic subject, so you get amateur/wrong shots and unknown licences.
- **Use curated sources** where the *subject is guaranteed*: a landmark's **Wikipedia lead image**
  (`/api/rest_v1/page/summary/{Landmark}` → `originalimage`), or **Unsplash/Pexels** (API, free
  commercial licence). Pick the **landmark page**, not the country page (e.g. `Taj_Mahal`, not `India`).
- **Always verify the rendered crop in a browser**, not just the downloaded file — a great photo
  can still crop badly in the card.

## 1. Licensing — the hard gate (check first)
- **Only royalty-free, commercial-use, no-attribution-required** sources: Unsplash, Pexels,
  Pixabay (all CC0-style). Wikimedia Commons is OK **only** for CC0 / CC-BY with attribution recorded.
- **Never**: Google Images, Getty/Shutterstock/Adobe (paid/editorial), anything watermarked,
  "editorial use only", or unknown provenance.
- **Record the source + licence per image** in `docs/image-credits.md` (slug → URL + licence +
  photographer). If we can't name a clean source, we don't ship the image.

## 2. Content
- A **recognisable, iconic** landmark or skyline of that destination (Eiffel Tower for France,
  Colosseum for Italy, Sagrada Família/Alhambra for Spain, etc.). It must read as *that place*.
- **Daytime, clear weather, golden-hour preferred** — warm, aspirational, premium.
- **No identifiable faces** (privacy/consent), no crowds as the subject, no text/logos/watermarks,
  no flags or politically sensitive framing, nothing that misrepresents the country.
- Culturally respectful; avoid tired clichés that distort.

## 3. Composition & crop
- **Landscape orientation.** Must survive two crops without losing the subject:
  - card banner — wide, short strip (~240×96 ratio), top-aligned;
  - money-page hero — full-bleed behind a navy gradient overlay (left-weighted text).
- Keep the hero/landmark off-centre or central but **clear of the lower-left** (where hero copy sits).
- Looks good at both thumbnail (card) and full width (hero).

## 4. Technical
- **JPEG**, sRGB, no alpha. Source **≥ 1600px wide**; export optimised to **≤ 300 KB**.
- Consistent treatment across the set — similar brightness/saturation/warmth so the grid is uniform.
  (If sources vary wildly, normalise exposure/saturation before saving.)

## 5. File & wiring
- Filename = **`{slug}.jpg`**, lowercase, exactly matching the destination slug
  (e.g. `france.jpg`, `usa-esta.jpg`), saved to **`public/assets/img/destinations/`**.
- `DestinationImageSeeder` auto-sets `image_path` for any slug whose file exists — so dropping the
  file in + running the seeder is all the wiring needed. No DB edit by hand.
- Missing file → the page falls back to the skyline SVG (safe default).

## 6. Accessibility
- Alt text is set from the destination name (`"{Name}"` / `"{Name} skyline"`). The image is
  decorative-supportive, not information — never put facts only in the image.

## 7. Consistency checklist (per image, before commit)
- [ ] Clean licence recorded in `image-credits.md`
- [ ] Iconic + unmistakably that destination
- [ ] Landscape, survives both crops, subject clear of lower-left
- [ ] JPEG, ≥1600px source, ≤300 KB export, sRGB
- [ ] Named `{slug}.jpg` in `public/assets/img/destinations/`
- [ ] Tonally matches the rest of the set
