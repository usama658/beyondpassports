{{-- Site-wide floating WhatsApp button. Chat = the universal capture channel. --}}
<div class="wa-float" data-wa-float>
    @include('partials.wa-cta', [
        'message' => "Hi Beyond Passports — I'd like some help with my trip.",
        'label' => 'Chat to a real UK person',
        'variant' => 'floating',
    ])
</div>
@once
<style>
  .wa-float{position:fixed;right:18px;bottom:18px;z-index:35}
  /* Mobile: collapse to a circular FAB (glyph only). */
  @media (max-width:620px){
    .wa-float{right:14px;bottom:14px}
    .wa-float .wa-cta{padding:14px;border-radius:50%}
    .wa-float .wa-cta__label{display:none}
  }
</style>
@endonce
