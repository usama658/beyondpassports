<?php

return [
    // Public base URL (the coded front-end host, e.g. Netlify) used in email links.
    'base_url' => env('UKV_BASE_URL', ''),

    // GDPR document retention: purge stored docs this many days after order closure.
    'doc_retention_days' => env('UKV_DOC_RETENTION_DAYS', 90),

    // Owner/ops digest recipient (daily pending-actions email).
    'owner_email' => env('UKV_OWNER_EMAIL', ''),

    // Public contact details — surfaced site-wide (topbar, footer, contact page, CTAs).
    'phone' => env('UKV_PHONE', ''),            // human-readable display, e.g. +44 20 1234 5678
    'phone_e164' => env('UKV_PHONE_E164', ''),  // for tel: links, e.g. +442012345678
    'whatsapp' => env('UKV_WHATSAPP', ''),      // wa.me number (digits only), e.g. 442012345678
    'email' => env('UKV_EMAIL', ''),            // public enquiries inbox

    // Travel-insurance INTRODUCER (FCA-safe signpost only). UKVisaCo does NOT sell or arrange
    // insurance and takes no charge — this is an optional affiliate link to an FCA-authorised
    // partner. Leave blank to show a neutral "ask us" note instead of a link.
    'insurance_partner_name' => env('UKV_INSURANCE_PARTNER', ''),  // e.g. AcmeTravelCover
    'insurance_partner_url' => env('UKV_INSURANCE_URL', ''),       // affiliate/landing URL
];
