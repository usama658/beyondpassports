# Beyond Passports — brand assets

Winged-passport mark, redrawn clean (Route B) from the approved reference.
Palette: navy `#16222e`, petrol `#155E7A`, stamp-green `#5C9A7B`. Wordmark: Outfit.

## Vector masters (use these for web + print; scale infinitely)
- `bp-logo.svg` — horizontal lockup, full colour (header, light backgrounds)
- `bp-logo-reversed.svg` — white lockup (dark / navy backgrounds)
- `bp-symbol.svg` — symbol only, full colour (square)
- `favicon.svg` — simplified mark (wing + globe) in a navy tile

## Raster (generated from the source art; transparent unless noted)
- `bp-logo@1x/2x/3x.png` — transparent lockup (600 / 1200 / 1800 px wide) for email, decks, Office
- `bp-logo-grey.png` — greyscale lockup
- `bp-symbol-{16,32,48,180,192,512}.png` — transparent symbol at icon sizes
- `favicon.ico` — 16/32/48 multi-size
- `apple-touch-icon.png` — 180, white bg
- `icon-192.png`, `icon-512.png` — PWA, white bg
- `icon-512-maskable.png` — PWA maskable (navy, safe-zone padded)
- `social-avatar.png` — 1024 square, paper bg (social profile)
- `bp-logo-master.png` / `bp-symbol-master.png` — full-res transparent sources

## Notes / to finish
- SVG wordmark uses live Outfit (self-hosted on site). For a print master, convert text to outlines.
- Minimum sizes: full lockup 120px / 24mm wide; symbol 16px floor (24px recommended).
- Clear space: keep the symbol-height "X" clear on all sides.
- Do not stretch, recolour off-palette, add effects, or place on low-contrast/busy backgrounds.
- Raster icons are derived from the AI source art (subtle gradients). For pixel-pure flat icons,
  render the SVGs to PNG with a vector tool (Inkscape / cairosvg) and replace.
