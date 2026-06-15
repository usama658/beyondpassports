{{--
    Guide cluster — hub listing cards for a set of guides (excerpt + read-time).

    Used on the destination money page ("Guides for {country}") and reusable anywhere a
    cluster of published guides needs to be listed. PURE PRESENTATIONAL — pass an iterable
    of App\Models\Guide rows in; compute nothing here that needs the DB.

    Expected variables:
      $cluster   iterable<App\Models\Guide>  published guides to list (already filtered/ordered)
      $heading   ?string                      optional section heading; omit to render cards only
      $country   ?string                      optional country name (for the eyebrow)

    Each card links to the guide's public URL: country guides -> /visa/{dest}/{topic},
    evergreen guides -> /guides/{slug}. Read-time is derived from the body word count.

    Guard the include with @@if so an empty cluster renders nothing:
      @@if ($guideCluster->isNotEmpty())
        @@include('partials.guide-cluster', ['cluster' => $guideCluster, 'country' => $name])
      @@endif
--}}
@php
    $cluster = $cluster ?? [];
    $heading = $heading ?? null;
    $country = $country ?? null;

    // Read-time helper: ~200 wpm over the rendered body, min 1 min. Null-safe.
    $readTime = function ($body): string {
        $words = str_word_count(trim(strip_tags((string) $body)));
        $mins  = max(1, (int) ceil($words / 200));
        return $mins.' min read';
    };

    // Public URL for a guide row: nested for country guides, flat for evergreen.
    $guideUrl = function ($guide): string {
        if ($guide->destination_id && $guide->destination && $guide->guide_type) {
            $topic = $guide->guide_type instanceof \App\Enums\GuideType
                ? $guide->guide_type->topicSlug()
                : \App\Enums\GuideType::from($guide->guide_type)->topicSlug();
            return url('/visa/'.$guide->destination->slug.'/'.$topic);
        }
        return url('/guides/'.$guide->slug);
    };
@endphp

<div class="guide-cluster">
  <style>
    /* guide-cluster partial — self-contained, palette via ukv.css vars where present,
       literal fallbacks so it is safe on the navy + paper surfaces alike. */
    .guide-cluster .gc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px;margin:0}
    .guide-cluster a.gc-card{display:flex;flex-direction:column;text-decoration:none;color:inherit;
      background:var(--white,#fff);border:1px solid var(--paper-edge,#dfe6ea);border-radius:10px;overflow:hidden;
      transition:transform .12s ease,box-shadow .15s ease}
    .guide-cluster a.gc-card:hover{transform:translateY(-3px);box-shadow:0 12px 26px rgba(10,37,64,.10)}
    .guide-cluster a.gc-card:focus-visible{outline:2px solid var(--cta,#c8a24a);outline-offset:3px}
    .guide-cluster .gc-card .band{height:7px;background:var(--cta,#c8a24a)}
    .guide-cluster .gc-card .gc-body{padding:18px 18px 16px;display:flex;flex-direction:column;flex:1}
    .guide-cluster .gc-card .gc-cat{font-family:var(--mono,"Space Mono",monospace);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp,#0e6e6e)}
    .guide-cluster .gc-card h3{font-family:var(--display,Fraunces,serif);font-size:19px;color:var(--navy,#0f2747);margin:8px 0 6px;line-height:1.18}
    .guide-cluster .gc-card .gc-excerpt{font-size:14.5px;color:#33454f;margin:0 0 14px;flex:1}
    .guide-cluster .gc-card .gc-meta{font-family:var(--mono,"Space Mono",monospace);font-size:11px;letter-spacing:.06em;color:var(--hint,#5d6f79);
      border-top:1px solid var(--paper-edge,#dfe6ea);padding-top:11px}
  </style>

  @if ($heading)
    <div class="sec-head reveal" style="margin-bottom:18px">
      <p class="eyebrow">{{ $country ? 'Guides for '.$country : 'Guides' }}</p>
      <h2 style="font-size:clamp(24px,3vw,30px);color:var(--navy)">{{ $heading }}</h2>
    </div>
  @endif

  <div class="gc-grid">
    @foreach ($cluster as $guide)
      <a class="gc-card reveal" href="{{ $guideUrl($guide) }}">
        <div class="band"></div>
        <div class="gc-body">
          <div class="gc-cat">{{ $guide->guide_type instanceof \App\Enums\GuideType ? $guide->guide_type->label() : 'Guide' }}</div>
          <h3>{{ $guide->title }}</h3>
          <p class="gc-excerpt">{{ $guide->excerpt }}</p>
          <div class="gc-meta">{{ $readTime($guide->body) }}</div>
        </div>
      </a>
    @endforeach
  </div>
</div>
