<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Laravel 12 does not ship a config/cors.php by default — the framework's
| Illuminate\Http\Middleware\HandleCors middleware (registered globally)
| reads this file automatically if it exists. We add it because the public
| coded front-end (hosted on Netlify) calls the JSON endpoints below
| cross-origin via XHR/fetch and therefore triggers a CORS preflight.
|
| Only the XHR endpoints need CORS:
|   - POST /apply            (front-end submits the application)
|   - POST /track/lookup     (front-end status lookup, if called via XHR)
|   - POST /documents/upload (front-end document upload, if called via XHR)
|
| /checkout/{ref} and /confirmation/{ref} are full-page navigations
| (top-level browser requests), so they are intentionally NOT listed —
| they do not involve CORS at all. The Stripe webhook is server-to-server
| (no browser origin) and is likewise excluded.
|
*/

/*
| Allowed origins are driven by the UKV_FRONTEND_ORIGIN env var so we never
| commit a hard-coded production domain. It accepts a comma-separated list
| (e.g. "https://apply.example.com,https://www.example.com") which we split
| and trim inline below.
|
| DEV DEFAULT: '*' (any origin) so local Netlify previews / localhost just
| work. LOCK THIS DOWN before / at cutover by setting UKV_FRONTEND_ORIGIN in
| .env to the exact front-end origin(s). With supports_credentials = false a
| wildcard is permitted by the spec, but a wildcard still lets ANY site POST
| to these endpoints from a browser, so prefer an explicit allowlist in prod.
*/

$frontendOrigin = env('UKV_FRONTEND_ORIGIN', '*');

$allowedOrigins = $frontendOrigin === '*'
    ? ['*']
    : array_values(array_filter(array_map('trim', explode(',', $frontendOrigin))));

return [

    // Only the cross-origin XHR endpoints. Full-page navigations are omitted.
    'paths' => ['apply', 'track/lookup', 'documents/upload'],

    // The front-end only ever POSTs to these endpoints. The browser also
    // issues OPTIONS preflights, which HandleCors answers automatically —
    // it does not need to be listed here.
    'allowed_methods' => ['POST'],

    'allowed_origins' => $allowedOrigins,

    // No regex-based origin matching; the explicit list above is sufficient.
    'allowed_origins_patterns' => [],

    // Headers the front-end is allowed to send on these requests. JSON
    // submissions need Content-Type; Accept is harmless and commonly sent.
    // Add 'X-Requested-With' here if the front-end XHR sets it.
    'allowed_headers' => ['Content-Type', 'Accept'],

    // No custom response headers need to be exposed to the front-end JS.
    'exposed_headers' => [],

    // Preflight cache lifetime (seconds). 0 = browser default.
    'max_age' => 0,

    // We do NOT use cookies/Authorization for these public endpoints
    // (auth is by order ref + email in the body), so credentials are off.
    // This MUST stay false while allowed_origins may be '*' — the CORS spec
    // forbids credentialed requests against a wildcard origin.
    'supports_credentials' => false,

];
