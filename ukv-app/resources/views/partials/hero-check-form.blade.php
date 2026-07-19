{{-- Shared hero visa-check form: destination combobox + passport picker + "See what I need"
     WhatsApp CTA. Same fields as the home hero. Self-contained (own data, @once CSS + JS).

     Options:
       'stack' => false (default) — horizontal bar. true — stacked (narrow columns).
       'bare'  => false (default) — own white card chrome. true — transparent (sits in a card).

     NOTE: the home hero (public/home.blade.php) still renders this markup inline. Keep the two
     in sync, or refactor home to @include this partial. --}}
@php
  $stack = $stack ?? false;
  $bare  = $bare  ?? false;
  $hcfDests = \App\Models\Destination::query()->where('visa_type', 'Schengen')->orderBy('name')->get();
  $regionOrder = ['Western Europe', 'Southern Europe', 'Northern Europe', 'Central & Eastern Europe'];
  $regionOf = [
    'France'=>'Western Europe','Germany'=>'Western Europe','Netherlands'=>'Western Europe','Austria'=>'Western Europe','Belgium'=>'Western Europe','Luxembourg'=>'Western Europe','Switzerland'=>'Western Europe','Liechtenstein'=>'Western Europe',
    'Spain'=>'Southern Europe','Italy'=>'Southern Europe','Portugal'=>'Southern Europe','Greece'=>'Southern Europe','Croatia'=>'Southern Europe','Malta'=>'Southern Europe','Slovenia'=>'Southern Europe',
    'Denmark'=>'Northern Europe','Sweden'=>'Northern Europe','Iceland'=>'Northern Europe','Norway'=>'Northern Europe','Finland'=>'Northern Europe','Estonia'=>'Northern Europe','Latvia'=>'Northern Europe','Lithuania'=>'Northern Europe',
    'Poland'=>'Central & Eastern Europe','Czechia'=>'Central & Eastern Europe','Hungary'=>'Central & Eastern Europe','Slovakia'=>'Central & Eastern Europe','Bulgaria'=>'Central & Eastern Europe','Romania'=>'Central & Eastern Europe',
  ];
  $flagOf = [
    'France'=>'🇫🇷','Germany'=>'🇩🇪','Netherlands'=>'🇳🇱','Austria'=>'🇦🇹','Belgium'=>'🇧🇪','Luxembourg'=>'🇱🇺','Switzerland'=>'🇨🇭','Liechtenstein'=>'🇱🇮',
    'Spain'=>'🇪🇸','Italy'=>'🇮🇹','Portugal'=>'🇵🇹','Greece'=>'🇬🇷','Croatia'=>'🇭🇷','Malta'=>'🇲🇹','Slovenia'=>'🇸🇮',
    'Denmark'=>'🇩🇰','Sweden'=>'🇸🇪','Iceland'=>'🇮🇸','Norway'=>'🇳🇴','Finland'=>'🇫🇮','Estonia'=>'🇪🇪','Latvia'=>'🇱🇻','Lithuania'=>'🇱🇹',
    'Poland'=>'🇵🇱','Czechia'=>'🇨🇿','Hungary'=>'🇭🇺','Slovakia'=>'🇸🇰','Bulgaria'=>'🇧🇬','Romania'=>'🇷🇴',
  ];
  $groupedDests = [];
  foreach ($hcfDests as $d) { $groupedDests[$regionOf[$d->name] ?? 'Other Schengen'][] = $d; }
  $orderedGroups = [];
  foreach ($regionOrder as $r) { if (!empty($groupedDests[$r])) { $orderedGroups[$r] = $groupedDests[$r]; } }
  foreach ($groupedDests as $r => $list) { if (!isset($orderedGroups[$r])) { $orderedGroups[$r] = $list; } }
