{{-- Site-wide native-<select> enhancer.
     Upgrades every public select into a custom listbox whose caret flips on open AND
     close, while keeping the real <select> in the DOM as the source of truth so form
     submission, `getElementById(id).value` reads, and required-field validation all keep
     working untouched. Opt a select out with `data-native`. No dynamic-option pages exist,
     so the panel is built once per select. --}}
<style>
  .uk-combo{position:relative;display:block}
  /* keep the native select for value + submit + validation, but invisible & inert */
  .uk-combo > select{position:absolute;inset:0;width:100%;height:100%;margin:0;padding:0;border:0;opacity:0;pointer-events:none;-webkit-appearance:none;appearance:none}
  .uk-combo-btn{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:10px;box-sizing:border-box;cursor:pointer;text-align:left;width:100%;overflow:hidden}
  .uk-combo-btn .uk-combo-val{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .uk-combo-btn.is-placeholder .uk-combo-val{color:var(--muted,#697079)}
  .uk-combo-caret{flex:none;width:15px;height:15px;color:var(--muted,#697079);transition:transform .18s ease}
  .uk-combo.open .uk-combo-caret{transform:rotate(180deg)}
  .uk-combo.uk-invalid .uk-combo-btn{border-color:#b23b3b !important}
  .uk-combo-panel{position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:60;margin:0;padding:6px;list-style:none;
    background:#fff;border:1px solid var(--paper-edge,#e6ddcf);border-radius:12px;box-shadow:0 26px 50px -24px rgba(22,34,46,.55);
    max-height:300px;overflow-y:auto;overscroll-behavior:contain;font-size:15px}
  .uk-combo-panel[hidden]{display:none}
  .uk-combo-panel li[role=option]{padding:9px 12px;border-radius:8px;color:var(--ink,#243039);cursor:pointer;display:flex;align-items:center;gap:8px}
  .uk-combo-panel li[role=option]:hover,.uk-combo-panel li[role=option].active{background:rgba(21,94,122,.08)}
  .uk-combo-panel li[role=option][aria-selected="true"]{font-weight:700;color:var(--cta,#155E7A)}
  .uk-combo-panel .uk-combo-grp{font:800 10px var(--display,system-ui);letter-spacing:.12em;text-transform:uppercase;color:var(--muted,#697079);padding:10px 12px 4px}
</style>
<script>
(function () {
  var CARET = '<svg class="uk-combo-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
  // Visual box properties copied from the original <select> onto the button, so the
  // enhanced control looks identical to however that select was styled on its page.
  var COPY = ['borderTopWidth','borderRightWidth','borderBottomWidth','borderLeftWidth','borderTopStyle','borderRightStyle','borderBottomStyle','borderLeftStyle','borderTopColor','borderRightColor','borderBottomColor','borderLeftColor','borderTopLeftRadius','borderTopRightRadius','borderBottomLeftRadius','borderBottomRightRadius','backgroundColor','color','fontFamily','fontSize','fontWeight','lineHeight','paddingTop','paddingBottom','paddingLeft','boxShadow'];
  var uid = 0;

  function enhance(sel) {
    if (sel.dataset.ukEnhanced || sel.multiple || sel.hasAttribute('data-native')) return;
    if (!sel.options || !sel.options.length) return;
    sel.dataset.ukEnhanced = '1';
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

    var panel = document.createElement('ul');
    panel.className = 'uk-combo-panel';
    panel.id = pid;
    panel.setAttribute('role', 'listbox');
    panel.hidden = true;
    // Build items from options + optgroups, mapping each back to its option index.
    var items = [];
    Array.prototype.forEach.call(sel.children, function (node) {
      if (node.tagName === 'OPTGROUP') {
        var h = document.createElement('li');
        h.className = 'uk-combo-grp'; h.setAttribute('aria-hidden', 'true');
        h.textContent = node.label; panel.appendChild(h);
        Array.prototype.forEach.call(node.children, function (o) { addOpt(o); });
      } else if (node.tagName === 'OPTION') {
        addOpt(node);
      }
    });
    function addOpt(o) {
      var li = document.createElement('li');
      li.setAttribute('role', 'option');
      li.dataset.idx = o.index;
      li.textContent = o.textContent;
      panel.appendChild(li);
      items.push(li);
    }
    wrap.appendChild(panel);

    var active = -1;
    function isOpen(){ return wrap.classList.contains('open'); }
    function open(){ wrap.classList.add('open'); panel.hidden = false; btn.setAttribute('aria-expanded', 'true'); setActive(currentIdxItem()); }
    function close(){ wrap.classList.remove('open'); panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
    function currentIdxItem(){ for (var k = 0; k < items.length; k++){ if (+items[k].dataset.idx === sel.selectedIndex) return k; } return 0; }
    function setActive(k){
      items.forEach(function (it){ it.classList.remove('active'); });
      active = k;
      if (k >= 0 && k < items.length){ items[k].classList.add('active'); items[k].scrollIntoView({block:'nearest'}); }
    }
    function sync(){
      var o = sel.options[sel.selectedIndex] || sel.options[0];
      valEl.textContent = o ? o.textContent : '';
      var isPlaceholder = o && o.value === '';
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

    btn.addEventListener('click', function(){ isOpen() ? close() : open(); });
    panel.addEventListener('mousedown', function(e){ var li = e.target.closest('li[role=option]'); if (li){ e.preventDefault(); choose(li); } });
    btn.addEventListener('keydown', function(e){
      if (e.key === 'ArrowDown'){ e.preventDefault(); if (!isOpen()) open(); else setActive(Math.min(active + 1, items.length - 1)); }
      else if (e.key === 'ArrowUp'){ e.preventDefault(); if (isOpen()) setActive(Math.max(active - 1, 0)); }
      else if (e.key === 'Enter' || e.key === ' '){ if (isOpen() && active >= 0){ e.preventDefault(); choose(items[active]); } else if (!isOpen()){ e.preventDefault(); open(); } }
      else if (e.key === 'Escape'){ close(); }
    });
    document.addEventListener('click', function(e){ if (!wrap.contains(e.target)) close(); });
    // External code that changes the select value keeps the button in sync.
    sel.addEventListener('change', sync);
    // Required-field validation: native select still validates; surface it on the visible control.
    sel.addEventListener('invalid', function(){ wrap.classList.add('uk-invalid'); });

    sync();
  }

  function run(){ Array.prototype.forEach.call(document.querySelectorAll('select:not([data-native])'), enhance); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
  else run();
})();
</script>
