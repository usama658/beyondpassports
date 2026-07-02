@php
    /**
     * Saved document-checklist result — the shareable, value-first page.
     *
     * View data (from ChecklistController::show):
     *   $request      App\Models\ChecklistRequest  the persisted request (routeKey = token)
     *   $destination  App\Models\Destination|null   the request's destination (eager-loaded)
     *
     * Renders the snapshotted checklist (partials.doc-checklist), a "send me this" delivery
     * offer (POST /checklist/{token}/send — owned by the delivery agent), a share link, an
     * apply CTA and the compliance strip. noindex — the page is per-user / thin.
     *
     * Standalone document (like track.blade.php) rather than @extends('layouts.public') so it
     * can emit its own noindex robots meta without touching the shared layout.
     */
    $items        = $request->items ?? [];
    $paid         = $paid ?? true;
    $peek         = $peek ?? ['count' => count($items), 'categories' => [], 'teaser' => null];
    $tierCards    = $tierCards ?? [];
    $tierMeta     = [
        'standard' => ['name' => 'Standard', 'feat' => false, 'lines' => ['Personalised document list', 'Checked against your trip', 'Saved shareable link']],
        'express'  => ['name' => 'Express',  'feat' => true,  'lines' => ['Everything in Standard', 'Downloadable PDF pack', 'Calendar reminders + emailed copy']],
        'premium'  => ['name' => 'Premium',  'feat' => false, 'lines' => ['Everything in Express', 'Document templates & samples', 'Family checklist + 1:1 WhatsApp review']],
    ];
    $destName     = $destination?->name ?? 'your trip';
    $destSlug     = $destination?->slug;
    $shareUrl     = url('/checklist/'.$request->token);
    $applyUrl     = $destName !== 'your trip'
        ? url('/apply').'?destination='.urlencode($destName)
        : url('/apply');
    $sendAction   = url('/checklist/'.$request->token.'/send');
    $sentOk       = session('checklist_sent');

    // Boarding-pass header facts (live from the snapshot + wizard inputs).
    $inputs       = is_array($request->inputs) ? $request->inputs : [];
    $docCount     = count($items);
    $purposeMap   = ['tourist' => 'Tourism', 'business' => 'Business', 'study' => 'Study', 'other' => 'Travel'];
    $entriesMap   = ['single' => 'single entry', 'multiple' => 'multiple entry'];
    $tripPurpose  = $purposeMap[$inputs['trip_purpose'] ?? ''] ?? null;
    $tripEntries  = $entriesMap[$inputs['visa_entries'] ?? ''] ?? null;
    $tripFacts    = implode(' · ', array_filter([$tripPurpose, $tripEntries])) ?: 'Tailored to your trip';
    $routeTo      = $destName !== 'your trip' ? \Illuminate\Support\Str::upper($destName) : 'YOUR TRIP';
    $waNum        = config('ukv.whatsapp') ?: '440000000000';
    $waHref       = 'https://wa.me/'.$waNum.'?text='.urlencode('Hi Beyond Passports, I would like help with my document checklist for '.$destName.'.');
