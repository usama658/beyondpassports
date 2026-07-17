{{--
    Guide cluster — bento grid (pick G): first guide = large navy hero tile,
    the rest = lighter tiles. Used on the destination money page ("Guides for {country}")
    and the /guides hub. PURE PRESENTATIONAL — pass an iterable of App\Models\Guide rows in.

    Expected variables:
      $cluster   iterable<App\Models\Guide>  published guides (already filtered/ordered)
      $heading   ?string                      optional section heading
      $country   ?string                      optional country name (eyebrow)

    Guard with @@if ($cluster->isNotEmpty()) before including.
--}}
@php
    $cluster = $cluster ?? [];
    $heading = $heading ?? null;
    $country = $country ?? null;

    $readTime = function ($body): string {
        $words = str_word_count(trim(strip_tags((string) $body)));
        return max(1, (int) ceil($words / 200)).' min read';
    };
    $guideUrl = function ($guide): string {
        if ($guide->destination_id && $guide->destination && $guide->guide_type) {
            $topic = $guide->guide_type instanceof \App\Enums\GuideType
                ? $guide->guide_type->topicSlug()
                : \App\Enums\GuideType::from($guide->guide_type)->topicSlug();
            return url('/visa/'.$guide->destination->slug.'/'.$topic);
        }
        return url('/guides/'.$guide->slug);
    };
    $catLabel = fn ($g) => $g->guide_type instanceof \App\Enums\GuideType ? $g->guide_type->label() : 'Guide';
@endphp

<div class="guide-cluster">
  <style>
    /* guide-cluster — bento grid. Self-contained; literal warm-light colours. */
    .guide-cluster .gc-bento{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .guide-cluster .gc-tile{position:relative;display:flex;flex-direction:column;justify-content:flex-end;min-height:186px;
      text-decoration:none;border:1px solid #dde3ec;border-radius:16px;padding:22px;background:#fff;color:#16222E;
      transition:transform .14s ease,box-shadow .15s ease}
    .guide-cluster .gc-tile:hover{transform:translateY(-3px);box-shadow:0 20px 44px -28px rgba(40,50,70,.5)}
    .guide-cluster .gc-tile:focus-visible{outline:2px solid #155E7A;outline-offset:3px}
    .guide-cluster .gc-hero{grid-column:span 2;grid-row:span 2;border:0;color:#fff;
      background:radial-gradient(460px 240px at 14% 0,rgba(21,94,122,.55),transparent 60%),
                 radial-gradient(420px 220px at 96% 100%,rgba(46,154,140,.45),transparent 60%),#16222E}
    .guide-cluster .gc-cat{font-family:"Outfit",system-ui,sans-serif;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#2E9A8C}
    .guide-cluster .gc-hero .gc-cat{color:#A9CCDA}
    .guide-cluster .gc-tile h3{font-family:"Outfit",system-ui,sans-serif;font-weight:700;font-size:17px;line-height:1.2;letter-spacing:-.01em;color:#16222E;margin:8px 0 0}
    .guide-cluster .gc-hero h3{color:#fff;font-size:clamp(22px,2.4vw,27px);margin:10px 0 8px}
    .guide-cluster .gc-hero .gc-excerpt{color:rgba(255,255,255,.85);font-size:14.5px;line-height:1.5;margin:0 0 12px;max-width:46ch}
    .guide-cluster .gc-meta{font-family:"Outfit",system-ui,sans-serif;font-size:11px;font-weight:600;letter-spacing:.06em;color:#697079;margin-top:10px}
    .guide-cluster .gc-hero .gc-meta{color:rgba(255,255,255,.72)}
    @media (max-width:820px){.guide-cluster .gc-bento{grid-template-columns:1fr 1fr}.guide-cluster .gc-hero{grid-row:auto}}
    @media (max-width:520px){.guide-cluster .gc-bento{grid-template-columns:1fr}.guide-cluster .gc-hero{grid-column:span 1}}
  </style>

  @if ($heading)
    <div class="sec-head reveal" style="margin-bottom:18px">
      <p class="eyebrow">Travel guides</p>
      <h2 style="font-size:clamp(24px,3vw,30px);color:var(--navy)">{{ $heading }}</h2>
    </div>
  @endif

  <div class="gc-bento">
    @foreach ($cluster as $guide)
      @if ($loop->first)
        <a class="gc-tile gc-hero reveal" href="{{ $guideUrl($guide) }}">
          <div class="gc-cat">{{ $catLabel($guide) }}</div>
          <h3>{{ $guide->title }}</h3>
          @if ($guide->excerpt)<p class="gc-excerpt">{{ $guide->excerpt }}</p>@endif
          <div class="gc-meta">{{ $readTime($guide->body) }}</div>
        </a>
      @else
        <a class="gc-tile reveal" href="{{ $guideUrl($guide) }}">
          <div class="gc-cat">{{ $catLabel($guide) }}</div>
          <h3>{{ $guide->title }}</h3>
          <div class="gc-meta">{{ $readTime($guide->body) }}</div>
        </a>
      @endif
    @endforeach
  </div>
</div>
