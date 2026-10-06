# Competitor research: multi-market Schengen visa sites (fetched live 2026-10-06)

Agent: general-purpose. Method: WebFetch rendered text + raw curl of HTML heads, robots.txt, sitemaps; hreflang/canonical claims are from page source. Atlys needed a Googlebot UA to bypass geo redirect. GoVisaEurope has no reachable sitemap. Part of the 2026-10-06 competition-led research set.

## 1. atlys.com
**Market/geo architecture**
- Path-prefix locale `lang-COUNTRY`: `/en-GB/`, `/en-US/`, `/en-AE/`, `/en-CA/`, `/en-IN/`, `/en-SA/`, `/ar-AE/`, `/fr-FR/` etc. Product URL `https://www.atlys.com/{locale}/visa/{destination}-visa`.
- HARD GEO-IP REDIRECT overrides path for normal browsers: `/en-GB/visa/france-visa` with Chrome UA returned `307 -> /en-ID/visa/france-visa`, cookies `userCountryCode=ID`. Googlebot UA got 200 on `/en-GB/` ("France Visa From UK: Fees, Documents, and How to Apply"). Only crawlers get the path they ask for.
- 26 locales in homepage hreflang (en, en-US, en-AE, en-GB, en-AU, en-SG, en-SA, en-FR, en-DE, en-NG, en-PH, en-CA, en-QA, en-NP, en-TR, en-OM, en-NL, en-TH, en-IT, en-JP, en-EG, en-VN, fr-FR, de-DE, es-ES, ar-AE). Products sitemap also has en-IN (207 URLs, largest).
- NO SOUTH AFRICA MARKET: no `en-ZA` in hreflang, 0 en-ZA URLs. `/en-ZA/visa/france-visa` returns 200 but serves the India template ("France Visa for Indians").
- `/en-US/visa/france-visa` and `/en-CA/visa/france-visa` also returned "France Visa for Indians" H1 as Googlebot: fallback bug or thin US/CA localisation (unverified which).
- Offline footprint: "London New York Dubai Delhi"; UAE page promotes a physical store at BurJuman Mall, Dubai.

**Residency/nationality input**: implied by locale; market-specific titles ("France Visa From UK", "France Visa for UAE Residents"). Flag selector + destination search + filters. Application flow: "Fill in your address to ascertain your visa jurisdiction".

**Currency & pricing**: local currency per locale, server-rendered on en-AE: "Appointment Fee Paid to government | Zero commission AED 108 / Service Fee AED 499 / Total AED 607 ... Visa Fee paid in person directly to a government official". Includes "Flight & hotel reservation assistance". en-GB France page did NOT render price server-side ("Unlock for free", "Unlock a lower price", "Pay when visa approved" strings in bundle); page showed "No Visa Slots Available Right Now / Let Atlys search for new slots 24/7". UK fee unverified (review mentions "about £200").

**hreflang/SEO**: homepage full 26-locale alternate set + canonical. PRODUCT PAGES CARRY 0 HREFLANG; canonical self. ~2,000 locale x destination URLs not cross-linked. robots.txt: 6 sitemaps (products, tools, blogs, passports, rejectionrecovery); explicit allow-lists for ClaudeBot, GPTBot, PerplexityBot on `/transparency/`.

**Template**: 2,000 product URLs = 22 locales x 249 destination slugs (en-IN 207, en-AE 198, en-PH 152, en-GB 107, en-US 106, en-CA 76). Schengen slugs: france, germany, greece, italy, netherlands, portugal, spain, switzerland (no generic schengen-visa product; 404). Global pages: `/appointments/schengen`, `/tools/schengen-cover-letter-generator`, `/tools/schengen-invitation-letter-generator`, `/passport-index/...`. France page sections: visa facts strip, "Get to France faster, book through another Schengen country" ("4303 travelled to France through another Schengen country"), stats citing "official Schengen Visa data of 2025", rejection reasons, FAQ tabs, live "Check Appointment availability".

**Trust/compliance**: no "not the government" line on homepage/France page. Terms: "Atlys Inc. USA", Delaware law, arbitration. Refunds: "Atlys will not refund the Visa fees or the Atlys fees for any reason whatsoever, except ... 'Atlys Protect'". Headline "Visas On Time, Guaranteed". Footer: Refunds Policy / Fee Change Audit / Status / Transparency. No licence/registration number shown.

**Bundling**: flight + hotel reservations folded into visa fee; visa photo maker; eSIM strings in bundle (unverified); `/acko/` in robots suggests insurance tie-in (unverified). No tours.

**Copy**: "paid to government | zero commission" vs "service fee" split; live availability widget on destination page. **Avoid**: IP 307 overriding explicit locale path; locale fallback serving "for Indians" on US/CA/ZA URLs.

