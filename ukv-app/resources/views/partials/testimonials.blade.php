{{--
  Reusable consented-testimonials section (task L4.2 / #184).

  Single source of truth: App\Http\Controllers\ReviewController::all().
  This partial pulls from there by default, so home/about can include it with
  zero extra wiring and never duplicate the copy.

  Privacy: every entry is ANONYMISED + consented — generic attribution only
  ("UK traveller · Egypt eVisa"). No names, faces or identifying details.
  Compliance: nothing here implies a guaranteed visa outcome.

  Params (all optional):
    $limit        int   — how many cards to show (default 3; pass 0 for all)
    $testimonials array — override the source list (e.g. the full set on /reviews)
    $eyebrow      string — section eyebrow text
    $heading      string — section heading
    $showAll      bool   — render a "Read more reviews" link to /reviews (default true)

  Include (home.blade.php / about.blade.php):
    @include('partials.testimonials')
--}}
@php
    $limit        = $limit        ?? 3;
    $eyebrow      = $eyebrow      ?? 'Trusted by UK travellers';
    $heading      = $heading      ?? 'In our travellers’ words';
    $showAll      = $showAll      ?? true;

    $source = $testimonials ?? \App\Http\Controllers\ReviewController::all();
    $items  = ($limit > 0) ? array_slice($source, 0, $limit) : $source;
@endphp

{{-- Section-local styles for the card grid. Builds on the design-system .quote
     rules in assets/ukv.css; @once guards against double output when included
     alongside the full /reviews page. --}}
@once
@push('head')
<style>
  /* Testimonials — summary rail + review rows (pick E) */
  .tm-wrap{display:grid;grid-template-columns:280px 1fr;gap:34px;align-items:start}
  @media (max-width:760px){.tm-wrap{grid-template-columns:1fr;gap:24px}}

  .tm-sum{position:sticky;top:84px;background:var(--navy);color:#fff;border-radius:16px;padding:26px 24px;overflow:hidden}
  .tm-sum::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 70% at 90% 0,rgba(199,93,56,.30),transparent 60%),radial-gradient(60% 70% at 0 100%,rgba(92,154,123,.30),transparent 62%)}
  .tm-sum > *{position:relative;z-index:2}
  .tm-sum .eyebrow{color:var(--soft);margin:0 0 12px}
  .tm-sum h2{color:#fff;font-size:22px;letter-spacing:-.02em;margin:0 0 10px}
  .tm-sum p{font-size:13px;line-height:1.55;color:rgba(255,255,255,.78);margin:0}
  @media (max-width:760px){.tm-sum{position:static}}

  .tm-tiles{display:flex;gap:3px;margin:0 0 14px}
  .tm-tiles i{width:22px;height:22px;background:var(--stamp);display:flex;align-items:center;justify-content:center;border-radius:3px}
  .tm-tiles i svg{width:13px;height:13px;fill:#fff;stroke:none}

  .tm-rows{display:flex;flex-direction:column}
  .tm-row{padding:20px 0;border-bottom:1px solid var(--paper-edge);display:flex;flex-direction:column;gap:10px;margin:0}
  .tm-row:first-child{padding-top:0}
  .tm-row .tm-tiles{margin:0}.tm-row .tm-tiles i{width:18px;height:18px}.tm-row .tm-tiles i svg{width:11px;height:11px}
  .tm-row blockquote{margin:0;font-size:clamp(16px,1.5vw,18px);line-height:1.5;color:var(--navy);font-weight:500}
  .tm-row .meta{display:flex;align-items:center;flex-wrap:wrap;gap:8px 14px;font-size:11.5px;font-weight:700}
  .tm-row .verified{display:inline-flex;align-items:center;gap:6px;color:var(--stamp-text);letter-spacing:.03em}
  .tm-row .verified svg{width:13px;height:13px;fill:none;stroke:var(--stamp);stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
  .tm-row .who{color:var(--muted);letter-spacing:.03em;text-transform:uppercase}
</style>
@endpush
@endonce

<section class="alt" aria-labelledby="testimonials-head"><div class="wrap">
  <div class="tm-wrap">

    {{-- Summary rail (no aggregate score — page intentionally avoids a rating dataset) --}}
    <aside class="tm-sum reveal">
      <p class="eyebrow">{{ $eyebrow }}</p>
      <h2 id="testimonials-head">{{ $heading }}</h2>
      <p>Every review here is shared with the traveller's consent and anonymised. We show their words — not a score we can't verify.</p>
      @if ($showAll)
        <p style="margin-top:16px"><a class="rlink" style="font-weight:700;color:var(--soft)" href="{{ url('/reviews') }}">Read more reviews →</a></p>
      @endif
    </aside>

    {{-- Review rows --}}
    <div class="tm-rows">
      @foreach ($items as $t)
        <figure class="tm-row reveal">
          @if (!empty($t['rating']))
            @php $r = max(1, min(5, (int) $t['rating'])); @endphp
            <span class="tm-tiles" role="img" aria-label="Rated {{ $r }} out of 5">
              @for ($s = 1; $s <= $r; $s++)
                <i aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2l3 6.5 7 .8-5 4.7 1.4 7L12 18l-6.4 3 1.4-7-5-4.7 7-.8z"/></svg></i>
              @endfor
            </span>
          @endif
          <blockquote>“{{ $t['quote'] }}”</blockquote>
          <figcaption class="meta">
            <span class="verified"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Verified &middot; consented</span>
            <span class="who">{{ $t['attribution'] }}</span>
          </figcaption>
        </figure>
      @endforeach
    </div>

  </div>
</div></section>
