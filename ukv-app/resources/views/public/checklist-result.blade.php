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
    $destName     = $destination?->name ?? 'your trip';
    $destSlug     = $destination?->slug;
    $shareUrl     = url('/checklist/'.$request->token);
    $applyUrl     = $destName !== 'your trip'
        ? url('/apply').'?destination='.urlencode($destName)
        : url('/apply');
    $sendAction   = url('/checklist/'.$request->token.'/send');
    $sentOk       = session('checklist_sent');
@endphp
<!doctype html>
<html lang="en-GB">
<head>
@include('partials.seo-meta', [
    'title'       => 'Your document checklist for '.$destName.' | UKVisaCo',
    'description' => 'Your tailored document checklist. Keep it, share it, or have it sent to you. Independent service — not a government website.',
    'canonical'   => $shareUrl,
    'noindex'     => true,
])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/ukv.css') }}">
<style>
  /* checklist-result.blade.php — page-scoped layout. Palette/type/components from ukv.css. */
  .cr-hero{padding:48px 0 0}
  .cr-grid{max-width:760px;margin:0 auto}
  .cr-hero .eyebrow{font-family:var(--mono);font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 6px}
  .cr-hero h1{font-size:clamp(30px,4.4vw,44px);color:var(--navy);letter-spacing:-.015em;margin:0 0 10px}
  .cr-hero p.lede{font-size:17px;color:#33454f;max-width:54ch;margin:0}
  .cr-panel{background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;box-shadow:var(--shadow);padding:26px 24px;margin:22px auto 0;max-width:760px}
  /* "send me this" delivery offer */
  .deliver{background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;box-shadow:var(--shadow);padding:24px 24px;margin:22px auto 0;max-width:760px}
  .deliver .dhead{display:flex;align-items:center;gap:12px;margin:0 0 6px}
  .deliver h2{font-size:22px;color:var(--navy);margin:0}
  .deliver .sub{font-size:14px;color:#33454f;margin:0 0 18px}
  .deliver label{display:block;font-family:var(--body);font-weight:600;font-size:13px;color:#4a5b65;margin:0 0 5px;letter-spacing:.01em}
  .deliver input[type=email],.deliver input[type=tel]{width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:6px;font:inherit;font-size:15px;background:var(--white);color:var(--ink)}
  .deliver .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .deliver .field{margin:0 0 14px}
  .deliver .channels{display:flex;flex-wrap:wrap;gap:14px;margin:6px 0 14px}
  .deliver .chan{display:flex;gap:8px;align-items:flex-start;font-size:14px;color:#33454f;font-weight:500}
  .deliver .chan input{width:18px;height:18px;flex:0 0 18px;margin-top:2px}
  .deliver .consent{display:flex;gap:10px;align-items:flex-start;margin:6px 0 16px}
  .deliver .consent input{width:18px;height:18px;flex:0 0 18px;margin-top:3px}
  .deliver .consent label{font-weight:400;font-size:13px;color:var(--muted);margin:0;line-height:1.5}
  .deliver .hint{font-family:var(--mono);font-size:11px;color:var(--hint);margin:8px 0 0;letter-spacing:.04em}
  .deliver [aria-invalid="true"]{border-color:#c0392b;box-shadow:0 0 0 1px #c0392b}
  .server-errors{background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:12px 16px;font-size:14px;margin:0 0 16px}
  .server-errors ul{margin:6px 0 0;padding-left:20px}
  .sent-ok{background:#eaf3f2;border:1px solid #bfe0db;color:#0e6e6e;border-radius:8px;padding:12px 16px;font-size:14px;margin:0 0 16px;font-weight:600}
  /* share row */
  .share{margin:22px auto 0;max-width:760px;background:#f7fafb;border:1px dashed var(--paper-edge);border-radius:12px;padding:18px 20px}
  .share .k{font-family:var(--mono);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 8px}
  .share .url-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
  .share input{flex:1 1 280px;min-width:0;font-family:var(--mono);font-size:13px;padding:11px 12px;border:1px solid var(--paper-edge);border-radius:6px;background:var(--white);color:var(--ink)}
  .share .note{font-size:13px;color:var(--muted);margin:10px 0 0;line-height:1.5}
  .compliance{font-size:12.5px;color:var(--muted);line-height:1.6;margin:16px auto 0;max-width:760px}
  .compliance strong{color:var(--ink)}
  @media (max-width:620px){
    .deliver .grid2{grid-template-columns:1fr}
  }
</style>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<div class="topbar">Independent service — not a government website · <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}">Call us</a> · <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}">WhatsApp</a></div>
<header class="site-head"><div class="wrap">
  <a href="{{ url('/') }}" class="brand">UKVisa<b>Co</b></a>
  <nav class="nav" aria-label="Primary">
    <a href="{{ url('/destinations') }}">Destinations</a>
    <a href="{{ url('/tools') }}">Visa checker</a>
    <a href="{{ url('/document-checklist') }}" aria-current="page">Checklist</a>
    <a href="{{ url('/guides') }}">Guides</a>
    <a href="{{ url('/track') }}" class="btn btn--ghost" style="padding:8px 16px">Track</a>
  </nav>
</div></header>

@include('partials.svg-symbols')

<main id="main">

  {{-- HERO --}}
  <section class="cr-hero"><div class="wrap">
    <div class="cr-grid">
      <p class="eyebrow">Your document checklist</p>
      <h1>What you'll need for {{ $destName }}</h1>
      <p class="lede">Here's your tailored list, free and yours to keep. Bookmark this page or send it to yourself below — and when you're ready, we'll confirm your exact requirements before you apply.</p>
    </div>
  </div></section>

  {{-- THE CHECKLIST (snapshotted items) --}}
  <section><div class="wrap">
    <div class="cr-panel">
      @include('partials.doc-checklist', ['items' => $items, 'personalised' => true])
    </div>
  </div></section>

  {{-- "SEND ME THIS" DELIVERY OFFER --}}
  {{-- Posts to POST /checklist/{token}/send (owned by the delivery agent). Fields:
       email, phone (nullable), channels[] (email|whatsapp|pdf|calendar), marketing_consent. --}}
  <section><div class="wrap">
    <div class="deliver" id="send">
      <div class="dhead">
        <svg width="30" height="30" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <h2>Send me this checklist</h2>
      </div>
      <p class="sub">Want a copy to keep? We'll send your checklist plus the saved link — and a calendar reminder to start in good time, if you'd like one.</p>

      @if ($sentOk)
        <p class="sent-ok" role="status">Thanks — we're sending your checklist now. Check your inbox shortly.</p>
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

        <p style="font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text);margin:6px 0 8px">How should we send it?</p>
        <div class="channels">
          <label class="chan"><input type="checkbox" name="channels[]" value="email" @checked(! old('channels') || in_array('email', (array) old('channels'), true))> Email</label>
          <label class="chan"><input type="checkbox" name="channels[]" value="whatsapp" @checked(in_array('whatsapp', (array) old('channels'), true))> WhatsApp</label>
          <label class="chan"><input type="checkbox" name="channels[]" value="pdf" @checked(in_array('pdf', (array) old('channels'), true))> Attach a PDF</label>
          <label class="chan"><input type="checkbox" name="channels[]" value="calendar" @checked(in_array('calendar', (array) old('channels'), true))> Calendar reminder (.ics)</label>
        </div>

        <div class="consent">
          <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1" @checked(old('marketing_consent'))>
          <label for="marketing_consent">Keep me posted with occasional tips and reminders about my trip. (Optional — sending the checklist doesn't need this, and you can unsubscribe any time.)</label>
        </div>

        <button type="submit" class="btn">Send me my checklist →</button>
        <p class="hint">WhatsApp is opt-in and only used if you tick it and give a number. We send the checklist you asked for; marketing is separate and only with your consent above.</p>
      </form>
    </div>
  </div></section>

  {{-- SHARE LINK --}}
  <section><div class="wrap">
    <div class="share">
      <p class="k">Your saved link</p>
      <div class="url-row">
        <input type="text" value="{{ $shareUrl }}" readonly aria-label="Saved checklist link" onfocus="this.select()">
        <a href="https://wa.me/?text={{ urlencode('My document checklist for '.$destName.': '.$shareUrl) }}" class="btn btn--wa">Share on WhatsApp</a>
      </div>
      <p class="note">This page is your saved checklist — bookmark it or share it with anyone travelling with you. It won't appear in search results.</p>
    </div>
  </div></section>

  {{-- APPLY CTA --}}
  <section class="cta-band"><div class="wrap reveal">
    <div class="rule"></div>
    <h2>Got your list — ready to apply?</h2>
    <p style="max-width:52ch;color:#cdd9e1">Start your application and our UK-based team will confirm your exact requirements and check every document before anything is submitted.</p>
    <div class="row">
      <a href="{{ $applyUrl }}" class="btn">Start my application →</a>
      <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Chat on WhatsApp</a>
    </div>
  </div></section>

  {{-- COMPLIANCE STRIP --}}
  <section><div class="wrap">
    <p class="compliance">
      <strong>UKVisaCo is an independent service and is not a government website.</strong>
      This checklist is general guidance based on the answers you gave — your exact requirements depend on your nationality, residence and full situation, which we confirm before anything is submitted.
      Any service fee is separate from, and additional to, any government or scheme fee. No approval is guaranteed.
    </p>
  </div></section>

</main>

<footer><div class="wrap">
  <div class="cols">
    <div>
      <div class="brand" style="color:#fff">UKVisa<b>Co</b></div>
      <p style="max-width:34ch">Independent UK visa &amp; eVisa facilitation. Not a government website.</p>
    </div>
    <div>
      <strong>Service</strong><br>
      <a href="{{ url('/destinations') }}">Destinations</a><br>
      <a href="{{ url('/document-checklist') }}">Document checklist</a><br>
      <a href="{{ url('/apply') }}">Start an application</a><br>
      <a href="{{ url('/track') }}">Track application</a>
    </div>
    <div>
      <strong>Legal</strong><br>
      <a href="{{ url('/legal') }}#privacy">Privacy</a><br>
      <a href="{{ url('/legal') }}#terms">Terms</a><br>
      <a href="{{ url('/legal') }}#disclaimer">Disclaimer</a>
    </div>
  </div>
</div>
<div class="mrz" style="margin-top:8px"><div class="wrap"><span>UKV&lt;INDEPENDENT&lt;SERVICE&lt;NOT&lt;A&lt;GOVERNMENT&lt;WEBSITE&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>
</footer>

</body>
</html>