## 2. ivisa.com
**Architecture**: single global site by LANGUAGE (`/es/`, `/zh/`, `/de/`, ... 15), English at root. No `/en-gb/`, no `/za/`, no geo redirect. Source market handled by NATIONALITY pages: `/visas/{destination}/{nationality}` e.g. `/visas/france/united-kingdom`, `/visas/france/united-arab-emirates`, `/visas/france/usa`, `/visas/france/canada`. `/visas/france/south-africa` = 404. Legacy `/france-visa` 301 -> `/visas/france/schengen`.

**Input**: dropdown pair on every hero "Your passport / Your nationality" + "Destination"; prefilled on nationality pages. No auto-detect. API model `api/visa/product_information/{nationality}/{destination}/{currency}`.

**Pricing**: USD default with client-side currency switcher (`?currency=GBP` did not change server render). Visible pre-contact: "One all-in price ... France Schengen Visa From $399.99"; schema Offer priceCurrency USD 399.99. Government fee "paid at that appointment". Trustpilot 3.4/5 (136) in schema; on-page refund complaints.

**hreflang/SEO**: homepage 15-language alternates; product pages only `hreflang="en"` self. English sitemap 1,766 URLs: 1,221 `/visas/`, 296 `/news/`, 213 `/blog/`. Googlebot blocked from apply-now, location params, visa-match.

**Template**: three-level: `/visas/france` hub, `/visas/france/schengen` product, `/visas/france/{nationality}` (970 two-segment URLs), `/visas/france/reviews`. Nationality pages generated even for visa-free pairs (UK->France = ETIAS filler). Blocks: hero pair, "Requirements verified against official government sources", pricing/validity, FAQ, other-passport interlinks.

**Trust**: strongest disclaimer: "iVisa is a private company, not a government agency. We prepare, check, and submit your application; you can also apply directly with the government." Footer: "Document Advisor Inc. ('iVisa') is a subsidiary of HelloGov AI Inc ... registered with the U.S. Department of State." Delaware/Florida. "13+ years", "99% Approval rate", "3M+ travelers".

**Bundling**: YES, `/travel-packages`: "EU Directive 2015/2302 Compliant Package Travel Arrangements ... Organizer: iVisa", e.g. "Mediterranean Escape Barcelona-Madrid-Rome-Venice 10 Days / 9 Nights €2,890", 4-star hotels, rail, guided tours, "Not Included: Travel insurance (mandatory), Flights". Not cross-linked from visa pages.

**Copy**: destination x nationality grid; "all-in price, government fee paid at appointment". **Avoid**: nationality pages for visa-free pairs; USD-only for GBP/ZAR/AED buyers.

## 3. visard.io
**Architecture**: path prefix per SOURCE MARKET: `/uk`, `/ireland`, `/uae`, `/usa`, `/india`, `/turkey`, `/morocco`; root = x-default. `/uk-alerts`, `/{market}/resources/...`, `/{market}/schengen-visa-appointment-tracker`. No geo redirect. English only. Framer-built.

**Input**: residency = market page; destination/visa type chosen in Telegram bot. "Notifications need no passport or personal data."

**Pricing** (local currency, fully visible): UK "Notifications £35 (single) or £65 (all 20) ... Auto-booking £100 first applicant + £50 each additional, charged only after your appointment is confirmed"; UAE AED 200 / AED 350; USA $40 / $60, auto-book $100 / $50; Ireland €40, €100 / €50; India ₹2,500; Turkey/Morocco $25. Comparison table: "Visard £35 ... A UK visa agency £400 to £1,000 per applicant".

**hreflang/SEO**: market pages carry correct cluster: en-GB -> /uk, en-IE, en-AE -> /uae, en-US -> /usa, en-IN, en-TR, en-MA, x-default -> /. Canonical self. Corridor pages 0 hreflang. Sitemap 303 URLs. Schema.org includes Companies House identifier 15502153.

**Template**: market x destination corridor pages `/{market}/{destination}-schengen-visa-appointment-bot-in-{market}` e.g. `/uk/france-schengen-visa-appointment-bot-in-uk` (H1 "France Schengen Visa Appointments from the UK"). Counts: uk 22, ireland 13, uae 12, turkey 10, morocco 10, india 6, usa 4 = 77. Honest coverage gaps stated ("Visard no longer monitors France appointments from the UK"). `/{market}/resources/schengen-visa-documents-checklist`. B2B `/visa-agencies`.

**Trust**: "UK-registered company (verifiable on Companies House) ... UK GDPR"; "private monitoring service, not affiliated with the EU or any government body". "Payments go through Stripe". Refunds: fee after booking; centre fee refunded if no booking; alerts refund with evidence of missed slot. "4.8 Trustpilot", "15,000+ appointments secured", "no auto-renewal".

**Bundling**: none.

