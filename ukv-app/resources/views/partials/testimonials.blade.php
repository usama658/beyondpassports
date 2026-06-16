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
  .quote-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;align-items:start}
  .quote.quote--card{max-width:none;background:var(--white,#fff);border:1px solid var(--paper-edge,#e3e9ed);border-radius:12px;padding:26px 24px;display:flex;flex-direction:column;height:100%}
  .quote.quote--card blockquote{font-size:clamp(18px,1.6vw,21px);line-height:1.4}
  .quote.quote--card .rating{font-family:var(--mono);font-size:15px;color:var(--gold,#C8A24A);margin-top:16px;letter-spacing:2px}
  .quote.quote--card .by{margin-top:auto;padding-top:18px}
  .quote.quote--card .by .avatar svg{width:100%;height:100%;display:block}
  @media (max-width:860px){.quote-grid{grid-template-columns:1fr}}
</style>
@endpush
@endonce

<section class="alt" aria-labelledby="testimonials-head"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">{{ $eyebrow }}</p>
    <h2 id="testimonials-head">{{ $heading }}</h2>
  </div>

  <div class="quote-grid">
    @foreach ($items as $t)
      <figure class="quote quote--card reveal">
        <blockquote>“{{ $t['quote'] }}”</blockquote>
        @if (!empty($t['rating']))
          @php $r = max(1, min(5, (int) $t['rating'])); @endphp
          <div class="rating" role="img" aria-label="Rated {{ $r }} out of 5">
            <span aria-hidden="true">{{ str_repeat('★', $r) }}{{ str_repeat('☆', 5 - $r) }}</span>
          </div>
        @endif
        <figcaption class="by">
          <span class="avatar">
            <svg viewBox="0 0 44 44" role="img" aria-label="Beyond Passports traveller"><use href="#ukv-monogram"></use></svg>
          </span>
          <span>{{ $t['attribution'] }}</span>
        </figcaption>
      </figure>
    @endforeach
  </div>

  @if ($showAll)
    <p style="margin-top:26px">
      <a class="rlink" style="font-weight:600" href="{{ url('/reviews') }}">Read more traveller reviews →</a>
    </p>
  @endif
</div></section>
