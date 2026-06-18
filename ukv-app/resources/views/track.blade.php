@php
    /**
     * Public order status tracker.
     *
     * View data (all optional — page renders the bare form with none):
     *   $result      array|null  successful lookup: ref, stages[], current_index, outcome, now, next
     *   $notFound    bool|null   true when a lookup matched no order
     *   $searchedRef string|null the (normalised) ref the visitor searched for
     *
     * Privacy: this view only ever receives the order reference + generic, derived stage
     * data from TrackController. No customer PII is available here by design.
     */
    $result      = $result      ?? null;
    $notFound    = $notFound    ?? false;
    $searchedRef = $searchedRef ?? null;
    // Document Requirements Engine: personalised checklist for a matched order (from
    // TrackController::lookup via RequirementService::for()). Empty/absent on the bare form
    // and on a not-found result. Document-type guidance only — no PII.
    $docItems    = $docItems    ?? [];

    /** Display the order reference plainly (MRZ flavour removed in the re-skin). */
    $mrz = fn (string $ref): string => strtoupper(trim($ref));
@endphp
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Track your application — UK visa &amp; eVisa status | Beyond Passports</title>
<meta name="description" content="Track your Beyond Passports application with your order reference. See each stage from received to delivered. Independent service — not a government website.">
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  /* Self-contained styles — warm-light "Sunset Coast" palette, matching assets/ukv.css.
     Terracotta CTA, sage accent, cool-grey background, Plus Jakarta Sans throughout. */
  :root{
    --ink:#22282b; --navy:#22282b; --paper:#F4F5F6; --gold:#C75D38; --stamp:#5C9A7B;
    --stamp-text:#3f7259;
    --cta:#C75D38; --paper-edge:#e6e8ea; --white:#fff; --muted:#697079; --hint:#697079;
    --shadow:0 18px 44px -26px rgba(40,50,70,.30);
    --display:"Plus Jakarta Sans",system-ui,-apple-system,sans-serif;
    --body:"Plus Jakarta Sans",system-ui,-apple-system,sans-serif;
    --mono:"Plus Jakarta Sans",system-ui,-apple-system,sans-serif;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:var(--body);color:var(--ink);background:var(--paper);line-height:1.6}
  .wrap{max-width:1080px;margin:0 auto;padding:0 22px}
  a{color:var(--cta)}
  .skip-link{position:absolute;left:-9999px;top:0}
  .skip-link:focus{left:8px;top:8px;background:#fff;padding:8px 12px;border-radius:6px;z-index:10}
  .topbar{background:var(--navy);color:#cdd9e1;font-size:13px;text-align:center;padding:7px 12px}
  .topbar a{color:#fff}
  .site-head{background:var(--white);border-bottom:1px solid var(--paper-edge)}
  .site-head .wrap{display:flex;align-items:center;justify-content:space-between;padding-top:14px;padding-bottom:14px}
  .brand{font-family:var(--display);font-weight:800;font-size:22px;color:var(--ink);text-decoration:none}
  .brand b{color:var(--cta)}
  .nav a{margin-left:18px;text-decoration:none;color:var(--ink);font-weight:500;font-size:15px}
  .btn{display:inline-block;background:var(--cta);color:#fff;text-decoration:none;border:0;border-radius:8px;padding:13px 22px;font-weight:600;font-size:15px;cursor:pointer;font-family:inherit}
  .btn--ghost{background:transparent;color:var(--navy);border:1px solid var(--paper-edge)}

  .track-hero{padding:56px 0 0}
  .track-grid{max-width:640px;margin:0 auto;text-align:center}
  .eyebrow{font-family:var(--body);font-weight:700;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:var(--cta);margin:0 0 6px}
  .track-hero h1{font-family:var(--display);font-weight:800;font-size:clamp(34px,5vw,54px);color:var(--ink);letter-spacing:-.015em;margin:0 0 12px}
  .track-hero p.lede{font-size:18px;color:#33454f;max-width:48ch;margin:0 auto}

  /* Navy tracker card (pick A) */
  .lookup{position:relative;overflow:hidden;max-width:520px;margin:30px auto 0;text-align:left;background:var(--navy);border:0;border-radius:18px;box-shadow:0 30px 70px -42px rgba(0,0,0,.6)}
  .lookup::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 70% at 92% 0,rgba(199,93,56,.30),transparent 60%),radial-gradient(60% 70% at 0 100%,rgba(92,154,123,.30),transparent 62%)}
  .lookup > *{position:relative;z-index:2}
  .lookup .stub{display:flex;justify-content:space-between;align-items:center;background:transparent;color:#fff;font-family:var(--body);font-weight:700;font-size:13px;padding:14px 22px;border-bottom:1px solid rgba(255,255,255,.12)}
  .lookup .stub .live{display:inline-flex;align-items:center;gap:7px;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#bfe6d2}
  .lookup .stub .live i{width:8px;height:8px;border-radius:50%;background:#7fc7a3;box-shadow:0 0 0 4px rgba(127,199,163,.25)}
  .lookup .cbody{padding:22px}
  .lookup label{display:block;font-weight:700;font-size:13px;margin:0 0 8px;color:rgba(255,255,255,.85)}
  .lookup .ref-input{width:100%;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;font-size:16px;padding:13px 14px;border:1px solid transparent;border-radius:11px;background:rgba(255,255,255,.96);color:var(--ink)}
  .lookup .ref-input:focus{outline:2px solid var(--soft);outline-offset:1px}
  .lookup .hint{font-family:var(--mono);font-size:11px;color:rgba(255,255,255,.55);margin:10px 0 0;letter-spacing:.04em}
  .lookup .form-error{background:rgba(192,57,43,.18);border:1px solid rgba(243,198,194,.4);color:#ffd9d4;border-radius:8px;padding:11px 13px;font-size:14px;margin:14px 0 0}
  .lookup button{margin-top:16px}

  .status{max-width:640px;margin:32px auto 0}
  /* Navy status header (pick C) */
  .status-mrz{position:relative;overflow:hidden;background:var(--navy);border-radius:16px 16px 0 0;padding:24px 26px;color:#fff}
  .status-mrz::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 80% at 92% 0,rgba(199,93,56,.32),transparent 60%),radial-gradient(60% 70% at 0 100%,rgba(92,154,123,.30),transparent 62%)}
  .status-mrz > *{position:relative;z-index:2}
  .status-mrz .lab{font-family:var(--mono);font-weight:700;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--soft);margin:0 0 4px}
  .status-mrz .ref{font-family:var(--mono);font-weight:700;font-size:16px;color:#fff;letter-spacing:.04em;margin:0;word-break:break-all}
  .status-mrz .pill{display:inline-flex;align-items:center;gap:7px;background:rgba(199,93,56,.22);border:1px solid rgba(242,194,172,.5);color:var(--soft);border-radius:999px;padding:5px 12px;font-size:11px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;margin:14px 0 0}
  .status-mrz .pill.is-outcome{background:rgba(92,154,123,.2);border-color:rgba(127,199,163,.5);color:#bfe6d2}
  .status-mrz .stage-now{font-family:var(--display);font-weight:800;font-size:22px;letter-spacing:-.02em;color:#fff;margin:8px 0 0}
  .status-card{background:var(--white);border:1px solid var(--paper-edge);border-top:0;border-radius:0 0 16px 16px;box-shadow:var(--shadow);padding:26px}

  /* Vertical timeline (pick C) */
  .timeline{list-style:none;margin:4px 0 6px;padding:0;display:block}
  .stage{position:relative;display:grid;grid-template-columns:30px 1fr;align-items:start;gap:14px;text-align:left;padding:0 0 20px}
  .stage:last-child{padding-bottom:0}
  .stage::before{content:"";position:absolute;left:14px;top:32px;bottom:-4px;width:2px;background:var(--paper-edge);z-index:0}
  .stage:last-child::before{display:none}
  .stage.is-done::before{background:var(--stamp)}
  .stage .dot{position:relative;z-index:1;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f1f5f6;border:2px solid var(--paper-edge);color:var(--hint);font-family:var(--mono);font-weight:700;font-size:13px}
  .stage.is-done .dot{background:var(--stamp);border-color:var(--stamp);color:#fff}
  .stage.is-current .dot{background:var(--cta);border-color:var(--cta);color:#fff;box-shadow:0 0 0 4px rgba(199,93,56,.18)}
  .stage.is-outcome .dot{background:#fdeceb;border-color:#c0392b;color:#8a2a22}
  .stage .name{display:block;font-size:15px;line-height:1.3;color:var(--muted);font-weight:600;padding-top:4px}
  .stage.is-current .name{color:var(--navy);font-weight:700}
  .stage.is-done .name{color:var(--ink);font-weight:700}
  .stage .when{display:block;font-family:var(--mono);font-size:10.5px;color:var(--hint);letter-spacing:.04em;margin-top:3px;text-transform:uppercase}

  .now-next{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:22px 0 0}
  .nn{border:1px solid var(--paper-edge);border-radius:10px;padding:16px 18px;background:#f7fafb}
  .nn .k{font-family:var(--mono);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp);margin:0 0 6px}
  .nn p{margin:0;font-size:14.5px;color:#33454f}
  .reassure{margin:20px 0 0;padding:16px 18px;border:1px dashed var(--paper-edge);border-radius:10px;background:var(--white)}
  .reassure p{margin:0;font-size:14px;color:var(--muted);line-height:1.55}
  .reassure strong{color:var(--ink)}

  .notfound{max-width:640px;margin:24px auto 0;background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:10px;padding:18px 20px}
  .notfound p{margin:0 0 6px;font-size:15px}
  .notfound p:last-child{margin:0}

  /* Navy help band (pick A) */
  .help{position:relative;overflow:hidden;max-width:640px;margin:32px auto 0;display:flex;flex-wrap:wrap;gap:18px;align-items:center;justify-content:space-between;border:0;border-radius:16px;background:var(--navy);color:#fff;padding:22px 24px}
  .help::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 80% at 92% 0,rgba(199,93,56,.30),transparent 60%),radial-gradient(60% 70% at 0 100%,rgba(92,154,123,.30),transparent 62%)}
  .help > *{position:relative;z-index:2}
  .help .help-t b{display:block;font-size:15px;font-weight:700;color:#fff}
  .help .help-t span{font-size:13px;color:rgba(255,255,255,.72)}
  .help .links{display:flex;gap:10px;flex-wrap:wrap}
  .help .links a{display:inline-flex;align-items:center;gap:8px;font-weight:700;font-size:14px;border-radius:11px;padding:11px 18px;text-decoration:none}
  .help .links a svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .help .links .call{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);color:#fff}
  .help .links .wa{background:#25D366;color:#0a3d23}

  footer{background:var(--navy);color:#cdd9e1;margin-top:48px;padding:32px 0}
  footer .brand{color:#fff}
  footer p{max-width:34ch;font-size:14px}

  @media (max-width:620px){
    .now-next{grid-template-columns:1fr}
    .help{flex-direction:column;align-items:flex-start}
  }
</style>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<div class="topbar">Independent service — not a government website · <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}">Call us</a> · <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}">WhatsApp</a></div>
<header class="site-head"><div class="wrap">
  <a href="/" class="brand">Beyond <b>Passports</b></a>
  <nav class="nav" aria-label="Primary"><a href="/#how">How it works</a><a href="/track" aria-current="page">Track</a></nav>
</div></header>

<main id="main">

  <section class="track-hero mesh-hero mesh-hero--sm"><div class="wrap">
    <div class="mh-grid"><div class="mh-copy track-grid">
      <p class="eyebrow">Track your application</p>
      <h1>Where's my visa?</h1>
      <p class="lede">Enter the order reference from your confirmation email to see exactly where your application is — from our first check to delivery.</p>
    </div></div>

    <form class="lookup" method="POST" action="/track/lookup" novalidate>
      @csrf
      <div class="stub"><span>Status tracker</span><span class="live"><i aria-hidden="true"></i> Live</span></div>
      <div class="cbody">
        <label for="ref">Your order reference</label>
        <input
          type="text"
          id="ref"
          name="ref"
          class="ref-input"
          value="{{ old('ref', $searchedRef) }}"
          placeholder="UKV-2026-004821"
          autocomplete="off"
          inputmode="text"
          maxlength="32"
          aria-describedby="ref-hint"
          @error('ref') aria-invalid="true" aria-errormessage="ref-error" @enderror
          required
          aria-required="true">
        <p class="hint" id="ref-hint">Format: UKV-YEAR-NUMBER · it's in your confirmation email</p>
        @error('ref')
          <p class="form-error" id="ref-error" role="alert">{{ $message }}</p>
        @enderror
        <button type="submit" class="btn">Track →</button>
      </div>
    </form>
  </div></section>

  @if ($notFound)
    <section><div class="wrap">
      <div class="notfound" role="status" aria-live="polite">
        <p>We couldn't find an application matching <strong>{{ $searchedRef }}</strong>.</p>
        <p>Please double-check the reference in your confirmation email — it looks like <code>UKV-2026-004821</code> — and try again. If you're still stuck, get in touch and we'll help.</p>
      </div>
    </div></section>
  @endif

  @if ($result)
    <section><div class="wrap">
      <div class="status" role="region" aria-label="Application status" tabindex="-1">

        @php
          $ci = $result['current_index'];
          $currentStage = $result['stages'][$ci]['label'] ?? 'In progress';
          $isOutcomeHead = (bool) $result['outcome'];
          $pillText = match ($result['outcome']) {
              'rejected' => 'Decision received',
              'refunded' => 'Closed',
              default    => 'In progress',
          };
        @endphp
        <div class="status-mrz">
          <p class="lab">Order reference &middot; {{ $result['ref'] }}</p>
          <span class="pill {{ $isOutcomeHead ? 'is-outcome' : '' }}">{{ $pillText }}</span>
          <p class="stage-now">{{ $currentStage }}</p>
        </div>

        <div class="status-card">
          <ol class="timeline" aria-label="Application stages">
            @foreach ($result['stages'] as $i => $stage)
              @php
                $isOutcome = $result['outcome'] && $i === $result['current_index'];
                $classes = match (true) {
                    $isOutcome                  => 'stage is-outcome',
                    $stage['state'] === 'done'  => 'stage is-done',
                    $stage['state'] === 'current' => 'stage is-current',
                    default                     => 'stage',
                };
                $when = match (true) {
                    $isOutcome && $result['outcome'] === 'rejected' => 'Decision in',
                    $isOutcome && $result['outcome'] === 'refunded' => 'Closed',
                    $stage['state'] === 'done'    => 'Done',
                    $stage['state'] === 'current' => 'In progress',
                    default                       => 'To come',
                };
              @endphp
              <li class="{{ $classes }}" @if ($stage['state'] === 'current' && ! $result['outcome']) aria-current="step" @endif>
                <span class="dot" aria-hidden="true">{{ $stage['state'] === 'done' ? '✓' : $i + 1 }}</span>
                <span class="name">{{ $stage['label'] }}<span class="when">{{ $when }}</span></span>
              </li>
            @endforeach
          </ol>

          <div class="now-next">
            <div class="nn">
              <p class="k">What's happening now</p>
              <p>{{ $result['now'] }}</p>
            </div>
            @if ($result['next'])
              <div class="nn">
                <p class="k">What's next</p>
                <p>{{ $result['next'] }}</p>
              </div>
            @endif
          </div>

          <div class="reassure">
            <p><strong>Government processing time is set by the destination's authorities</strong> — not by us, and express speeds our handling only, not their decision. We'll notify you the moment there's an update.</p>
          </div>
        </div>
      </div>
    </div></section>

    {{-- Personalised document checklist (Document Requirements Engine).
         $docItems is the RequirementService::for() output for the matched order — document-type
         guidance only, no PII. Renders nothing when empty (the partial handles that). --}}
    @if (! empty($docItems))
      <section><div class="wrap">
        <div class="status" style="margin-top:24px">
          <div class="status-card" style="border-radius:12px;border-top:1px solid var(--paper-edge)">
            @include('partials.doc-checklist', ['items' => $docItems, 'personalised' => true])
          </div>
        </div>
      </div></section>
    @endif
  @endif

  <section><div class="wrap">
    <div class="help">
      <span class="help-t"><b>Can't find your reference?</b><span>It's in your confirmation email — or we'll look it up with you.</span></span>
      <div class="links">
        <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}" class="call"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>Call us</a>
        <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="wa"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 21l2.2-5.3A8.5 8.5 0 1 1 21 11.5z"/></svg>WhatsApp</a>
      </div>
    </div>
  </div></section>

</main>

<footer><div class="wrap">
  <div class="brand">Beyond <b>Passports</b></div>
  <p>Independent UK visa &amp; eVisa facilitation. Not a government website.</p>
</div></footer>

</body>
</html>
