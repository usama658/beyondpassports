{{--
    Public self-verification trust strip — Module A (guide engine SEO silo).

    PURE PRESENTATIONAL. No DB / service calls here. Pass a Destination model in.
    Renders: "Facts last reviewed {date} — confirm current rules at the official source →"
    plus inline links to each official source from $destination->sources.

    Expected variables:
      $destination  App\Models\Destination — uses ->facts_checked_at (Carbon|null) and
                    ->sources (array of strings or {label,url} maps).

    Include with:
      @@include('partials.verify-strip', ['destination' => $destination])

    No facts_checked_at => omits the date clause (still shows the verify prompt + links).
    No sources => shows the prompt without links. Never errors.
--}}
@php
    /** @var \App\Models\Destination $destination */
    $reviewed = $destination->facts_checked_at ?? null;
    $reviewedLabel = $reviewed instanceof \Illuminate\Support\Carbon ? $reviewed->format('j M Y') : null;

    // Normalise sources to [{label, url}] — accept bare URL strings or {label,url} maps.
    $rawSources = is_array($destination->sources ?? null) ? $destination->sources : [];
    $sources = [];
    foreach ($rawSources as $source) {
        $url = is_array($source) ? ($source['url'] ?? null) : $source;
        $url = is_string($url) ? trim($url) : '';
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            continue;
        }
        $label = is_array($source) ? trim((string) ($source['label'] ?? '')) : '';
        if ($label === '') {
            $label = preg_replace('#^www\.#', '', parse_url($url, PHP_URL_HOST) ?: 'official source');
        }
        $sources[] = ['label' => $label, 'url' => $url];
    }
@endphp

<aside class="verify-strip" role="note" aria-label="Fact verification">
  <style>
    /* verify-strip partial — self-contained, warm-light palette (ink/terracotta/sage, Plus Jakarta). */
    .verify-strip{font-family:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#16222E;background:#f7fafb;border:1px solid #dde3ec;border-left:3px solid #155E7A;border-radius:8px;padding:14px 16px;margin:18px 0}
    .verify-strip .vs-head{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
    .verify-strip .vs-badge{font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-weight:700;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#1F6E63}
    .verify-strip .vs-text{font-size:14px;color:#16222E;line-height:1.5;margin:0}
    .verify-strip .vs-text strong{color:#16222E;font-weight:600}
    .verify-strip .vs-sources{list-style:none;margin:8px 0 0;padding:0;display:flex;flex-wrap:wrap;gap:6px 16px}
    .verify-strip .vs-sources li{font-size:13px}
    .verify-strip .vs-sources a{color:#1F6E63;font-weight:600;text-decoration:none;border-bottom:1px solid rgba(63,114,89,.35)}
    .verify-strip .vs-sources a:hover{border-bottom-color:#1F6E63}
  </style>

  <div class="vs-head">
    <span class="vs-badge">Verify</span>
    <p class="vs-text">
      @if ($reviewedLabel)
        Facts last reviewed <strong>{{ $reviewedLabel }}</strong> —
      @endif
      always confirm current rules at the official source
      @if (empty($sources))
        before you travel.
      @else
        →
      @endif
    </p>
  </div>

  @if (! empty($sources))
    <ul class="vs-sources">
      @foreach ($sources as $source)
        <li>
          <a href="{{ $source['url'] }}" rel="nofollow noopener" target="_blank">{{ $source['label'] }}</a>
        </li>
      @endforeach
    </ul>
  @endif
</aside>