@endphp
<!doctype html>
<html lang="en-GB">
<head>
@include('partials.seo-meta', [
    'title'       => 'Your document checklist for '.$destName.' | Beyond Passports',
    'description' => 'Your tailored document checklist. Keep it, share it, or have it sent to you. Independent service, not a government website.',
    'canonical'   => $shareUrl,
    'noindex'     => true,
])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="{{ asset('assets/ukv.css') }}">
@include('partials.meta-pixel')
<style>
  /* checklist-result.blade.php — page-scoped layout. Palette/type/components from ukv.css. */

  /* ── HERO — boarding-pass ticket (pick D) ── */
  .crp-hero{background:linear-gradient(180deg,#eef1f3,var(--paper,#F4F6FA));padding:48px 0 40px}
  .crp-pass{
    max-width:840px;margin:0 auto;
    display:grid;grid-template-columns:1fr 232px;
    background:var(--white,#fff);border:1px solid var(--paper-edge,#dde3ec);
    border-radius:18px;box-shadow:0 30px 70px -45px rgba(40,50,70,.55);overflow:hidden;
  }
  .crp-main{padding:30px 32px}
  .crp-route{display:flex;align-items:center;gap:13px;margin:0 0 16px}
  .crp-route .pt{font-size:13px;font-weight:800;letter-spacing:.05em;color:var(--navy)}
  .crp-route .ln{flex:0 0 56px;height:2px;position:relative;
    background:repeating-linear-gradient(90deg,var(--cta,#155E7A) 0 6px,transparent 6px 11px)}
  .crp-route .ln svg{position:absolute;right:-7px;top:-7px;width:16px;height:16px;color:var(--cta,#155E7A)}
  .crp-main .eyebrow{color:var(--cta,#155E7A)}
  .crp-main h1{color:var(--navy,#16222E);font-size:clamp(26px,3.6vw,38px);font-weight:800;letter-spacing:-.03em;line-height:1.06;margin:0 0 12px}
  .crp-main .lede{color:#46505a;font-size:15px;line-height:1.55;max-width:50ch;margin:0}
  .crp-stub{position:relative;background:var(--navy,#16222E);color:#fff;padding:28px 26px;
    display:flex;flex-direction:column;justify-content:center;gap:16px}
  .crp-stub::before{content:"";position:absolute;left:-9px;top:0;bottom:0;width:18px;z-index:3;
    background:radial-gradient(circle at center,var(--paper,#F4F6FA) 0 6px,transparent 6.5px) 0 0/18px 22px repeat-y}
  .crp-stub .l{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--soft,#A9CCDA);display:block;margin-bottom:3px}
  .crp-stub .v{font-size:18px;font-weight:800;line-height:1.1}
  .crp-stub .v small{font-size:12px;font-weight:600;color:rgba(255,255,255,.7)}
  .crp-stub .v.sm{font-size:15px}
  .crp-stub .ready{color:#79CFC2}
  @media (max-width:680px){
    .crp-pass{grid-template-columns:1fr}
    .crp-stub{flex-direction:row;flex-wrap:wrap;gap:20px 26px;padding:22px 26px}
    .crp-stub::before{left:0;right:0;top:-9px;bottom:auto;width:auto;height:18px;
      background:radial-gradient(circle at center,var(--paper,#F4F6FA) 0 6px,transparent 6.5px) 0 0/22px 18px repeat-x}
  }

  /* ── Checklist panel ── */
  .cr-panel{
    background:var(--white);
    border:1px solid var(--paper-edge);
    border-radius:18px;
    box-shadow:var(--lift-2);
    padding:30px 28px;
    margin:0 auto;
    max-width:760px;
  }

  /* ── "Send me this" delivery offer ── */
  .deliver{
    background:var(--white);
    border:1px solid var(--paper-edge);
    border-radius:18px;
    box-shadow:var(--lift-1);
    padding:28px 28px 24px;
    margin:0 auto;
    max-width:760px;
  }
  .deliver .dhead{display:flex;align-items:center;gap:13px;margin:0 0 6px}
  .deliver .dhead .ic{width:42px;height:42px;border-radius:11px;background:#E2F1EE;color:var(--cta);display:flex;align-items:center;justify-content:center;flex:0 0 42px}
  .deliver .dhead .ic svg{width:21px;height:21px}
  .deliver h2{font-size:clamp(20px,2.6vw,24px);color:var(--navy);margin:0;letter-spacing:-.015em}
  .deliver .sub{font-size:14px;color:#33454f;margin:0 0 20px;line-height:1.5}

  .deliver label{display:block;font-family:var(--body);font-weight:600;font-size:13px;color:#4a5b65;margin:0 0 5px;letter-spacing:.01em}
  .deliver input[type=email],
  .deliver input[type=tel]{
    width:100%;
    padding:12px 13px;
    border:1px solid var(--paper-edge);
    border-radius:11px;
    font:inherit;
    font-size:14.5px;
    background:var(--white);
    color:var(--ink);
    transition:border-color .15s ease,box-shadow .15s ease;
  }
  .deliver .dlv-submit{display:inline-flex;align-items:center;gap:8px;border-radius:12px;padding:13px 24px;box-shadow:0 12px 26px -12px rgba(21,94,122,.6)}
  .deliver .dlv-submit svg{width:17px;height:17px}
  .deliver input[type=email]:hover,
  .deliver input[type=tel]:hover{border-color:#c4cace}
  .deliver input[type=email]:focus,
  .deliver input[type=tel]:focus{border-color:var(--cta);box-shadow:0 0 0 3px rgba(21,94,122,.14);outline:none}

  .deliver .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .deliver .field{margin:0 0 14px}

  .deliver .ch-label{
    font-family:var(--body);
    font-weight:700;
    font-size:11px;
    letter-spacing:.12em;
    text-transform:uppercase;
    color:var(--stamp-text);
    margin:8px 0 10px;
  }
  .deliver .channels{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:0 0 16px}
  .deliver .chan{
    display:flex;
    gap:11px;
    align-items:center;
    background:var(--white);
    border:1px solid var(--paper-edge);
    border-radius:12px;
    padding:13px 14px;
    cursor:pointer;
    transition:border-color .15s,box-shadow .15s;
  }
  .deliver .chan .t{width:34px;height:34px;border-radius:9px;background:#E2F1EE;color:var(--cta);display:flex;align-items:center;justify-content:center;flex:0 0 34px}
  .deliver .chan .t svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .deliver .chan .tx{flex:1;min-width:0}
  .deliver .chan .tx b{display:block;font-size:14px;font-weight:700;color:var(--navy);line-height:1.25}
  .deliver .chan .tx span{font-size:11.5px;color:var(--muted)}
  .deliver .chan input{width:20px;height:20px;flex:0 0 20px;margin:0;accent-color:var(--cta)}
  .deliver .chan:has(input:checked){border-color:var(--cta);box-shadow:0 0 0 2px rgba(21,94,122,.15);background:#fff}
  @media (max-width:560px){.deliver .channels{grid-template-columns:1fr}}

  .deliver .consent{display:flex;gap:10px;align-items:flex-start;margin:4px 0 18px;padding:14px 16px;background:var(--paper);border:1px solid var(--paper-edge);border-radius:10px}
  .deliver .consent input{width:18px;height:18px;flex:0 0 18px;margin-top:3px;accent-color:var(--cta)}
  .deliver .consent label{font-weight:400;font-size:13px;color:var(--muted);margin:0;line-height:1.5}
  .deliver .hint{font-size:12px;color:var(--muted);margin:10px 0 0;line-height:1.5}
  .deliver [aria-invalid="true"]{border-color:#c0392b;box-shadow:0 0 0 3px rgba(192,57,43,.14)}

  /* status messages */
  .server-errors{
    background:#fdeceb;
    border:1px solid #f3c6c2;
    color:#8a2a22;
    border-radius:10px;
    padding:14px 18px;
    font-size:14px;
    margin:0 0 18px;
  }
  .server-errors ul{margin:6px 0 0;padding-left:20px}
  .sent-ok{
    background:#E2F1EE;
    border:1px solid #cfe6e0;
    color:var(--stamp-text);
    border-radius:10px;
    padding:13px 16px;
    font-size:14px;
    margin:0 0 18px;
    font-weight:600;
  }

  /* ── Share link panel — navy card (pick B, matches hero + bar) ── */
  .share{
    position:relative;overflow:hidden;
    margin:0 auto;max-width:760px;
    background:var(--navy);
    border-radius:18px;
    box-shadow:0 30px 70px -42px rgba(0,0,0,.7);
    padding:26px 28px;color:#fff;
  }
  .share::before{content:"";position:absolute;inset:0;
    background:radial-gradient(70% 70% at 90% 0,rgba(21,94,122,.32),transparent 60%),radial-gradient(60% 60% at 0 100%,rgba(46,154,140,.3),transparent 62%)}
  .share > *{position:relative;z-index:2}
  .share .k{
    font-family:var(--body);font-weight:800;font-size:11px;letter-spacing:.12em;text-transform:uppercase;
    color:var(--soft);margin:0 0 12px;display:flex;align-items:center;gap:9px;
  }
  .share .k svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .share .url-row{display:flex;gap:10px;align-items:center;background:rgba(255,255,255,.95);border-radius:12px;padding:6px 6px 6px 14px}
  .share input{
    flex:1;min-width:0;border:0;background:transparent;outline:none;
    font-family:var(--mono);font-size:13px;color:var(--navy);
  }
  .share .copy{
    flex:0 0 auto;display:inline-flex;align-items:center;gap:7px;
    background:var(--cta);color:#fff;border:0;font-family:inherit;font-weight:700;font-size:13.5px;
    border-radius:9px;padding:10px 15px;cursor:pointer;transition:background .15s;
  }
  .share .copy:hover{background:#0F4A61}
  .share .copy svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .share .action-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
  .share .action-row a{display:inline-flex;align-items:center;gap:8px;font-family:inherit;font-weight:700;font-size:14px;border-radius:11px;padding:11px 18px;text-decoration:none;transition:filter .15s,background .15s}
  .share .action-row a svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .share .btn--wa{background:#25D366;color:#0a3d23}
  .share .btn--wa:hover{filter:brightness(.96)}
  .share .btn--ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);color:#fff}
  .share .btn--ghost:hover{background:rgba(255,255,255,.14)}
  .share .note{font-size:13px;color:rgba(255,255,255,.62);margin:14px 0 0;line-height:1.5}

  /* compliance — shield badge + text card (matches Guides page) */
  .compliance{display:grid;grid-template-columns:auto 1fr;gap:20px;align-items:center;margin:0 auto;max-width:760px;
    background:var(--white);border:1px solid var(--paper-edge);border-radius:16px;padding:20px 24px;
    box-shadow:0 12px 32px -28px rgba(40,50,70,.5)}
  .compliance .gc-badge{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;
    background:var(--navy);color:#fff;border-radius:13px;width:104px;height:104px;flex:0 0 104px;text-align:center;padding:10px}
  .compliance .gc-badge svg{width:26px;height:26px;color:var(--soft)}
  .compliance .gc-badge span{font-family:var(--body);font-size:10.5px;font-weight:800;letter-spacing:.06em;line-height:1.2}
  .compliance p{margin:0;font-size:13px;line-height:1.65;color:#3a4b55}
  .compliance strong{color:var(--navy)}
  @media (max-width:560px){.compliance{grid-template-columns:1fr;justify-items:start}}

  /* free WhatsApp banner (unpaid gate) — premium dark green, pops vs white tiers */
  .cr-free{position:relative;overflow:hidden;max-width:760px;margin:0 auto;border-radius:18px;padding:24px 26px;
    background:radial-gradient(130% 150% at 0 0,#13361f,#0c2a18);color:#fff;
    display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}
  .cr-free::before{content:"";position:absolute;right:-30px;top:-30px;width:160px;height:160px;border-radius:50%;background:rgba(37,211,102,.18)}
  .cr-free>*{position:relative;z-index:2}
  .cr-free-pill{display:inline-block;font:800 10px var(--display);letter-spacing:.12em;text-transform:uppercase;color:#0c2a18;background:#7ef0aa;border-radius:999px;padding:4px 11px}
  .cr-free b{font:800 19px var(--display);color:#fff;display:block;margin:9px 0 4px}
  .cr-free p{margin:0;font-size:13px;color:rgba(255,255,255,.82);max-width:44ch;line-height:1.5}
  .cr-free-wa{display:inline-flex;align-items:center;gap:9px;border-radius:12px;padding:14px 24px;font:800 15px var(--display);
    color:#0f7a3c;background:#fff;text-decoration:none;white-space:nowrap;box-shadow:0 14px 30px -14px rgba(0,0,0,.6)}
  .cr-free-wa svg{width:20px;height:20px;fill:currentColor}
  @media(max-width:600px){.cr-free{flex-direction:column;align-items:flex-start}}

  /* section spacing */
  .cr-section{padding:20px 0}

  @media (max-width:620px){
    .deliver .grid2{grid-template-columns:1fr}
    .cr-panel,.deliver,.share{padding:22px 18px}
  }
</style>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
@include('partials.site-header')

@include('partials.svg-symbols')

<main id="main">

  {{-- ── HERO ── --}}
  <section class="crp-hero">
    <div class="wrap">
      <div class="crp-pass reveal">
        <div class="crp-main">
          <div class="crp-route" aria-hidden="true">
            <span class="pt">UK</span>
            <span class="ln"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M2 13l20-7-7 20-3-8-8-3z"/></svg></span>
            <span class="pt">{{ $routeTo }}</span>
          </div>
          <p class="eyebrow">Your document checklist</p>
          <h1>What you'll need for {{ $destName }}</h1>
          <p class="lede">Here's your tailored list, free and yours to keep. Bookmark this page or send it to yourself below. When you're ready, we'll confirm your exact requirements before you apply.</p>
        </div>
        <div class="crp-stub">
          <div><span class="l">Documents</span><span class="v">{{ $docCount }} <small>{{ \Illuminate\Support\Str::plural('item', $docCount) }}</small></span></div>
          <div><span class="l">Trip</span><span class="v sm">{{ $tripFacts }}</span></div>
          <div><span class="l">Status</span><span class="v sm ready">Ready &check;</span></div>
        </div>
      </div>
    </div>
  </section>

  {{-- STICKY QUICK-ACTION BAR — keeps save/email/share/apply reachable without deep scroll.
       Config-gated (ukv.checklist.sticky_action_bar): off => original scroll-only layout. The full
       sections below are untouched; this bar just mirrors them as always-visible triggers. --}}
  @if ($paid && config('ukv.checklist.sticky_action_bar', true))
  <style>
    /* Sticky action bar — navy command bar (pick A), ties to the boarding-pass hero. */
    .cr-actionbar{position:sticky;top:64px;z-index:40;padding:12px 0 4px}
    .cr-actionbar .wrap{
      display:flex;gap:8px;align-items:center;flex-wrap:wrap;
      background:var(--navy,#16222E);
      border-radius:14px;
      padding:11px 16px;
      box-shadow:0 16px 40px -28px rgba(0,0,0,.7);
    }
    .cr-actionbar .ab-label{
      margin-right:auto;display:inline-flex;align-items:center;gap:9px;
      font-family:var(--body,"Outfit",system-ui,sans-serif);
      font-weight:800;font-size:11px;letter-spacing:.1em;text-transform:uppercase;
      color:var(--soft,#A9CCDA);
    }
    .cr-actionbar .ab-label svg{width:16px;height:16px;flex:0 0 16px}
    .cr-actionbar a{
      display:inline-flex;align-items:center;gap:8px;
      font-size:13.5px;font-weight:600;padding:9px 14px;border-radius:10px;
      text-decoration:none;white-space:nowrap;transition:background .15s,box-shadow .15s,transform .1s;
    }
    .cr-actionbar a svg{width:16px;height:16px;flex:0 0 16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .cr-actionbar a:hover{transform:translateY(-1px)}
    .cr-actionbar .ab-ghost{border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.06);color:#fff}
    .cr-actionbar .ab-ghost:hover{background:rgba(255,255,255,.12)}
    .cr-actionbar .ab-primary{background:var(--cta,#155E7A);color:#fff;font-weight:700;padding:10px 17px}
    .cr-actionbar .ab-primary:hover{background:#0F4A61;box-shadow:0 0 0 3px rgba(21,94,122,.28)}
    @media (max-width:640px){
      .cr-actionbar{position:fixed;top:auto;bottom:0;left:0;right:0;padding:0;z-index:60}
      .cr-actionbar .wrap{border-radius:0;padding:8px 12px;gap:6px;justify-content:space-between;box-shadow:0 -2px 14px rgba(0,0,0,.3)}
      .cr-actionbar .ab-label{display:none}
      .cr-actionbar a{flex:1;justify-content:center;padding:10px 4px;font-size:12px}
      .cr-actionbar a svg{display:none}
      main#main{padding-bottom:70px}
    }
  </style>
  <div class="cr-actionbar"><div class="wrap">
    <span class="ab-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>Your checklist</span>
    <a class="ab-ghost" href="{{ url('/checklist/'.$request->token.'/print') }}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>Save / PDF</a>
    <a class="ab-ghost" href="#send"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>Email me</a>
    <a class="ab-ghost" href="#share"><svg viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5 8.6 10.5"/></svg>Share</a>
    <a class="ab-primary" href="{{ $applyUrl }}">Start application <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
  </div></div>
  @endif

  {{-- ── THE CHECKLIST — full when paid, redacted peek + gate when unpaid ── --}}
  <section class="cr-section"><div class="wrap">
    @if ($paid)
      <div class="cr-panel reveal">
        @include('partials.doc-checklist', ['items' => $items, 'personalised' => true])
      </div>
    @else
      {{-- PEEK: server-side redaction — count + categories + ONE teaser. No other labels. --}}
      <div class="cr-panel reveal" style="position:relative">
        <p class="ch-label" style="margin-top:0">Your {{ $destName }} checklist · {{ $peek['count'] }} items</p>
        @if (! empty($peek['categories']))
          <p style="color:var(--muted);font-size:13.5px;margin:0 0 16px">Covers: {{ implode(' · ', $peek['categories']) }}</p>
        @endif
        @if ($peek['teaser'] && $peek['count'] > 1)
          <div style="display:flex;gap:11px;align-items:flex-start;padding:12px 0;border-top:1px dashed var(--paper-edge)">
            <span style="color:var(--stamp);font-weight:800">✓</span>
            <span><b style="color:var(--navy)">{{ $peek['teaser']['label'] }}</b>
            @if ($peek['teaser']['note'])<span style="display:block;font-size:12.5px;color:var(--muted)">{{ $peek['teaser']['note'] }}</span>@endif</span>
          </div>
        @endif
        @for ($i = 1; $i < min($peek['count'], 6); $i++)
          <div style="display:flex;gap:11px;align-items:center;padding:12px 0;border-top:1px dashed var(--paper-edge);filter:blur(0)">
            <span style="color:var(--paper-edge);font-weight:800">●</span>
            <span style="height:13px;flex:1;max-width:{{ 60 - ($i*6) }}%;background:var(--paper-edge);border-radius:7px"></span>
          </div>
        @endfor
        <p style="font-size:12.5px;color:var(--muted);margin:16px 0 0">Get the full list below. It appears here instantly.</p>
      </div>

      {{-- FREE WhatsApp path (premium dark-green banner — pops against the white tiers below) --}}
      <div class="cr-free reveal" style="margin-top:18px">
        <div>
          <span class="cr-free-pill">Free</span>
          <b>Just need a quick answer?</b>
          <p>A real UK person on WhatsApp. No payment, general guidance for your trip.</p>
        </div>
        <a href="{{ $waHref }}" target="_blank" rel="noopener" class="cr-free-wa">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
          Ask free on WhatsApp →
        </a>
      </div>

      <div style="display:flex;align-items:center;gap:14px;margin:20px 2px;max-width:760px;margin-inline:auto">
        <span style="flex:1;height:1px;background:var(--paper-edge)"></span>
        <span style="font:800 11px var(--display);letter-spacing:.1em;text-transform:uppercase;color:var(--muted)">or get the full checklist instantly</span>
        <span style="flex:1;height:1px;background:var(--paper-edge)"></span>
      </div>

      @if (session('pay_unavailable'))
        <div role="status" style="max-width:760px;margin:0 auto 14px;background:#fff7e6;border:1px solid #f0d9a8;border-radius:12px;padding:14px 18px;font-size:14px;color:#6b4e10">
          <strong>Card payment is being switched on.</strong> Meanwhile, message our UK team free on WhatsApp above and we'll sort your checklist right away.
        </div>
      @endif

      @if ($errors->any())
        <div class="server-errors" role="alert" style="max-width:760px;margin:0 auto 14px">
          <strong>Please fix the following:</strong>
          <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <form method="POST" action="{{ url('/checklist/'.$request->token.'/checkout') }}" class="cr-panel reveal" style="max-width:760px;margin:0 auto" id="dct-pay">
        @csrf
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
          @foreach (['standard','express','premium'] as $key)
            @continue(! isset($tierCards[$key]))
            <label style="border:1px solid {{ $tierMeta[$key]['feat'] ? 'var(--cta)' : 'var(--paper-edge)' }};border-radius:14px;padding:18px 16px;display:block;cursor:pointer;text-align:left">
              <input type="radio" name="tier" value="{{ $key }}" @checked($key==='express') style="accent-color:var(--cta)">
              <span style="display:block;font:800 12px var(--display);letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text);margin:8px 0 2px">{{ $tierMeta[$key]['name'] }}</span>
              <span style="display:block;font:800 20px var(--display);color:var(--navy)">£{{ rtrim(rtrim(number_format($tierCards[$key], 2), '0'), '.') }}</span>
              <span style="display:block;font-size:12px;color:var(--muted);margin-top:8px">
                @foreach ($tierMeta[$key]['lines'] as $line)✓ {{ $line }}<br>@endforeach
              </span>
            </label>
          @endforeach
        </div>

        <label style="display:flex;gap:10px;align-items:flex-start;margin:16px 0;padding:14px 16px;background:var(--paper);border:1px solid var(--paper-edge);border-radius:10px;font-size:13px;color:var(--muted)">
          <input type="checkbox" name="consent" value="1" style="margin-top:3px;accent-color:var(--cta)" @error('consent') aria-invalid="true" @enderror>
          <span>I want my checklist <strong>delivered immediately</strong> and understand that, because it's digital content provided at once, I <strong>lose my 14-day right to cancel</strong>. No refund once the list is shown.</span>
        </label>

        <button type="submit" class="btn" style="width:100%;padding:15px;font-size:16px">Get my full checklist →</button>
        <p style="font-size:12px;color:var(--muted);text-align:center;margin:12px 0 0">Service fee only, separate from any government fee. No approval guaranteed.</p>
      </form>
    @endif
  </div></section>

  {{-- ── "SEND ME THIS" DELIVERY OFFER ── --}}
  {{-- Posts to POST /checklist/{token}/send (owned by the delivery agent). Fields:
       email, phone (nullable), channels[] (email|whatsapp|pdf|calendar), marketing_consent. --}}
  @if ($paid)
  <section class="cr-section"><div class="wrap">
    <div class="deliver reveal" id="send">
      <div class="dhead">
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 13l20-7-7 20-3-8-8-3z"/></svg></span>
        <h2>Send me this checklist</h2>
      </div>
      <p class="sub">Want a copy to keep? We'll send your checklist plus the saved link, and a calendar reminder to start in good time, if you'd like one.</p>

      @if ($sentOk)
        <p class="sent-ok" role="status">Thanks. We're sending your checklist now. Check your inbox shortly.</p>
      @endif

      @if ($errors->any())
        <div class="server-errors" role="alert">
          <strong>Please fix the following and try again:</strong>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ $sendAction }}" novalidate>
        @csrf
        <div class="grid2">
          <div class="field">
            <label for="email">Email <span style="color:var(--cta)" aria-hidden="true">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com"
              @error('email') aria-invalid="true" @enderror required aria-required="true">
          </div>
          <div class="field">
            <label for="phone">WhatsApp number (optional)</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+44 …">
          </div>
        </div>

        <p class="ch-label">How should we send it?</p>
        <div class="channels">
          <label class="chan">
            <span class="t"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
            <span class="tx"><b>Email</b><span>To your inbox</span></span>
            <input type="checkbox" name="channels[]" value="email" @checked(! old('channels') || in_array('email', (array) old('channels'), true))>
          </label>
          <label class="chan">
            <span class="t"><svg viewBox="0 0 24 24"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 21l2.2-5.3A8.5 8.5 0 1 1 21 11.5z"/></svg></span>
            <span class="tx"><b>WhatsApp</b><span>If you add a number</span></span>
            <input type="checkbox" name="channels[]" value="whatsapp" @checked(in_array('whatsapp', (array) old('channels'), true))>
          </label>
          <label class="chan">
            <span class="t"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></span>
            <span class="tx"><b>Attach a PDF</b><span>Printable copy</span></span>
            <input type="checkbox" name="channels[]" value="pdf" @checked(in_array('pdf', (array) old('channels'), true))>
          </label>
          <label class="chan">
            <span class="t"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
            <span class="tx"><b>Calendar reminder</b><span>.ics, start in time</span></span>
            <input type="checkbox" name="channels[]" value="calendar" @checked(in_array('calendar', (array) old('channels'), true))>
          </label>
        </div>

        <div class="consent">
          <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1" @checked(old('marketing_consent'))>
          <label for="marketing_consent">Keep me posted with occasional tips and reminders about my trip. (Optional: sending the checklist doesn't need this, and you can unsubscribe any time.)</label>
        </div>

        <button type="submit" class="btn dlv-submit">Send me my checklist <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 13l20-7-7 20-3-8-8-3z"/></svg></button>
        <p class="hint">WhatsApp is opt-in and only used if you tick it and give a number. We send the checklist you asked for; marketing is separate and only with your consent above.</p>
      </form>
    </div>
  </div></section>
  @endif

  {{-- ── SHARE LINK ── --}}
  @if ($paid)
  <section class="cr-section"><div class="wrap">
    <div class="share reveal" id="share">
      <p class="k"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>Your saved link</p>
      <div class="url-row">
        <input type="text" id="share-url" value="{{ $shareUrl }}" readonly aria-label="Saved checklist link" onfocus="this.select()">
        <button type="button" class="copy" onclick="copyShareLink(this)">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg><span>Copy</span>
        </button>
      </div>
      <div class="action-row">
        <a href="https://wa.me/?text={{ urlencode('My document checklist for '.$destName.': '.$shareUrl) }}" class="btn--wa"><svg viewBox="0 0 24 24"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 21l2.2-5.3A8.5 8.5 0 1 1 21 11.5z"/></svg>Share on WhatsApp</a>
        <a href="{{ url('/checklist/'.$request->token.'/print') }}" class="btn--ghost" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>Download / print (PDF)</a>
        <a href="{{ url('/checklist/'.$request->token.'/calendar.ics') }}" class="btn--ghost"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Add a reminder</a>
      </div>
      <p class="note">This page is your saved checklist. Bookmark it or share it with anyone travelling with you. It won't appear in search results.</p>
    </div>
  </div></section>
  @endif

  {{-- ── COMPLIANCE STRIP — shield badge + text (pick A) ── --}}
  <section style="padding:16px 0 32px"><div class="wrap">
    <div class="compliance reveal">
      <span class="gc-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>
        <span>NOT A GOVT SITE</span>
      </span>
      <p>
        <strong>Beyond Passports is an independent service and is not a government website.</strong>
        This checklist is general guidance based on the answers you gave. Your exact requirements depend on your nationality, residence and full situation, which we confirm before anything is submitted.
        Any service fee is separate from, and additional to, any government or scheme fee. No approval is guaranteed.
      </p>
    </div>
  </div></section>

  {{-- ── APPLY CTA ── --}}
  <section class="cta-band"><div class="wrap reveal">
    <div class="rule"></div>
    <h2>Got your list. Ready to apply?</h2>
    <p style="max-width:52ch;color:#cdd9e1">Start your application and our UK &amp; Germany team will confirm your exact requirements and check every document before anything is submitted.</p>
    <div class="row">
      <a href="{{ $applyUrl }}" class="btn">Start my application &rarr;</a>
      <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">@include('partials.wa-glyph')Chat on WhatsApp</a>
    </div>
  </div></section>

</main>

{{-- Lead conversion — visitor reached a generated document checklist (free tool). --}}
@include('partials.track-event', ['teEvent' => 'Lead', 'teGa' => 'generate_lead', 'teParams' => ['content_name' => 'document_checklist']])

@include('partials.wa-float')

@include('partials.site-footer')
@include('partials.site-scripts')

<script>
  function copyShareLink(btn){
    var input = document.getElementById('share-url');
    var label = btn.querySelector('span');
    var done = function(){ var t = label.textContent; label.textContent = 'Copied'; setTimeout(function(){ label.textContent = t; }, 1800); };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(input.value).then(done, function(){ input.select(); document.execCommand('copy'); done(); });
    } else {
      input.select(); document.execCommand('copy'); done();
    }
  }
</script>
</body>
</html>
