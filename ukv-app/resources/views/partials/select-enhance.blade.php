{{-- Site-wide native-<select> enhancer.
     Upgrades every public select into a custom listbox whose caret flips on open AND
     close, keeping the real <select> in the DOM (invisible/inert) as the source of truth
     so value reads, form submission, and required-field validation stay unchanged.

     Two modes:
       - default: a plain custom listbox (optgroups become group headers).
       - data-dest: the hero-style destination picker — type-to-search + region groups +
         flags, built from a shared region/flag map. Countries outside the Schengen map
         fall into an "Other destinations" group.
     Opt any select out entirely with data-native. --}}
<style>
  .uk-combo{position:relative;display:block}
  .uk-combo > select{position:absolute;inset:0;width:100%;height:100%;margin:0;padding:0;border:0;opacity:0;pointer-events:none;-webkit-appearance:none;appearance:none}
  .uk-combo-btn{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:10px;box-sizing:border-box;cursor:pointer;text-align:left;width:100%;overflow:hidden}
  .uk-combo-btn .uk-combo-val{display:flex;align-items:center;gap:8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .uk-combo-btn.is-placeholder .uk-combo-val{color:var(--muted,#697079)}
  .uk-combo-caret{flex:none;width:15px;height:15px;color:var(--muted,#697079);transition:transform .18s ease}
  .uk-combo.open .uk-combo-caret{transform:rotate(180deg)}
  .uk-combo.uk-invalid .uk-combo-btn{border-color:#b23b3b !important}
  .uk-combo-panel{position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:60;margin:0;padding:6px;
    background:#fff;border:1px solid var(--paper-edge,#e6ddcf);border-radius:12px;box-shadow:0 26px 50px -24px rgba(22,34,46,.55);
    max-height:300px;overflow-y:auto;overscroll-behavior:contain;font-size:15px}
  .uk-combo-panel[hidden]{display:none}
  .uk-combo-panel ul{list-style:none;margin:0;padding:0}
  .uk-combo-panel li[role=option]{padding:9px 12px;border-radius:8px;color:var(--ink,#243039);cursor:pointer;display:flex;align-items:center;gap:8px}
  .uk-combo-panel li[role=option]:hover,.uk-combo-panel li[role=option].active{background:rgba(21,94,122,.08)}
  .uk-combo-panel li[role=option][aria-selected="true"]{font-weight:700;color:var(--cta,#155E7A)}
  .uk-combo-panel .uk-combo-grp{font:800 10px var(--display,system-ui);letter-spacing:.12em;text-transform:uppercase;color:var(--muted,#697079);padding:10px 12px 4px}
  .uk-combo-panel .uk-combo-grp[hidden]{display:none}
  .uk-combo-panel .flag{width:20px;text-align:center;flex:none;font-size:15px;line-height:1}
  .uk-combo-search{position:sticky;top:-6px;background:#fff;padding:2px 2px 8px;margin:-6px -6px 4px;border-bottom:1px solid var(--paper-edge,#e6ddcf)}
  .uk-combo-search input{width:100%;box-sizing:border-box;padding:9px 11px;border:1px solid var(--paper-edge,#e6ddcf);border-radius:8px;font:inherit;font-size:14px;background:#fff;color:var(--ink,#243039)}
  .uk-combo-none{padding:10px 12px;color:var(--muted,#697079);font-size:14px}
  .uk-combo-none[hidden]{display:none}
</style>
<script>
(function () {
  var CARET = '<svg class="uk-combo-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
  var COPY = ['borderTopWidth','borderRightWidth','borderBottomWidth','borderLeftWidth','borderTopStyle','borderRightStyle','borderBottomStyle','borderLeftStyle','borderTopColor','borderRightColor','borderBottomColor','borderLeftColor','borderTopLeftRadius','borderTopRightRadius','borderBottomLeftRadius','borderBottomRightRadius','backgroundColor','color','fontFamily','fontSize','fontWeight','lineHeight','paddingTop','paddingBottom','paddingLeft','boxShadow'];

  // Shared Schengen region + flag map (mirrors home.blade.php). Countries not listed fall
  // into "Other destinations" with no flag.
  var REGION_ORDER = ['Western Europe','Southern Europe','Northern Europe','Central & Eastern Europe','Other destinations'];
  var REGION = {
    'France':'Western Europe','Germany':'Western Europe','Netherlands':'Western Europe','Austria':'Western Europe','Belgium':'Western Europe','Luxembourg':'Western Europe','Switzerland':'Western Europe','Liechtenstein':'Western Europe',
    'Spain':'Southern Europe','Italy':'Southern Europe','Portugal':'Southern Europe','Greece':'Southern Europe','Croatia':'Southern Europe','Malta':'Southern Europe','Slovenia':'Southern Europe',
    'Denmark':'Northern Europe','Sweden':'Northern Europe','Iceland':'Northern Europe','Norway':'Northern Europe','Finland':'Northern Europe','Estonia':'Northern Europe','Latvia':'Northern Europe','Lithuania':'Northern Europe',
    'Poland':'Central & Eastern Europe','Czechia':'Central & Eastern Europe','Hungary':'Central & Eastern Europe','Slovakia':'Central & Eastern Europe','Bulgaria':'Central & Eastern Europe','Romania':'Central & Eastern Europe'
  };
  var FLAG = {
    'France':'🇫🇷','Germany':'🇩🇪','Netherlands':'🇳🇱','Austria':'🇦🇹','Belgium':'🇧🇪','Luxembourg':'🇱🇺','Switzerland':'🇨🇭','Liechtenstein':'🇱🇮',
    'Spain':'🇪🇸','Italy':'🇮🇹','Portugal':'🇵🇹','Greece':'🇬🇷','Croatia':'🇭🇷','Malta':'🇲🇹','Slovenia':'🇸🇮',
    'Denmark':'🇩🇰','Sweden':'🇸🇪','Iceland':'🇮🇸','Norway':'🇳🇴','Finland':'🇫🇮','Estonia':'🇪🇪','Latvia':'🇱🇻','Lithuania':'🇱🇹',
    'Poland':'🇵🇱','Czechia':'🇨🇿','Hungary':'🇭🇺','Slovakia':'🇸🇰','Bulgaria':'🇧🇬','Romania':'🇷🇴'
  };
  var uid = 0;

  function enhance(sel) {
    if (sel.dataset.ukEnhanced || sel.multiple || sel.hasAttribute('data-native')) return;
    if (!sel.options || !sel.options.length) return;
    sel.dataset.ukEnhanced = '1';
    var isDest = sel.hasAttribute('data-dest');
    var cs = getComputedStyle(sel);
    var minH = sel.offsetHeight;

    var wrap = document.createElement('div');
    wrap.className = 'uk-combo';
    sel.parentNode.insertBefore(wrap, sel);
    wrap.appendChild(sel);
    sel.setAttribute('tabindex', '-1');
    sel.setAttribute('aria-hidden', 'true');

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'uk-combo-btn';
    COPY.forEach(function (p) { btn.style[p] = cs[p]; });
    btn.style.paddingRight = '38px';
    btn.style.minHeight = minH + 'px';
    var pid = 'uk-combo-panel-' + (++uid);
    btn.setAttribute('role', 'combobox');
    btn.setAttribute('aria-haspopup', 'listbox');
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-controls', pid);
    var lbl = sel.id && document.querySelector('label[for="' + sel.id + '"]');
    if (lbl) btn.setAttribute('aria-label', lbl.textContent.trim());
    else if (sel.getAttribute('aria-label')) btn.setAttribute('aria-label', sel.getAttribute('aria-label'));
    btn.innerHTML = '<span class="uk-combo-val"></span>' + CARET;
    var valEl = btn.querySelector('.uk-combo-val');
    wrap.appendChild(btn);

    var panel = document.createElement('div');
    panel.className = 'uk-combo-panel';
    panel.id = pid;
    panel.setAttribute('role', 'listbox');
    panel.hidden = true;

    var searchInput = null, noneEl = null;
    var ul = document.createElement('ul');
    var items = [];

    function optFlag(o) { return isDest ? (FLAG[o.textContent.trim()] || '·') : null; }
    function addOpt(o) {
      var li = document.createElement('li');
      li.setAttribute('role', 'option');
      li.dataset.idx = o.index;
      li.dataset.s = o.textContent.trim().toLowerCase();
      var flag = optFlag(o);
      li.innerHTML = (flag ? '<span class="flag" aria-hidden="true">' + flag + '</span>' : '') + '<span>' + o.textContent + '</span>';
      ul.appendChild(li);
      items.push(li);
    }
    function addGrp(label) {
      var h = document.createElement('li');
      h.className = 'uk-combo-grp'; h.setAttribute('aria-hidden', 'true');
      h.textContent = label; ul.appendChild(h);
    }

    if (isDest) {
      // Search box + region-grouped options (skip the empty-value placeholder option).
      var sw = document.createElement('div');
      sw.className = 'uk-combo-search';
      searchInput = document.createElement('input');
      searchInput.type = 'text'; searchInput.autocomplete = 'off';
      searchInput.placeholder = 'Search a country…'; searchInput.setAttribute('aria-label', 'Search destinations');
      sw.appendChild(searchInput); panel.appendChild(sw);

      var buckets = {}; REGION_ORDER.forEach(function (r) { buckets[r] = []; });
      Array.prototype.forEach.call(sel.options, function (o) {
        if (o.value === '') return; // placeholder
        var r = REGION[o.textContent.trim()] || 'Other destinations';
        buckets[r].push(o);
      });
      REGION_ORDER.forEach(function (r) {
        if (!buckets[r].length) return;
        addGrp(r);
        buckets[r].forEach(addOpt);
      });
      noneEl = document.createElement('div');
      noneEl.className = 'uk-combo-none'; noneEl.hidden = true; noneEl.textContent = 'No match.';
      panel.appendChild(ul); panel.appendChild(noneEl);
    } else {
      // Plain: options + optgroups in document order.
      Array.prototype.forEach.call(sel.children, function (node) {
        if (node.tagName === 'OPTGROUP') { addGrp(node.label); Array.prototype.forEach.call(node.children, function (o) { if (o.tagName === 'OPTION') addOpt(o); }); }
        else if (node.tagName === 'OPTION') { addOpt(node); }
      });
      panel.appendChild(ul);
    }
    wrap.appendChild(panel);

    var active = -1;
    function isOpen(){ return wrap.classList.contains('open'); }
    function open(){ wrap.classList.add('open'); panel.hidden = false; btn.setAttribute('aria-expanded', 'true'); if (isDest && searchInput){ filter(); searchInput.focus(); } else { setActive(currentIdxItem()); } }
    function close(){ wrap.classList.remove('open'); panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
    function currentIdxItem(){ for (var k = 0; k < items.length; k++){ if (+items[k].dataset.idx === sel.selectedIndex) return k; } return items.length ? 0 : -1; }
    function visibleItems(){ return items.filter(function (it){ return !it.hidden; }); }
    function setActive(k){
      items.forEach(function (it){ it.classList.remove('active'); });
      var vis = visibleItems(); active = k;
      if (k >= 0 && k < vis.length){ vis[k].classList.add('active'); vis[k].scrollIntoView({block:'nearest'}); }
    }
    function filter(){
      if (!isDest) return;
      var q = searchInput.value.trim().toLowerCase(), shown = 0;
      items.forEach(function (it){ var m = it.dataset.s.indexOf(q) !== -1; it.hidden = !m; if (m) shown++; });
      Array.prototype.forEach.call(ul.querySelectorAll('.uk-combo-grp'), function (g){
        var any = false, n = g.nextElementSibling;
        while (n && !n.classList.contains('uk-combo-grp')){ if (n.getAttribute('role') === 'option' && !n.hidden){ any = true; break; } n = n.nextElementSibling; }
        g.hidden = !any;
      });
      if (noneEl) noneEl.hidden = shown > 0;
      setActive(shown > 0 ? 0 : -1);
    }
    function sync(){
      var o = sel.options[sel.selectedIndex] || sel.options[0];
      var name = o ? o.textContent : '';
      var isPlaceholder = o && o.value === '';
      var flag = (isDest && !isPlaceholder) ? (FLAG[name.trim()] || '·') : null;
      valEl.innerHTML = (flag ? '<span class="flag" aria-hidden="true">' + flag + '</span>' : '') + '<span>' + name + '</span>';
      btn.classList.toggle('is-placeholder', !!isPlaceholder);
      items.forEach(function (it){ it.setAttribute('aria-selected', (+it.dataset.idx === sel.selectedIndex) ? 'true' : 'false'); });
    }
    function choose(li){
      sel.selectedIndex = +li.dataset.idx;
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      sel.dispatchEvent(new Event('input', { bubbles: true }));
      wrap.classList.remove('uk-invalid');
      sync(); close(); btn.focus();
    }
    function onKey(e){
      if (e.key === 'ArrowDown'){ e.preventDefault(); if (!isOpen()){ open(); } else { setActive(Math.min(active + 1, visibleItems().length - 1)); } }
      else if (e.key === 'ArrowUp'){ e.preventDefault(); if (isOpen()) setActive(Math.max(active - 1, 0)); }
      else if (e.key === 'Enter'){ var vis = visibleItems(); if (isOpen() && active >= 0 && vis[active]){ e.preventDefault(); choose(vis[active]); } else if (!isOpen()){ e.preventDefault(); open(); } }
      else if (e.key === ' ' && !isDest){ if (!isOpen()){ e.preventDefault(); open(); } }
      else if (e.key === 'Escape'){ close(); }
    }

    btn.addEventListener('click', function(){ isOpen() ? close() : open(); });
    panel.addEventListener('mousedown', function(e){ var li = e.target.closest('li[role=option]'); if (li){ e.preventDefault(); choose(li); } });
    btn.addEventListener('keydown', onKey);
    if (searchInput){ searchInput.addEventListener('input', filter); searchInput.addEventListener('keydown', onKey); }
    document.addEventListener('click', function(e){ if (!wrap.contains(e.target)) close(); });
    sel.addEventListener('change', sync);
    sel.addEventListener('invalid', function(){ wrap.classList.add('uk-invalid'); });

    sync();
  }

  function run(){ Array.prototype.forEach.call(document.querySelectorAll('select:not([data-native])'), enhance); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
  else run();
})();
</script>
