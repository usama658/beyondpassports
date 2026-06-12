# Kit section map — clone closest kit design, modify in Elementor

Your workflow: keep header/footer as-is; for each section, **duplicate the closest existing kit block**, then
modify it in the Elementor frontend. This maps every UKV section to its closest kit starting point, what to
change, and the live-data shortcode to drop in (Elementor → add **Shortcode** widget → paste the code).

How to clone a kit block in Elementor: open the source template (e.g. Home), right-click the section → **Copy**,
then **Paste** onto your target page (or right-click → Save as Template). Edit text/colours/columns inline. The
kit's global colours (navy #0A2540 / blue #1456B8 / gold #C8A24A / Inter) carry over automatically.

| UKV section | Clone from (template → section) | What to modify | Live-data shortcode |
|---|---|---|---|
| **Hero** (landing) | Home #202 → [0] "Travisa Travels…" | Headline → "UK visas & eVisas, sorted"; subtext; button → /apply/ | — |
| **Trust / stats** | FAQ #193 → [3] (4× `jkit_fun_fact`) | Numbers → "12k+ applications · 4.8★ · 24h support · 14 destinations" | or `[ukv_trust_bar]` |
| **Choose your visa** | Home #202 → [4] "Choose Your Visa" (`jkit_icon_box`×12) | Icons/labels → visa categories (tourist/business/eVisa/ETA…) | — |
| **Destinations grid** | Home #202 → [5] "Favourite Destination" (`jkit_image_box`×4) | Images/titles → top destinations, link to money pages; OR replace with dynamic | `[ukv_dest_grid]` |
| **Pricing / tiers** | Pricing #187 → [1] "Pricing & Plans" (cards + `icon-list` + button) | 3 cards → Standard/Express/Premium; features; buttons → /apply/?tier= | `[ukv_visa_table]` (fees) |
| **Testimonials** | Home #202 → [6] "Testimonial" (`jkit_testimonials`) | Use native slider, OR swap for the dynamic published ones | `[ukv_testimonials]` |
| **FAQ** | FAQ #193 → [1] (`jkit_accordion`) | Fill Q&A (visa-specific) | — (per-destination FAQ auto-renders on money pages) |
| **How we help / process** | Visa #229 → [4] "how we help clients" (steps) | 3–4 steps: apply → we review → we submit → delivered | — |
| **Track application** | Contact #211 → [1] (form band) | Remove metform; drop the tracker shortcode in a Shortcode widget | `[ukv_tracker]` |
| **Contact / CTA band** | Home #202 → [3] "we are ready to help you" (heading+text+button) | Headline → "Speak to our UK visa team"; button → tel: / WhatsApp | `[ukv_whatsapp]` |

## Notes
- **Dynamic vs manual:** where a shortcode exists, dropping it keeps the section auto-updating (e.g. destinations,
  testimonials, fees). Where you want full visual control, build it with the kit widgets manually. Both inherit
  the kit styling.
- **Responsiveness:** the cloned kit blocks are already responsive; after editing, check the tablet/mobile views
  in Elementor's device switcher (esp. the destinations grid + pricing cards → columns should stack).
- **My 5 generated section templates (#617–621)** are now redundant for your workflow — they were JSON-built, not
  clone-and-edit. Safe to delete (Templates → Saved Templates) or ignore. The shortcodes inside them are the same
  ones listed above, so nothing is lost.
- The full at-risk surface list (what to eyeball on mobile) is in `website-audit-2026-06-12.md`.
