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
];
