{{-- Config-driven team + location section (design: abt-d). Data: config('ukv.team'), config('ukv.address'). --}}
@push('head')
<style>
  /* ── about team + location (abt-d) — scoped under .abt; tokens inherited from ukv.css ── */
  .abt { padding: 84px 0; }

  .abt .head { max-width: 680px; margin: 0 0 40px; }
  .abt .head h2 { margin: 0 0 12px; font-size: clamp(28px, 3.4vw, 42px); line-height: 1.07; font-weight: 800; letter-spacing: -.02em; }
  .abt .head p { margin: 0; color: var(--muted); font-size: 17px; line-height: 1.62; }

  /* ── team cards ── */
  .abt .team { display: grid; grid-template-columns: 1.18fr 1fr 1fr; gap: 22px; align-items: stretch; }
  .abt .member {
    position: relative; background: var(--white); border: 1px solid var(--paper-edge);
    border-radius: 18px; padding: 34px 30px 30px;
    box-shadow: 0 1px 2px rgba(22,34,46,.05), 0 24px 48px -38px rgba(22,34,46,.4);
    display: flex; flex-direction: column; align-items: flex-start;
  }
  .abt .member.lead {
    border: 1px solid rgba(46,154,140,.55);
    box-shadow: 0 1px 2px rgba(22,34,46,.06), 0 28px 54px -34px rgba(46,154,140,.45);
  }
  .abt .member.lead::before {
    content: ""; position: absolute; left: 0; right: 0; top: 0; height: 4px; border-radius: 18px 18px 0 0;
    background: linear-gradient(90deg, var(--stamp), var(--cta));
  }
  .abt .lead-tag {
    position: absolute; top: 26px; right: 26px;
    font-size: 10px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase;
    color: var(--stamp-text); border: 1.5px solid rgba(46,154,140,.45);
    border-radius: 7px; padding: 5px 9px; transform: rotate(-4deg);
    box-shadow: inset 0 0 0 3px rgba(46,154,140,.08); background: var(--white);
  }
  .abt .photo {
    width: 84px; height: 84px; border-radius: 50%; overflow: hidden;
    border: 2px solid var(--stamp);
    box-shadow: 0 10px 22px -12px rgba(46,154,140,.6), 0 0 0 6px rgba(46,154,140,.07);
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #eef5f2, #dff0eb);
  }
  .abt .member.lead .photo { width: 100px; height: 100px; }
  .abt .photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .abt .photo .monogram { font-weight: 800; font-size: 26px; letter-spacing: .02em; color: var(--stamp-text); }
  .abt .member.lead .photo .monogram { font-size: 31px; }
  .abt .member .name { margin: 20px 0 4px; font-size: 18px; font-weight: 800; letter-spacing: -.01em; }
  .abt .member.lead .name { font-size: 21px; }
  .abt .member .role { margin: 0 0 14px; font-size: 12px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--stamp-text); }
  .abt .member .bio { margin: 0; color: var(--muted); font-size: 14.5px; line-height: 1.58; }
  .abt .member.lead .bio { font-size: 15.5px; }

  /* ── split location card ── */
  .abt .loc {
    position: relative; margin-top: 24px; background: var(--white);
    border: 1px solid var(--paper-edge); border-radius: 18px; overflow: hidden;
    box-shadow: 0 1px 2px rgba(22,34,46,.05), 0 30px 60px -40px rgba(22,34,46,.42);
    display: grid; grid-template-columns: 1fr 1.25fr;
  }
  .abt .loc-body { padding: 46px 48px; align-self: center; }
  .abt .loc-eyebrow { display: inline-flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; color: var(--stamp-text); margin: 0 0 14px; }
  .abt .loc-eyebrow::before { content: ""; width: 22px; height: 1px; background: var(--stamp); opacity: .7; }
  .abt .loc-body h3 { margin: 0 0 18px; font-size: clamp(22px, 2.4vw, 30px); font-weight: 800; letter-spacing: -.02em; line-height: 1.1; }
  .abt .address { font-style: normal; color: var(--ink); font-size: 16px; line-height: 1.65; }
  .abt .address .company { font-weight: 800; }
  .abt .hours { margin-top: 18px; display: inline-flex; align-items: center; gap: 9px; font-size: 13.5px; font-weight: 700; color: var(--stamp-text);
    border: 1px solid rgba(46,154,140,.4); border-radius: 999px; padding: 8px 15px; }
  .abt .hours .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--stamp); }
  .abt .loc-map { position: relative; min-height: 340px; padding: 14px; background: linear-gradient(160deg, #eef2f7, #e3e9f1); }
  .abt .map-frame { position: relative; width: 100%; height: 100%; min-height: 312px; border-radius: 14px; overflow: hidden;
    border: 1px solid var(--paper-edge); box-shadow: inset 0 0 0 1px rgba(255,255,255,.6), 0 18px 36px -28px rgba(22,34,46,.5); }
  .abt .map-frame iframe { border: 0; width: 100%; height: 100%; display: block; }

  @media (max-width: 880px) {
    .abt { padding: 60px 0; }
    .abt .team { grid-template-columns: 1fr; gap: 18px; }
    .abt .member.lead .photo { width: 84px; height: 84px; }
    .abt .member.lead .photo .monogram { font-size: 26px; }
    .abt .member.lead .name { font-size: 19px; }
    .abt .loc { grid-template-columns: 1fr; }
    .abt .loc-body { padding: 36px 28px; }
    .abt .loc-map { min-height: 280px; }
    .abt .map-frame { min-height: 252px; }
    .abt .lead-tag { position: static; display: inline-block; transform: none; margin-bottom: 14px; align-self: flex-start; }
  }
</style>
@endpush

@php
  $team = config('ukv.team', []);
  $addr = config('ukv.address', []);

  // Derive initials monogram from a name, e.g. "Sarah Whitfield" → "SW".
  $initials = function (string $name): string {
      $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
      if (empty($parts)) return '';
      $first = mb_substr($parts[0], 0, 1);
      $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';
      return mb_strtoupper($first . $last);
  };

  // Build "{city} {postcode} · {country}" from non-empty pieces.
  $cityLine = trim(($addr['city'] ?? '') . ' ' . ($addr['postcode'] ?? ''));
  $cityCountry = array_filter([$cityLine, $addr['country'] ?? '']);
@endphp

<section class="abt"><div class="wrap">
  <div class="head reveal">
    <p class="eyebrow">The team</p>
    <h2>Real people checking your application</h2>
    <p>Your application isn't passed through software and forgotten. A small UK-based team reviews every file by hand &mdash; the same people, start to finish.</p>
  </div>

  <div class="team">
    @foreach ($team as $m)
    <article class="member reveal{{ !empty($m['lead']) ? ' lead' : '' }}">
      @if (!empty($m['lead']))
        <span class="lead-tag">Case lead</span>
      @endif
      <div class="photo">
        @if (!empty($m['photo']))
          <img src="{{ $m['photo'] }}" alt="{{ $m['name'] }}" loading="lazy">
        @else
          <span class="monogram" aria-hidden="true">{{ $initials($m['name'] ?? '') }}</span>
        @endif
      </div>
      <p class="name">{{ $m['name'] ?? '' }}</p>
      <p class="role">{{ $m['role'] ?? '' }}</p>
      <p class="bio">{{ $m['bio'] ?? '' }}</p>
    </article>
    @endforeach
  </div>

  <div class="loc">
    <div class="loc-body reveal">
      <p class="loc-eyebrow">Where we are</p>
      <h3>A UK-based team you can reach</h3>
      <address class="address">
        @if (!empty($addr['company']))<span class="company">{{ $addr['company'] }}</span><br>@endif
        @if (!empty($addr['line1'])){{ $addr['line1'] }}<br>@endif
        @if (!empty($addr['line2'])){{ $addr['line2'] }}<br>@endif
        @if (!empty($cityCountry)){{ implode(' · ', $cityCountry) }}@endif
      </address>
      <div class="hours"><span class="dot"></span>UK-based team &middot; Mon&ndash;Sat</div>
    </div>
    <div class="loc-map">
      <div class="map-frame">
        <iframe src="https://www.google.com/maps?q=London%2C%20UK&z=12&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </div>
</div></section>
