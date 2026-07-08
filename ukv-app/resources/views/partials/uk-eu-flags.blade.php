{{-- UK + EU flag pair. Params: size (height px, default 16), gap (px, default 5).
     Self-contained inline SVG with explicit inline width/height so host chip CSS
     (e.g. `.ti svg{width:20px}`) cannot squish them. Unique star id per render. --}}
@php
    $h   = (int) ($size ?? 16);
    $w   = (int) round($h * 1.5);
    $gap = (int) ($gap ?? 5);
    $uid = 'euf'.substr(md5(uniqid('', true)), 0, 8);
    $svgStyle = "width:{$w}px;height:{$h}px;border-radius:3px;box-shadow:0 1px 2px rgba(0,0,0,.22);display:block;flex:none";
@endphp
<span class="bp-flags" style="display:inline-flex;align-items:center;gap:{{ $gap }}px;vertical-align:middle;flex:none">
<svg viewBox="0 0 60 40" width="{{ $w }}" height="{{ $h }}" style="{{ $svgStyle }}" role="img" aria-label="United Kingdom"><rect width="60" height="40" fill="#012169"/><path d="M0 0 60 40M60 0 0 40" stroke="#fff" stroke-width="8"/><path d="M0 0 60 40" stroke="#C8102E" stroke-width="4"/><path d="M60 0 0 40" stroke="#C8102E" stroke-width="4"/><path d="M30 0V40M0 20H60" stroke="#fff" stroke-width="12"/><path d="M30 0V40M0 20H60" stroke="#C8102E" stroke-width="7"/></svg>
<svg viewBox="0 0 60 40" width="{{ $w }}" height="{{ $h }}" style="{{ $svgStyle }}" role="img" aria-label="European Union"><rect width="60" height="40" fill="#003399"/><g fill="#FFCC00"><polygon id="{{ $uid }}" points="0,-2.6 0.588,-0.809 2.473,-0.803 0.951,0.309 1.528,2.104 0,1 -1.528,2.104 -0.951,0.309 -2.473,-0.803 -0.588,-0.809" transform="translate(30,6.5)"/><use href="#{{ $uid }}" transform="translate(6.75,1.81)"/><use href="#{{ $uid }}" transform="translate(11.7,6.75)"/><use href="#{{ $uid }}" transform="translate(13.5,13.5)"/><use href="#{{ $uid }}" transform="translate(11.7,20.25)"/><use href="#{{ $uid }}" transform="translate(6.75,25.19)"/><use href="#{{ $uid }}" transform="translate(0,27)"/><use href="#{{ $uid }}" transform="translate(-6.75,25.19)"/><use href="#{{ $uid }}" transform="translate(-11.7,20.25)"/><use href="#{{ $uid }}" transform="translate(-13.5,13.5)"/><use href="#{{ $uid }}" transform="translate(-11.7,6.75)"/><use href="#{{ $uid }}" transform="translate(-6.75,1.81)"/></g></svg>
</span>
