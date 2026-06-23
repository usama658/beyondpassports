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
        'line1' => env('UKV_ADDR_LINE1', '1 Example Street'),       // PLACEHOLDER — swap for the real registered office
        'line2' => env('UKV_ADDR_LINE2', ''),
        'city' => env('UKV_ADDR_CITY', 'London'),
        'postcode' => env('UKV_ADDR_POSTCODE', 'EC1A 1AA'),         // PLACEHOLDER postcode
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
        'pinterest' => env('UKV_SOCIAL_PINTEREST', ''),
    ],

    // Pinterest domain-verify token (Settings > Claim > add HTML tag). Renders the
    // <meta name="p:domain_verify"> tag in the site head when set.
    'pinterest_verify' => env('UKV_PINTEREST_VERIFY', '1d5c7b9df2ba8448ade05e212387a705'),

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
];
