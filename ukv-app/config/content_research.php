<?php

/**
 * Secondary content-research keyword bank.
 *
 * Market data (external) that feeds `php artisan content:research-secondary`.
 * The command runs on the server and cannot call Ahrefs directly — so the
 * numbers here are refreshed periodically from Ahrefs (via the Claude Ahrefs
 * MCP) and baked in. Treat as a snapshot: re-pull + update `refreshed` when
 * the cluster shifts.
 *
 * Fields per keyword:
 *   term       search phrase
 *   uk         UK monthly search volume (Ahrefs, gb)
 *   global     worldwide monthly volume
 *   kd         keyword difficulty 0-100 (lower = easier to rank)
 *   product    'Schengen prep' | 'Tours' | 'Long-stay'
 *   campaign   maps to docs/social-campaigns.md: refusals | tours | refused-before | move-to-europe
 *   platform   suggested primary platform
 *   landing    site path for the UTM'd link
 *   angle      the post hook
 *   fresh      true = newsjack/time-sensitive (score boost + verify-before-post)
 *
 * Compliance: volumes are planning inputs, not public claims. Verify every
 * visa fact before posting (rules change) — route through the guide freshness
 * check. Sources: docs/long-stay-visa-research.md + Ahrefs gb/global pulls.
 */

return [

    'refreshed' => '2026-07-04',

    'keywords' => [

        // ── Long-stay (verified UK Ahrefs, docs/long-stay-visa-research.md) ──
        ['term' => 'spain digital nomad visa', 'uk' => 1300, 'global' => 12000, 'kd' => 5,  'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'LinkedIn',  'landing' => 'contact', 'angle' => 'Spain digital nomad visa: who qualifies from the UK', 'fresh' => false],
        ['term' => 'portugal d7 visa',          'uk' => 800,  'global' => 5900,  'kd' => 12, 'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'LinkedIn',  'landing' => 'contact', 'angle' => 'Portugal D7: the passive-income route Brits are using', 'fresh' => false],
        ['term' => 'portugal digital nomad visa','uk' => 600, 'global' => 4600,  'kd' => 12, 'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'LinkedIn',  'landing' => 'contact', 'angle' => 'Portugal D8 vs D7: which nomad route fits you', 'fresh' => false],
        ['term' => 'spain non lucrative visa',  'uk' => 400,  'global' => 2500,  'kd' => 5,  'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'LinkedIn',  'landing' => 'contact', 'angle' => 'Spain non-lucrative visa: retire or remote-work in Spain', 'fresh' => false],
        ['term' => 'germany opportunity card',  'uk' => 400,  'global' => 6100,  'kd' => 24, 'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'LinkedIn',  'landing' => 'contact', 'angle' => 'Germany Opportunity Card (Chancenkarte): the points explained', 'fresh' => true],
        ['term' => 'moving to germany from uk', 'uk' => 350,  'global' => 500,   'kd' => 0,  'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'YouTube',   'landing' => 'contact', 'angle' => 'Moving to Germany from the UK: visa options after Brexit', 'fresh' => false],
        ['term' => 'italy digital nomad visa',  'uk' => 250,  'global' => 2200,  'kd' => 3,  'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'Instagram', 'landing' => 'contact', 'angle' => 'Italy digital nomad visa: finally live, and the catch', 'fresh' => false],
        ['term' => 'germany freelance visa',    'uk' => 80,   'global' => 2100,  'kd' => 2,  'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'LinkedIn',  'landing' => 'contact', 'angle' => 'Germany freelance (Freiberufler) visa in plain English', 'fresh' => false],
        ['term' => 'greece digital nomad visa', 'uk' => 150,  'global' => 1300,  'kd' => 3,  'product' => 'Long-stay', 'campaign' => 'move-to-europe', 'platform' => 'Instagram', 'landing' => 'contact', 'angle' => 'Greek islands on a digital nomad visa: how it works', 'fresh' => false],

        // ── Schengen prep / short-stay (refusal-fear cluster) ───────────────
        ['term' => 'schengen visa refused',     'uk' => 300,  'global' => 3500,  'kd' => 8,  'product' => 'Schengen prep', 'campaign' => 'refused-before', 'platform' => 'Instagram', 'landing' => 'visa-refused', 'angle' => 'Refused a Schengen visa? A rebuild, not a retry', 'fresh' => false],
        ['term' => 'schengen visa documents',   'uk' => 500,  'global' => 9000,  'kd' => 10, 'product' => 'Schengen prep', 'campaign' => 'refusals',       'platform' => 'Pinterest', 'landing' => 'document-checklist', 'angle' => 'The Schengen document list that avoids a refusal', 'fresh' => false],
        ['term' => 'schengen visa appointment', 'uk' => 400,  'global' => 8000,  'kd' => 9,  'product' => 'Schengen prep', 'campaign' => 'refusals',       'platform' => 'Instagram', 'landing' => 'find-a-centre', 'angle' => 'No Schengen appointments? How to find the soonest slot', 'fresh' => false],
        ['term' => 'schengen visa proof of funds','uk' => 250,'global' => 4000,  'kd' => 7,  'product' => 'Schengen prep', 'campaign' => 'refusals',       'platform' => 'LinkedIn',  'landing' => 'document-checklist', 'angle' => 'Proof of funds: the number that gets you refused', 'fresh' => false],
        ['term' => 'schengen travel insurance', 'uk' => 600,  'global' => 12000, 'kd' => 14, 'product' => 'Schengen prep', 'campaign' => 'refusals',       'platform' => 'Pinterest', 'landing' => 'document-checklist', 'angle' => 'Schengen insurance: the €30,000 cover rule', 'fresh' => false],

        // ── Rule-change newsjacks (verify before every post) ────────────────
        ['term' => 'ETIAS',                     'uk' => 8000, 'global' => 90000, 'kd' => 35, 'product' => 'Schengen prep', 'campaign' => 'refusals', 'platform' => 'Instagram', 'landing' => 'guides', 'angle' => 'ETIAS: what changes for UK travellers (and what does not)', 'fresh' => true],
        ['term' => 'EES entry exit system',     'uk' => 2000, 'global' => 20000, 'kd' => 25, 'product' => 'Schengen prep', 'campaign' => 'refusals', 'platform' => 'Instagram', 'landing' => 'guides', 'angle' => 'EES explained: fingerprints at the Schengen border', 'fresh' => true],

        // ── Tours (enquiry-only; ATOL-gated — soft CTA) ─────────────────────
        ['term' => 'europe tour packages',      'uk' => 700,  'global' => 9000,  'kd' => 30, 'product' => 'Tours', 'campaign' => 'tours', 'platform' => 'Instagram', 'landing' => 'tour-packages', 'angle' => 'The Europe tour that sorts your visa too', 'fresh' => false],
        ['term' => 'schengen visa for holiday', 'uk' => 200,  'global' => 3000,  'kd' => 6,  'product' => 'Tours', 'campaign' => 'tours', 'platform' => 'Facebook',  'landing' => 'tour-packages', 'angle' => 'Booking a Europe holiday? Do the visa first', 'fresh' => false],
    ],
];
