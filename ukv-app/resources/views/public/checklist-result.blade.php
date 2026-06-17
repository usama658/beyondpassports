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

    // Boarding-pass header facts (live from the snapshot + wizard inputs).
    $inputs       = is_array($request->inputs) ? $request->inputs : [];
    $docCount     = count($items);
    $purposeMap   = ['tourist' => 'Tourism', 'business' => 'Business', 'study' => 'Study', 'other' => 'Travel'];
    $entriesMap   = ['single' => 'single entry', 'multiple' => 'multiple entry'];
    $tripPurpose  = $purposeMap[$inputs['trip_purpose'] ?? ''] ?? null;
    $tripEntries  = $entriesMap[$inputs['visa_entries'] ?? ''] ?? null;
    $tripFacts    = implode(' · ', array_filter([$tripPurpose, $tripEntries])) ?: 'Tailored to your trip';
    $routeTo      = $destName !== 'your trip' ? \Illuminate\Support\Str::upper($destName) : 'YOUR TRIP';
@endphp
<!doctype html>
<html lang="en-GB">
<head>
@include('partials.seo-meta', [
    'title'       => 'Your document checklist for '.$destName.' | Beyond Passports',
    'description' => 'Your tailored document checklist. Keep it, share it, or have it sent to you. Independent service — not a government website.',
    'canonical'   => $shareUrl,
    'noindex'     => true,
])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/ukv.css') }}">
<style>
  /* checklist-result.blade.php — page-scoped layout. Palette/type/components from ukv.css. */

  /* ── HERO — boarding-pass ticket (pick D) ── */
  .crp-hero{background:linear-gradient(180deg,#eef1f3,var(--paper,#f4f5f6));padding:48px 0 40px}
  .crp-pass{
    max-width:840px;margin:0 auto;
    display:grid;grid-template-columns:1fr 232px;
    background:var(--white,#fff);border:1px solid var(--paper-edge,#e6e8ea);
    border-radius:18px;box-shadow:0 30px 70px -45px rgba(40,50,70,.55);overflow:hidden;
  }
  .crp-main{padding:30px 32px}
  .crp-route{display:flex;align-items:center;gap:13px;margin:0 0 16px}
  .crp-route .pt{font-size:13px;font-weight:800;letter-spacing:.05em;color:var(--navy)}
  .crp-route .ln{flex:0 0 56px;height:2px;position:relative;
    background:repeating-linear-gradient(90deg,var(--cta,#C75D38) 0 6px,transparent 6px 11px)}
  .crp-route .ln svg{position:absolute;right:-7px;top:-7px;width:16px;height:16px;color:var(--cta,#C75D38)}
  .crp-main .eyebrow{color:var(--cta,#C75D38)}
  .crp-main h1{color:var(--navy,#22282b);font-size:clamp(26px,3.6vw,38px);font-weight:800;letter-spacing:-.03em;line-height:1.06;margin:0 0 12px}
  .crp-main .lede{color:#46505a;font-size:15px;line-height:1.55;max-width:50ch;margin:0}
  .crp-stub{position:relative;background:var(--navy,#22282b);color:#fff;padding:28px 26px;
    display:flex;flex-direction:column;justify-content:center;gap:16px}
  .crp-stub::before{content:"";position:absolute;left:-9px;top:0;bottom:0;width:18px;z-index:3;
    background:radial-gradient(circle at center,var(--paper,#f4f5f6) 0 6px,transparent 6.5px) 0 0/18px 22px repeat-y}
  .crp-stub .l{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--soft,#F2C2AC);display:block;margin-bottom:3px}
  .crp-stub .v{font-size:18px;font-weight:800;line-height:1.1}
  .crp-stub .v small{font-size:12px;font-weight:600;color:rgba(255,255,255,.7)}
  .crp-stub .v.sm{font-size:15px}
  .crp-stub .ready{color:#7fc7a3}
  @media (max-width:680px){
    .crp-pass{grid-template-columns:1fr}
    .crp-stub{flex-direction:row;flex-wrap:wrap;gap:20px 26px;padding:22px 26px}
    .crp-stub::before{left:0;right:0;top:-9px;bottom:auto;width:auto;height:18px;
      background:radial-gradient(circle at center,var(--paper,#f4f5f6) 0 6px,transparent 6.5px) 0 0/22px 18px repeat-x}
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
  .deliver .dhead{display:flex;align-items:center;gap:14px;margin:0 0 6px}
  .deliver h2{font-size:clamp(20px,2.6vw,24px);color:var(--navy);margin:0;letter-spacing:-.015em}
  .deliver .sub{font-size:14px;color:#33454f;margin:0 0 20px;line-height:1.5}

  .deliver label{display:block;font-family:var(--body);font-weight:600;font-size:13px;color:#4a5b65;margin:0 0 5px;letter-spacing:.01em}
  .deliver input[type=email],
  .deliver input[type=tel]{
    width:100%;
    padding:13px 14px;
    border:1.5px solid var(--paper-edge);
    border-radius:10px;
    font:inherit;
    font-size:15px;
    background:var(--white);
    color:var(--ink);
    transition:border-color .15s ease,box-shadow .15s ease;
  }
  .deliver input[type=email]:hover,
  .deliver input[type=tel]:hover{border-color:#c4cace}
  .deliver input[type=email]:focus,
  .deliver input[type=tel]:focus{border-color:var(--cta);box-shadow:0 0 0 3px rgba(199,93,56,.14);outline:none}

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
  .deliver .channels{display:flex;flex-wrap:wrap;gap:12px;margin:0 0 16px}
  .deliver .chan{
    display:flex;
    gap:9px;
    align-items:flex-start;
    font-size:14px;
    color:#33454f;
    font-weight:500;
    background:var(--paper);
    border:1px solid var(--paper-edge);
    border-radius:10px;
    padding:10px 14px;
    cursor:pointer;
    transition:border-color .15s,box-shadow .15s;
  }
  .deliver .chan:has(input:checked){border-color:var(--cta);box-shadow:0 0 0 2px rgba(199,93,56,.15);background:#fff}
  .deliver .chan input{width:18px;height:18px;flex:0 0 18px;margin-top:1px;accent-color:var(--cta)}

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
    background:#eaf3f2;
    border:1px solid #bfe0db;
    color:var(--stamp-text);
    border-radius:10px;
    padding:13px 16px;
    font-size:14px;
    margin:0 0 18px;
    font-weight:600;
  }

  /* ── Share link panel ── */
  .share{
    margin:0 auto;
    max-width:760px;
    background:var(--white);
    border:1px solid var(--paper-edge);
    border-radius:18px;
    box-shadow:var(--lift-1);
    padding:22px 26px;
  }
  .share .k{
    font-family:var(--body);
    font-weight:700;
    font-size:11px;
    letter-spacing:.12em;
    text-transform:uppercase;
    color:var(--stamp-text);
    margin:0 0 10px;
  }
  .share .url-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
  .share input{
    flex:1 1 280px;
    min-width:0;
    font-family:var(--mono);
    font-size:13px;
    padding:12px 14px;
    border:1.5px solid var(--paper-edge);
    border-radius:10px;
    background:var(--paper);
    color:var(--ink);
  }
  .share input:focus{border-color:var(--cta);outline:none;box-shadow:0 0 0 3px rgba(199,93,56,.12)}
  .share .note{font-size:13px;color:var(--muted);margin:12px 0 0;line-height:1.5}
  .share .action-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}

  /* compliance */
  .compliance{font-size:12.5px;color:var(--muted);line-height:1.6;margin:16px auto 0;max-width:760px}
  .compliance strong{color:var(--ink)}

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
<div class="topbar">Independent service — not a government website &middot; <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}">Call us</a> &middot; <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}">WhatsApp</a></div>
<header class="site-head"><div class="wrap">
  <a href="{{ url('/') }}" class="brand">Beyond <b>Passports</b></a>
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
          <p class="lede">Here's your tailored list, free and yours to keep. Bookmark this page or send it to yourself below — and when you're ready, we'll confirm your exact requirements before you apply.</p>
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
  @if (config('ukv.checklist.sticky_action_bar', true))
  <style>
    /* Sticky action bar — navy command bar (pick A), ties to the boarding-pass hero. */
    .cr-actionbar{position:sticky;top:0;z-index:50;padding:12px 0 4px}
    .cr-actionbar .wrap{
      display:flex;gap:8px;align-items:center;flex-wrap:wrap;
      background:var(--navy,#22282b);
      border-radius:14px;
      padding:11px 16px;
      box-shadow:0 16px 40px -28px rgba(0,0,0,.7);
    }
    .cr-actionbar .ab-label{
      margin-right:auto;display:inline-flex;align-items:center;gap:9px;
      font-family:var(--body,'Plus Jakarta Sans',sans-serif);
      font-weight:800;font-size:11px;letter-spacing:.1em;text-transform:uppercase;
      color:var(--soft,#F2C2AC);
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
    .cr-actionbar .ab-primary{background:var(--cta,#C75D38);color:#fff;font-weight:700;padding:10px 17px}
    .cr-actionbar .ab-primary:hover{background:#b04e2c;box-shadow:0 0 0 3px rgba(199,93,56,.28)}
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

  {{-- ── THE CHECKLIST (snapshotted items) ── --}}
  <section class="cr-section"><div class="wrap">
    <div class="cr-panel reveal">
      @include('partials.doc-checklist', ['items' => $items, 'personalised' => true])
    </div>
  </div></section>

  {{-- ── "SEND ME THIS" DELIVERY OFFER ── --}}
  {{-- Posts to POST /checklist/{token}/send (owned by the delivery agent). Fields:
       email, phone (nullable), channels[] (email|whatsapp|pdf|calendar), marketing_consent. --}}
  <section class="cr-section"><div class="wrap">
    <div class="deliver reveal" id="send">
      <div class="dhead">
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
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

        <p class="ch-label">How should we send it?</p>
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

        <button type="submit" class="btn">Send me my checklist &rarr;</button>
        <p class="hint">WhatsApp is opt-in and only used if you tick it and give a number. We send the checklist you asked for; marketing is separate and only with your consent above.</p>
      </form>
    </div>
  </div></section>

  {{-- ── SHARE LINK ── --}}
  <section class="cr-section"><div class="wrap">
    <div class="share reveal" id="share">
      <p class="k">Your saved link</p>
      <div class="url-row">
        <input type="text" value="{{ $shareUrl }}" readonly aria-label="Saved checklist link" onfocus="this.select()">
        <a href="https://wa.me/?text={{ urlencode('My document checklist for '.$destName.': '.$shareUrl) }}" class="btn btn--wa">Share on WhatsApp</a>
      </div>
      <p class="note">This page is your saved checklist — bookmark it or share it with anyone travelling with you. It won't appear in search results.</p>
      <div class="action-row">
        <a href="{{ url('/checklist/'.$request->token.'/print') }}" class="btn btn--ghost" target="_blank" rel="noopener">Download / print (PDF)</a>
        <a href="{{ url('/checklist/'.$request->token.'/calendar.ics') }}" class="btn btn--ghost">Add a reminder to my calendar</a>
      </div>
    </div>
  </div></section>

  {{-- ── APPLY CTA ── --}}
  <section class="cta-band"><div class="wrap reveal">
    <div class="rule"></div>
    <h2>Got your list — ready to apply?</h2>
    <p style="max-width:52ch;color:#cdd9e1">Start your application and our UK-based team will confirm your exact requirements and check every document before anything is submitted.</p>
    <div class="row">
      <a href="{{ $applyUrl }}" class="btn">Start my application &rarr;</a>
      <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a>
    </div>
  </div></section>

  {{-- ── COMPLIANCE STRIP ── --}}
  <section style="padding:16px 0 40px"><div class="wrap">
    <p class="compliance">
      <strong>Beyond Passports is an independent service and is not a government website.</strong>
      This checklist is general guidance based on the answers you gave — your exact requirements depend on your nationality, residence and full situation, which we confirm before anything is submitted.
      Any service fee is separate from, and additional to, any government or scheme fee. No approval is guaranteed.
    </p>
  </div></section>

</main>

<footer style="padding:0">
  <div class="ft-main"><div class="wrap">
    <div class="cols" style="grid-template-columns:1fr 1fr 1fr;padding:32px 0 22px">
      <div>
        <div class="brand" style="color:#fff">Beyond <b>Passports</b></div>
        <p style="max-width:34ch;font-size:14px;color:#aab0b5">Independent UK visa &amp; eVisa facilitation. Not a government website.</p>
      </div>
      <div>
        <strong>Service</strong>
        <a href="{{ url('/destinations') }}">Destinations</a>
        <a href="{{ url('/document-checklist') }}">Document checklist</a>
        <a href="{{ url('/apply') }}">Start an application</a>
        <a href="{{ url('/track') }}">Track application</a>
      </div>
      <div>
        <strong>Legal</strong>
        <a href="{{ url('/legal') }}#privacy">Privacy</a>
        <a href="{{ url('/legal') }}#terms">Terms</a>
        <a href="{{ url('/legal') }}#disclaimer">Disclaimer</a>
      </div>
    </div>
    <div class="ft-bottom">
      <span>&copy; Beyond Passports. Service fee separate from any government fee. No approval guarantee.</span>
      <span>UK-based team &middot; &#9733; 4.9 rated</span>
    </div>
  </div></div>
</footer>

</body>
</html>
