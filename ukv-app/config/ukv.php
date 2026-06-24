<?php

return [
    // Public base URL (the coded front-end host, e.g. Netlify) used in email links.
    'base_url' => env('UKV_BASE_URL', ''),

    // GDPR document retention: purge stored docs this many days after order closure.
    'doc_retention_days' => env('UKV_DOC_RETENTION_DAYS', 90),

    // Owner/ops digest recipient (daily pending-actions email).
    'owner_email' => env('UKV_OWNER_EMAIL', ''),

    // Show service-fee PRICES on marketing surfaces (home/destination cards, header mega-menu,
    // money-page tier amounts, Offer schema). When false, those become "fee — on request" /
    // "View" and the tier buttons become quote CTAs. The apply-step price and the order receipt
    // are ALWAYS shown (consumer law) and are not affected. Flip with: php artisan ukv:prices on|off
    'show_prices' => (bool) env('UKV_SHOW_PRICES', true),

    // Public contact details — surfaced site-wide (topbar, footer, contact page, CTAs).
    'phone' => env('UKV_PHONE', ''),            // human-readable display, e.g. +44 20 1234 5678
    'phone_e164' => env('UKV_PHONE_E164', ''),  // for tel: links, e.g. +442012345678
    'whatsapp' => env('UKV_WHATSAPP', ''),      // wa.me number (digits only), e.g. 442012345678
    'email' => env('UKV_EMAIL', 'hello@beyondpassports.co.uk'),  // public enquiries inbox

    // Travel-insurance INTRODUCER (FCA-safe signpost only). Beyond Passports does NOT sell or arrange
    // insurance and takes no charge — this is an optional affiliate link to an FCA-authorised
    // partner. Leave blank to show a neutral "ask us" note instead of a link.
    'insurance_partner_name' => env('UKV_INSURANCE_PARTNER', ''),  // e.g. AcmeTravelCover
    'insurance_partner_url' => env('UKV_INSURANCE_URL', ''),       // affiliate/landing URL

    // Document-checklist tool: calendar-reminder timing (days). The .ics "start your application
    // by" deadline = travel_date − default_processing_days − deadline_buffer_days.
    'checklist' => [
        'default_processing_days' => (int) env('UKV_CHECKLIST_PROCESSING_DAYS', 21),
        'deadline_buffer_days' => (int) env('UKV_CHECKLIST_BUFFER_DAYS', 7),
        // Sticky quick-action bar on the result page (save/email/share/apply always reachable).
        // Set UKV_CHECKLIST_STICKY_BAR=false to revert to the original scroll-only layout.
        'sticky_action_bar' => (bool) env('UKV_CHECKLIST_STICKY_BAR', true),
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
        'company_no' => env('UKV_COMPANY_NO', ''),                  // blank until real — no invented Companies House no.
        'line1' => env('UKV_ADDR_LINE1', ''),       // blank until real registered office (address block hides while empty)
        'line2' => env('UKV_ADDR_LINE2', ''),
        'city' => env('UKV_ADDR_CITY', ''),
        'postcode' => env('UKV_ADDR_POSTCODE', ''), // blank until real
        'country' => env('UKV_ADDR_COUNTRY', 'United Kingdom'),
    ],

    // Public team — /about (full grid) + named-lead line on home + Organization schema. PLACEHOLDER
    // people until real names/roles/credentials supplied. `lead` marks the case-lead.
    // `photo` = public path or URL; blank → an initials monogram is shown. Current photos are
    // royalty-free PLACEHOLDER portraits — replace with real, consented team photos.
    // `email` = per-member contact pill on /about; blank → no email pill. PLACEHOLDER addresses.
    'team' => [
        ['name' => 'Sarah Whitfield', 'role' => 'UK Case Lead', 'bio' => 'Personally checks every application before it is submitted, nothing outsourced.', 'lead' => true, 'photo' => '/assets/img/team/sarah-whitfield.jpg', 'email' => 'sarah@beyondpassports.co.uk'],
        ['name' => 'James Okonkwo', 'role' => 'Senior Document Reviewer', 'bio' => 'Reviews funds evidence, validity and consistency, the things that get applications refused.', 'lead' => false, 'photo' => '/assets/img/team/james-okonkwo.jpg', 'email' => 'james@beyondpassports.co.uk'],
        ['name' => 'Priya Sharma', 'role' => 'Client Support Lead', 'bio' => 'Keeps you updated at every step, from first check to delivered authorisation.', 'lead' => false, 'photo' => '/assets/img/team/priya-sharma.jpg', 'email' => 'priya@beyondpassports.co.uk'],
    ],

    // Shared WhatsApp shown on each /about team card (wa.me digits, no +). PLACEHOLDER on the
    // Ofcom reserved-for-drama range (07700 900xxx → not a real number); swap for the real line.
    // Kept separate from the sitewide 'whatsapp' key above so a placeholder never leaks sitewide.
    'team_whatsapp' => env('UKV_TEAM_WHATSAPP', '447700900123'),

    // Social profiles — rendered as footer icons + Organization sameAs schema. Add each URL
    // as the account is created; blank entries are skipped. Env overrides per key.
    'social' => [
        'facebook'  => env('UKV_SOCIAL_FACEBOOK', 'https://www.facebook.com/people/Beyond-Passports/61591144445879/'),
        'instagram' => env('UKV_SOCIAL_INSTAGRAM', 'https://www.instagram.com/beyondpassportsuk/'),
        'tiktok'    => env('UKV_SOCIAL_TIKTOK', ''),
        'youtube'   => env('UKV_SOCIAL_YOUTUBE', 'https://www.youtube.com/@beyondpassports'),
        'linkedin'  => env('UKV_SOCIAL_LINKEDIN', ''),
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
        'business_unit_id' => env('UKV_TRUSTPILOT_BUSINESS_UNIT_ID', '6a399ad3747eb53086311900'), // Beyond Passports business unit
        'template_id'      => env('UKV_TRUSTPILOT_TEMPLATE_ID', '5419b6ffb0d04a076446a9af'), // default: Micro Combo; swap for your chosen TrustBox
        'domain'           => env('UKV_TRUSTPILOT_DOMAIN', 'beyondpassports.co.uk'),         // your verified review domain
        'profile_url'      => env('UKV_TRUSTPILOT_PROFILE_URL', ''),                         // public review-collection link (optional CTA)
        // Automatic Feedback BCC: Trustpilot's unique invite alias. BCC'd ONLY on the
        // post-delivery ReviewRequest email, so a genuine review invite fires when an order
        // is delivered. Blank = off. (This sends the customer's email + name to Trustpilot,
        // a US sub-processor; ensure the privacy policy lists Trustpilot.)
        'invite_bcc'       => env('UKV_TRUSTPILOT_INVITE_BCC', 'beyondpassports.co.uk+bfd39d893d@invite.trustpilot.com'),
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
            'url'   => '/destinations',
            'intro' => 'We prepare the whole application for where you are actually going, not a template, not a guess.',
            'kicker' => 'Done for you, start to finish',
            'cta' => ['label' => 'Browse destinations', 'url' => '/destinations'],
            'items' => [
                ['title' => 'Schengen visa preparation', 'desc' => 'We build and submit your full Schengen application. Funds, forms and cover letter checked against the rules before it reaches the consulate.', 'status' => 'available', 'url' => '/destinations'],
                ['title' => 'eVisa & ETA facilitation', 'desc' => 'Turkey, India and more. We complete the online authorisation correctly the first time, so a small slip does not cost you boarding.', 'status' => 'available', 'url' => '/destinations'],
                ['title' => 'Worldwide / non-Schengen visas', 'desc' => 'Heading further afield? Tell us where and we scope and handle the visa for you, case by case.', 'status' => 'on-request', 'url' => '/contact'],
                ['title' => 'Group & family applications', 'desc' => 'Travelling as a family or group? We link every applicant into one managed case so nobody is left behind.', 'status' => 'on-request', 'url' => '/contact'],
            ],
        ],
        [
            'key'   => 'visa-types',
            'layout' => 'cards',
            'label' => 'By visa type',
            'url'   => '/visa/schengen',
            'intro' => 'Pick the wrong category and you are refused before anyone reads your file. We match your trip to the right one.',
            'kicker' => 'The right category, first time',
            'cta' => ['label' => 'Check what you need', 'url' => '/tools'],
            'items' => [
                ['title' => 'Tourist & visitor', 'desc' => 'Holidays, sightseeing or visiting friends, prepared as a clean, low-risk visitor application.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Business', 'desc' => 'Meetings, conferences and trade events, with the invitation and employer evidence consulates expect.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Family visit', 'desc' => 'Visiting relatives abroad, with the invitation, sponsorship and proof-of-ties evidence that carries weight.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Short study, medical & cultural', 'desc' => 'Courses, treatment or events under 90 days, documented for the exact purpose of your trip.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Airport transit', 'desc' => 'Just changing planes? We confirm whether you need a transit visa and prepare it if you do.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Long-stay & multi-entry', 'desc' => 'Travelling often or staying longer. We build the case for multi-entry and longer-validity visas.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'appointments',
            'layout' => 'cards',
            'label' => 'Appointments',
            'url'   => '/appointments',
            'intro' => 'The slot is half the battle. We find it, book it and get you ready for the day.',
            'kicker' => 'Soonest slots, sorted',
            'cta' => ['label' => 'Find your nearest centre', 'url' => '/find-a-centre'],
            'items' => [
                ['title' => 'Appointment booking', 'desc' => 'We secure your VFS or embassy slot and make sure you walk in with everything in order.', 'status' => 'available', 'url' => '/find-a-centre'],
                ['title' => 'Find your nearest centre', 'desc' => 'Enter your postcode and we point you to the closest visa or IDP centre.', 'status' => 'available', 'url' => '/find-a-centre'],
                ['title' => 'Express appointments', 'desc' => 'Need to go sooner? We hunt the earliest slots. This speeds the appointment, not the consulate decision.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'By city', 'desc' => 'London, Manchester, Edinburgh or Birmingham. Local appointment help wherever you are.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'documents',
            'layout' => 'cards',
            'label' => 'Documents',
            'url'   => '/documents',
            'intro' => 'Most refusals come down to documents. This is where we earn our fee.',
            'kicker' => 'Where refusals start',
            'cta' => ['label' => 'Get your free checklist', 'url' => '/document-checklist'],
            'items' => [
                ['title' => 'Free document checklist', 'desc' => 'A personalised checklist for your exact destination and trip. Free, in under a minute.', 'status' => 'available', 'url' => '/document-checklist'],
                ['title' => 'Document review & pre-check', 'desc' => 'We check every document, by human and AI, against the rules before you submit, catching the gaps that cause refusals.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Cover-letter service', 'desc' => 'A clear, persuasive cover letter that tells your travel story the way a caseworker wants to read it.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Financial-evidence guidance', 'desc' => 'Bank statements and proof of funds presented properly, one of the biggest reasons applications get knocked back.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Document authentication & legalisation', 'desc' => 'Apostille and legalisation arranged when documents need to be officially recognised abroad.', 'status' => 'on-request', 'url' => '/contact'],
            ],
        ],
        [
            'key'   => 'refusals',
            'label' => 'Refusals & prevention',
            'url'   => '/visa-refused',
            'intro' => 'A refusal is costly and it follows you. We stop it before it happens, and help you recover if it already has.',
            'featured' => true,
            'kicker' => 'Prevention first',
            'cta' => ['label' => 'Talk to our UK team', 'url' => '/contact'],
            'items' => [
                ['title' => 'Refusal-risk check', 'desc' => 'We pressure-test your application and show you exactly where it is weak, before the consulate does.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Refused before? Reapply support', 'desc' => 'We read your refusal letter, fix the real reason and rebuild a stronger case for the next attempt.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'authorisations',
            'layout' => 'cards',
            'label' => 'Other travel authorisations',
            'url'   => '/travel-authorisation',
            'intro' => 'Not a visa, but skip it and you do not fly. We track the ones travellers forget.',
            'kicker' => 'Easy to miss',
            'cta' => ['label' => 'Check what your trip needs', 'url' => '/tools'],
            'items' => [
                ['title' => 'ETIAS guidance', 'desc' => 'The EU travel authorisation. We prepare you the moment it goes live.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'ETA (other countries)', 'desc' => 'Electronic travel authorisations for countries that now require one before arrival.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'ESTA (USA)', 'desc' => 'The US visa-waiver authorisation, completed correctly so a typo does not ground you.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Other e-visas', 'desc' => 'Online visas for destinations worldwide, handled end to end.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'driving',
            'label' => 'Driving abroad',
            'url'   => '/driving-abroad',
            'intro' => 'Hire a car abroad without the right permit and you may be uninsured. We sort the right one.',
            'kicker' => 'Stay insured abroad',
            'cta' => ['label' => 'Check the IDP rules', 'url' => '/driving-abroad'],
            'items' => [
                ['title' => 'International Driving Permit (IDP)', 'desc' => 'We confirm the exact IDP type for your destination and licence: 1949, 1968 or both.', 'status' => 'available', 'url' => '/driving-abroad'],
            ],
        ],
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
            'label' => 'Free tools',
            'url'   => '/tools',
            'intro' => 'Start here. No account, no card, just answers.',
            'kicker' => 'Free, no account',
            'cta' => ['label' => 'Open the visa checker', 'url' => '/tools'],
            'items' => [
                ['title' => 'Visa checker', 'desc' => 'Tell us your trip and we confirm exactly what you need in about a minute.', 'status' => 'available', 'url' => '/tools'],
                ['title' => 'Document checklist', 'desc' => 'A personalised document list for your destination, instantly.', 'status' => 'available', 'url' => '/document-checklist'],
                ['title' => 'Status tracker', 'desc' => 'Follow your application from submission to passport back in your hand.', 'status' => 'available', 'url' => '/track'],
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
            'url'   => '/destinations',
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
            'url'   => '/destinations',
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
