{{-- Refund Promise passport-stamp. Over-prints the top-right corner of a
     position:relative CTA/offer card. Parent must be position:relative.
     Optional: $stampClass to add modifiers (e.g. 'ondark'). --}}
<div class="rstamp {{ $stampClass ?? '' }}" aria-hidden="true">
  <span class="rdisc"></span><span class="ring"></span><span class="ring2"></span>
  <svg class="curve" viewBox="0 0 120 120"><defs><path id="rstampArc-{{ $sid ?? 'x' }}" d="M60,60 m-45,0 a45,45 0 1,1 90,0"/></defs>
    <text><textPath href="#rstampArc-{{ $sid ?? 'x' }}" startOffset="10%">BEYOND PASSPORTS &middot; COVERED &middot;</textPath></text></svg>
  <span class="core"><span class="b1">REFUND</span><span class="big">PROMISE</span><span class="b2">FEE BACK IF REFUSED</span></span>
</div>
