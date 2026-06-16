@extends('layouts.public')

@section('title', 'Find your nearest visa centre or PayPoint | Beyond Passports')
@section('description', 'Find your nearest UK visa application centre, PayPoint (for IDPs), embassy or courier drop-off by postcode. We flag centres where we can book your appointment. Independent service — not a government website.')

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
  /* find-a-centre.blade — page-scoped layout only. Palette/type/components inherited from ukv.css. */
  .fc-hero{padding:48px 0 0;text-align:center}
  .fc-hero h1{font-size:clamp(32px,4.8vw,52px);color:var(--navy);letter-spacing:-.015em;max-width:16ch;margin:0 auto .3em}
  .fc-hero p.lede{font-size:18px;color:#33454f;max-width:54ch;margin:0 auto}

  .fc-finder{max-width:640px;margin:26px auto 0;background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;box-shadow:var(--shadow);overflow:hidden}
  .fc-finder .stub{display:flex;justify-content:space-between;background:#f7fafb;color:var(--cta);font-family:var(--mono);font-size:11px;letter-spacing:.14em;padding:12px 22px;border-bottom:1px solid var(--paper-edge)}
  .fc-finder .cbody{padding:24px 22px}
  .fc-finder label{display:block;font-weight:600;font-size:14px;margin:0 0 8px}
  .fc-row{display:grid;grid-template-columns:1.4fr 1fr;gap:14px;align-items:end}
  .fc-finder input.pc-input{width:100%;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;font-size:16px;padding:13px 14px;border:1px solid var(--paper-edge);border-radius:8px}
  .fc-finder input.pc-input:focus{outline:2px solid var(--cta);outline-offset:1px}
  .fc-finder select{width:100%;font-size:15px;padding:13px 14px;border:1px solid var(--paper-edge);border-radius:8px;background:var(--white);font-family:inherit}
  .fc-finder select:focus{outline:2px solid var(--cta);outline-offset:1px}
  .fc-actions{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-top:18px}
  .fc-geo{display:none;background:transparent;color:var(--navy);border:1px solid var(--paper-edge);border-radius:8px;padding:12px 18px;font-weight:600;font-size:14px;cursor:pointer;font-family:inherit}
  .fc-geo svg{vertical-align:-2px;margin-right:6px}
  .fc-geo[hidden]{display:none}
  .js .fc-geo{display:inline-flex;align-items:center}
  .fc-hint{font-family:var(--mono);font-size:11px;color:var(--hint);margin:14px 0 0;letter-spacing:.04em}
  .fc-geo-status{font-size:13px;color:var(--muted);margin:10px 0 0}
  .fc-note{background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:11px 13px;font-size:14px;margin:14px 0 0}

  .fc-results{max-width:760px;margin:32px auto 0}
  .fc-results .searched{font-family:var(--mono);font-size:12px;letter-spacing:.06em;color:var(--muted);text-align:center;margin:0 0 16px}
  .fc-results .searched strong{color:var(--navy)}

  .fc-compliance{max-width:760px;margin:28px auto 0;background:var(--white);border:1px solid var(--paper-edge);border-left:4px solid var(--cta);border-radius:10px;padding:18px 20px}
  .fc-compliance p{margin:0;font-size:14px;color:#33454f;line-height:1.6}
  .fc-compliance strong{color:var(--ink)}

  @media (max-width:560px){
    .fc-row{grid-template-columns:1fr}
  }
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="mesh-hero mesh-hero--sm"><div class="wrap"><div class="mh-grid"><div class="mh-copy">
  <p class="eyebrow">Find a centre</p>
  <h1>Find your nearest centre</h1>
  <p class="lede">Enter your postcode to see the nearest visa application centres, PayPoint IDP issuers, embassies and courier drop-offs — closest first. We flag any where we can book your appointment for you.</p>
</div></div></div></section>

{{-- FINDER FORM (GET — works with no JS) --}}
<section><div class="wrap">
  <form class="fc-finder" method="GET" action="{{ url('/find-a-centre/search') }}" novalidate>
    <div class="stub"><span>Centre finder</span><span>By postcode</span></div>
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

      <p class="fc-hint" id="fc-hint">UK postcode · we use postcodes.io to find your location · nothing is stored</p>
      <p class="fc-geo-status" id="fc-geo-status" role="status" aria-live="polite"></p>

      @if ($gentleMessage)
        <p class="fc-note" role="status" aria-live="polite">{{ $gentleMessage }}</p>
      @endif
    </div>
  </form>
</div></section>

{{-- RESULTS --}}
<section><div class="wrap">
  <div class="fc-results">
    @if ($searchedLabel)
      <p class="searched">Showing centres near <strong>{{ $searchedLabel }}</strong></p>
    @endif

    @include('partials.nearest-centre', [
      'results' => $results,
      'heading' => $searchedLabel ? 'Nearest to '.$searchedLabel : 'Centres near you',
    ])
  </div>
</div></section>

{{-- COMPLIANCE STRIP --}}
<section><div class="wrap">
  <div class="fc-compliance reveal">
    <p><strong>Beyond Passports is an independent service and is not a government website.</strong> Distances are straight-line estimates to help you find your nearest option — always confirm opening hours and booking with the centre before you travel. International Driving Permits (IDPs) are issued in person at PayPoint; for the full list of PayPoint locations, use the <a href="https://www.paypoint.com/en-gb/consumers/store-locator" target="_blank" rel="noopener noreferrer">official PayPoint store locator</a>. Most UK travel documents (eVisa / ETA) need no in-person visit at all.</p>
  </div>
</div></section>

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
