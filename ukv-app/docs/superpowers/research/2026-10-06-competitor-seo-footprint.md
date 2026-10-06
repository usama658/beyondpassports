# Schengen-visa competitor SEO footprint audit (fetched 2026-10-06)

Agent: general-purpose. Method: robots.txt, sitemap index + children (exact counts via curl+grep where fetchable), raw HTML of homepage + France page + a market page (JSON-LD @type grep, link hreflang, canonical, robots meta), main-content word counts via html.parser stripping chrome. "Exact" = grep of <loc>; others estimates. Part of the 2026-10-06 competition-led research set.

## 1. atlys.com (scale leader, Next.js/RSC, Cloudflare)
- Sitemaps: `/sitemap/0.xml` 33; `products-0.xml` 2,000 exact; `blogs-0.xml` 335; `tools-0.xml` 1,273 (status checkers, cover-letter generator, `/appointments/schengen`); `passports-0.xml` 195; `rejectionrecovery-0.xml` 267. Total ~4,100.
- Taxonomy: locale prefix = market: `/en-IN/visa/france-visa`, `/en-GB/`, `/en-US/`, `/en-AE/` (22 en-XX locales). Only 4 locales carry France (US/AE/GB/IN); long-tail destinations up to 22. Blog unprefixed. Rejection recovery `/en-IN/rejection-recovery/{iso2}`.
- hreflang: ONLY in sitemap (xhtml:link, 31,628 alternates), none in HTML head, NO x-default. Locale forced by IP: every fetch 307 -> `/en-ID/...` which is `noindex,nofollow` self-canonical. Core locales (IN/GB/US/AE) indexable; others generated but noindexed.
- Schema (France): BreadcrumbList, ListItem only. No FAQPage despite ~93 FAQs; no Product/Service/AggregateRating.
- Depth (France en-IN): H1 "France Visa for Indians..."; H2s: Visa Information, Why via Atlys, Get to France faster via another Schengen country, What you get, Final Application Preview, Statistics (sourced "Official EU Schengen data 2025"), Rejection Reasons, FAQ (7 categories ~93 Qs), "How We Reviewed This Page". Fee block server-rendered (EUR 90/45/free). ~7,800 words extractable. No named author, no visible date.
- Linking: hub-and-spoke, 30+ Schengen siblings per page; tools + blog cross-linked; footer ~49 anchors.
- AI readiness (strongest): robots.txt explicit Allow for GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, anthropic-ai, PerplexityBot, Google-Extended, CCBot, Bytespider, Meta-ExternalAgent, cohere-ai, Applebot-Extended, Gemini-Deep-Research. `/llms.txt` ~800-900 lines, 250+ links. EVERY PRODUCT PAGE HAS A MARKDOWN TWIN: `/en-IN/docs/france-visa.md` (7,166 words), `/en-GB/docs/france-visa.md`. "How We Reviewed This Page", `/transparency`, `/transparency/price-change-log`, `/transparency/status`. No named author.
- Freshness: all sitemap lastmod = single build timestamp (noise). Blog has real dates 2024-11 to 2026-07.
- Blog: 335 posts (22 "schengen" slugs) + 1,273 tool pages as programmatic guide surfaces.

## 2. ivisa.com (destination x origin matrix)
- Sitemaps: index -> 15 locale children. EN child 1,766 exact (1,220 `/visas/`, 212 `/blog/`, 295 `/news/`). Whole site est. 12-15k.
- Taxonomy: `/visas/{destination}/{visa-type}` and `/visas/{destination}/{origin}` (970 pairs exact); language prefix `/es/visas/`. Reviews own URL `/visas/france/reviews`. No `/en-za`, no France/South-Africa pair (404). Dated news slugs.
- hreflang: homepage 15 languages, NO x-default. Destination pages only self `hreflang="en"`. None in sitemaps.
- Schema (France): FAQPage (14 Q/A), Product + Offer + Brand + AggregateRating, Organization + ContactPoint, WebSite, BreadcrumbList. RICHEST COMMERCIAL SCHEMA of the group.
- Depth: `/visas/france/schengen` ~2,100 words main (2,800-3,200 incl reviews), 10 H2s: requirements, documents, pricing/validity (server-rendered "From $399.99", govt fee at biometrics), how to apply, reviews (3.4/5, 136), FAQ 14, "iVisa vs official process", why iVisa, other passports, other countries, app. UK pair page ~1,375 words ETIAS-framed. No tables, no author, no dates.
- Linking: matrix cross-links: 5 origin variants + 6-9 sibling destinations; 78-83 links/page. Author + category pages on blog.
- AI readiness: no AI rules. `/llms.txt` thin (23 links). Blog authors named; destination pages not.
- Freshness: real lastmod per URL 2020-10 to 2026-09.
- Blog: 212 + 295 news EN; `/tools/global-policy-tracker`.

