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
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
  }
  /* whole-card hover: lift, teal border, deeper shadow, photo zoom */
  .abt .member:hover {
    transform: translateY(-6px); border-color: rgba(46,154,140,.55);
    box-shadow: 0 2px 4px rgba(22,34,46,.06), 0 38px 64px -34px rgba(46,154,140,.5);
  }
  .abt .photo img { transition: transform .35s ease; }
  .abt .member:hover .photo img { transform: scale(1.06); }
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

  /* ── per-member contact pills (Variant A) ── */
  .abt .member .contact { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--paper-edge); }
  .abt .member .chip { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 600; text-decoration: none;
    border: 1px solid var(--paper-edge); border-radius: 999px; padding: 7px 13px; color: var(--ink); background: #fbfcfe;
    transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease, border-color .16s ease, color .16s ease; }
  .abt .member .chip svg { width: 15px; height: 15px; stroke: var(--stamp-text); stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; transition: stroke .16s ease; }
  /* fill + lift the whole pill on hover/focus; icon flips to white */
  .abt .member .chip:hover, .abt .member .chip:focus-visible { background: var(--stamp); border-color: var(--stamp); color: #fff;
    transform: translateY(-2px); box-shadow: 0 12px 22px -12px rgba(46,154,140,.7); outline: none; }
  .abt .member .chip:hover svg, .abt .member .chip:focus-visible svg { stroke: #fff; }
  .abt .member .chip:active { transform: translateY(0); box-shadow: 0 6px 12px -9px rgba(46,154,140,.7); }
  .abt .member .chip.wa { border-color: rgba(31,168,85,.4); color: #177a3e; }
  .abt .member .chip.wa svg { stroke: #1FA855; }
  .abt .member .chip.wa:hover, .abt .member .chip.wa:focus-visible { background: #1FA855; border-color: #1FA855; color: #fff;
    box-shadow: 0 12px 22px -12px rgba(31,168,85,.75); }
  .abt .member .chip.wa:hover svg, .abt .member .chip.wa:focus-visible svg { stroke: #fff; }

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
  .abt .follow-work { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--paper-edge); display: flex; flex-direction: column; align-items: center; text-align: center; gap: 14px; }
  .abt .follow-work .fw-lab { font: 800 14px var(--display); color: var(--ink); }
  .abt .follow-work .fw-lab span { display: block; font: 400 13px var(--display); color: var(--muted); margin-top: 2px; }
  .abt .follow-work .fw-row { display: flex; gap: 9px; justify-content: center; }
  .abt .follow-work .fw-soc { display: inline-flex; width: 40px; height: 40px; align-items: center; justify-content: center; border-radius: 10px; background: var(--white); border: 1px solid var(--paper-edge); color: var(--ink); transition: transform .18s ease, border-color .18s ease, color .18s ease; }
  .abt .follow-work .fw-soc:hover { border-color: var(--stamp); color: var(--stamp-text); transform: translateY(-2px); }
  .abt .follow-work .fw-soc:focus-visible { outline: 2px solid var(--stamp); outline-offset: 2px; }
  .abt .follow-work .fw-soc svg { display: block; }
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
    .abt .follow-work .fw-row { margin-left: 0; }
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

  // Shared team WhatsApp (wa.me digits); blank → no WhatsApp pill.
  $teamWa = preg_replace('/\D+/', '', (string) config('ukv.team_whatsapp', ''));

  // Map query from the registered-office postcode (+ city), falling back to a plain UK view.
  $mapQuery = trim(($addr['postcode'] ?? '') . ' ' . ($addr['city'] ?? '')) ?: 'United Kingdom';
@endphp

<section class="abt"><div class="wrap">
  <div class="head reveal">
    <p class="eyebrow">The team</p>
    <h2>Real people checking your application</h2>
    <p>Your application isn't passed through software and forgotten. Registered in the UK &amp; Europe, we review every file by hand: the same people, start to finish.</p>
  </div>

  <div class="team">
    @foreach ($team as $m)
    <article class="member reveal{{ !empty($m['lead']) ? ' lead' : '' }}">
      @if (!empty($m['lead']))
        <span class="lead-tag">Case lead</span>
      @endif
      <div class="photo">
        @if (!empty($m['photo']))
          <img src="{{ $m['photo'] }}" alt="{{ $m['name'] }}, {{ $m['role'] ?? '' }} at Beyond Passports" loading="lazy" width="96" height="96">
        @else
          <span class="monogram" aria-hidden="true">{{ $initials($m['name'] ?? '') }}</span>
        @endif
      </div>
      <p class="name">{{ $m['name'] ?? '' }}</p>
      <p class="role">{{ $m['role'] ?? '' }}</p>
      <p class="bio">{{ $m['bio'] ?? '' }}</p>
      @if (!empty($m['email']) || $teamWa)
        <div class="contact">
          @if (!empty($m['email']))
            <a class="chip" href="mailto:{{ $m['email'] }}">
              <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>Email
            </a>
          @endif
          @if ($teamWa)
            <a class="chip wa" href="https://wa.me/{{ $teamWa }}" aria-label="WhatsApp {{ $m['name'] ?? 'the team' }}">
              <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.5 8.5 8.5 0 0 1-4-1L3 21l1.9-5.5a8.5 8.5 0 1 1 16.1-4z"/></svg>WhatsApp
            </a>
          @endif
        </div>
      @endif
    </article>
    @endforeach
  </div>

  <div class="loc" id="where-we-are">
    <div class="loc-body reveal">
      <p class="loc-eyebrow">Where we are</p>
      <h3>Registered in UK &amp; Europe, and easy to reach</h3>
      <address class="address">
        @if (!empty($addr['company']))<span class="company">{{ $addr['company'] }}</span><br>@endif
        @if (!empty($addr['line1'])){{ $addr['line1'] }}<br>@endif
        @if (!empty($addr['line2'])){{ $addr['line2'] }}<br>@endif
        @if (!empty($cityCountry)){{ implode(' · ', $cityCountry) }}@endif
      </address>
      <div class="hours"><span class="dot"></span>UK &amp; Europe registered &middot; Mon&ndash;Sat</div>
      {{-- Follow our work — quiet line, only when socials configured and not suppressed by host page --}}
      @if (($showFollow ?? true) && array_filter(config('ukv.social', [])))
      <div class="follow-work">
        <span class="fw-lab">Follow our work<span>Visa-rule changes and traveller stories</span></span>
        <span class="fw-row">@include('partials.social-row', ['cls' => 'fw-soc', 'size' => 18])</span>
      </div>
      @endif
    </div>
    <div class="loc-map">
      <div class="map-frame">
        <iframe title="Map of our UK location" src="https://www.google.com/maps?q={{ urlencode($mapQuery) }}&z=15&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </div>
</div></section>
