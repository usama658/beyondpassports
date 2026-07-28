{{-- UK flag (single). Params: size (height px, default 16), gap (px, default 5).
     Paired with the "Registered in England and Wales" badge, so it shows only the
     UK flag — the EU flag was removed (we are not registered in the EU).
     Self-contained inline SVG with explicit inline width/height so host chip CSS
     (e.g. `.ti svg{width:20px}`) cannot squish it. Partial name kept for compatibility. --}}
@php
    $h   = (int) ($size ?? 16);
    $w   = (int) round($h * 1.5);
    $gap = (int) ($gap ?? 5);
    $svgStyle = "width:{$w}px;height:{$h}px;border-radius:3px;box-shadow:0 1px 2px rgba(0,0,0,.22);display:block;flex:none";
@endphp
<span class="bp-flags" style="display:inline-flex;align-items:center;gap:{{ $gap }}px;vertical-align:middle;flex:none">
<svg viewBox="0 0 60 40" width="{{ $w }}" height="{{ $h }}" style="{{ $svgStyle }}" role="img" aria-label="United Kingdom"><rect width="60" height="40" fill="#012169"/><path d="M0 0 60 40M60 0 0 40" stroke="#fff" stroke-width="8"/><path d="M0 0 60 40" stroke="#C8102E" stroke-width="4"/><path d="M60 0 0 40" stroke="#C8102E" stroke-width="4"/><path d="M30 0V40M0 20H60" stroke="#fff" stroke-width="12"/><path d="M30 0V40M0 20H60" stroke="#C8102E" stroke-width="7"/></svg>
</span>
