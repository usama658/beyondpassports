{{--
    Reusable nearest-centre results list (Wave 1 / A3).

    PURE PRESENTATIONAL. No DB / service calls here. Compute $results in the controller
    (CentreFinderService::nearest) and pass it in. Safe to drop on any surface (the finder
    page, the IDP/driving-abroad page, the checklist result) — scoped to .nearest-centre and
    uses literal colours (matched to ukv.css: ink #22282b / terracotta #C75D38 / sage #5C9A7B).

    Expected variables:
      $results  \Illuminate\Support\Collection|null  — each item:
                  ['node' => App\Models\SupplyNode, 'distance_km' => float]
                null  = no search yet (shows the gentle "enter a postcode" prompt)
                empty = searched, nothing nearby (shows the "no centres / most visas online" note)
      $heading  ?string (default "Centres near you")

    Each node exposes: name, type (SupplyNodeType|string: centre|paypoint|embassy|courier),
    address, postcode, contact, we_book_here.

    Include with:
      @@include('partials.nearest-centre', ['results' => $results])

    Never errors. Always renders something sensible.
--}}
@php
    /** @var \Illuminate\Support\Collection|null $results */
    $results = $results ?? null;
    $heading = $heading ?? 'Centres near you';

    // Type -> visitor-facing badge label. Accepts the SupplyNodeType enum or a bare string.
    $typeLabels = [
        'centre'   => 'Visa centre',
        'paypoint' => 'PayPoint · IDP',
        'embassy'  => 'Embassy',
        'courier'  => 'Courier',
    ];

    $typeKey = function ($type): string {
        // SupplyNodeType enum -> ->value; string -> itself.
        if ($type instanceof \App\Enums\SupplyNodeType) {
            return $type->value;
        }
        return is_string($type) ? strtolower(trim($type)) : '';
    };

    // Official PayPoint locator — IDPs are collected in person at a PayPoint, and we don't
    // replicate PayPoint's full database. Linked when a paypoint node has no specific contact.
    $paypointLocator = 'https://www.paypoint.com/en-gb/consumers/store-locator';
@endphp

<div class="nearest-centre" role="region" aria-label="{{ $heading }}">
  <style>
    /* nearest-centre partial — self-contained, warm-light palette (ink/terracotta/sage, Plus Jakarta).
       Scoped to .nearest-centre so it is safe on any surface. Literal colours, not vars. */
    .nearest-centre{font-family:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#22282b}
    .nearest-centre .nc-head{font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#3f7259;margin:0 0 6px}
    .nearest-centre .nc-title{font-size:clamp(24px,3vw,32px);font-weight:700;letter-spacing:-.02em;color:#22282b;margin:0 0 18px;line-height:1.12}
    /* empty state — centred soft-sky card with a pin (premium) */
    .nearest-centre .nc-empty{text-align:center;background:linear-gradient(180deg,#EAF1F4,#fff);border:1px solid #e6e8ea;border-radius:18px;padding:34px 26px;box-shadow:0 14px 36px -22px rgba(40,50,70,.32)}
    .nearest-centre .nc-empty .nc-empty-ic{width:54px;height:54px;border-radius:16px;background:#fff;border:1px solid #e6e8ea;color:#C75D38;display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
    .nearest-centre .nc-empty .nc-empty-ic svg{width:28px;height:28px}
    .nearest-centre .nc-empty p{margin:0 auto;max-width:48ch;font-size:14.5px;color:#697079;line-height:1.6}
    .nearest-centre .nc-empty strong{color:#22282b}
    .nearest-centre .nc-list{list-style:none;margin:0;padding:0;display:grid;gap:16px}
    /* result card — info + action rail (pick F) */
    .nearest-centre .nc-card{display:grid;grid-template-columns:1fr auto;gap:0;border:1px solid #e6e8ea;border-radius:16px;background:#fff;overflow:hidden;box-shadow:0 10px 30px -24px rgba(40,50,70,.4);transition:transform .2s ease,box-shadow .2s ease}
    .nearest-centre .nc-card:hover{transform:translateY(-2px);box-shadow:0 18px 44px -26px rgba(40,50,70,.5)}
    .nearest-centre .nc-card.is-booked{border-color:#eccab4}
    .nearest-centre .nc-info{padding:20px 22px;min-width:0}
    .nearest-centre .nc-rail{background:#f4f5f6;border-left:1px solid #e6e8ea;padding:18px 20px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;text-align:center;min-width:158px}
    .nearest-centre .nc-card.is-booked .nc-rail{background:linear-gradient(180deg,#fbeee6,#fff);border-left-color:#eccab4}
    .nearest-centre .nc-km{font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-size:19px;font-weight:800;color:#22282b;line-height:1.1}
    .nearest-centre .nc-km small{display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#697079;margin-top:3px}
    .nearest-centre .nc-railbtn{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:100%;border-radius:10px;padding:10px 14px;font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-weight:700;font-size:13px;text-decoration:none}
    .nearest-centre .nc-railbtn.is-pri{background:#C75D38;color:#fff}
    .nearest-centre .nc-railbtn.is-ghost{background:#fff;border:1px solid #e6e8ea;color:#22282b}
    .nearest-centre .nc-card-top{margin:0 0 10px}
    .nearest-centre .nc-name{font-size:17px;font-weight:700;color:#22282b;margin:0;line-height:1.3}
    .nearest-centre .nc-badges{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 10px}
    .nearest-centre .nc-badge{display:inline-flex;align-items:center;gap:5px;font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-weight:700;font-size:10.5px;letter-spacing:.06em;text-transform:uppercase;border-radius:999px;padding:4px 10px;background:#eef3f7;color:#22282b;border:1px solid #e6e8ea}
    .nearest-centre .nc-badge.is-type{background:#eaf3f2;color:#3f7259;border-color:#cfe6e3}
    .nearest-centre .nc-badge.is-book{background:#faecdf;color:#9c4a26;border-color:#eccab4}
    .nearest-centre .nc-addr{font-size:14px;color:#697079;margin:0 0 10px;line-height:1.5}
    .nearest-centre .nc-meta{display:flex;flex-wrap:wrap;gap:8px 18px;align-items:center;margin:0}
    .nearest-centre .nc-link{font-size:14px;font-weight:600;color:#C75D38;text-decoration:none}
    .nearest-centre .nc-link:hover{text-decoration:underline}
    .nearest-centre .nc-pp{font-size:12.5px;color:#697079}
    .nearest-centre .nc-pp a{color:#C75D38}
  </style>

  <p class="nc-head">Nearest centres</p>
  <h2 class="nc-title">{{ $heading }}</h2>

  @php
    $ncPin = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
  @endphp
  @if ($results === null)
    {{-- No search run yet. --}}
    <div class="nc-empty"><span class="nc-empty-ic">{!! $ncPin !!}</span><p>Enter a postcode above to see the centres nearest to you. We'll show the closest first, and flag any where we can book your appointment for you.</p></div>
  @elseif ($results->isEmpty())
    {{-- Searched, nothing located nearby. --}}
    <div class="nc-empty"><span class="nc-empty-ic">{!! $ncPin !!}</span><p><strong>No centres found nearby.</strong> Good news — most UK travel documents are now online: an <strong>eVisa</strong> or <strong>ETA</strong> needs no in-person visit at all. Tell us your destination and we'll confirm exactly what you need.</p></div>
  @else
    <ul class="nc-list">
      @foreach ($results as $r)
        @php
          $node     = $r['node'];
          $distance = $r['distance_km'] ?? null;
          $tkey     = $typeKey($node->type);
          $tlabel   = $typeLabels[$tkey] ?? 'Centre';
          $booked   = (bool) ($node->we_book_here ?? false);
          $contact  = $node->contact ?? null;
          // A contact may be a URL, an email, or a phone — link it sensibly.
          $contactHref = null;
          if (is_string($contact) && $contact !== '') {
              if (str_contains($contact, '@') && ! str_contains($contact, ' ') && ! str_starts_with($contact, 'http')) {
                  $contactHref = 'mailto:'.$contact;
              } elseif (str_starts_with($contact, 'http://') || str_starts_with($contact, 'https://')) {
                  $contactHref = $contact;
              } elseif (preg_match('/^[\d+][\d\s()-]{5,}$/', $contact)) {
                  $contactHref = 'tel:'.preg_replace('/\s+/', '', $contact);
              }
          }
          $addressLine = trim(implode(', ', array_filter([
              $node->address ?? null,
              $node->postcode ?? null,
          ])));
        @endphp
        @php
          // Rail action label
          $railLabel = $contactHref
              ? (str_starts_with($contactHref, 'tel:') ? 'Call' : (str_starts_with($contactHref, 'mailto:') ? 'Email' : 'Website ↗'))
              : null;
        @endphp
        <li class="nc-card @if ($booked) is-booked @endif">
          <div class="nc-info">
            <h3 class="nc-name">{{ $node->name }}</h3>

            <div class="nc-badges">
              <span class="nc-badge is-type">{{ $tlabel }}</span>
              @if ($booked)
                <span class="nc-badge is-book" title="We can book your appointment here">✓ We book here</span>
              @endif
            </div>

            @if ($addressLine !== '')
              <p class="nc-addr"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex:0 0 15px;margin-top:2px;color:#9aa1a8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> {{ $addressLine }}</p>
            @endif

            @if ($tkey === 'paypoint' && $contactHref === null)
              <p class="nc-pp" style="margin:8px 0 0">IDPs are issued in person — <a href="{{ $paypointLocator }}" target="_blank" rel="noopener noreferrer">find a PayPoint near you</a>.</p>
            @elseif (! $contactHref && is_string($contact) && $contact !== '')
              <p class="nc-pp" style="margin:8px 0 0">{{ $contact }}</p>
            @endif

            {{-- Held-slot availability (Wave 2). Guarded — renders nothing if absent. --}}
            @includeIf('partials.centre-slots', ['node' => $node])
          </div>

          <div class="nc-rail">
            @if ($distance !== null)
              <span class="nc-km" aria-label="{{ number_format((float) $distance, 1) }} kilometres away">{{ number_format((float) $distance, 1) }}<small>km away</small></span>
            @endif
            @if ($contactHref !== null)
              <a class="nc-railbtn {{ $booked ? 'is-pri' : 'is-ghost' }}"
                 href="{{ $contactHref }}"
                 @if (str_starts_with($contactHref, 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $railLabel }}</a>
            @endif
          </div>
        </li>
      @endforeach
    </ul>
  @endif
</div>
