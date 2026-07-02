# Free Consultation — button placement map & suggestions

Goal: feature a **Free Consultation** call-to-action across the site. Every button lands in
**WhatsApp chat** (not the /apply funnel), using the shared helper so the number + prefilled
message are single-source.

## The button (single source)

Link target — reuse the eligibility chat helper with a consultation message:

```blade
<a href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my free consultation.') }}"
   target="_blank" rel="noopener" class="btn btn--ghost">Book a free consultation</a>
```

Recommended: wrap this in one partial `resources/views/partials/consult-cta.blade.php`
(accept `variant` + `label`) and include it at each spot below. One partial = consistent styling,
easy to restyle/relabel, and it inherits the real WhatsApp number the moment `UKV_WHATSAPP` is set.

Number today falls back to the placeholder `440000000000` until `UKV_WHATSAPP` is configured
(same as every other chat CTA on the site).

## Label options
- **Book a free consultation** (recommended — clear, no time commitment)
- Free 15-min consultation (sets expectation, feels low-cost)
- Talk to an expert — free
- Free eligibility chat

## Placement map

### Tier 1 — highest intent (do first)
| Page | Route | Spot | Suggested copy |
|------|-------|------|----------------|
| Home | `/` | Secondary CTA under the hero visa-check bar | "Not sure where to start? **Book a free consultation**" |
| Checklist result | `/checklist/{token}` | Below the generated document list | "Questions about your list? **Book a free consultation**" |
| Visa checker / tools | `/tools` | Under the checker result | "Want a human to confirm it? **Free consultation**" |
| Destination / money pages | `/destinations`, `destinations/show`, `/schengen-visa` | Right after the pricing tiers | "Unsure which tier fits? **Free consultation**" |
| Contact | `/contact` | Feature as the primary contact method | "The fastest way to us: **Book a free consultation**" |

### Tier 2 — reach (shared end-of-page `.cta-band`, add a ghost button beside the primary CTA)
Pages that already have a `.cta-band`: `about`, `compare`, `document-checklist`,
`driving-abroad`, `guides/index`, `guides/show`, `legal`, `reviews`, `services`.
One include per band → 9 pages covered with the same pattern.

### Tier 3 — already chat-heavy
- 6 landing pages (`lp-*`) — add a "free consultation" framing to the existing WhatsApp CTAs.
- Floating WhatsApp button (`partials/wa-float`) — optionally relabel to "Free consultation".

## Rollout order (suggested)
1. Build `partials/consult-cta.blade.php`.
2. Wire Tier 1 (5 spots) — warmest traffic, biggest lead lift.
3. Add to the 9 Tier-2 `.cta-band`s.
4. LP + float framing pass.

## Notes
- Chat-first: these do **not** touch the self-serve `/apply` + Stripe funnel (still reachable by URL).
- Compliance: "free consultation" is a genuine free chat — no fee implied, no approval promise.
- See `app/Support/SiteStats.php::chatUrl()` (the single-source link) and
  `partials/wa-cta.blade.php` (existing green WhatsApp button pattern) if a filled/primary style
  is wanted instead of the ghost variant.
