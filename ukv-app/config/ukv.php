<?php

return [
    // Public base URL (the coded front-end host, e.g. Netlify) used in email links.
    'base_url' => env('UKV_BASE_URL', ''),

    // GDPR document retention: purge stored docs this many days after order closure.
    'doc_retention_days' => env('UKV_DOC_RETENTION_DAYS', 90),

    // Owner/ops digest recipient (daily pending-actions email).
    'owner_email' => env('UKV_OWNER_EMAIL', 'hello@beyondpassports.co.uk'), // contact/callback leads land here (master inbox)

    // Content CMS (theme-safe block builder). OFF by default: public pages render their existing
    // coded Blade until a page is explicitly switched to cms mode AND published.
    'cms' => [
        'enabled' => env('UKV_CMS_ENABLED', false),
    ],

    // Show service-fee PRICES on marketing surfaces (home/destination cards, header mega-menu,
    // money-page tier amounts, Offer schema). When false, those become "fee — on request" /
    // "View" and the tier buttons become quote CTAs. The apply-step price and the order receipt
    // are ALWAYS shown (consumer law) and are not affected. Flip with: php artisan ukv:prices on|off
    'show_prices' => (bool) env('UKV_SHOW_PRICES', true),

    // Public contact details — surfaced site-wide (topbar, footer, contact page, CTAs).
    'phone' => env('UKV_PHONE', '+44 7882 747584'),      // human-readable display
    'phone_e164' => env('UKV_PHONE_E164', '+447882747584'),  // for tel: links
    'phone_de' => env('UKV_PHONE_DE', '+49 30 0000 0000'),   // Germany display — PLACEHOLDER until real number set
    'phone_de_e164' => env('UKV_PHONE_DE_E164', '+4930000000'),// Germany tel: link — PLACEHOLDER
    // Master toggle for the DE/Europe phone line site-wide. OFF until a real number
    // is live; set UKV_SHOW_DE_PHONE=true to surface it everywhere again.
    'show_de_phone' => filter_var(env('UKV_SHOW_DE_PHONE', false), FILTER_VALIDATE_BOOLEAN),
    'whatsapp' => env('UKV_WHATSAPP', '447882747584'),   // wa.me number (digits only)
    'email' => env('UKV_EMAIL', 'hello@beyondpassports.co.uk'),  // public enquiries inbox
    'email_billing' => env('UKV_EMAIL_BILLING', 'billing@beyondpassports.co.uk'), // reply-to on receipt + refund emails (forward to hello@)
    'email_adviser' => env('UKV_EMAIL_ADVISER', 'adviser@beyondpassports.co.uk'), // named "reach us directly" line on /about (forward to hello@)

    // Travel-insurance INTRODUCER (FCA-safe signpost only). Beyond Passports does NOT sell or arrange
    // insurance and takes no charge — this is an optional affiliate link to an FCA-authorised
    // partner. Leave blank to show a neutral "ask us" note instead of a link.
    'insurance_partner_name' => env('UKV_INSURANCE_PARTNER', ''),  // e.g. AcmeTravelCover
    'insurance_partner_url' => env('UKV_INSURANCE_URL', ''),       // affiliate/landing URL

    // Document-checklist tool: calendar-reminder timing (days). The .ics "start your application
    // by" deadline = travel_date − default_processing_days − deadline_buffer_days.
    'checklist' => [
        // The on-page /checklist/{token} result page is DRAFTED (off). The wizard now emails the
        // checklist + redirects to WhatsApp via a thank-you page. Flip to true (or set
        // UKV_CHECKLIST_RESULT_ENABLED=true) to restore the on-screen result, PDF, calendar + paid tiers.
        'result_enabled' => (bool) env('UKV_CHECKLIST_RESULT_ENABLED', false),
        'default_processing_days' => (int) env('UKV_CHECKLIST_PROCESSING_DAYS', 21),
        'deadline_buffer_days' => (int) env('UKV_CHECKLIST_BUFFER_DAYS', 7),
        // Sticky quick-action bar on the result page (save/email/share/apply always reachable).
        // Set UKV_CHECKLIST_STICKY_BAR=false to revert to the original scroll-only layout.
        'sticky_action_bar' => (bool) env('UKV_CHECKLIST_STICKY_BAR', true),
    ],

    // Public status tracker (/track). DRAFTED (off) — the page + lookup redirect home and the
    // tool card / footer link are hidden. Flip to true (or UKV_TRACK_ENABLED=true) to relaunch.
    'track' => [
        'enabled' => (bool) env('UKV_TRACK_ENABLED', false),
    ],

    // Per-country money pages (/visa/{slug}) + country guide pages (/visa/{slug}/{topic}).
    // DRAFTED (off) — both 302-redirect to /schengen-visa and are dropped from the sitemap, so
    // only the single /schengen-visa hub is live. Flip UKV_COUNTRY_PAGES_ENABLED=true to relaunch.
    'destinations' => [
        'country_pages_enabled' => (bool) env('UKV_COUNTRY_PAGES_ENABLED', false),
    ],

    // "Apply yourself vs us" comparison page (/compare). DRAFTED (off) — redirects home, dropped
    // from the sitemap, footer link hidden. Flip UKV_COMPARE_ENABLED=true to relaunch.
    'compare' => [
        'enabled' => (bool) env('UKV_COMPARE_ENABLED', false),
    ],

    // Appointment slots. auto_hold_on_apply tentatively reserves the soonest slot at a centre we
    // book at when an in-person/biometric order is created (online visas are skipped). The short
    // hold (minutes) auto-releases via slots:release-expired if the customer doesn't proceed.
    'slots' => [
        'auto_hold_on_apply' => (bool) env('UKV_SLOTS_AUTO_HOLD', true),
        'hold_minutes' => (int) env('UKV_SLOTS_HOLD_MINUTES', 60),
    ],

    // Public registered office / location — footer (sitewide), contact, about, legal + Organization
    // schema. PLACEHOLDER until real details supplied; clearly marked so nothing fake-but-real ships.
    // Leave any line blank to omit it.
    'address' => [
        'company' => env('UKV_COMPANY_NAME', 'Beyond Passports Ltd'),
        'company_no' => env('UKV_COMPANY_NO', '17331903'),          // Companies House registration number
        'line1' => env('UKV_ADDR_LINE1', 'Unit 82a James Carter Road'),  // registered office
        'line2' => env('UKV_ADDR_LINE2', 'Mildenhall'),
        'city' => env('UKV_ADDR_CITY', 'Bury St. Edmunds'),
        'postcode' => env('UKV_ADDR_POSTCODE', 'IP28 7DE'),
        'country' => env('UKV_ADDR_COUNTRY', 'United Kingdom'),
    ],

    // Public compliance credentials — /about "document handling" badge strip. Each is blank/false
    // until genuinely held; a certification badge only renders when its value is set, so no
    // uncertified claim ever ships. Empty slots fall back to true operational badges (encrypted
    // transfer, 30-day deletion, confidential access). ICO registration tracked in task #215.
    'compliance' => [
        'ico_number'       => env('UKV_ICO_NUMBER', 'ZC197159'),  // real ICO reg (Beyond Passports Ltd, exp 12 Jul 2027)
        'cyber_essentials' => env('UKV_CYBER_ESSENTIALS', false), // true only once certified
        'insurer'          => env('UKV_INSURER', ''),             // insurer name — blank hides badge
        'indemnity'        => env('UKV_INDEMNITY', ''),           // e.g. "£500,000 per claim" — blank omits amount
    ],

    // Public team — /about (full grid) + named-lead line on home + Organization schema. PLACEHOLDER
    // people until real names/roles/credentials supplied. `lead` marks the case-lead.
    // `photo` = public path or URL; blank → an initials monogram is shown. Current photos are
    // royalty-free PLACEHOLDER portraits — replace with real, consented team photos.
    // `email` = per-member contact pill on /about; blank → no email pill. PLACEHOLDER addresses.
    'team' => [
        ['name' => 'Sarah Whitmore', 'role' => 'Lead Visa Consultant', 'bio' => 'Personally checks every application before it is submitted, nothing outsourced.', 'lead' => true, 'photo' => '/assets/img/team/sarah-whitmore-visa-consultant.jpg', 'email' => 'consultant@beyondpassports.co.uk'],
        ['name' => 'Karim Haddad', 'role' => 'Refusal-Recovery Specialist', 'bio' => 'Decodes refusal letters, finds the real trigger, and rebuilds the file, or tells you honestly when it cannot be recovered.', 'lead' => false, 'photo' => '/assets/img/team/karim-haddad-refusal-specialist.jpg', 'email' => 'refusals@beyondpassports.co.uk'],
        ['name' => 'Chloe Adams', 'role' => 'Appointments & Client Coordinator', 'bio' => 'Monitors slots across all 29 countries daily and keeps you updated at every step.', 'lead' => false, 'photo' => '/assets/img/team/chloe-adams-appointments-coordinator.jpg', 'email' => 'appointments@beyondpassports.co.uk'],
        // Hidden for now — asset + profile kept (photo: david-hartley-case-reviewer.jpg, docs/team-profiles.md). Uncomment to restore.
        // ['name' => 'David Hartley', 'role' => 'Senior Case Reviewer', 'bio' => 'Final review before submission, the wrong bank statement or missing letter caught before an officer sees it.', 'lead' => false, 'photo' => '/assets/img/team/david-hartley-case-reviewer.jpg', 'email' => 'reviewer@beyondpassports.co.uk'],
    ],

    // Shared WhatsApp shown on each /about team card (wa.me digits, no +). Defaults to the real
    // sitewide line so a placeholder never renders; override with UKV_TEAM_WHATSAPP if the team
    // ever gets a dedicated number.
    'team_whatsapp' => env('UKV_TEAM_WHATSAPP', '447882747584'),

    // Social profiles — rendered as footer icons + Organization sameAs schema. Add each URL
    // as the account is created; blank entries are skipped. Env overrides per key.
    'social' => [
        'facebook'  => env('UKV_SOCIAL_FACEBOOK', 'https://www.facebook.com/people/Beyond-Passports/61591144445879/'),
        'instagram' => env('UKV_SOCIAL_INSTAGRAM', 'https://www.instagram.com/beyondpassportsuk/'),
        'tiktok'    => env('UKV_SOCIAL_TIKTOK', 'https://www.tiktok.com/@beyond.passports'),
        'youtube'   => env('UKV_SOCIAL_YOUTUBE', 'https://www.youtube.com/@beyondpassports'),
        'linkedin'  => env('UKV_SOCIAL_LINKEDIN', 'https://www.linkedin.com/in/beyond-passports'),
        'pinterest' => env('UKV_SOCIAL_PINTEREST', 'https://www.pinterest.com/beyondpassports/'),
        'reddit'    => env('UKV_SOCIAL_REDDIT', 'https://www.reddit.com/r/beyondpassports/'),
        'quora'     => env('UKV_SOCIAL_QUORA', 'https://www.quora.com/profile/Beyond-Passports'),
    ],

    // Pinterest domain-verify token (Settings > Claim > add HTML tag). Renders the
    // <meta name="p:domain_verify"> tag in the site head when set.
    'pinterest_verify' => env('UKV_PINTEREST_VERIFY', '1d5c7b9df2ba8448ade05e212387a705'),

    // Google Search Console site-verification token (GSC > HTML tag method). Renders
    // <meta name="google-site-verification"> in the site head — consent-independent, so
    // it works where the consent-gated GTM verification cannot. Blank = tag omitted.
    'google_site_verification' => env('UKV_GOOGLE_SITE_VERIFICATION', '1jOX6QKbeuTbBQyMBqPocBKt_JkTT0rAGwdBgZ6kIR8'),

    // Google Tag Manager container ID. NON-ESSENTIAL (analytics/marketing) — loaded
    // ONLY after the visitor accepts cookies (UK PECR), inside the cookie-consent
    // partial's loadAcceptedScripts(). Blank = GTM never loads. Configure GA4/ads tags
    // INSIDE the GTM container to also respect Consent Mode.
    'gtm_id' => env('UKV_GTM_ID', 'GTM-5DMLL4HR'),

    // Analytics + marketing tags. ALL NON-ESSENTIAL — loaded ONLY after cookie
    // consent (UK PECR) in the cookie-consent partial's loadAcceptedScripts().
    // Blank any one to disable it. Set up direct (not via GTM) so no GTM-tag config
    // is needed; GTM stays available for anything added later.
    'ga4_id'        => env('UKV_GA4_ID', 'G-KR93N3DF55'),       // Google Analytics 4 Measurement ID
    'clarity_id'    => env('UKV_CLARITY_ID', 'xbfqfhmnvp'),     // Microsoft Clarity project ID
    'meta_pixel_id' => env('UKV_META_PIXEL_ID', '1780802003331250'), // Meta (Facebook) Pixel / dataset ID

    // Trustpilot social proof (third-party reviews). Paste the Business Unit ID once the
    // Trustpilot business account is verified; the TrustBox widget stays hidden until then, so
    // no fake stars ever ship. Find these in Trustpilot Business > Integrations > TrustBox.
    'trustpilot' => [
        // Master on/off for ALL public Trustpilot surfaces (TrustBox widget, rating/CTA block,
        // bootstrap + invitejs scripts). OFF now = kept fully in code ("draft"), just hidden.
        // Flip on again with UKV_TRUSTPILOT_ENABLED=true in .env (then php artisan config:cache).
        'enabled'          => env('UKV_TRUSTPILOT_ENABLED', false),
        'business_unit_id' => env('UKV_TRUSTPILOT_BUSINESS_UNIT_ID', '6a399ad11e7ab73189428ce3'), // Beyond Passports business unit
        'template_id'      => env('UKV_TRUSTPILOT_TEMPLATE_ID', '56278e9abfbbba0bdcd568bc'), // Review Collector (the box enabled on this unit)
        'review_token'     => env('UKV_TRUSTPILOT_REVIEW_TOKEN', '4da63923-ed36-4abf-ac4c-18f0db428119'), // required by the Review Collector template
        'domain'           => env('UKV_TRUSTPILOT_DOMAIN', 'beyondpassports.co.uk'),         // your verified review domain
        'profile_url'      => env('UKV_TRUSTPILOT_PROFILE_URL', ''),                         // public review-collection link (optional CTA)
        // Automatic Feedback BCC: Trustpilot's unique invite alias. BCC'd ONLY on the
        // post-delivery ReviewRequest email, so a genuine review invite fires when an order
        // is delivered. Blank = off. (This sends the customer's email + name to Trustpilot,
        // a US sub-processor; ensure the privacy policy lists Trustpilot.)
        'invite_bcc'       => env('UKV_TRUSTPILOT_INVITE_BCC', 'beyondpassports.co.uk+bfd39d893d@invite.trustpilot.com'),
        // Trustpilot Automatic Feedback (invitejs) integration key — loads tp.min.js sitewide
        // and registers the site so review invitations can fire. Blank = script not loaded.
        'invite_js_key'    => env('UKV_TRUSTPILOT_INVITE_JS_KEY', 'CuZ3elh5rTHgYmZL'),

        // ── Manual rating mirror (custom Trustpilot-style widget) ────────────────
        // Type your REAL Trustpilot figures here to show the stars + score widget.
        // Leave blank and the site shows the "Review us on Trustpilot" CTA instead.
        // Update by hand whenever your Trustpilot profile changes (manual sync).
        // ONLY enter true figures — fake ratings are illegal (DMCCA 2024).
        'rating'        => env('UKV_TRUSTPILOT_RATING', '3.7'),       // out of 5 (real, manual)
        'reviews_count' => env('UKV_TRUSTPILOT_REVIEWS_COUNT', '1'),  // real count (manual)
        // Optional: a few real, verbatim reviews to feature (cards). Keep them genuine.
        //   ['name' => 'Aisha K.', 'stars' => 5, 'title' => 'Visa sorted fast',
        //    'text' => 'They checked everything and...', 'date' => 'Jun 2026']
        'reviews'       => [],
    ],

    // Review-platform tiles (the Google + Trustpilot score cards on About).
    // OFF now = hidden until real verified reviews are connected, so we never
    // show empty "load once connected" placeholders. Flip on again with
    // UKV_REVIEW_TILES=true in .env (then php artisan config:cache).
    'review_tiles' => env('UKV_REVIEW_TILES', false),

    // ── Headline stats (single source of truth) ───────────────────────────────
    // Every public page reads its numbers from here so they can never diverge
    // across pages again. The two counters are TIME-BASED ODOMETERS: value =
    // base + floor(days since anchor × per_day), computed by App\Support\SiteStats.
    // Deterministic per calendar day (no DB), so the number ticks up on its own.
    //
    // Compliance note: an odometer increments on a schedule, not per real order —
    // keep the per_day rates conservative and truthful. 'founded' anchors the
    // "since 2019" copy used across the footer, About, stat blocks and LP strips.
    'stats' => [
        'founded_year' => (int) env('UKV_FOUNDED_YEAR', 2019),

        // Applications filed — odometer.
        'applications' => [
            'base'    => (int) env('UKV_STAT_APPLICATIONS_BASE', 647),
            'per_day' => (float) env('UKV_STAT_APPLICATIONS_PER_DAY', 3),
            'anchor'  => env('UKV_STAT_ANCHOR', '2026-07-02'), // date the base was true
        ],
        // Refusal reversals since 2019 — slow odometer (~1 every 20 days).
        'reversals' => [
            'base'    => (int) env('UKV_STAT_REVERSALS_BASE', 13),
            'per_day' => (float) env('UKV_STAT_REVERSALS_PER_DAY', 0.05),
            'anchor'  => env('UKV_STAT_ANCHOR', '2026-07-02'),
        ],

        'approval_pct'  => env('UKV_STAT_APPROVAL_PCT', '94'),      // headline approval rate (%)
        'insurance_min' => env('UKV_STAT_INSURANCE_MIN', '€30,000'), // Schengen medical-cover minimum
        'response_sla'  => env('UKV_STAT_RESPONSE_SLA', '7 minutes'),// first-response target
    ],

    // ── Service pricing ───────────────────────────────────────────────────────
    // One price ladder for the whole site (replaces the divergent per-page ladders).
    // 'show' => false hides the £ figures and renders 'placeholder' in the price
    // slot instead (same font), so the layout holds until real prices are locked.
    // Flip 'show' => true and fill 'amount' on each tier to publish prices.
    'pricing' => [
        'show'        => (bool) env('UKV_PRICING_SHOW', false),
        'placeholder' => env('UKV_PRICING_PLACEHOLDER', 'Get a quote'),
        'currency'    => env('UKV_PRICING_CURRENCY', '£'),
        'tiers' => [
            ['key' => 'basic',    'name' => 'Basic',    'amount' => null, 'featured' => false],
            ['key' => 'popular',  'name' => 'Popular',  'amount' => null, 'featured' => true],
            ['key' => 'advanced', 'name' => 'Advanced', 'amount' => null, 'featured' => false],
        ],
    ],

    // ── Tour packages ("Plan a trip") ─────────────────────────────────────────
    // Drives /plan-a-trip (public.tours). Visa-led holiday packages: we prepare the
    // Schengen visa + book the appointment first, then wrap flights + hotels.
    //
    // COMPLIANCE (do not remove):
    //   'enquiry_only' => true keeps every package CTA as a WhatsApp enquiry (no
    //   priced checkout). Selling FLIGHT-INCLUSIVE packages needs an ATOL licence
    //   (CAA) and organiser insolvency protection under the Package Travel Regs
    //   2018. Until both are in place, leave 'enquiry_only' => true — the page
    //   generates leads only, it does not complete a sale. Prices are never shown.
    //   'img' is a CSS background (gradient placeholder); swap for a real photo URL
    //   under /assets when photography is ready.
    'tours' => [
        'nav_label'    => 'Tour Packages',
        'enquiry_only' => (bool) env('UKV_TOURS_ENQUIRY_ONLY', true),
        // 'img' layers the destination photo over a colour-matched gradient fallback
        // (shown if the photo ever fails to load). Photos are self-hosted, Unsplash-
        // licensed (free commercial use); swap for owned/bought shots any time.
        'packages' => [
            ['name' => 'Paris Long Weekend',     'where' => 'France',                        'days' => '4 days',  'flagship' => false, 'flag' => 'linear-gradient(90deg,#0055A4 33%,#fff 33% 66%,#EF4135 66%)',                 'img' => "url('/assets/tours/paris.jpg') center/cover no-repeat, linear-gradient(135deg,#485563,#29323c)", 'bens' => ['Schengen visa prepared in-house', 'Biometric appointment booked', 'Return flights from the UK', 'Central Paris hotel, 3 nights']],
            ['name' => 'Italy Highlights',       'where' => 'Rome · Florence · Venice',       'days' => '7 days',  'flagship' => false, 'flag' => 'linear-gradient(90deg,#009246 33%,#fff 33% 66%,#CE2B37 66%)',                 'img' => "url('/assets/tours/italy.jpg') center/cover no-repeat, linear-gradient(135deg,#8e2de2,#4a1042)", 'bens' => ['Schengen visa prepared in-house', 'Biometric appointment booked', 'Return flights + internal rail', 'Rome, Florence & Venice, 6 nights']],
            ['name' => 'Greek Islands Escape',   'where' => 'Greece',                         'days' => '7 days',  'flagship' => false, 'flag' => 'linear-gradient(180deg,#0D5EAF 0 20%,#fff 20% 40%,#0D5EAF 40% 60%,#fff 60% 80%,#0D5EAF 80%)', 'img' => "url('/assets/tours/greece.jpg') center/cover no-repeat, linear-gradient(135deg,#1c92d2,#2b5876)", 'bens' => ['Schengen visa prepared in-house', 'Biometric appointment booked', 'Return flights + island ferries', 'Athens & island hotels, 6 nights']],
            ['name' => 'Amsterdam & the Rhine',  'where' => 'Netherlands + Germany',          'days' => '6 days',  'flagship' => false, 'flag' => 'linear-gradient(90deg,#21468B 50%,#FFCE00 50%)',                             'img' => "url('/assets/tours/amsterdam.jpg') center/cover no-repeat, linear-gradient(135deg,#134e5e,#71b280)", 'bens' => ['Schengen visa prepared in-house', 'Biometric appointment booked', 'Return flights + Rhine transfers', 'Amsterdam & Rhine hotels, 5 nights']],
            ['name' => 'Spain & Portugal',       'where' => 'Spain + Portugal',               'days' => '10 days', 'flagship' => false, 'flag' => 'linear-gradient(90deg,#AA151B 50%,#046A38 50%)',                             'img' => "url('/assets/tours/spain-portugal.jpg') center/cover no-repeat, linear-gradient(135deg,#c04848,#480048)", 'bens' => ['Schengen visa prepared in-house', 'Biometric appointment booked', 'Return flights + inter-city rail', 'Madrid, Lisbon & Seville, 9 nights']],
            ['name' => 'Best of Western Europe', 'where' => 'France · Switzerland · Italy',    'days' => '14 days', 'flagship' => true,  'flag' => 'linear-gradient(90deg,#0055A4 33%,#DA291C 33% 66%,#009246 66%)',             'img' => "url('/assets/tours/western-europe.jpg') center/cover no-repeat, linear-gradient(135deg,#0f2027,#203a43,#2c5364)", 'bens' => ['Schengen visa prepared in-house', 'Biometric appointment booked', 'Return flights + scenic first-class rail', 'France, Switzerland & Italy, 13 nights']],
        ],
    ],

    // ── Services catalogue ────────────────────────────────────────────────────
    // Drives the /services hub (public.services). Mirrors the locked 14-silo SEO
    // map (docs/seo-silo-map.md): every service we offer or plan to offer, grouped
    // by silo, each tagged with an honest availability status so nothing reads as
    // "live" before it is. Edit here — the page renders straight from this array.
    //
    //   status: 'available'    → built + bookable now (link goes to the live page)
    //           'coming-soon'  → planned, not live yet (shown, no false promise)
    //           'on-request'   → we help case-by-case; contact us
    //   url:    internal path for the service/hub, or null (falls back to /contact)
    //
    // Compliance: no approval/refund guarantee anywhere; "express" speeds our
    // handling not the government's decision; service fee is separate from any
    // government fee; insurance is signposted via an FCA-authorised partner only.
    'services' => [
        [
            'key'   => 'destinations',
            'layout' => 'cards',
            'label' => 'Destinations & visa preparation',
            'url'   => '/schengen-visa',
            'intro' => 'We get your visa ready for wherever you are going.',
            'kicker' => 'Get your visa',
            'cta' => ['label' => 'See destinations', 'url' => '/schengen-visa'],
            'items' => [
                ['title' => 'Schengen visa', 'desc' => 'We prepare and submit your full Schengen visa.', 'status' => 'available', 'url' => '/schengen-visa'],
                ['title' => 'Tourist & visitor', 'desc' => 'For holidays and visiting friends and family in Europe.', 'status' => 'available', 'url' => '/schengen-visa'],
                ['title' => 'Business & short work trips', 'desc' => 'Meetings, conferences and short trips across the Schengen Area.', 'status' => 'available', 'url' => '/schengen-visa'],
                ['title' => 'Group & family', 'desc' => 'Travelling together? We handle the whole group.', 'status' => 'on-request', 'url' => '/contact'],
            ],
        ],
        [
            'key'   => 'visa-types',
            'layout' => 'cards',
            'label' => 'Which visa do I need?',
            'url'   => '/visa/schengen',
            'intro' => 'Not sure which visa you need? We pick the right one.',
            'kicker' => 'Pick the right visa',
            'cta' => ['label' => 'Ask which visa I need', 'url' => '/tools'],
            'items' => [
                ['title' => 'Tourist & visitor', 'desc' => 'For holidays and visiting friends.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Business', 'desc' => 'For meetings and work trips.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Family visit', 'desc' => 'For visiting family abroad.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Study, medical & cultural', 'desc' => 'Courses, treatment or events under 90 days.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Airport transit', 'desc' => 'Just changing planes? We check if you need one.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Long-stay & multi-entry', 'desc' => 'For longer or frequent trips.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'appointments',
            'layout' => 'cards',
            'label' => 'Appointments',
            'url'   => '/appointments',
            'intro' => 'We book your visa appointment and get you ready for the day.',
            'kicker' => 'Book your appointment',
            'cta' => ['label' => 'Find my nearest centre', 'url' => '/find-a-centre'],
            'items' => [
                ['title' => 'Appointment booking', 'desc' => 'We book your VFS or embassy slot.', 'status' => 'available', 'url' => '/find-a-centre'],
                ['title' => 'Find your nearest centre', 'desc' => 'Find your closest visa centre by postcode.', 'status' => 'available', 'url' => '/find-a-centre'],
                ['title' => 'Express appointments', 'desc' => 'Need it sooner? We find the earliest slot.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'By city', 'desc' => 'Help in London, Manchester, Edinburgh and Birmingham.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'documents',
            'layout' => 'cards',
            'label' => 'Documents',
            'url'   => '/documents',
            'intro' => 'Most visas are refused over documents. We get yours right.',
            'kicker' => 'Get documents right',
            'cta' => ['label' => 'Get my checklist', 'url' => '/document-checklist'],
            'items' => [
                ['title' => 'Document checklist', 'desc' => 'A list of what you need.', 'status' => 'available', 'url' => '/document-checklist'],
                ['title' => 'Document check', 'desc' => 'We check your documents before you submit.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Cover letter', 'desc' => 'We write your cover letter.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Proof of funds', 'desc' => 'We get your bank statements right.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Document legalisation', 'desc' => 'Apostille and legalisation when needed.', 'status' => 'on-request', 'url' => '/contact'],
            ],
        ],
        [
            'key'   => 'refusals',
            'layout' => 'cards',
            'label' => 'Refusals & prevention',
            'url'   => '/visa-refused',
            'intro' => 'Refused before, or worried you might be? We can help.',
            'featured' => true,
            'kicker' => 'Avoid a refusal',
            'cta' => ['label' => 'Talk to our team', 'url' => '/contact'],
            'items' => [
                ['title' => 'Refusal-risk check', 'desc' => 'We spot weak points before you apply.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Refused before?', 'desc' => 'We fix the reason and reapply.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        // ── Schengen-only pivot (2026-06-24): removed non-Schengen silos
        //    'authorisations' (ETIAS/ETA/ESTA/e-visas) and 'driving' (IDP) from the
        //    public catalogue. Recover from git history when restoring other destinations.
        [
            'key'   => 'essentials',
            'label' => 'Travel essentials',
            'url'   => '/travel-essentials',
            'intro' => 'The bits beyond the visa that still get people turned away at the border.',
            'kicker' => 'Beyond the visa',
            'cta' => ['label' => 'Talk to our UK team', 'url' => '/contact'],
            'items' => [
                ['title' => 'Travel insurance', 'desc' => 'Schengen-compliant cover that meets the medical-cover rule, via an FCA-authorised partner. We do not sell it or take a cut. We just point you to it.', 'status' => 'on-request', 'url' => '/contact'],
                ['title' => 'Entry requirements beyond the visa', 'desc' => 'Vaccinations, transit rules and onward tickets, the conditions a visa alone does not cover.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'tools',
            'layout' => 'cards',
            'label' => 'Visa tools',
            'url'   => '/tools',
            'intro' => 'Start here. No account, no card, just answers.',
            'kicker' => 'No account needed',
            'cta' => ['label' => 'Open the visa checker', 'url' => '/tools'],
            'items' => [
                ['title' => 'Visa checker', 'desc' => 'Tell us your trip and we confirm exactly what you need in about a minute.', 'status' => 'available', 'url' => '/tools'],
                ['title' => 'Document checklist', 'desc' => 'A personalised document list for your destination, instantly.', 'status' => 'available', 'url' => '/document-checklist'],
                ['title' => 'Status tracker', 'desc' => 'Follow your application from submission to passport back in your hand.', 'status' => env('UKV_TRACK_ENABLED', false) ? 'available' : 'coming-soon', 'url' => env('UKV_TRACK_ENABLED', false) ? '/track' : null],
                ['title' => '90/180-day calculator & estimators', 'desc' => 'Schengen day-count, fee and timing estimates and a photo check.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'guides',
            'label' => 'Guides & stories',
            'url'   => '/guides',
            'intro' => 'Plain-English answers from people who do this every day.',
            'kicker' => 'Know before you go',
            'cta' => ['label' => 'Read the guides', 'url' => '/guides'],
            'items' => [
                ['title' => 'Country guides & how-tos', 'desc' => 'Step-by-step guides and real traveller stories for each destination.', 'status' => 'available', 'url' => '/guides'],
                ['title' => 'Glossary & FAQ', 'desc' => 'Visa jargon decoded, and the questions everyone asks, answered simply.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'nationality',
            'label' => 'For your nationality',
            'url'   => '/schengen-visa',
            'intro' => 'British passport or not, we get UK residents to Europe.',
            'featured' => true,
            'kicker' => 'Any passport, UK resident',
            'cta' => ['label' => 'Talk to our UK team', 'url' => '/contact'],
            'items' => [
                ['title' => 'UK residents on a non-UK passport', 'desc' => 'Applying for Schengen from the UK on an Indian, Pakistani, Nigerian, Bangladeshi, South African, Filipino or Ghanaian passport, handled with the extra evidence your case needs.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'which-embassy',
            'label' => 'Which embassy do I apply through?',
            'url'   => '/schengen-visa',
            'intro' => 'Apply to the wrong country\'s consulate and it is an automatic refusal. We get this right.',
            'kicker' => 'Avoid wrong-embassy refusals',
            'cta' => ['label' => 'Talk to our UK team', 'url' => '/contact'],
            'items' => [
                ['title' => 'Main-destination & first-entry rule', 'desc' => 'We work out which consulate you must apply to, including tricky multi-country trips.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'pricing',
            'label' => 'Pricing & how it works',
            'url'   => '/compare',
            'intro' => 'One fixed service fee, shown before you pay. No surprises.',
            'kicker' => 'No surprises',
            'cta' => ['label' => 'Compare apply yourself vs us', 'url' => '/compare'],
            'items' => [
                ['title' => 'Apply yourself vs with us', 'desc' => 'A straight side-by-side so you can see exactly what you are paying for.', 'status' => 'available', 'url' => '/compare'],
                ['title' => 'What is included & government fees explained', 'desc' => 'Everything our fee covers, and why the government fee is always separate and set by the authorities.', 'status' => 'available', 'url' => '/compare'],
            ],
        ],
        [
            'key'   => 'partners',
            'label' => 'For travel agents & business',
            'url'   => '/contact',
            'intro' => 'Add visa handling to your business without building a team.',
            'kicker' => 'For your business',
            'cta' => ['label' => 'Get in touch', 'url' => '/contact'],
            'items' => [
                ['title' => 'Partner & referral programme', 'desc' => 'White-label or refer. Give your clients visa support and earn from every booking.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
    ],
];
