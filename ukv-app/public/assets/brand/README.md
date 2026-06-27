# Beyond Passports — brand assets

Winged-passport + globe mark. **Canonical = the traced final art (v1)** approved 2026-06-26.
Palette: navy `#16222e`, petrol `#155E7A`, teal `#2f8f80`, stamp-green `#5C9A7B`. Wordmark: Outfit.

Because the colour mark is a detailed traced gradient, the masters split by use:
**mono = SVG (scales infinitely), colour = high-res PNG.**

## Vector masters — mono (use on web + print; scale infinitely)
- `bp-logo.svg` — horizontal lockup, solid mono (light backgrounds)
- `bp-logo-reversed.svg` — white lockup (dark / navy backgrounds)
- `bp-symbol.svg` — symbol only, mono (square)
- `bp-symbol-reversed.svg` — symbol only, white (dark backgrounds)
- `favicon.svg` — white mark on a navy tile

## Raster masters — colour (transparent unless noted)
- `bp-logo-master.png` / `bp-symbol-master.png` — full-res transparent colour sources
- `bp-logo-colour.png` — colour lockup, 1200px (header / decks, light backgrounds)
- `bp-logo@1x/2x/3x.png` — transparent colour lockup (600 / 1200 / 1800 px) for email, Office
- `bp-logo-grey.png` — greyscale lockup
- `bp-symbol-{16,32,48,180,192,512}.png` — transparent colour symbol at icon sizes
- `favicon.ico` — 16/32/48 multi-size
- `apple-touch-icon.png` — 180, white bg
- `icon-192.png`, `icon-512.png` — PWA, white bg
- `icon-512-maskable.png` — PWA maskable (white mark on navy, safe-zone padded)
- `social-avatar.png` — 1024 square, paper bg (social profile)

## Notes
- Colour logo = light backgrounds only (navy wordmark disappears on dark). On dark use
  `bp-logo-reversed.svg` / `bp-symbol-reversed.svg`.
- SVG wordmark is outlined in the traced art (no live font dependency).
- Minimum sizes: full lockup 120px / 24mm wide; symbol 16px floor (24px recommended).
- Clear space: keep the symbol-height "X" clear on all sides.
- Do not stretch, recolour off-palette, add effects, or place on low-contrast/busy backgrounds.
- NOT yet wired into the site (header is text, `<head>` has no favicon links) — see task #376.
- Source finals live in `C:\Users\mumya\Downloads\` (bp-lockup-bw-*, bp-symbol-*, *-nobg).