**Copy**: `/{market}` + hreflang cluster + local currency + corridor page per destination with live coverage status. **Avoid**: pushing decision into Telegram; "agency £400-£1,000" comparison (BP would be on the wrong side).

## 4. govisaeurope.com
**Architecture**: single WordPress site, English root + GTranslate machine-translated prefixes (`/ar/`, `/zh-CN/`, `/fr/`, `/de/`, ... 15). No market segmentation, no geo redirect, no currency selector.
**Input**: form "What is your citizenship?" (visa-required list). Nothing about residence.
**Pricing**: EUR only on `/apply/`: "SchengenPro Service Start at 190,00 € Government fees NOT included"; one tier labelled both "POPULAR" and "BEST VALUE". T&Cs mention credit card fee + handling fee.
**hreflang/SEO**: GTranslate 15-code alternates on every page; canonical self; robots.txt no Sitemap; no sitemap reachable.
**Template**: no programmatic pages. Home, /apply/, /about/, /faq/, /blog/ (~6 posts), /contact/, /global-support-center/, legal. Destinations = unlinked tile row.
**Trust**: "NEITHER GOVISAEUROPE NOR GOVISAEUROPE.COM IS AFFILIATED WITH ANY GOVERNMENTAL AGENCY ... IS NOT A LAW FIRM." On-page: "does not provide consular appointments, guarantee approval, expedite processing ... you may apply directly via the official government websites ... https://home-affairs.ec.europa.eu". Entity: "LexBridge LLC ('GoVisaEurope') ... LexBridge LLC ('LegalMova') ... based in the UAE"; IFZA Business Park, DSO, UAE; phones +971 and +1. Refunds pre-submission only; "SATISFACTION GUARANTEE". Counters render "0 + Years / 0 + Visas / 0 % On Time" in HTML; "98%" acceptance claim.
**Bundling**: none.
**Copy**: "you may apply directly via the official government websites" next to the price (Ads Govt Documents policy friendly). **Avoid**: machine-translated hreflang sprawl; vague tiering; inconsistent entity naming.

## Synthesis
1. Market architecture: serious players segment by SOURCE MARKET in the path: Atlys `/{lang-COUNTRY}/visa/{dest}-visa` (geo-IP enforced), Visard `/{market}/...` (manual, hreflang-linked). iVisa segments by nationality in URL + language prefix; GoVisaEurope none.
2. NOBODY SERVES SOUTH AFRICA: Atlys no en-ZA, iVisa no /visas/france/south-africa, Visard covers UK/IE/UAE/US/IN/TR/MA. ZA open ground. UAE/US/CA contested by Atlys (en-AE strongest, Dubai store) and Visard (UAE/USA).
3. Pricing visibility: winners show price before contact, split "government/appointment fee" vs "service fee" in local currency (Atlys AED 108 + 499; Visard £35/£100, AED 200, $40). iVisa USD all-in $399.99; GoVisa from 190 EUR. Hidden pricing is the exception in this auction.
4. Per-country strategy: Atlys market x destination (2,000), Visard corridor pages (77), iVisa destination x nationality (970). Dominant pattern = one indexable page per (source market, Schengen destination) with market-specific H1.
5. hreflang done well only at market-hub level (Atlys home, Visard /uk cluster incl. x-default); ALL leave destination pages without alternates. Hreflang on every destination page = cheap edge.
6. Trust pattern: "private company, not a government agency, you can apply directly" (iVisa, GoVisa, Visard) + verifiable registration (Visard puts Companies House 15502153 in schema.org). Atlys relies on guarantees instead.
7. Bundling: Atlys folds flight/hotel reservations into fee; iVisa runs separate EU-2015/2302 package catalogue (€2,890 10-day) not linked from visa pages. No one sells visa-led tours on the visa page itself = BP differentiator.
8. REC: `/za/`, `/ae/`, `/us/`, `/ca/` hubs with full hreflang cluster (en-ZA, en-AE, en-US, en-CA, x-default -> /) and a MANUAL market switcher with remembered cookie, never a forced IP 307 (Atlys weakness); UK stays on .co.uk cross-linked as en-GB.
9. REC: under each hub, market x destination grid `/za/schengen-visa/france/` with market-specific H1, local currency, visible "VAC/appointment fee vs our fee" split, live slot tiles, "you can apply directly at the consulate/VFS" line next to price; hreflang across the four sibling destination pages (none of the four do this).
10. REC: visa-led tour packages as a second block ON the same market x destination page (not a separate catalogue like iVisa), local currency, "not included: insurance/flights"; company registration 17331903 + refund terms in footer like Visard; skip Atlys-style approval/on-time guarantees (express-not-faster-govt constraint).

Scratch raw files: scratchpad atlys_prod.txt, ivisa_en.txt, visard_sm.txt, atl_en-*.html (session-local).
