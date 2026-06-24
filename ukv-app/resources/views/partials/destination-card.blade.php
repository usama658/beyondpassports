{{-- Reusable boarding-pass destination card. Expects $destination. Uses global .pass/.dests
     classes (ukv.css). Price slot honours config('ukv.show_prices'); falls back to max-stay
     then "View". Shared by the destinations index and the Schengen hub. --}}
@php $fromFee = $destination->tier_standard_gbp; @endphp
<a class="pass reveal" href="{{ url('/visa/'.$destination->slug) }}" data-name="{{ strtolower($destination->name) }}" data-region="{{ $destination->region ?? '' }}" style="text-decoration:none;color:inherit">
  <div class="sky">@if ($destination->image_path)<img src="{{ asset(ltrim($destination->image_path, '/')) }}" alt="{{ $destination->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $destination->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div>
  <div class="lower">
    <div class="main">
      <span class="k">{{ $destination->visa_type ? strtoupper($destination->visa_type) : 'Visa' }}</span>
      <h3>{{ $destination->name }}</h3>
      <p class="t">Prepared &amp; checked by our UK team</p>
    </div>
    <div class="stub">
      @if (config('ukv.show_prices'))
        <span class="fee">@if (! is_null($fromFee)) £{{ rtrim(rtrim(number_format((float) $fromFee, 2), '0'), '.') }} @else - @endif</span>
        <span class="lab">{{ ! is_null($fromFee) ? 'from / service' : 'service fee' }}</span>
      @elseif ($destination->max_stay_days)
        <span class="fee">{{ $destination->max_stay_days }}</span>
        <span class="lab">days max</span>
      @else
        <span class="fee">View&nbsp;→</span>
        <span class="lab">details</span>
      @endif
    </div>
  </div>
</a>
