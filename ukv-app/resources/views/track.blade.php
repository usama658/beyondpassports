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
{{-- Canonical design system: gives this standalone page the shared header/footer styling. --}}
<link rel="stylesheet" href="{{ asset('assets/ukv.css') }}">
<style>
  /* Page-scoped layout for the tracker. Palette/type/components + the shared header
     and footer all come from assets/ukv.css (loaded above). */
  :root{
    --ink:#16222E; --navy:#16222E; --paper:#F4F6FA; --gold:#155E7A; --stamp:#2E9A8C;
    --stamp-text:#1F6E63;
    --cta:#155E7A; --paper-edge:#dde3ec; --white:#fff; --muted:#697079; --hint:#697079;
    --shadow:0 18px 44px -26px rgba(40,50,70,.30);
    --display:"Outfit",system-ui,sans-serif;
    --body:"Outfit",system-ui,sans-serif;
    --mono:"Outfit",ui-monospace,monospace;
  }
  *{box-sizing:border-box}
  a{color:var(--cta)}
  /* The shared topbar, header, footer, .wrap, .brand, .nav, .btn and skip-link are all
     styled by assets/ukv.css (via partials.site-header / partials.site-footer) — no page
     overrides here, so this page's chrome matches every other page exactly. */

  .track-hero{padding:48px 0 10px}
  /* Tracker content sections: ukv.css sets a global section{padding:72px 0} for marketing
     pages — far too airy for this stacked status flow, so override to a tight rhythm. */
  .track-sec{padding:20px 0}
  .eyebrow{font-family:var(--body);font-weight:700;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:var(--cta);margin:0 0 8px}

  /* Boarding-pass lookup (pick B) — matches the checklist-result hero */
  .tp-pass{display:grid;grid-template-columns:1fr 256px;max-width:840px;margin:0 auto;background:var(--white);border:1px solid var(--paper-edge);border-radius:18px;box-shadow:0 30px 70px -45px rgba(40,50,70,.55);overflow:hidden}
  .tp-main{padding:32px 34px}
  .tp-route{display:flex;align-items:center;gap:13px;margin:0 0 14px}
  .tp-route .pt{font-size:13px;font-weight:800;letter-spacing:.05em;color:var(--navy)}
  .tp-route .ln{flex:0 0 64px;height:2px;position:relative;background:repeating-linear-gradient(90deg,var(--cta) 0 6px,transparent 6px 11px)}
  .tp-route .ln svg{position:absolute;right:-7px;top:-8px;width:16px;height:16px;color:var(--cta);fill:none;stroke:currentColor;stroke-width:2.1;stroke-linecap:round;stroke-linejoin:round}
  .tp-main h1{font-family:var(--display);font-weight:800;font-size:clamp(28px,3.8vw,42px);letter-spacing:-.02em;line-height:1.05;color:var(--ink);margin:0 0 8px}
  .tp-main .lede{font-size:15.5px;color:#33454f;margin:0 0 18px;max-width:46ch;line-height:1.55}
  .tp-look{display:flex;gap:10px;align-items:center;background:var(--paper);border:1px solid var(--paper-edge);border-radius:13px;padding:7px 7px 7px 16px;max-width:480px}
  .tp-look:focus-within{border-color:var(--cta);box-shadow:0 0 0 3px rgba(21,94,122,.14)}
  .tp-look .ref-input{flex:1;min-width:0;border:0;background:transparent;outline:none;font-family:var(--mono);font-size:16px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink)}
  .tp-look .btn{flex:0 0 auto;white-space:nowrap}
  .tp-hint{font-family:var(--mono);font-size:11.5px;color:var(--muted);margin:11px 0 0;letter-spacing:.02em}
  .tp-error{background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:9px;padding:10px 13px;font-size:14px;margin:12px 0 0;max-width:480px}
  .tp-stub{position:relative;background:var(--navy);color:#fff;padding:30px 26px;display:flex;flex-direction:column;justify-content:center;gap:16px}
  .tp-stub::before{content:"";position:absolute;left:-9px;top:0;bottom:0;width:18px;z-index:3;background:radial-gradient(circle at center,var(--paper) 0 6px,transparent 6.5px) 0 0/18px 22px repeat-y}
  .tp-stub .l{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--soft);display:block;margin-bottom:4px}
  .tp-stub .v{font-size:16px;font-weight:800;line-height:1.18}
  .tp-stub .live{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;color:#79CFC2}
  .tp-stub .live i{width:9px;height:9px;border-radius:50%;background:#79CFC2;box-shadow:0 0 0 4px rgba(121,207,194,.22)}
  @media (max-width:680px){
    .tp-pass{grid-template-columns:1fr}
    .tp-main{padding:26px 22px}
    .tp-stub{flex-direction:row;flex-wrap:wrap;gap:18px 26px;padding:22px}
    .tp-stub::before{left:0;right:0;top:-9px;bottom:auto;width:auto;height:18px;background:radial-gradient(circle at center,var(--paper) 0 6px,transparent 6.5px) 0 0/22px 18px repeat-x}
  }

  .status{max-width:640px;margin:0 auto}
  /* Navy status header (pick C) */
  .status-mrz{position:relative;overflow:hidden;background:var(--navy);border-radius:16px 16px 0 0;padding:24px 26px;color:#fff}
  .status-mrz::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 80% at 92% 0,rgba(21,94,122,.32),transparent 60%),radial-gradient(60% 70% at 0 100%,rgba(46,154,140,.30),transparent 62%)}
  .status-mrz > *{position:relative;z-index:2}
  .status-mrz .lab{font-family:var(--mono);font-weight:700;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--soft);margin:0 0 4px}
  .status-mrz .ref{font-family:var(--mono);font-weight:700;font-size:16px;color:#fff;letter-spacing:.04em;margin:0;word-break:break-all}
  .status-mrz .pill{display:inline-flex;align-items:center;gap:7px;background:rgba(21,94,122,.22);border:1px solid rgba(169,204,218,.5);color:var(--soft);border-radius:999px;padding:5px 12px;font-size:11px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;margin:14px 0 0}
  .status-mrz .pill.is-outcome{background:rgba(46,154,140,.2);border-color:rgba(121,207,194,.5);color:#79CFC2}
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
  .stage.is-current .dot{background:var(--cta);border-color:var(--cta);color:#fff;box-shadow:0 0 0 4px rgba(21,94,122,.18)}
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

  .notfound{max-width:640px;margin:0 auto;background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:10px;padding:18px 20px}
  .notfound p{margin:0 0 6px;font-size:15px}
  .notfound p:last-child{margin:0}

  /* Navy help band (pick A) */
  .help{position:relative;overflow:hidden;max-width:640px;margin:0 auto;display:flex;flex-wrap:wrap;gap:18px;align-items:center;justify-content:space-between;border:0;border-radius:16px;background:var(--navy);color:#fff;padding:22px 24px}
  .help::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 80% at 92% 0,rgba(21,94,122,.30),transparent 60%),radial-gradient(60% 70% at 0 100%,rgba(46,154,140,.30),transparent 62%)}
  .help > *{position:relative;z-index:2}
  .help .help-t b{display:block;font-size:15px;font-weight:700;color:#fff}
  .help .help-t span{font-size:13px;color:rgba(255,255,255,.72)}
  .help .links{display:flex;gap:10px;flex-wrap:wrap}
  .help .links a{display:inline-flex;align-items:center;gap:8px;font-weight:700;font-size:14px;border-radius:11px;padding:11px 18px;text-decoration:none}
  .help .links a svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .help .links .call{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);color:#fff}
  .help .links .wa{background:#25D366;color:#0a3d23}

  @media (max-width:620px){
    .now-next{grid-template-columns:1fr}
    .help{flex-direction:column;align-items:flex-start}
  }
</style>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
@include('partials.site-header')

<main id="main">

  <section class="track-hero"><div class="wrap">
    <form class="tp-pass reveal" method="POST" action="/track/lookup" novalidate>
      @csrf
      <div class="tp-main">
        <div class="tp-route" aria-hidden="true">
          <span class="pt">UK</span>
          <span class="ln"><svg viewBox="0 0 24 24"><path d="M2 13l20-7-7 20-3-8-8-3z"/></svg></span>
          <span class="pt">YOUR VISA</span>
        </div>
        <p class="eyebrow">Track your application</p>
        <h1>Where's my visa?</h1>
        <p class="lede">Pop in the reference from your confirmation email and we'll show you every stage, live.</p>
        <div class="tp-look">
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
            aria-label="Your order reference"
            aria-describedby="ref-hint"
            @error('ref') aria-invalid="true" aria-errormessage="ref-error" @enderror
            required
            aria-required="true">
          <button type="submit" class="btn">Track →</button>
        </div>
        <p class="tp-hint" id="ref-hint">Format: UKV-YEAR-NUMBER · it's in your confirmation email</p>
        @error('ref')
          <p class="tp-error" id="ref-error" role="alert">{{ $message }}</p>
        @enderror
      </div>
      <div class="tp-stub">
        <div><span class="l">Live status</span><span class="v">Received → Delivered</span></div>
        <div><span class="l">Tracking</span><span class="live"><i aria-hidden="true"></i> Any reference</span></div>
      </div>
    </form>
  </div></section>

  @if ($notFound)
    <section class="track-sec"><div class="wrap">
      <div class="notfound" role="status" aria-live="polite">
        <p>We couldn't find an application matching <strong>{{ $searchedRef }}</strong>.</p>
        <p>Please double-check the reference in your confirmation email — it looks like <code>UKV-2026-004821</code> — and try again. If you're still stuck, get in touch and we'll help.</p>
      </div>
    </div></section>
  @endif

  @if ($result)
    <section class="track-sec"><div class="wrap">
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
      <section class="track-sec" style="padding-top:0"><div class="wrap">
        <div class="status">
          <div class="status-card" style="border-radius:12px;border-top:1px solid var(--paper-edge)">
            @include('partials.doc-checklist', ['items' => $docItems, 'personalised' => true])
          </div>
        </div>
      </div></section>
    @endif
  @endif

  <section class="track-sec"><div class="wrap">
    <div class="help">
      <span class="help-t"><b>Can't find your reference?</b><span>It's in your confirmation email — or we'll look it up with you.</span></span>
      <div class="links">
        <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}" class="call"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>Call us</a>
        <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="wa"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 21l2.2-5.3A8.5 8.5 0 1 1 21 11.5z"/></svg>WhatsApp</a>
      </div>
    </div>
  </div></section>

</main>

@include('partials.site-footer')
@include('partials.site-scripts')

</body>
</html>