@endphp
<form class="hp-bar{{ $stack ? ' hp-bar--stack' : '' }}{{ $bare ? ' hp-bar--bare' : '' }}" onsubmit="return false">
  <div class="f hp-combo" id="hpc">
    <label for="dest">Where are you going?</label>
    <input id="dest" type="text" autocomplete="off" placeholder="Search a country, or all of Schengen"
           role="combobox" aria-expanded="false" aria-controls="hp-destlist" aria-autocomplete="list">
    <button type="button" class="hpc-caret" tabindex="-1" aria-label="Show destination list">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <ul class="hpc-panel" id="hp-destlist" role="listbox" aria-label="Schengen destinations" hidden>
      <li class="hpc-all" role="option" data-v="Anywhere in the Schengen Area" data-s="anywhere schengen area all everywhere"><span class="flag" aria-hidden="true">🇪🇺</span>Anywhere in the Schengen Area</li>
      @foreach ($orderedGroups as $region => $list)
        <li class="hpc-grp" aria-hidden="true">{{ $region }}</li>
        @foreach ($list as $d)
          <li role="option" data-v="{{ $d->name }}" data-s="{{ strtolower($d->name) }}"><span class="flag" aria-hidden="true">{{ $flagOf[$d->name] ?? '·' }}</span>{{ $d->name }}</li>
        @endforeach
      @endforeach
      <li class="hpc-none" aria-hidden="true" hidden>No match — we cover all of Schengen.</li>
    </ul>
  </div>
  <div class="f hp-combo hp-sel" id="npc">
    <label id="nat-label">Your passport</label>
    <div class="hp-selbtn" id="nat-btn" role="combobox" tabindex="0" aria-haspopup="listbox" aria-expanded="false" aria-controls="nat-list" aria-labelledby="nat-label nat-text">
      <span id="nat-text">United Kingdom</span>
    </div>
    <button type="button" class="hpc-caret" tabindex="-1" aria-label="Show passport options">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <input type="hidden" id="nat" value="a UK">
    <ul class="hpc-panel" id="nat-list" role="listbox" aria-label="Your passport" hidden>
      <li role="option" data-v="a UK" data-label="United Kingdom" aria-selected="true">United Kingdom</li>
      <li role="option" data-v="a non-UK" data-label="Other (we'll confirm your rules)" aria-selected="false">Other (we'll confirm your rules)</li>
    </ul>
  </div>
  <button class="btn" type="button" id="hp-chat">See what I need →</button>
</form>
@once
<style>
  .hp-bar{display:flex;gap:12px;align-items:flex-end;background:#fff;border:1px solid var(--paper-edge);border-radius:18px;
    box-shadow:0 30px 64px -30px rgba(40,50,70,.45);padding:18px;text-align:left;position:relative}
  .hp-bar--bare{background:transparent;border:0;box-shadow:none;padding:0;border-radius:0}
  .hp-bar--stack{flex-direction:column;align-items:stretch;gap:13px}
  .hp-bar .f{flex:1;min-width:0}
  .hp-bar label{display:block;font:700 12px var(--display);margin:0 0 5px;color:var(--ink)}
  .hp-bar select,.hp-bar input{width:100%;box-sizing:border-box;padding:12px;border:1px solid var(--paper-edge);border-radius:11px;font:inherit;font-size:15px;background:#fff;color:var(--ink)}
  .hp-bar .hp-selbtn{width:100%;box-sizing:border-box;padding:12px;padding-right:38px;min-height:45px;
    border:1px solid var(--paper-edge);border-radius:11px;font:inherit;font-size:15px;background:#fff;color:var(--ink);
    cursor:pointer;display:flex;align-items:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .hp-bar .hp-selbtn:focus-visible{outline:2px solid var(--cta);outline-offset:1px}
  .hp-sel .hpc-panel li[role=option][aria-selected="true"]{font-weight:700;color:var(--cta)}
  .hp-bar input[readonly]{background:var(--paper);color:var(--muted);cursor:default}
  .hp-bar .btn{white-space:nowrap}
  .hp-bar--stack .btn{width:100%}
  .hp-combo{position:relative}
  .hp-combo input{padding-right:40px}
  .hp-combo .hpc-caret{position:absolute;right:6px;bottom:6px;width:32px;height:34px;display:flex;align-items:center;justify-content:center;padding:0;border:0;background:transparent;color:var(--muted);cursor:pointer;border-radius:8px}
  .hp-combo .hpc-caret svg{width:15px;height:15px;transition:transform .18s ease}
  .hp-combo.open .hpc-caret svg{transform:rotate(180deg)}
  .hp-combo .hpc-caret:hover{background:var(--paper);color:var(--ink)}
  .hpc-panel{position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:50;margin:0;padding:6px;list-style:none;
    background:#fff;border:1px solid var(--paper-edge);border-radius:12px;box-shadow:0 26px 50px -24px rgba(22,34,46,.55);
    max-height:300px;overflow-y:auto;overscroll-behavior:contain}
  .hpc-panel[hidden]{display:none}
  .hpc-panel li[role=option]{padding:9px 12px;border-radius:8px;font:600 14.5px var(--display);color:var(--ink);cursor:pointer;display:flex;align-items:center;gap:10px}
  .hpc-panel li[role=option][hidden]{display:none}
  .hpc-panel li[role=option]:hover,.hpc-panel li[role=option].active{background:rgba(21,94,122,.08)}
  .hpc-panel .hpc-all{font-weight:700;color:var(--cta)}
  .hpc-panel .flag{width:20px;text-align:center;flex:none;font-size:15px;line-height:1}
  .hpc-panel .hpc-grp{font:800 10px var(--display);letter-spacing:.12em;text-transform:uppercase;color:var(--muted);padding:10px 12px 4px}
  .hpc-panel .hpc-grp[hidden]{display:none}
  .hpc-panel .hpc-none{padding:10px 12px;color:var(--muted);font-size:14px}
  .hpc-panel .hpc-none[hidden]{display:none}
</style>
@endonce
@once
<script>
  // Destination combobox — click to drop the grouped list, or type to filter.
  (function () {
    var combo = document.getElementById('hpc');
    if (!combo) return;
    var input = combo.querySelector('input'),
        panel = combo.querySelector('.hpc-panel'),
        caret = combo.querySelector('.hpc-caret'),
        opts  = Array.prototype.slice.call(panel.querySelectorAll('li[role=option]')),
        grps  = Array.prototype.slice.call(panel.querySelectorAll('.hpc-grp')),
        none  = panel.querySelector('.hpc-none'),
        active = -1;
    function isOpen(){ return combo.classList.contains('open'); }
    function open(){ combo.classList.add('open'); panel.hidden = false; input.setAttribute('aria-expanded','true'); }
    function close(){ combo.classList.remove('open'); panel.hidden = true; input.setAttribute('aria-expanded','false'); setActive(-1); }
    function visible(){ return opts.filter(function(o){ return !o.hidden; }); }
    function setActive(i){
      opts.forEach(function(o){ o.classList.remove('active'); });
      var vis = visible(); active = i;
      if (i >= 0 && i < vis.length){ vis[i].classList.add('active'); vis[i].scrollIntoView({block:'nearest'}); }
    }
    function filter(){
      var q = input.value.trim().toLowerCase(), shown = 0;
      opts.forEach(function(o){ var m = o.dataset.s.indexOf(q) !== -1; o.hidden = !m; if (m) shown++; });
      grps.forEach(function(g){
        var any = false, n = g.nextElementSibling;
        while (n && !n.classList.contains('hpc-grp')){ if (n.getAttribute('role') === 'option' && !n.hidden){ any = true; break; } n = n.nextElementSibling; }
        g.hidden = !any;
      });
      none.hidden = shown > 0;
      setActive(shown > 0 ? 0 : -1);
    }
    function choose(o){ input.value = o.dataset.v; close(); input.focus(); }
    input.addEventListener('focus', function(){ filter(); open(); });
    input.addEventListener('input', function(){ filter(); open(); });
    caret.addEventListener('mousedown', function(e){ e.preventDefault(); if (isOpen()){ close(); } else { filter(); open(); input.focus(); } });
    panel.addEventListener('mousedown', function(e){ var o = e.target.closest('li[role=option]'); if (o){ e.preventDefault(); choose(o); } });
    input.addEventListener('keydown', function(e){
      if (e.key === 'ArrowDown'){ e.preventDefault(); if (!isOpen()){ filter(); open(); } else { setActive(Math.min(active + 1, visible().length - 1)); } }
      else if (e.key === 'ArrowUp'){ e.preventDefault(); if (isOpen()){ setActive(Math.max(active - 1, 0)); } }
      else if (e.key === 'Enter'){ var vis = visible(); if (isOpen() && active >= 0 && vis[active]){ e.preventDefault(); choose(vis[active]); } }
      else if (e.key === 'Escape'){ close(); }
    });
    document.addEventListener('click', function(e){ if (!combo.contains(e.target)) close(); });
  })();
  // Passport picker (custom listbox so the caret flips on open AND close).
  (function () {
    var combo = document.getElementById('npc');
    if (!combo) return;
    var btn   = document.getElementById('nat-btn'),
        caret = combo.querySelector('.hpc-caret'),
        panel = combo.querySelector('.hpc-panel'),
        hidden= document.getElementById('nat'),
        text  = document.getElementById('nat-text'),
        opts  = Array.prototype.slice.call(panel.querySelectorAll('li[role=option]'));
    function isOpen(){ return combo.classList.contains('open'); }
    function open(){ combo.classList.add('open'); panel.hidden = false; btn.setAttribute('aria-expanded','true'); }
    function close(){ combo.classList.remove('open'); panel.hidden = true; btn.setAttribute('aria-expanded','false'); }
    function choose(o){
      hidden.value = o.dataset.v; text.textContent = o.dataset.label;
      opts.forEach(function(x){ x.setAttribute('aria-selected', x === o ? 'true' : 'false'); });
      close(); btn.focus();
    }
    btn.addEventListener('click', function(){ isOpen() ? close() : open(); });
    caret.addEventListener('mousedown', function(e){ e.preventDefault(); isOpen() ? close() : open(); });
    panel.addEventListener('mousedown', function(e){ var o = e.target.closest('li[role=option]'); if (o){ e.preventDefault(); choose(o); } });
    btn.addEventListener('keydown', function(e){
      if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' '){ e.preventDefault(); if (!isOpen()) open(); }
      else if (e.key === 'Escape'){ close(); }
    });
    document.addEventListener('click', function(e){ if (!combo.contains(e.target)) close(); });
  })();
  // "See what I need" → open a WhatsApp chat with the trip pre-filled.
  (function () {
    var WA = @json(config('ukv.whatsapp') ?: '447882747584');
    var btn = document.getElementById('hp-chat');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var nat = document.getElementById('nat');
      var pass = nat && nat.value ? nat.value : 'a UK';
      var destEl = document.getElementById('dest');
      var raw = destEl && destEl.value.trim() ? destEl.value.trim() : '';
      var dest = (!raw || /^anywhere/i.test(raw)) ? 'the Schengen Area' : raw;
      var msg = 'Hi Beyond Passports, I am applying for a Schengen visa on ' + pass + ' passport for ' + dest + '. What do I need?';
      window.open('https://wa.me/' + WA + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
    });
  })();
</script>
@endonce
