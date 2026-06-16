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
];
