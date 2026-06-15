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

    /** Render a reference in MRZ flavour: UKV-2026-004821 -> UKV<2026<004821<<< */
    $mrz = function (string $ref): string {
        $core = strtoupper(str_replace('-', '<', trim($ref)));
        return $core . '<<<';
    };
@endphp
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Track your application — UK visa &amp; eVisa status | UKVisaCo</title>
<meta name="description" content="Track your UKVisaCo application with your order reference. See each stage from received to delivered. Independent service — not a government website.">
<meta name="robots" content="noindex,nofollow">
<style>
  /* Self-contained styles. Palette/type lifted from the coded design (assets/ukv.css);
     swap this <style> for <link rel="stylesheet" href="..."> once the shared CSS ships. */
  :root{
    --ink:#14202E; --navy:#0A2540; --paper:#EEF2F4; --gold:#C8A24A; --stamp:#0E6E6E;
    --cta:#1456B8; --paper-edge:#dfe6ea; --white:#fff; --muted:#5a6b75;
    --shadow:0 18px 40px -24px rgba(10,37,64,.35);
    --mono:'Space Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:var(--ink);background:var(--paper);line-height:1.6}
  .wrap{max-width:1080px;margin:0 auto;padding:0 22px}
  a{color:var(--cta)}
  .skip-link{position:absolute;left:-9999px;top:0}
  .skip-link:focus{left:8px;top:8px;background:#fff;padding:8px 12px;border-radius:6px;z-index:10}
  .topbar{background:var(--navy);color:#cdd9e1;font-size:13px;text-align:center;padding:7px 12px}
  .topbar a{color:#fff}
  .site-head{background:var(--white);border-bottom:1px solid var(--paper-edge)}
  .site-head .wrap{display:flex;align-items:center;justify-content:space-between;padding-top:14px;padding-bottom:14px}
  .brand{font-family:Fraunces,Georgia,serif;font-weight:600;font-size:22px;color:var(--navy);text-decoration:none}
  .brand b{color:var(--gold)}
  .nav a{margin-left:18px;text-decoration:none;color:var(--ink);font-weight:500;font-size:15px}
  .btn{display:inline-block;background:var(--cta);color:#fff;text-decoration:none;border:0;border-radius:8px;padding:13px 22px;font-weight:600;font-size:15px;cursor:pointer;font-family:inherit}
  .btn--ghost{background:transparent;color:var(--navy);border:1px solid var(--paper-edge)}

  .track-hero{padding:56px 0 0}
  .track-grid{max-width:640px;margin:0 auto;text-align:center}
  .eyebrow{font-family:var(--mono);font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--stamp);margin:0 0 6px}
  .track-hero h1{font-family:Fraunces,Georgia,serif;font-size:clamp(34px,5vw,54px);color:var(--navy);letter-spacing:-.015em;margin:0 0 12px}
  .track-hero p.lede{font-size:18px;color:#33454f;max-width:48ch;margin:0 auto}

  .lookup{max-width:640px;margin:24px auto 0;text-align:left;background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;box-shadow:var(--shadow);overflow:hidden}
  .lookup .stub{display:flex;justify-content:space-between;background:var(--navy);color:var(--gold);font-family:var(--mono);font-size:11px;letter-spacing:.14em;padding:12px 22px}
  .lookup .cbody{padding:24px 22px}
  .lookup label{display:block;font-weight:600;font-size:14px;margin:0 0 8px}
  .lookup .ref-input{width:100%;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;font-size:16px;padding:13px 14px;border:1px solid var(--paper-edge);border-radius:8px}
  .lookup .ref-input:focus{outline:2px solid var(--cta);outline-offset:1px}
  .lookup .hint{font-family:var(--mono);font-size:11px;color:#6b7d87;margin:10px 0 0;letter-spacing:.04em}
  .lookup .form-error{background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:11px 13px;font-size:14px;margin:14px 0 0}
  .lookup button{margin-top:16px}

  .status{max-width:760px;margin:32px auto 0}
  .status-mrz{background:var(--navy);border-radius:10px 10px 0 0;padding:20px 22px}
  .status-mrz .lab{font-family:var(--mono);font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--gold);margin:0 0 6px}
  .status-mrz .ref{font-family:var(--mono);font-weight:700;font-size:clamp(18px,3.4vw,26px);color:#fff;letter-spacing:.14em;margin:0;word-break:break-all}
  .status-card{background:var(--white);border:1px solid var(--paper-edge);border-top:0;border-radius:0 0 12px 12px;box-shadow:var(--shadow);padding:26px 22px}

  .timeline{list-style:none;margin:4px 0 6px;padding:0;display:grid;grid-template-columns:repeat(5,1fr);gap:0}
  .stage{position:relative;text-align:center;padding:8px 6px 0}
  .stage::before{content:"";position:absolute;top:30px;left:-50%;width:100%;height:2px;background:repeating-linear-gradient(90deg,var(--paper-edge) 0 6px,transparent 6px 12px);z-index:0}
  .stage:first-child::before{display:none}
  .stage.is-done::before,.stage.is-current::before{background:var(--stamp)}
  .stage .dot{position:relative;z-index:1;width:48px;height:48px;margin:0 auto 10px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f1f5f6;border:2px solid var(--paper-edge);color:#6b7d87;font-family:var(--mono);font-weight:700;font-size:16px}
  .stage.is-done .dot{background:#eaf3f2;border-color:var(--stamp);color:var(--stamp)}
  .stage.is-current .dot{background:var(--navy);border-color:var(--gold);color:#fff;box-shadow:0 0 0 4px rgba(200,162,74,.25)}
  .stage.is-outcome .dot{background:#fdeceb;border-color:#c0392b;color:#8a2a22}
  .stage .name{display:block;font-size:12.5px;line-height:1.35;color:var(--muted);font-weight:600}
  .stage.is-current .name{color:var(--navy)}
  .stage.is-done .name{color:var(--ink)}
  .stage .when{display:block;font-family:var(--mono);font-size:10px;color:#6b7d87;letter-spacing:.04em;margin-top:3px;text-transform:uppercase}

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

  .help{max-width:760px;margin:32px auto 0;display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;border:1px solid var(--paper-edge);border-radius:12px;background:var(--white);padding:18px 22px}
  .help p{margin:0;font-size:15px;color:#33454f}
  .help .links{display:flex;gap:10px;flex-wrap:wrap}

  footer{background:var(--navy);color:#cdd9e1;margin-top:48px;padding:32px 0}
  footer .brand{color:#fff}
  footer p{max-width:34ch;font-size:14px}

  @media (max-width:620px){
    .timeline{grid-template-columns:1fr;gap:0}
    .stage{display:grid;grid-template-columns:48px 1fr;align-items:center;gap:14px;text-align:left;padding:10px 0}
    .stage::before{top:0;left:23px;width:2px;height:50%}
    .stage:first-child::before{display:none}
    .stage .dot{margin:0}
    .now-next{grid-template-columns:1fr}
    .help{flex-direction:column;align-items:flex-start}
  }
</style>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<div class="topbar">Independent service — not a government website · <a href="tel:+440000000000">Call us</a> · <a href="https://wa.me/440000000000">WhatsApp</a></div>
<header class="site-head"><div class="wrap">
  <a href="/" class="brand">UKVisa<b>Co</b></a>
  <nav class="nav" aria-label="Primary"><a href="/#how">How it works</a><a href="/track" aria-current="page">Track</a></nav>
</div></header>

<main id="main">

  <section class="track-hero"><div class="wrap">
    <div class="track-grid">
      <p class="eyebrow">Track your application</p>
      <h1>Where's my visa?</h1>
      <p class="lede">Enter the order reference from your confirmation email to see exactly where your application is — from our first check to delivery.</p>
    </div>

    <form class="lookup" method="POST" action="/track/lookup" novalidate>
      @csrf
      <div class="stub"><span>STATUS TRACKER</span><span>UKV&lt;TRACK&lt;&lt;&lt;</span></div>
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

        <div class="status-mrz">
          <p class="lab">Order reference</p>
          <p class="ref">{{ $mrz($result['ref']) }}</p>
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
  @endif

  <section><div class="wrap">
    <div class="help">
      <p>Can't find your reference? It's in your confirmation email.</p>
      <div class="links">
        <a href="tel:+440000000000" class="btn btn--ghost">Call us</a>
        <a href="https://wa.me/440000000000" class="btn btn--ghost">WhatsApp</a>
      </div>
    </div>
  </div></section>

</main>

<footer><div class="wrap">
  <div class="brand">UKVisa<b>Co</b></div>
  <p>Independent UK visa &amp; eVisa facilitation. Not a government website.</p>
</div></footer>

</body>
</html>