## 3. visard.io (Framer, Schengen-only appointment bot, 7 markets)
- Sitemap: single, 303 exact (77 destination bot pages, 37 resources, 152 blog). No lastmod/hreflang in sitemap.
- Taxonomy: MARKET FIRST: `/uk`, `/ireland`, `/uae`, `/india`, `/usa`, `/turkey`, `/morocco`; destination under market `/uk/france-schengen-visa-appointment-bot-in-uk` (uk 33, ireland 21, uae 20, morocco 16, turkey 16, india 12, usa 10); `/uk/resources/schengen-visa-documents-checklist`; `/uk/schengen-visa-appointment-tracker`; blog market-suffixed `/blog/schengen-visa-uk-guide-2026`. No `/za`.
- hreflang: hubs + resources: en-GB, en-IE, en-AE, en-US, en-IN, en-TR, en-MA, x-default -> `/`. Destination pages NONE (gap).
- Schema (France bot page): Service, Country, Audience, AggregateRating, Offer, SoftwareApplication, Organization, Person, BreadcrumbList, WebPage, WebSite, VideoObject (hub). No FAQPage. Duplicated graphs (Framer repetition).
- Depth: France ~1,150 words: server-rendered fee <table> (under-6 free, 6-12 EUR 45, adults EUR 90), per-visa-type cards, savings table (GBP 35 vs 345), 4-step process, 5 FAQs, 2 testimonials, "Updated Sep 30, 2026". UK hub ~3,300 words, 12 H2s, 22 destination links, pricing table, documents, 11 FAQs, "Average 4-7 days to appointment", "checked every 3 seconds". Resources: 10-document table with "common mistake" column, VFS vs TLScontact section, eVisa share code section.
- Linking: tight per-market hub-and-spoke + cross-market links to all other hubs on every page; 213-235 anchors/page.
- AI readiness: robots open. `/llms.txt` 80+ links, sections "The questions people actually ask", "How the booking systems actually work", "Facts a machine might need". Visible updated dates; founder Person nodes.
- Freshness: visible "Updated" stamps (Sep 30 2026; 25 May 2026).
- Blog: 152 posts, market x topic grid.

## 4. govisaeurope.com (WordPress, LexBridge LLC UAE, machine-translated)
- Sitemap: not fetchable (all 404; robots declares none). Est. ~10 core pages + ~17 posts x 15 language copies.
- Taxonomy: flat `/apply/`, `/about/`, `/faq/`, `/blog/`, posts at root. No destination pages (`/france/` 404). Language prefixes via translation plugin.
- hreflang: 15 alternates every page, no x-default.
- Schema: none (0 JSON-LD).
- Depth: `/apply/` only product page: EUR 190 "SchengenPro", 129-nationality dropdown, 29-destination dropdown. Homepage ~16k words mostly duplicated template/policy text, placeholder stats unfilled.
- Linking: nav + footer only.
- AI readiness: `/llms.txt` 200 but serves HTML 404 template. No authors/dates.
- Blog: ~17 posts.

## 5. visahq.co.uk / visahq.com (legacy PHP, ccTLD network)
- Sitemaps: .co.uk 1,046 exact (238 country roots, 222 embassy, 577 apply-*, 0 blog). .com 89,145 exact, 86,545 `/embassy/`; `/es/` `/ar/` `/fr/` `/zh/` 17,829 each. Only 5 URLs contain "schengen".
- Taxonomy: destination-first `/france/`, `/france/apply-tourist-visa/`, `/france/embassy/united-kingdom/`; `/schengen-visas/` hub. Source market = ccTLD (18 regional domains in footer); languages prefixes on .com.
- hreflang: .com France: x-default -> `/france/` + es/fr/ar/zh same-domain only. .co.uk France: single self en-gb. ccTLDs NOT hreflang-linked.
- Schema: no JSON-LD; microdata BreadcrumbList only. Reviews.io 4.4/5 4,190 not in schema.
- Depth: .co.uk `/france/` ~1,700 words; fee <table> shell server-rendered but FEE FIGURES JS-INJECTED (crawlers see empty cells). 6-step process, FAQ heading with 0 server Qs, OISC badge F202534845. .com `/france/` ~5,100 words. `/schengen-visas/` ~2,760 words with fully server-rendered fee table (EUR 90/99/45/free), 10 FAQs, links 29 countries; title geo-templated "Schengen Visa from United States".
- Linking: 195-country dropdowns, 100-180 anchors, 18-domain footer farm; weak Schengen hub.
- AI readiness: none (no llms.txt, no authors/dates/blog).
- Freshness: no lastmod.

## 6. tourloom.co.uk (WordPress + Yoast)
- Sitemap: posts 20, pages 12, ~40 total.
- Taxonomy: flat service pages `/schengen-visa-consultants-for-uk-residents/`, `/schengen-visa-assistance-for-african-travelers/`, `/schengen-visa-consultants/`; posts at root. No destination pages.
- hreflang: none. Schema: Yoast graph: WebSite, WebPage, LocalBusiness + PostalAddress + AggregateRating, BreadcrumbList, ImageObject; posts add Article + Person (author "Michelle"). No FAQPage, no Service.
- Depth: UK page ~1,300 words: eligibility check, 7-phase process, refusal reasons, 4 FAQs, no pricing, stat counters render empty. Africa page ~1,250 words, 10 FAQs, 5 named testimonials. Posts ~400 words.
- AI readiness: `/llms.txt` (AIOSEO) with stale URLs. datePublished/dateModified in schema (home modified 2026-09-23).
- Freshness: posts weekly 2026-05-29 to 2026-10-06.
- Blog: 20 posts (rejection rates UK, cheapest/most expensive Schengen visa UK, appointment waiting times UK, 90/180 rule, ETIAS, cover letter, proof of funds, dummy bookings, family visit, travel history).

