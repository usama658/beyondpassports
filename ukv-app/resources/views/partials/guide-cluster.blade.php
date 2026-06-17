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
    /* Featured layout (option F): big dark hero guide + a column of smaller cards. */
    .guide-cluster .gc-wrap{display:grid;grid-template-columns:1.35fr 1fr;gap:18px;align-items:stretch}
    .guide-cluster .gc-side{display:grid;gap:18px;align-content:stretch}
    .guide-cluster a.gc-feat{position:relative;display:flex;flex-direction:column;justify-content:flex-end;min-height:300px;
      text-decoration:none;color:#fff;border-radius:16px;padding:30px;overflow:hidden;
      background:radial-gradient(520px 240px at 14% 0%,rgba(199,93,56,.5),transparent 60%),radial-gradient(480px 220px at 90% 100%,rgba(92,154,123,.45),transparent 60%),var(--navy,#22282b);
      box-shadow:var(--lift2,0 24px 52px -24px rgba(40,50,70,.34));transition:transform .12s ease,box-shadow .15s ease}
    .guide-cluster a.gc-feat:hover{transform:translateY(-3px)}
    .guide-cluster a.gc-feat:focus-visible{outline:2px solid var(--soft,#F2C2AC);outline-offset:3px}
    .guide-cluster a.gc-feat .gc-cat{color:var(--soft,#F2C2AC)}
    .guide-cluster a.gc-feat h3{color:#fff;font-size:clamp(22px,2.4vw,28px);margin:10px 0 8px;line-height:1.12}
    .guide-cluster a.gc-feat .gc-excerpt{color:rgba(255,255,255,.85);font-size:15px;margin:0 0 14px}
    .guide-cluster a.gc-feat .gc-meta{color:rgba(255,255,255,.72);border-top:1px solid rgba(255,255,255,.18);padding-top:11px}
    @media (max-width:820px){.guide-cluster .gc-wrap{grid-template-columns:1fr}}
    .guide-cluster a.gc-card{display:flex;flex-direction:column;text-decoration:none;color:inherit;
      background:var(--white,#fff);border:1px solid var(--paper-edge,#dfe6ea);border-radius:10px;overflow:hidden;
      transition:transform .12s ease,box-shadow .15s ease}
    .guide-cluster a.gc-card:hover{transform:translateY(-3px);box-shadow:0 12px 26px rgba(40,50,70,.12)}
    .guide-cluster a.gc-card:focus-visible{outline:2px solid var(--cta,#C75D38);outline-offset:3px}
    .guide-cluster .gc-card .band{height:7px;background:var(--cta,#C75D38)}
    .guide-cluster .gc-card .gc-body{padding:18px 18px 16px;display:flex;flex-direction:column;flex:1}
    .guide-cluster .gc-card .gc-cat{font-family:var(--mono,"Plus Jakarta Sans",system-ui,sans-serif);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text,#3f7259)}
    .guide-cluster .gc-card h3{font-family:var(--display,"Plus Jakarta Sans",system-ui,sans-serif);font-size:19px;color:var(--navy,#22282b);margin:8px 0 6px;line-height:1.18}
    .guide-cluster .gc-card .gc-excerpt{font-size:14.5px;color:#33454f;margin:0 0 14px;flex:1}
    .guide-cluster .gc-card .gc-meta{font-family:var(--mono,"Plus Jakarta Sans",system-ui,sans-serif);font-size:11px;letter-spacing:.06em;color:var(--hint,#697079);
      border-top:1px solid var(--paper-edge,#dfe6ea);padding-top:11px}
  </style>

  @if ($heading)
    <div class="sec-head reveal" style="margin-bottom:18px">
      <p class="eyebrow">Free travel guides</p>
      <h2 style="font-size:clamp(24px,3vw,30px);color:var(--navy)">{{ $heading }}</h2>
    </div>
  @endif

  <div class="gc-wrap">
    @foreach ($cluster as $guide)
      @if ($loop->first)
        <a class="gc-feat reveal" href="{{ $guideUrl($guide) }}">
          <div class="gc-cat">{{ $guide->guide_type instanceof \App\Enums\GuideType ? $guide->guide_type->label() : 'Guide' }}</div>
          <h3>{{ $guide->title }}</h3>
          <p class="gc-excerpt">{{ $guide->excerpt }}</p>
          <div class="gc-meta">{{ $readTime($guide->body) }}</div>
        </a>
        <div class="gc-side">
      @else
        <a class="gc-card reveal" href="{{ $guideUrl($guide) }}">
          <div class="band"></div>
          <div class="gc-body">
            <div class="gc-cat">{{ $guide->guide_type instanceof \App\Enums\GuideType ? $guide->guide_type->label() : 'Guide' }}</div>
            <h3>{{ $guide->title }}</h3>
            <div class="gc-meta">{{ $readTime($guide->body) }}</div>
          </div>
        </a>
      @endif
    @endforeach
    </div>
  </div>
</div>
