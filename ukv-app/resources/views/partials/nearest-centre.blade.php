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
    .nearest-centre .nc-title{font-size:20px;font-weight:600;color:#22282b;margin:0 0 16px;line-height:1.3}
    /* empty state — centred soft-sky card with a pin (premium) */
    .nearest-centre .nc-empty{text-align:center;background:linear-gradient(180deg,#EAF1F4,#fff);border:1px solid #e6e8ea;border-radius:18px;padding:34px 26px;box-shadow:0 14px 36px -22px rgba(40,50,70,.32)}
    .nearest-centre .nc-empty .nc-empty-ic{width:54px;height:54px;border-radius:16px;background:#fff;border:1px solid #e6e8ea;color:#C75D38;display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
    .nearest-centre .nc-empty .nc-empty-ic svg{width:28px;height:28px}
    .nearest-centre .nc-empty p{margin:0 auto;max-width:48ch;font-size:14.5px;color:#697079;line-height:1.6}
    .nearest-centre .nc-empty strong{color:#22282b}
    .nearest-centre .nc-list{list-style:none;margin:0;padding:0;display:grid;gap:14px}
    /* result card — premium: soft shadow, hover lift, distance pill */
    .nearest-centre .nc-card{border:1px solid #e6e8ea;border-radius:14px;background:#fff;padding:20px 22px;box-shadow:0 10px 30px -24px rgba(40,50,70,.4);transition:transform .2s ease,box-shadow .2s ease}
    .nearest-centre .nc-card:hover{transform:translateY(-2px);box-shadow:0 18px 44px -26px rgba(40,50,70,.5)}
    .nearest-centre .nc-card.is-booked{border-color:#eccab4;box-shadow:0 10px 30px -24px rgba(199,93,56,.5)}
    .nearest-centre .nc-card-top{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:8px 14px;margin:0 0 10px}
    .nearest-centre .nc-name{font-size:17px;font-weight:700;color:#22282b;margin:0;line-height:1.3}
    .nearest-centre .nc-dist{font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-size:11.5px;font-weight:700;color:#3f7259;white-space:nowrap;letter-spacing:.02em;background:#eaf3f2;border:1px solid #cfe6e3;border-radius:999px;padding:3px 10px}
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
        <li class="nc-card @if ($booked) is-booked @endif">
          <div class="nc-card-top">
            <h3 class="nc-name">{{ $node->name }}</h3>
            @if ($distance !== null)
              <span class="nc-dist" aria-label="{{ number_format((float) $distance, 1) }} kilometres away">{{ number_format((float) $distance, 1) }} km away</span>
            @endif
          </div>

          <div class="nc-badges">
            <span class="nc-badge is-type">{{ $tlabel }}</span>
            @if ($booked)
              <span class="nc-badge is-book" title="We can book your appointment here">✓ We book here</span>
            @endif
          </div>

          @if ($addressLine !== '')
            <p class="nc-addr">{{ $addressLine }}</p>
          @endif

          <div class="nc-meta">
            @if ($contactHref !== null)
              <a class="nc-link"
                 href="{{ $contactHref }}"
                 @if (str_starts_with($contactHref, 'http')) target="_blank" rel="noopener noreferrer" @endif
              >{{ str_starts_with($contactHref, 'tel:') ? 'Call this centre' : (str_starts_with($contactHref, 'mailto:') ? 'Email this centre' : 'Visit website') }} →</a>
            @elseif (is_string($contact) && $contact !== '')
              <span class="nc-pp">{{ $contact }}</span>
            @endif

            @if ($tkey === 'paypoint' && $contactHref === null)
              {{-- PayPoint IDP nodes with no specific contact: link the official PayPoint
                   locator (we don't replicate their full store database). --}}
              <span class="nc-pp">IDPs are issued in person — <a href="{{ $paypointLocator }}" target="_blank" rel="noopener noreferrer">find a PayPoint near you</a>.</span>
            @endif
          </div>

          {{-- Held-slot availability (Wave 2, partial owned by agent B3). Referenced by
               contract only — guarded so it renders nothing in a Wave-1-only state. --}}
          @includeIf('partials.centre-slots', ['node' => $node])
        </li>
      @endforeach
    </ul>
  @endif
</div>
