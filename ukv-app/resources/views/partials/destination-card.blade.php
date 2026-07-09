{{-- Reusable boarding-pass destination card. Expects $destination. Uses global .pass/.dests
     classes (ukv.css). Price slot honours config('ukv.show_prices'); falls back to max-stay
     then "View". Shared by the destinations index and the Schengen hub. --}}
@php $destWa = 'https://wa.me/'.config('ukv.whatsapp').'?text='.rawurlencode("Hi, I'd like help with my Schengen visa for {$destination->name}. "); @endphp
<a class="pass reveal" href="{{ $destWa }}" target="_blank" rel="noopener" data-name="{{ strtolower($destination->name) }}" data-region="{{ $destination->region ?? '' }}" aria-label="Ask about a {{ $destination->name }} Schengen visa on WhatsApp" style="text-decoration:none;color:inherit">
  <div class="sky">@if ($destination->image_path)<img src="{{ asset(ltrim($destination->image_path, '/')) }}" alt="{{ $destination->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $destination->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div>
  <div class="lower">
    <div class="main">
      <span class="k">{{ $destination->visa_type ? strtoupper($destination->visa_type) : 'Visa' }}</span>
      <h3>{{ $destination->name }}</h3>
      <p class="t">Prepared &amp; checked by our UK team</p>
    </div>
    <div class="stub">
      <div class="fee">Chat&nbsp;→</div>
      <div class="lab">WhatsApp</div>
    </div>
  </div>
</a>
