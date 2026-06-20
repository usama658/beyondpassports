# Destination image credits

Per `image-selection-criteria.md`, every destination photo records its source + licence here.

## Status legend
- **VERIFIED** — clean, named source + confirmed commercial licence (Unsplash/Pexels/Pixabay CC0, or recorded CC-BY).
- **INTERIM** — placeholder pulled via a keyword stock service; licence not individually confirmed. **Must be replaced with a VERIFIED iconic image before go-live (#300).**

## Original 8 (SEO-targeted destinations) — refreshed to iconic 2026-06-20
Re-pulled via loremflickr with iconic-landmark queries (800×520), replacing the earlier
non-iconic stock. Same INTERIM licence caveat as Schengen — replace with VERIFIED before go-live.

| Slug | Iconic subject (query) | Source | Status |
|------|------------------------|--------|--------|
| turkey | Hagia Sophia, Istanbul | loremflickr | INTERIM |
| egypt | Pyramids of Giza | loremflickr | INTERIM |
| india | Taj Mahal | loremflickr | INTERIM |
| thailand | Bangkok temple | loremflickr | INTERIM |
| vietnam | Ha Long Bay | loremflickr | INTERIM |
| uae | Burj Khalifa, Dubai | loremflickr | INTERIM |
| usa-esta | Statue of Liberty, New York | loremflickr | INTERIM |
| australia-eta | Sydney Opera House | loremflickr | INTERIM |

## Schengen (14) — added 2026-06-20
Pulled via loremflickr keyword search (iconic-landmark query per country), 800×520, saved `{slug}.jpg`.
loremflickr serves Flickr images of **varying licences** — these are **INTERIM placeholders** for the
noindex/staging build. Replace each with a licence-VERIFIED iconic shot (Unsplash/Pexels) before go-live.

| Slug | Iconic subject (query) | Source | Status |
|------|------------------------|--------|--------|
| france | Eiffel Tower, Paris | loremflickr | INTERIM |
| germany | Brandenburg Gate, Berlin | loremflickr | INTERIM |
| netherlands | Amsterdam canals | loremflickr | INTERIM |
| austria | Vienna architecture | loremflickr | INTERIM |
| spain | Sagrada Família, Barcelona | loremflickr | INTERIM |
| italy | Colosseum, Rome | loremflickr | INTERIM |
| portugal | Lisbon tram | loremflickr | INTERIM |
| greece | Santorini | loremflickr | INTERIM |
| croatia | Dubrovnik | loremflickr | INTERIM |
| denmark | Nyhavn, Copenhagen | loremflickr | INTERIM |
| sweden | Stockholm | loremflickr | INTERIM |
| poland | Kraków old town | loremflickr | INTERIM |
| czechia | Charles Bridge, Prague | loremflickr | INTERIM |
| hungary | Hungarian Parliament, Budapest | loremflickr | INTERIM |

> To replace: drop a VERIFIED `{slug}.jpg` (≥1600px source, ≤300 KB, sRGB) into
> `public/assets/img/destinations/`, update the row to VERIFIED + real source URL, re-run
> `php artisan db:seed --class=DestinationImageSeeder`.
