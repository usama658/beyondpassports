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
        ['name' => 'Sarah Whitfield', 'role' => 'UK Case Lead', 'bio' => 'Personally checks every application before it is submitted — nothing outsourced.', 'lead' => true, 'photo' => '/assets/img/team/sarah-whitfield.jpg', 'email' => 'sarah@beyondpassports.co.uk'],
        ['name' => 'James Okonkwo', 'role' => 'Senior Document Reviewer', 'bio' => 'Reviews funds evidence, validity and consistency — the things that get applications refused.', 'lead' => false, 'photo' => '/assets/img/team/james-okonkwo.jpg', 'email' => 'james@beyondpassports.co.uk'],
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
            'label' => 'Destinations & visa preparation',
            'url'   => '/destinations',
            'intro' => 'Checked, prepared and submitted — for the place you are actually going.',
            'items' => [
                ['title' => 'Schengen visa preparation', 'desc' => 'Documents checked, forms prepared, application submitted.', 'status' => 'available', 'url' => '/destinations'],
                ['title' => 'eVisa & ETA facilitation', 'desc' => 'Turkey, India and more — the online authorisations, done right.', 'status' => 'available', 'url' => '/destinations'],
                ['title' => 'Worldwide / non-Schengen visas', 'desc' => 'Other destinations handled case by case.', 'status' => 'on-request', 'url' => '/contact'],
                ['title' => 'Group & family applications', 'desc' => 'One coordinated submission for everyone travelling.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'visa-types',
            'label' => 'By visa type',
            'url'   => '/visa/schengen',
            'intro' => 'The right category for your trip — get this wrong and it is a refusal.',
            'items' => [
                ['title' => 'Tourist & visitor', 'desc' => 'Holidays, sightseeing, visiting friends.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Business', 'desc' => 'Meetings, conferences, trade events.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Family visit', 'desc' => 'Visiting relatives, with the right invitation evidence.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Short study, medical & cultural', 'desc' => 'Courses, treatment, events under 90 days.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Airport transit', 'desc' => 'When a transit visa is required to change planes.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Long-stay & multi-entry', 'desc' => 'Frequent travel and stays beyond a single visit.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'appointments',
            'label' => 'Appointments',
            'url'   => '/appointments',
            'intro' => 'The slot, the centre, the soonest date — sorted.',
            'items' => [
                ['title' => 'Appointment booking', 'desc' => 'VFS / embassy slots booked for you.', 'status' => 'available', 'url' => '/find-a-centre'],
                ['title' => 'Find your nearest centre', 'desc' => 'Closest visa / IDP centre by postcode.', 'status' => 'available', 'url' => '/find-a-centre'],
                ['title' => 'Express appointments', 'desc' => 'Soonest available slots (speeds handling, not the decision).', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'By city', 'desc' => 'London, Manchester, Edinburgh, Birmingham.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'documents',
            'label' => 'Documents',
            'url'   => '/documents',
            'intro' => 'The paperwork that decides it — built, checked, evidenced.',
            'items' => [
                ['title' => 'Free document checklist', 'desc' => 'A personalised list for your destination.', 'status' => 'available', 'url' => '/document-checklist'],
                ['title' => 'Document review & pre-check', 'desc' => 'Human and AI check before you submit.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Cover-letter service', 'desc' => 'A clear, persuasive letter for your application.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Financial-evidence guidance', 'desc' => 'Bank statements and proof of funds, done properly.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Document authentication / legalisation', 'desc' => 'Apostille and legalisation handled case by case.', 'status' => 'on-request', 'url' => '/contact'],
            ],
        ],
        [
            'key'   => 'refusals',
            'label' => 'Refusals & prevention',
            'url'   => '/visa-refused',
            'intro' => 'Spot the weak points before the embassy does.',
            'featured' => true,
            'items' => [
                ['title' => 'Refusal-risk check', 'desc' => 'See where an application is weak before you apply.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Refused before? Reapply support', 'desc' => 'Understand the refusal and rebuild a stronger case.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'authorisations',
            'label' => 'Other travel authorisations',
            'url'   => '/travel-authorisation',
            'intro' => 'Not a visa, still required — the online permits travellers miss.',
            'items' => [
                ['title' => 'ETIAS guidance', 'desc' => 'The EU travel authorisation, when it goes live.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'ETA (other countries)', 'desc' => 'Electronic travel authorisations beyond the UK.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'ESTA (USA)', 'desc' => 'US visa-waiver authorisation.', 'status' => 'coming-soon', 'url' => null],
                ['title' => 'Other e-visas', 'desc' => 'Online visas for destinations worldwide.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'driving',
            'label' => 'Driving abroad',
            'url'   => '/driving-abroad',
            'intro' => 'The right permit for where you drive and the licence you hold.',
            'items' => [
                ['title' => 'International Driving Permit (IDP)', 'desc' => 'The correct IDP type for your destination.', 'status' => 'available', 'url' => '/driving-abroad'],
            ],
        ],
        [
            'key'   => 'essentials',
            'label' => 'Travel essentials',
            'url'   => '/travel-essentials',
            'intro' => 'The rest of what a clean entry needs.',
            'items' => [
                ['title' => 'Travel insurance', 'desc' => 'Schengen-compliant cover via an FCA-authorised partner. We do not sell it or take a charge.', 'status' => 'on-request', 'url' => '/contact'],
                ['title' => 'Entry requirements beyond the visa', 'desc' => 'Vaccinations, transit rules, onward tickets.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'tools',
            'label' => 'Free tools',
            'url'   => '/tools',
            'intro' => 'Use these now, no account, no card.',
            'items' => [
                ['title' => 'Visa checker', 'desc' => 'Tell us your trip; we confirm what you need.', 'status' => 'available', 'url' => '/tools'],
                ['title' => 'Document checklist', 'desc' => 'A personalised checklist in seconds.', 'status' => 'available', 'url' => '/document-checklist'],
                ['title' => 'Status tracker', 'desc' => 'Track your application end to end.', 'status' => 'available', 'url' => '/track'],
                ['title' => '90/180-day calculator & estimators', 'desc' => 'Schengen day-count, fee and timing estimates, photo check.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'guides',
            'label' => 'Guides & stories',
            'url'   => '/guides',
            'intro' => 'Plain-English help, country by country.',
            'items' => [
                ['title' => 'Country guides & how-tos', 'desc' => 'Step-by-step guides and traveller stories.', 'status' => 'available', 'url' => '/guides'],
                ['title' => 'Glossary & FAQ', 'desc' => 'Visa jargon, explained simply.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'nationality',
            'label' => 'For your nationality',
            'url'   => '/destinations',
            'intro' => 'Schengen from the UK on any passport.',
            'featured' => true,
            'items' => [
                ['title' => 'UK residents on a non-UK passport', 'desc' => 'Tailored help for Indian, Pakistani, Nigerian, Bangladeshi, South African, Filipino and Ghanaian passport holders living in the UK.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'which-embassy',
            'label' => 'Which embassy do I apply through?',
            'url'   => '/destinations',
            'intro' => 'Apply through the wrong country and it is refused — we get this right.',
            'items' => [
                ['title' => 'Main-destination & first-entry rule', 'desc' => 'Which consulate to apply to, including multi-country trips.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
        [
            'key'   => 'pricing',
            'label' => 'Pricing & how it works',
            'url'   => '/compare',
            'intro' => 'One fixed service fee, shown before you pay.',
            'items' => [
                ['title' => 'Apply yourself vs with us', 'desc' => 'A clear side-by-side comparison.', 'status' => 'available', 'url' => '/compare'],
                ['title' => 'What is included & government fees explained', 'desc' => 'Our service fee is separate from any government fee.', 'status' => 'available', 'url' => '/compare'],
            ],
        ],
        [
            'key'   => 'partners',
            'label' => 'For travel agents & business',
            'url'   => '/contact',
            'intro' => 'White-label and referral for agencies and corporate travel desks.',
            'items' => [
                ['title' => 'Partner & referral programme', 'desc' => 'Add visa handling to your agency or corporate travel desk.', 'status' => 'coming-soon', 'url' => null],
            ],
        ],
    ],
];
