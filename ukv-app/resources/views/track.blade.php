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
@extends('layouts.public')

@section('title', 'Track your application — UK visa & eVisa status | Beyond Passports')
@section('description', 'Track your Beyond Passports application with your order reference. See each stage from received to delivered. Independent service — not a government website.')

@push('head')
<meta name="robots" content="noindex,nofollow">
<style>
  /* ── Track page — page-scoped styles (trk- prefix where new, extends ukv.css vars) ── */

  /* ── Hero ────────────────────────────────────────────────────────────────────────── */
  .trk-hero {
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid var(--paper-edge);
    background: linear-gradient(180deg, #EAF1F4 0%, #F2F5F6 60%, var(--paper) 100%);
    padding: 0; /* reset global section{padding:72px 0} */
  }
  .trk-hero > .wrap {
    position: relative;
    z-index: 2;
    padding: 64px 24px 80px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 56px;
    align-items: center;
  }
  .trk-copy { }
  .trk-copy .eyebrow { color: var(--cta); margin: 0 0 10px; }
  .trk-copy h1 {
    font-family: var(--display);
    font-weight: 700;
    font-size: clamp(30px, 4vw, 46px);
    color: var(--ink);
    letter-spacing: -.03em;
    line-height: 1.04;
    margin: 0 0 16px;
  }
  .trk-copy .lede {
    font-size: 17px;
    color: var(--muted);
    line-height: 1.6;
    max-width: 44ch;
    margin: 0 0 22px;
  }
  .trk-trust {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0;
  }
  .trk-trust span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,.78);
    backdrop-filter: blur(6px);
    border: 1px solid var(--paper-edge);
    border-radius: 999px;
    padding: 7px 14px;
    font-size: 13px;
    color: var(--ink);
    font-weight: 600;
  }
  .trk-trust span b { color: var(--cta); }

  /* ── Lookup card ─────────────────────────────────────────────────────────────────── */
  .trk-card {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--lift-2);
    overflow: hidden;
  }
  .trk-card .trk-stub {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--stamp);
    color: #fff;
    font-family: var(--body);
    font-weight: 700;
    font-size: 11px;
    letter-spacing: .12em;
    text-transform: uppercase;
    padding: 14px 24px;
  }
  .trk-stub .trk-live {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .trk-stub .trk-live::before {
    content: "";
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #7debb0;
    box-shadow: 0 0 0 3px rgba(125,235,176,.28);
  }
  .trk-cbody { padding: 28px 24px 24px; }
  .trk-cbody label {
    display: block;
    font-weight: 700;
    font-size: 13.5px;
    color: var(--ink);
    margin: 0 0 8px;
  }
  .trk-ref-wrap { position: relative; }
  .trk-ref-wrap .trk-ref-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    pointer-events: none;
    font-size: 16px;
  }
  .ref-input {
    width: 100%;
    font-family: var(--mono);
    letter-spacing: .06em;
    text-transform: uppercase;
    font-size: 16px;
    padding: 13px 14px 13px 42px;
    border: 1.5px solid var(--paper-edge);
    border-radius: 12px;
    background: var(--white);
    color: var(--ink);
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .ref-input:focus {
    outline: none;
    border-color: var(--cta);
    box-shadow: 0 0 0 3px rgba(199,93,56,.14);
  }
  .ref-input[aria-invalid="true"] {
    border-color: #c0392b;
  }
  .hint {
    font-size: 12px;
    color: var(--muted);
    margin: 9px 0 0;
    letter-spacing: .02em;
  }
  .form-error {
    background: #fdeceb;
    border: 1px solid #f3c6c2;
    color: #8a2a22;
    border-radius: 8px;
    padding: 11px 14px;
    font-size: 14px;
    margin: 14px 0 0;
  }
  .trk-cbody .btn {
    margin-top: 18px;
    width: 100%;
    justify-content: center;
    text-align: center;
    padding: 14px 24px;
    font-size: 16px;
    border-radius: 12px;
  }

  /* ── Not-found banner ────────────────────────────────────────────────────────────── */
  .trk-notfound {
    max-width: 700px;
    margin: 32px auto 0;
    background: #fff8f7;
    border: 1px solid #f3c6c2;
    border-left: 4px solid #c0392b;
    border-radius: 12px;
    padding: 20px 24px;
  }
  .trk-notfound p { margin: 0 0 8px; font-size: 15px; color: #5a1a16; }
  .trk-notfound p:last-child { margin: 0; color: var(--muted); font-size: 14px; }

  /* ── Status result ───────────────────────────────────────────────────────────────── */
  .trk-status-wrap {
    max-width: 820px;
    margin: 40px auto 0;
  }

  /* Reference header band */
  .trk-ref-head {
    background: var(--navy);
    border-radius: 18px 18px 0 0;
    padding: 22px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background:
      radial-gradient(480px 160px at 0% 50%, rgba(199,93,56,.28), transparent 60%),
      radial-gradient(480px 160px at 100% 50%, rgba(92,154,123,.24), transparent 60%),
      var(--navy);
  }
  .trk-ref-head .trk-ref-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: rgba(255,255,255,.6);
    margin: 0 0 5px;
  }
  .trk-ref-head .trk-ref-val {
    font-family: var(--display);
    font-weight: 800;
    font-size: clamp(18px, 3vw, 26px);
    color: #fff;
    letter-spacing: .03em;
    margin: 0;
    word-break: break-all;
  }
  .trk-ref-head .trk-ref-badge {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 999px;
    padding: 7px 14px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #fff;
  }
  .trk-ref-badge::before {
    content: "";
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #7debb0;
    box-shadow: 0 0 0 3px rgba(125,235,176,.28);
  }

  /* Status card body */
  .trk-status-card {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-top: 0;
    border-radius: 0 0 18px 18px;
    box-shadow: var(--lift-2);
    padding: 30px 28px 26px;
  }

  /* ── Timeline stepper ─────────────────────────────────────────────────────────────── */
  .timeline {
    list-style: none;
    margin: 0 0 8px;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0;
  }
  .stage {
    position: relative;
    text-align: center;
    padding: 8px 4px 0;
  }
  /* connector line */
  .stage::before {
    content: "";
    position: absolute;
    top: 31px;
    left: -50%;
    width: 100%;
    height: 2px;
    background: repeating-linear-gradient(
      90deg, var(--paper-edge) 0 6px, transparent 6px 12px
    );
    z-index: 0;
  }
  .stage:first-child::before { display: none; }
  .stage.is-done::before,
  .stage.is-current::before {
    background: var(--stamp);
  }
  /* node dot */
  .stage .dot {
    position: relative;
    z-index: 1;
    width: 48px;
    height: 48px;
    margin: 0 auto 10px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f4f5;
    border: 2px solid var(--paper-edge);
    color: var(--muted);
    font-family: var(--mono);
    font-weight: 700;
    font-size: 15px;
    transition: transform .2s ease;
  }
  .stage.is-done .dot {
    background: #eaf3ef;
    border-color: var(--stamp);
    color: var(--stamp-text);
  }
  .stage.is-done .dot::after {
    content: "✓";
    font-size: 18px;
  }
  .stage.is-current .dot {
    background: var(--cta);
    border-color: var(--cta);
    color: #fff;
    box-shadow: 0 0 0 5px rgba(199,93,56,.18);
    transform: scale(1.08);
  }
  .stage.is-outcome .dot {
    background: #fdeceb;
    border-color: #c0392b;
    color: #8a2a22;
  }
  /* stage label */
  .stage .name {
    display: block;
    font-size: 12px;
    line-height: 1.3;
    color: var(--muted);
    font-weight: 600;
  }
  .stage.is-current .name { color: var(--ink); font-weight: 700; }
  .stage.is-done .name { color: #33454f; }
  .stage .when {
    display: block;
    font-family: var(--mono);
    font-size: 10.5px;
    color: var(--muted);
    letter-spacing: .04em;
    margin-top: 4px;
    text-transform: uppercase;
  }
  .stage.is-current .when { color: var(--cta); }
  .stage.is-done .when { color: var(--stamp-text); }

  /* ── Now / Next pair ────────────────────────────────────────────────────────────── */
  .trk-now-next {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin: 26px 0 0;
  }
  .trk-nn {
    background: #f7fafb;
    border: 1px solid var(--paper-edge);
    border-radius: 14px;
    padding: 18px 20px;
  }
  .trk-nn .trk-nn-k {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--stamp-text);
    margin: 0 0 7px;
  }
  .trk-nn p {
    margin: 0;
    font-size: 14.5px;
    color: #33454f;
    line-height: 1.55;
  }

  /* ── Compliance reassurance ──────────────────────────────────────────────────────── */
  .trk-reassure {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin: 20px 0 0;
    padding: 16px 18px;
    border: 1px solid var(--paper-edge);
    border-radius: 12px;
    background: var(--white);
  }
  .trk-reassure .trk-reassure-icon {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    background: rgba(92,154,123,.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--stamp-text);
    font-size: 16px;
  }
  .trk-reassure p {
    margin: 0;
    font-size: 13.5px;
    color: var(--muted);
    line-height: 1.55;
  }
  .trk-reassure strong { color: var(--ink); }

  /* ── Doc-checklist wrapper on track page ────────────────────────────────────────── */
  .trk-doc-wrap {
    max-width: 820px;
    margin: 22px auto 0;
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--lift-1);
    padding: 28px;
    overflow: hidden;
  }

  /* ── Help bar ─────────────────────────────────────────────────────────────────────── */
  .trk-help {
    max-width: 820px;
    margin: 32px auto 56px;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: center;
    justify-content: space-between;
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 16px;
    padding: 20px 26px;
    box-shadow: var(--shadow);
  }
  .trk-help p {
    margin: 0;
    font-size: 15px;
    color: #33454f;
  }
  .trk-help .trk-help-links {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  /* ── Responsive ──────────────────────────────────────────────────────────────────── */
  @media (max-width: 820px) {
    .trk-hero > .wrap {
      grid-template-columns: 1fr;
      padding: 48px 0 56px;
      gap: 32px;
    }
    .trk-copy .lede { max-width: none; }
    .timeline { grid-template-columns: 1fr; gap: 0; }
    .stage {
      display: grid;
      grid-template-columns: 48px 1fr;
      align-items: center;
      gap: 14px;
      text-align: left;
      padding: 10px 0;
    }
    .stage::before {
      top: 0;
      left: 23px;
      width: 2px;
      height: 50%;
    }
    .stage:first-child::before { display: none; }
    .stage .dot { margin: 0; }
    .trk-now-next { grid-template-columns: 1fr; }
    .trk-help { flex-direction: column; align-items: flex-start; }
  }
  @media (max-width: 540px) {
    .trk-ref-head { flex-direction: column; align-items: flex-start; }
    .trk-status-card { padding: 22px 18px 20px; }
  }
</style>
@endpush

@section('content')

{{-- ── Hero ───────────────────────────────────────────────────────────────────────── --}}
<section class="trk-hero" aria-label="Track your application">
  <div class="wrap">
    <div class="trk-copy reveal">
      <p class="eyebrow">Track your application</p>
      <h1>Where's my&nbsp;visa?</h1>
      <p class="lede">Enter the order reference from your confirmation email to see exactly where your application is — from our first check to delivery.</p>
      <div class="trk-trust">
        <span>&#x2714; Every stage tracked</span>
        <span>&#x1F512; Reference only — no&nbsp;PII</span>
        <span>&#x1F4AC; Live&nbsp;updates</span>
      </div>
    </div>

    {{-- ── Lookup form card ──────────────────────────────────────────────────────── --}}
    <div class="trk-card reveal">
      <div class="trk-stub">
        <span>Status tracker</span>
        <span class="trk-live">Live</span>
      </div>
      <div class="trk-cbody">
        <form method="POST" action="/track/lookup" novalidate>
          @csrf
          <label for="ref">Your order reference</label>
          <div class="trk-ref-wrap">
            <span class="trk-ref-icon" aria-hidden="true">&#x1F50D;</span>
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
          </div>
          <p class="hint" id="ref-hint">Format: UKV-YEAR-NUMBER · it's in your confirmation email</p>
          @error('ref')
            <p class="form-error" id="ref-error" role="alert">{{ $message }}</p>
          @enderror
          <button type="submit" class="btn">Track application &rarr;</button>
        </form>
      </div>
    </div>
  </div>
</section>

{{-- ── Not-found notice ─────────────────────────────────────────────────────────── --}}
@if ($notFound)
  <div class="wrap">
    <div class="trk-notfound reveal" role="status" aria-live="polite">
      <p><strong>We couldn't find an application matching&nbsp;{{ $searchedRef }}.</strong></p>
      <p>Please double-check the reference in your confirmation email — it looks like <code>UKV-2026-004821</code> — and try again. If you're still stuck, get in touch and we'll help.</p>
    </div>
  </div>
@endif

{{-- ── Status result ────────────────────────────────────────────────────────────── --}}
@if ($result)
  <div class="wrap">
    <div class="trk-status-wrap reveal" role="region" aria-label="Application status" tabindex="-1">

      {{-- Reference header --}}
      <div class="trk-ref-head">
        <div>
          <p class="trk-ref-label">Order reference</p>
          <p class="trk-ref-val">{{ $mrz($result['ref']) }}</p>
        </div>
        <span class="trk-ref-badge">Active</span>
      </div>

      {{-- Status card --}}
      <div class="trk-status-card">

        {{-- Stepper timeline --}}
        <ol class="timeline" aria-label="Application stages">
          @foreach ($result['stages'] as $i => $stage)
            @php
              $isOutcome = $result['outcome'] && $i === $result['current_index'];
              $classes = match (true) {
                  $isOutcome                    => 'stage is-outcome',
                  $stage['state'] === 'done'    => 'stage is-done',
                  $stage['state'] === 'current' => 'stage is-current',
                  default                       => 'stage',
              };
              $when = match (true) {
                  $isOutcome && $result['outcome'] === 'rejected' => 'Decision in',
                  $isOutcome && $result['outcome'] === 'refunded' => 'Closed',
                  $stage['state'] === 'done'    => 'Done',
                  $stage['state'] === 'current' => 'In progress',
                  default                       => 'To come',
              };
            @endphp
            <li class="{{ $classes }}"
              @if ($stage['state'] === 'current' && ! $result['outcome']) aria-current="step" @endif>
              <span class="dot" aria-hidden="true">
                @if ($stage['state'] !== 'done'){{ $i + 1 }}@endif
              </span>
              <span class="name">
                {{ $stage['label'] }}
                <span class="when">{{ $when }}</span>
              </span>
            </li>
          @endforeach
        </ol>

        {{-- Now / Next --}}
        <div class="trk-now-next">
          <div class="trk-nn">
            <p class="trk-nn-k">What's happening now</p>
            <p>{{ $result['now'] }}</p>
          </div>
          @if ($result['next'])
            <div class="trk-nn">
              <p class="trk-nn-k">What's next</p>
              <p>{{ $result['next'] }}</p>
            </div>
          @endif
        </div>

        {{-- Compliance reassurance — verbatim --}}
        <div class="trk-reassure">
          <div class="trk-reassure-icon" aria-hidden="true">&#x2139;</div>
          <p><strong>Government processing time is set by the destination's authorities</strong> — not by us, and express speeds our handling only, not their decision. We'll notify you the moment there's an update.</p>
        </div>

      </div>{{-- /.trk-status-card --}}
    </div>{{-- /.trk-status-wrap --}}
  </div>{{-- /.wrap --}}

  {{-- ── Personalised doc checklist (DRE) ──────────────────────────────────────── --}}
  {{--
      $docItems is the RequirementService::for() output for the matched order —
      document-type guidance only, no PII. Renders nothing when empty.
  --}}
  @if (! empty($docItems))
    <div class="wrap">
      <div class="trk-doc-wrap reveal">
        @include('partials.doc-checklist', ['items' => $docItems, 'personalised' => true])
      </div>
    </div>
  @endif

@endif {{-- $result --}}

{{-- ── Help bar ─────────────────────────────────────────────────────────────────── --}}
<div class="wrap">
  <div class="trk-help reveal">
    <p>Can't find your reference? It's in your confirmation email.</p>
    <div class="trk-help-links">
      <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}" class="btn btn--ghost">&#x1F4DE; Call us</a>
      <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--ghost">&#x1F4AC; WhatsApp</a>
    </div>
  </div>
</div>

@endsection