## 7. flyfastholiday.co.uk
- Sitemap: pages 60, posts 6.
- Taxonomy: 27 thin `/{country}/` (~450 words, lastmod 2026-01-01) + 9 `/uk-to-{country}-schengen-visa/` + hub `/schengen-visa-consultancy/` + `/turkey-tourist-visa-uk/`.
- hreflang none. Schema none.
- Depth: France ~1,700 words, 10 H2s, 14 question marks not schema'd, no figures in HTML. ZERO links to other country pages from France page; hub lists countries as text.
- AI readiness none. Freshness: hub 2026-10-02, country pages stale.
- Blog: 6 generic.

## 8. breakoutholidays.co.uk (same template family as FlyFast)
- Sitemap: pages 36 (10 member/login/checkout utility URLs should be noindex), evisa_country 27, evisa_template 4 (templates leaking), posts 4.
- Taxonomy: `/uk-to-{country}-schengen-visa/` x9 + hub `/schengen-visa-assistance/` + 27 `/country/{name}/` CPT (~180 words each).
- hreflang none. Schema none.
- Depth: France ~1,720 words, 23 H2s (duplicated blocks), 5 FAQs, no pricing, links 26-27 siblings. Trustpilot referenced not embedded.
- Freshness: service pages 2026-09-24 to 2026-10-01.

## Synthesis
**(a) Taxonomy that scales:** both leaders put source market in the path and generate a destination x market matrix: Atlys `/{locale}/visa/{dest}-visa` (2,000), iVisa `/visas/{dest}/{origin}` (970). Visard, the only Schengen-pure player, does market-first `/{market}/{dest}-...-in-{market}` with per-market hubs cross-linked to all markets, ranking with only 303 URLs because every page is hub-linked and dated. VisaHQ 89k embassy farm = scale without relevance. UK boutiques: `uk-to-{country}` slugs, no hub links, no schema, 1-2k words; not the benchmark.

**(b) Schema floor:** iVisa set = commercial benchmark: Organization + ContactPoint, WebSite, BreadcrumbList, Product/Offer (or Service + Offer), AggregateRating, FAQPage with real Q/A. Visard adds Service + Country + Audience (market targeting) + visible dateModified. Nobody emits HowTo, Article with named expert author on destination pages, or availability data: open.

**(c) Content-depth benchmark per destination page:** 3,000-4,500 words unique main content (Atlys ~7.8k half FAQ padding; iVisa 2.1k; Visard hub 3.3k). Sections: visa info summary, who needs it (market-specific), server-rendered fee table (govt fee by age + service fee), processing/wait data with source + date, documents table with "common mistake" column, 6-9 step process, appointment/VAC section (VFS vs TLS vs BLS by market), refusal reasons with stats, 10-15 FAQs in FAQPage schema, reviews with AggregateRating, sibling-destination + other-market links, "how we reviewed / updated on" block.

**(d) Recommended taxonomy + hreflang:** market-first paths, UK stays on .co.uk: `beyondpassports.co.uk/schengen-visa/france/` (en-GB), `beyondpassports.com/za/schengen-visa/france/` (en-ZA), `/ae/` (en-AE), `/us/` (en-US), `/ca/` (en-CA); market hubs `/za/schengen-visa/`; tours `/za/tours/{dest}/{tour-slug}/`. Full reciprocal hreflang in HTML head on every page (cross-domain allowed) AND in sitemap, each page listing all 5 siblings + x-default. x-default -> language-neutral chooser or global `.com/schengen-visa/france/` (not geo-redirected), not the UK page. Never IP-redirect (Atlys 307 makes non-core locales noindexed); dismissible banner instead. One sitemap per market with real per-URL lastmod.

**(e) Three structural advantages for Laravel SSR:** (1) server-rendered fee + VAC tables with real numbers + Offer/priceSpecification (VisaHQ cells JS-empty; Atlys/iVisa no tables). (2) live appointment availability as crawlable text + dated "Last checked" + dateModified per market/destination (existing slot pool + last_checked flags supply this; nobody exposes availability in HTML). (3) per-page Markdown/LLM endpoints + sectioned llms.txt generated from the same Blade data (Atlys `/docs/{dest}.md` twins only comparable asset) + explicit Allow for AI crawlers + named reviewer + "reviewed on" block per page.

Raw dumps: scratchpad/seo/ (ivisa_sm.xml, atlys_prod.xml, vhqcom_sm1-3.xml, *.html), session-local.
