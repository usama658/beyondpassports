# Website Audit — web design + responsiveness completeness (2026-06-12)

Purpose: before finishing the kit-native section work, inventory every customer-facing surface, its **design source** and **responsiveness posture**, and list what's pending + why + the solution — so nothing is missed.

Legend — Design source: **Kit** = Travisa Elementor template (responsive by design) · **Shortcode** = UKV mu-plugin output (custom CSS — responsiveness must be verified) · **Plain** = WP content.
Responsive: ✅ inherited/verified · ⚠️ needs mobile check · ❌ not responsive.

## 1. Inventory (live)
- **Pages:** 61 · **Destinations (money pages):** 14 · **Posts:** 2.
- System pages: `/track/` #501 ✅, `/request-a-callback/` #325 ✅, `/apply/` #300 ✅, `/terms/` #285 ✅, `/about/` #242, `/contact/` #243.

## 2. Surface-by-surface

| Surface | Design source | Responsive | Notes |
|---|---|---|---|
| Home | Kit (#202) | ✅ | Travisa responsive |
| Header / Footer | Kit (#166/#169) | ✅ | global |
| Money pages (14) | Pods template + Kit Visa Detail | ⚠️ | render via Pods template — verify tables/FAQ on mobile |
| Guides / comparison / hub (≈40) | Plain HTML in content | ⚠️ | HTML tables in comparison pages — check mobile overflow |
| Apply funnel | Forminator | ✅ | Forminator responsive |
| Visa/IDP checkers | Forminator | ✅ | **but page slugs not /visa-checker//idp-checker/ — VERIFY where embedded** |
| Tracker `/track/` | Shortcode `[ukv_tracker]` | ⚠️ | inline-CSS card + progress bar — mobile check |
| Trust bar | Shortcode (auto on money pages) | ⚠️ | flex strip — wrap on small screens? |
| Testimonials | Shortcode `[ukv_testimonials]` | ⚠️ | card grid — mobile columns |
| Exit-intent modal | JS (ukv-conversion) | ⚠️ | fixed modal — check small-screen sizing |
| Call/WhatsApp floating btns + call bar | Shortcode | ⚠️ | fixed-position — check overlap on mobile |
| Admin: Cockpit, Reports, meta boxes, settings | Shortcode/admin | n/a | staff-only, desktop — not customer-facing |

## 3. Gaps found by this audit (fix before/with kit work)
1. **Privacy Policy is a DRAFT (#3)** — but the callback consent line + footer link to `/privacy-policy/`. Broken link / compliance gap. **Solution:** publish it (and confirm content). Quick.
2. **Checker pages not at expected slugs** — `/visa-checker/` `/idp-checker/` missing. **Solution:** verify the actual slugs / that the checker forms are embedded somewhere reachable from nav; add pages if absent.
3. **No real device/browser responsiveness test has ever run** — all ⚠️ above are *unverified*, not *broken*. The kit templates are responsive; the risk is the **shortcode outputs** (custom inline CSS) on mobile. **Solution:** the kit-native section conversion (moving these into Elementor sections) inherits the kit's responsive system + lets us set per-breakpoint controls — which is exactly why this work matters. Plus one real mobile pass (browser/devtools) before launch.
4. **Comparison/guide HTML tables** may overflow on mobile. **Solution:** wrap in a responsive container or convert key ones to kit widgets.

## 4. Pending tasks — status / why / solution
| Task | Status | Why pending | Solution / unblock |
|---|---|---|---|
| #17 Production migration | Blocked | No host + domain | User provides host+domain → run migration kit |
| #25 WhatsApp Business API | Blocked | Paid account + number verification | User opens account → wire API |
| #51–56 Kit-native sections | In progress | Active dev | Kit-1 done; build Kit-2→5 (this audit gates the responsive design) |
| Activation: Zapier URL / real numbers / token rotation / SMTP / Stripe live | Built, awaiting input | Needs user secrets/accounts | Paste in the settings pages built for each; Tools→Pre-launch shows status |

## 5. Responsiveness plan for the kit sections (so nothing's missed)
Each kit-native section built will:
- Use the kit's global colours/typography + Jeg-Kit/core widgets (responsive by default).
- Embed live data via the `shortcode` widget, with the surrounding layout responsive at desktop/tablet/mobile breakpoints.
- Replace the most at-risk ⚠️ shortcode surfaces first: **trust bar, testimonials, tracker, destinations grid, contact CTA**.
- Be verified to render (Kit-5) + flagged for a final real-device mobile pass at launch.

## 6. Test coverage (context)
16 automated suites green (logic). UI/responsiveness is the **untested dimension** — closing it is the point of this audit + the kit work.
