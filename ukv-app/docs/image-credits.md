# Destination image credits

Per `image-selection-criteria.md`, every destination photo records its source here.

## Method (locked)
Each image is the **iconic landmark's Wikipedia lead image** (`/api/rest_v1/page/summary/{Landmark}`
→ `originalimage`), centre-cropped to **800×520** with PHP GD (ImageMagick isn't installed here).
Reliable + iconic + size-correct. Status **INTERIM** = Wikipedia/Commons file, licence per-file
varies (mostly CC-BY-SA / public domain) — confirm + record the exact licence URL before go-live (#300).

## Originals (8) + Schengen (14) — refreshed 2026-06-20

| Slug | Iconic landmark | Source | Status |
|------|-----------------|--------|--------|
| turkey | Hagia Sophia | Wikipedia lead | INTERIM |
| egypt | Giza pyramid complex | Wikipedia lead | INTERIM |
| india | Taj Mahal | Wikipedia lead | INTERIM |
| thailand | Wat Arun | Wikipedia lead | INTERIM |
| vietnam | Ha Long Bay | Wikipedia lead | INTERIM |
| uae | Burj Khalifa | Wikipedia lead | INTERIM |
| usa-esta | Statue of Liberty | Wikipedia lead | INTERIM |
| australia-eta | Sydney Opera House | Wikipedia lead | INTERIM |
| france | Eiffel Tower | loremflickr (reverted per request) | INTERIM |
| germany | Brandenburg Gate | Wikipedia lead | INTERIM |
| netherlands | Canals of Amsterdam | Wikipedia lead | INTERIM |
| austria | Hallstatt | Wikipedia lead | INTERIM |
| spain | Alhambra | Wikipedia lead | INTERIM |
| italy | Colosseum | Wikipedia lead | INTERIM |
| portugal | Belém Tower | Wikipedia lead | INTERIM |
| greece | Parthenon | Wikipedia lead | INTERIM |
| croatia | Dubrovnik | Wikipedia lead | INTERIM |
| denmark | Nyhavn | Wikipedia lead | INTERIM |
| sweden | Stockholm | Wikipedia lead | INTERIM |
| poland | Main Square, Kraków | Wikipedia lead | INTERIM |
| czechia | Charles Bridge | Wikipedia lead | INTERIM |
| hungary | Hungarian Parliament Building | Wikipedia lead | INTERIM |

> Replace any image: drop a licence-VERIFIED `{slug}.jpg` (800×520, ≤300 KB) into
> `public/assets/img/destinations/`, update its row to VERIFIED + source URL.
