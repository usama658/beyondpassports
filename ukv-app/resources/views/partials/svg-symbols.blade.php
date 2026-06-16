{{-- Beyond Passports inline SVG symbol library — ported from frontend/assets/ukv-illustrations.js.
     Renders the full document-world artwork: route map, passport stamp, monogram avatar,
     and the seven destination skylines. Hidden <defs> only; referenced via <use href="#...">.
     Self-contained so public pages render fully without the front host's JS sprite.

     ukv-skyline is an ALIAS duplicating sky-generic's geometry, kept so existing views
     (layout, destinations, home, tools, about, driving-abroad) that reference #ukv-skyline
     and #ukv-stamp keep working. Static partial — no JS. --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">

  {{-- hero route map: dotted arc + plane between two pins --}}
  <symbol id="ukv-route" viewBox="0 0 400 200">
    <g fill="none" stroke="#C8A24A" stroke-width="2" stroke-dasharray="2 7" stroke-linecap="round">
      <path d="M40 150 Q200 10 360 110"/>
    </g>
    <circle cx="40" cy="150" r="6" fill="#1456B8"/>
    <circle cx="40" cy="150" r="11" fill="none" stroke="#1456B8" stroke-opacity=".35"/>
    <circle cx="360" cy="110" r="6" fill="#0E6E6E"/>
    <circle cx="360" cy="110" r="11" fill="none" stroke="#0E6E6E" stroke-opacity=".4"/>
    <path d="M196 64 l22 6 -6 -16 6 -4 12 18 14 0 -8 10 8 10 -14 0 -12 18 -6 -4 6 -16z" fill="#EEF2F4" transform="rotate(34 205 70)"/>
  </symbol>

  {{-- passport "approved" stamp (used for ticks) --}}
  <symbol id="ukv-stamp" viewBox="0 0 48 48">
    <g transform="rotate(-8 24 24)" fill="none" stroke="#0E6E6E" stroke-width="2">
      <circle cx="24" cy="24" r="20"/>
      <circle cx="24" cy="24" r="15" stroke-dasharray="2 3"/>
      <path d="M16 24 l5 6 11 -13" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
    </g>
  </symbol>

  {{-- avatar monogram (testimonial) --}}
  <symbol id="ukv-monogram" viewBox="0 0 44 44">
    <rect width="44" height="44" fill="#0A2540"/>
    <text x="22" y="29" text-anchor="middle" font-family="Space Mono,monospace" font-size="16" fill="#C8A24A">UK</text>
  </symbol>

  {{-- skylines (silhouettes on a navy band; viewBox 240x96, baseline y=96) --}}

  <symbol id="sky-turkey" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#1c3a55">
      <path d="M0 96 V70 h20 v26z M30 70 a14 14 0 0 1 28 0 v26 h-28z M52 70 v-18 M70 96 V60 h16 v36z M96 96 a18 18 0 0 1 36 0z M120 50 v10 M150 96 V66 h22 v30z M182 96 a16 16 0 0 1 32 0z M198 56 v10 M224 96 V72 h16 v24z"/>
    </g>
  </symbol>

  <symbol id="sky-egypt" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#1c3a55">
      <path d="M0 96 L40 40 L80 96z M70 96 L110 52 L150 96z M150 96 h30 v-20 h16 v20 h30z"/>
      <circle cx="205" cy="34" r="12"/>
    </g>
  </symbol>

  <symbol id="sky-india" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#1c3a55">
      <path d="M120 96 V64 a20 20 0 0 1 40 0 V96z M134 44 q6 -14 6 -14 q0 0 6 14z"/>
      <rect x="92" y="70" width="10" height="26"/>
      <rect x="178" y="70" width="10" height="26"/>
      <path d="M0 96 V78 h26 v18z M214 96 V78 h26 v18z"/>
    </g>
  </symbol>

  <symbol id="sky-usa" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#1c3a55">
      <path d="M0 96 V46 h18 v50z M22 96 V62 h16 v34z M44 96 V36 h20 v60z M70 96 V56 h14 v40z M150 96 V40 h22 v56z M178 96 V58 h16 v38z M200 96 V48 h18 v48z M222 96 V64 h18 v32z"/>
      <g transform="translate(104 30)">
        <rect x="-3" y="14" width="14" height="52"/>
        <circle cx="4" cy="8" r="8"/>
        <path d="M4 0 l3 6 h-6z M-4 4 l3 4 M12 4 l-3 4"/>
      </g>
    </g>
  </symbol>

  <symbol id="sky-australia" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#1c3a55">
      <path d="M0 96 V72 h18 v24z M210 96 V66 h30 v30z"/>
      <g fill="#EEF2F4">
        <path d="M70 96 q8 -40 26 -40 q-6 14 0 40z"/>
        <path d="M96 96 q10 -46 30 -46 q-8 16 0 46z"/>
        <path d="M126 96 q12 -52 34 -52 q-10 18 0 52z"/>
      </g>
      <path d="M60 96 h120" stroke="#EEF2F4" stroke-width="3"/>
    </g>
  </symbol>

  <symbol id="sky-thailand" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#1c3a55">
      <path d="M118 96 L140 28 L162 96z M134 22 q6 -12 6 -12 q0 0 6 12z M86 96 L104 50 L122 96z M158 96 L176 50 L194 96z M0 96 V74 h22 v22z M218 96 V74 h22 v22z"/>
    </g>
  </symbol>

  <symbol id="sky-generic" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#1c3a55">
      <path d="M0 96 V58 h20 v38z M26 96 V44 h18 v52z M50 96 V64 h16 v32z M150 96 V50 h20 v46z M176 96 V62 h16 v34z M198 96 V46 h22 v50z M226 96 V66 h14 v30z"/>
      <circle cx="110" cy="36" r="14" fill="none" stroke="#C8A24A" stroke-width="2"/>
    </g>
  </symbol>

  {{-- ukv-skyline: alias of sky-generic, kept for existing #ukv-skyline references --}}
  <symbol id="ukv-skyline" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#1c3a55">
      <path d="M0 96 V58 h20 v38z M26 96 V44 h18 v52z M50 96 V64 h16 v32z M150 96 V50 h20 v46z M176 96 V62 h16 v34z M198 96 V46 h22 v50z M226 96 V66 h14 v30z"/>
      <circle cx="110" cy="36" r="14" fill="none" stroke="#C8A24A" stroke-width="2"/>
    </g>
  </symbol>

</svg>
