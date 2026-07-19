@extends('layouts.public')

@section('title', 'Find your nearest visa centre or PayPoint | Beyond Passports')
@section('description', 'Find your nearest visa application centre, PayPoint (for IDPs), embassy or courier drop-off by postcode. We flag centres where we can book your appointment. Independent service, not a government website.')

@php
    /**
     * Public nearest-centre finder (Wave 1 / A3).
     *
     * View data (from CentreController::page / ::search):
     *   $typeOptions   array<string,string>  type filter options (value => label)
     *   $postcode      string                sticky postcode value
     *   $selectedType  ?string               sticky selected type (or null = all)
     *   $results       \Illuminate\Support\Collection|null  finder results (null = no search yet)
     *   $searchedLabel ?string               human label for what was searched ("SW1A 1AA" / "your current location")
     *   $gentleMessage ?string               soft message for an unresolved postcode
     */
    $typeOptions   = $typeOptions   ?? [];
    $postcode      = $postcode      ?? '';
    $selectedType  = $selectedType  ?? null;
    $results       = $results       ?? null;
    $searchedLabel = $searchedLabel ?? null;
    $gentleMessage = $gentleMessage ?? null;
@endphp

@push('head')
<style>
  /* find-a-centre — page-scoped layout only. Palette/type/components from ukv.css. */

  /* ── hero — navy mesh (pick A) ────────────────────────────────── */
  .fc-hero{
    background:
      radial-gradient(640px 280px at 12% 0, rgba(21,94,122,.5), transparent 60%),
      radial-gradient(600px 260px at 92% 100%, rgba(46,154,140,.5), transparent 60%),
      var(--navy);
    color:#fff;padding:60px 0;
  }
  .fc-hero{text-align:center}
  .fc-hero .eyebrow{color:var(--soft)}
  .fc-hero h1{color:#fff;max-width:20ch;margin-inline:auto}
  .fc-hero .lede{color:rgba(255,255,255,.85);max-width:56ch;margin-inline:auto}
  .fc-hero .fc-trust{justify-content:center}
  /* ── hero trust pills (glass on dark) ─────────────────────────── */
  .fc-trust{display:flex;flex-wrap:wrap;gap:10px;margin:22px 0 0}
  .fc-trust span{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.1);
    border:1px solid rgba(255,255,255,.2);border-radius:999px;
    padding:8px 15px;font-size:13px;color:#fff}
  .fc-trust span b{color:var(--soft);font-weight:700}

  /* ── finder card ─────────────────────────────────────────────── */
  .fc-finder{max-width:660px;margin:0 auto;background:var(--white);
    border:1px solid var(--paper-edge);border-radius:18px;
    box-shadow:var(--lift-2);overflow:hidden}

  /* card header band */
  .fc-finder .fc-stub{display:flex;justify-content:space-between;align-items:center;
    background:linear-gradient(100deg,var(--navy),#2e3740);color:#fff;font-weight:700;font-size:11px;
    letter-spacing:.14em;text-transform:uppercase;padding:14px 24px}
  .fc-finder .fc-stub span:last-child{color:var(--soft)}
  .fc-finder .fc-stub-dot{width:8px;height:8px;border-radius:50%;
    background:var(--soft);display:inline-block;margin-right:7px;
    box-shadow:0 0 0 3px rgba(169,204,218,.25)}

  .fc-finder .cbody{padding:28px 24px 24px}
  .fc-finder label{display:block;font-weight:700;font-size:13px;
    letter-spacing:.02em;color:var(--ink);margin:0 0 8px}

  .fc-row{display:grid;grid-template-columns:1.4fr 1fr;gap:16px;align-items:end}

  .fc-finder input.pc-input,
  .fc-finder select{
    width:100%;font-size:15px;padding:13px 15px;
    border:1.5px solid var(--paper-edge);border-radius:10px;
    background:var(--white);color:var(--ink);font-family:inherit;
    transition:border-color .15s ease,box-shadow .15s ease}
  .fc-finder input.pc-input{font-family:var(--mono);letter-spacing:.07em;
    text-transform:uppercase;font-size:16px}
  .fc-finder input.pc-input:focus,
  .fc-finder select:focus{border-color:var(--cta);
    box-shadow:0 0 0 3px rgba(21,94,122,.14);outline:none}

  .fc-actions{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-top:20px}

  /* ghost "use my location" button */
  .fc-geo{display:none;background:transparent;color:var(--ink);
    border:1.5px solid var(--paper-edge);border-radius:10px;
    padding:13px 18px;font-weight:600;font-size:14px;cursor:pointer;
    font-family:inherit;transition:border-color .15s ease,color .15s ease}
  .fc-geo:hover{border-color:var(--cta);color:var(--cta)}
  .fc-geo svg{vertical-align:-3px;margin-right:6px}
  .js .fc-geo{display:inline-flex;align-items:center}

  .fc-hint{font-size:12px;color:var(--muted);margin:14px 0 0;line-height:1.5}
  .fc-geo-status{font-size:13px;color:var(--muted);margin:10px 0 0;min-height:1.2em}

  .fc-note{display:flex;gap:10px;align-items:flex-start;
    background:#fdf0ee;border:1px solid #f3c6c2;border-left:3px solid var(--cta);
    color:#8a2a22;border-radius:10px;padding:13px 15px;font-size:14px;margin:16px 0 0;
    line-height:1.55}

  /* ── results wrapper ─────────────────────────────────────────── */
  .fc-results-wrap{max-width:760px;margin:0 auto}

  .fc-searched{display:inline-flex;align-items:center;gap:8px;
    background:var(--white);border:1px solid var(--paper-edge);border-radius:999px;
    padding:7px 16px;font-size:13px;color:var(--muted);margin:0 0 20px}
  .fc-searched strong{color:var(--navy);font-weight:700}
  .fc-searched::before{content:"";display:inline-block;width:7px;height:7px;
    border-radius:50%;background:var(--sage);flex-shrink:0}

  /* ── compliance strip ────────────────────────────────────────── */
  .fc-compliance{max-width:760px;margin:0 auto;
    background:var(--white);border:1px solid var(--paper-edge);
    border-left:4px solid var(--cta);border-radius:14px;
    padding:20px 24px}
  .fc-compliance p{margin:0;font-size:14px;color:#3a4248;line-height:1.7}
  .fc-compliance strong{color:var(--ink)}
  .fc-compliance a{color:var(--cta);font-weight:600}

  /* ── finder on soft-sky ground (pick C) ──────────────────────── */
  .fc-form-sec{background:linear-gradient(180deg,#EAF1F4,var(--paper))}
  .fc-ctitle{font:700 18px var(--display);color:var(--navy);text-align:center;margin:0 0 18px}

  /* ── section spacing ─────────────────────────────────────────── */
  .fc-section{padding:40px 0}
  .fc-section-sm{padding:24px 0}

  @media (max-width:560px){
    .fc-row{grid-template-columns:1fr}
    .fc-finder .cbody{padding:22px 18px 20px}
    .fc-trust{gap:8px}
    .fc-trust span{font-size:12px;padding:7px 12px}
  }
</style>
@endpush

@section('content')

{{-- HERO — navy mesh --}}
<section class="fc-hero">
  <div class="wrap reveal">
    <p class="eyebrow">Find a centre</p>
    <h1>Find your nearest centre</h1>
    <p class="lede">Enter your postcode to see visa application centres, PayPoint IDP issuers, embassies and courier drop-offs, closest first. We flag any where we can book your appointment for you.</p>
    <div class="fc-trust">
      <span><b>✓</b> Sorted by distance</span>
      <span><b>✓</b> All types in one search</span>
      <span><b>✓</b> Nothing stored</span>
    </div>
    @include('partials.trustpilot-cta', ['align' => 'center', 'theme' => 'dark', 'margin' => '16px 0 0'])
  </div>
</section>

{{-- FINDER FORM (GET — works with no JS) --}}
<section class="fc-section fc-form-sec">
  <div class="wrap">
    <form class="fc-finder reveal" method="GET" action="{{ url('/find-a-centre/search') }}" novalidate>
      <div class="fc-stub">
        <span><span class="fc-stub-dot" aria-hidden="true"></span>Centre finder</span>
        <span style="font-weight:600;letter-spacing:.04em">Postcode search</span>
      </div>
      <div class="cbody">
        <div class="fc-row">
          <div>
            <label for="fc-postcode">Your postcode</label>
            <input
              type="text"
              id="fc-postcode"
              name="postcode"
              class="pc-input"
              value="{{ $postcode }}"
              placeholder="SW1A 1AA"
              autocomplete="postal-code"
              inputmode="text"
              maxlength="12"
              aria-describedby="fc-hint">
          </div>
          <div>
            <label for="fc-type">Type of centre</label>
            <select id="fc-type" name="type">
              <option value="">All types</option>
              @foreach ($typeOptions as $value => $label)
                <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Hidden coords — filled by the optional geolocation enhancement before submit. --}}
        <input type="hidden" name="lat" id="fc-lat" value="">
        <input type="hidden" name="lng" id="fc-lng" value="">

        <div class="fc-actions">
          <button type="submit" class="btn">Find centres near me →</button>
          <button type="button" class="fc-geo" id="fc-geo-btn" hidden>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2v3M12 19v3M2 12h3M19 12h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="6" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2" fill="currentColor"/></svg>
            Use my location
          </button>
        </div>

        <p class="fc-hint" id="fc-hint">UK postcode &middot; we use postcodes.io to find your location &middot; nothing is stored</p>
        <p class="fc-geo-status" id="fc-geo-status" role="status" aria-live="polite"></p>

        @if ($gentleMessage)
          <div class="fc-note" role="status" aria-live="polite">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex:0 0 17px;margin-top:1px"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
            <span>{{ $gentleMessage }}</span>
          </div>
        @endif
      </div>
    </form>
    <div style="margin-top:14px">@include('partials.disclaimer-strip', ['wrap' => false])</div>
  </div>
</section>

{{-- RESULTS --}}
<section class="fc-section-sm">
  <div class="wrap">
    <div class="fc-results-wrap">
      @if ($searchedLabel)
        <p class="fc-searched">Showing results near <strong>{{ $searchedLabel }}</strong></p>
      @endif

      @include('partials.nearest-centre', [
        'results' => $results,
        'heading' => $searchedLabel ? 'Nearest to '.$searchedLabel : 'Centres near you',
      ])
    </div>
  </div>
</section>

{{-- CONVERSION BAND — the page's whole pitch is "we can book this for you"; capture the lead. --}}
@php
  $fcWa = 'https://wa.me/'.(config('ukv.whatsapp') ?: '447882747584').'?text='.rawurlencode('Hi Beyond Passports, I found my nearest centre. Can you check my eligibility and book my Schengen appointment?');
@endphp
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>We can book your appointment for you</h2>
  <p style="max-width:54ch;color:#cdd9e1">Found your centre? A <x-reg-verify>UK &amp; Europe registered</x-reg-verify> service checks your documents and holds the soonest slot before it goes. You just turn up. Independent service, not a government website.</p>
  <div class="row">
    <a href="{{ $fcWa }}" class="btn btn--glass">@include('partials.wa-glyph')Check eligibility</a>
    <a href="{{ url('/apply') }}" class="btn btn--ghost" style="color:#fff;border-color:#cdd9e1">Start your application →</a>
  </div>
<div style="margin-top:18px">@include('partials.disclaimer-strip', ['variant' => 'dark', 'wrap' => false])</div></div></section>

{{-- COMPLIANCE STRIP --}}
<section class="fc-section-sm" style="padding-top:0">
  <div class="wrap">
    <div class="fc-compliance reveal">
      <p><strong>Beyond Passports is an independent service and is not a government website.</strong> Distances are straight-line estimates. Always confirm opening hours and booking with the centre before you travel. International Driving Permits (IDPs) are issued in person at PayPoint; for the full list of PayPoint locations, use the <a href="https://www.paypoint.com/en-gb/consumers/store-locator" target="_blank" rel="noopener noreferrer">official PayPoint store locator</a>. Most travel documents (eVisa&nbsp;/ ETA) need no in-person visit at all.</p>
    </div>
  </div>
</section>

@endsection

@push('head')
<script>
  // find-a-centre.blade — optional geolocation enhancement. Progressive: the postcode form
  // works with no JS; this only adds a one-tap "use my location" button. Graceful if the
  // browser denies permission or doesn't support geolocation.
  document.addEventListener('DOMContentLoaded', function () {
    // Mark the doc as JS-capable so the geo button can show via CSS (defensive — also unhide directly).
    document.documentElement.classList.add('js');

    var btn    = document.getElementById('fc-geo-btn');
    var status = document.getElementById('fc-geo-status');
    var form   = btn ? btn.closest('form') : null;
    var latEl  = document.getElementById('fc-lat');
    var lngEl  = document.getElementById('fc-lng');
    var pcEl   = document.getElementById('fc-postcode');

    if (!btn || !form || !('geolocation' in navigator)) {
      return; // no button / no API -> leave the plain postcode form as-is.
    }

    btn.hidden = false;

    btn.addEventListener('click', function () {
      status.textContent = 'Finding your location…';
      btn.disabled = true;

      navigator.geolocation.getCurrentPosition(
        function (pos) {
          latEl.value = pos.coords.latitude.toFixed(6);
          lngEl.value = pos.coords.longitude.toFixed(6);
          // Coords take precedence over a postcode for this submit — clear the box so the
          // controller uses lat/lng (it prefers coords when both are present anyway).
          if (pcEl) { pcEl.value = ''; }
          status.textContent = 'Got it — finding centres near you…';
          form.submit();
        },
        function (err) {
          btn.disabled = false;
          status.textContent = (err && err.code === err.PERMISSION_DENIED)
            ? 'Location access was declined — no problem, just enter your postcode above.'
            : "Couldn't get your location — please enter your postcode above instead.";
        },
        { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 }
      );
    });
  });
</script>
@endpush
