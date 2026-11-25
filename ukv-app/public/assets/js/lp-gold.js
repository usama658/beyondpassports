
  !function(f){if(f.fbq)return;var n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];}(window);
  fbq('consent', 'revoke');               // hold all cookies/data until consent is granted
  fbq('init', '1780802003331250');
  fbq('track', 'PageView');               // queued; only sent after fbq('consent','grant')
  (function(){
    var done=false;
    function load(){if(done)return;done=true;var t=document.createElement('script');t.async=true;t.src='https://connect.facebook.net/en_US/fbevents.js';document.head.appendChild(t);}
    ['pointerdown','scroll','keydown','touchstart'].forEach(function(e){window.addEventListener(e,load,{passive:true,once:true});});
    setTimeout(load,3500);
  })();

window.__bpTagsLoaded=true;

(function(){
  var loaded=false;
  function loadTags(){
    if(loaded)return;loaded=true;
        /* Google Tag Manager */
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-5DMLL4HR');
            /* Google tag (gtag.js) — served from the first-party measurement path (ukv.ga4_fp_path,
       e.g. /5u9d) when set, else the standard googletagmanager.com loader. */
    (function(){var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id=G-KR93N3DF55';document.head.appendChild(s);})();
    window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}window.gtag=window.gtag||gtag;gtag('js',new Date());gtag('config','G-KR93N3DF55');
            /* Microsoft Clarity */
    (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,'clarity','script','xbfqfhmnvp');
            if(window.fbq){fbq('consent','grant');}
        ['pointerdown','scroll','keydown','touchstart'].forEach(function(e){window.removeEventListener(e,loadTags,{passive:true});});
  }
  ['pointerdown','scroll','keydown','touchstart'].forEach(function(e){window.addEventListener(e,loadTags,{passive:true,once:true});});
  setTimeout(loadTags,3500);
})();


(function () {
  var NAME = 'ukv_consent';
  function get(n){ return document.cookie.split('; ').reduce(function(a,c){var p=c.split('=');return p[0]===n?decodeURIComponent(p[1]):a;},''); }
  function set(v){ var d=new Date(); d.setTime(d.getTime()+180*864e5); document.cookie=NAME+'='+v+'; expires='+d.toUTCString()+'; path=/; SameSite=Lax'; }
  var loaded=false;
  function loadAcceptedScripts(){
    if(loaded) return; loaded=true;
    // Trustpilot bootstrap now loads ungated in partials/trustpilot (reviews show without consent).
    // Google Tag Manager (analytics/marketing — also carries Google Ads tags).
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-5DMLL4HR');
    // Google Analytics 4 — first-party measurement path (ukv.ga4_fp_path) when set, else standard loader.
    (function(){var g=document.createElement('script');g.async=true;g.src='https://www.googletagmanager.com/gtag/js?id=G-KR93N3DF55';document.head.appendChild(g);})();
    window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-KR93N3DF55');
    // Microsoft Clarity.
    (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,'clarity','script','xbfqfhmnvp');
    // Meta Pixel is loaded in <head> with consent revoked (partials.meta-pixel).
    // Granting here releases the queued PageView + later events.
    if(window.fbq){ fbq('consent','grant'); }
    // LinkedIn profile badge (only when a badge is present on the page).
    if(document.querySelector('.LI-profile-badge')){var li=document.createElement('script');li.async=true;li.defer=true;li.src='https://platform.linkedin.com/badges/js/profile.js';document.head.appendChild(li);}
  }
  // No banner (UKV_COOKIE_BANNER=false): analytics load statically in <head> via
  // partials.analytics-head on layout pages (detectable by Google). Only fall back to
  // JS-injection here if that static install isn't present (e.g. standalone LP heads).
  if(!window.__bpTagsLoaded){ loadAcceptedScripts(); }
})();


(function () {
  window.dataLayer = window.dataLayer || [];
  var body = document.body;
  var PAGE = {
    page_key:  (body && body.dataset.page) || 'unknown',
    page_path: (body && body.dataset.pagePath) || location.pathname
  };
  function base(ev) {
    return { event: ev, page_key: PAGE.page_key, page_path: PAGE.page_path, page_title: document.title };
  }
  function clean(s) { return (s || '').replace(/\s+/g, ' ').trim().slice(0, 120); }

  // Does this element count as a CTA, and if so what kind?
  function ctaKind(el) {
    if (el.hasAttribute('data-cta')) return 'custom';
    var href = (el.getAttribute && el.getAttribute('href')) || '';
    if (/wa\.me|api\.whatsapp|web\.whatsapp/i.test(href)) return 'whatsapp';
    if (/^tel:/i.test(href))   return 'phone';
    if (/^mailto:/i.test(href)) return 'email';
    var cls = el.className && el.className.baseVal !== undefined ? el.className.baseVal : (el.className || '');
    if (/\b(btn|cta)[\w-]*/i.test(cls)) return el.type === 'submit' ? 'submit' : 'button';
    if (el.type === 'submit') return 'submit';
    return null;
  }

  document.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('a,button,[data-cta]');
    if (!el) return;
    var kind = ctaKind(el);
    if (!kind) return;
    var section = el.closest('section,[data-section]');
    var sectionId = section ? (section.id || section.getAttribute('data-section') || '') : '';
    var href = (el.getAttribute && el.getAttribute('href')) || '';

    // Email links get a dedicated 'email_click' event (parallel to form_submit/cta_click).
    if (kind === 'email') {
      var addr = href.replace(/^mailto:/i, '').split('?')[0];
      try { addr = decodeURIComponent(addr); } catch (_) {}
      var em = base('email_click');
      em.email_address = addr;
      em.email_text    = clean(el.getAttribute('data-cta') || el.innerText || el.getAttribute('aria-label'));
      em.email_id      = el.id || '';
      em.email_href    = href;
      em.email_section = sectionId;
      window.dataLayer.push(em);
      return;
    }

    var data = base('cta_click');
    data.cta_kind    = kind;
    data.cta_text    = clean(el.getAttribute('data-cta') || el.innerText || el.getAttribute('aria-label') || el.value);
    data.cta_id      = el.id || '';
    data.cta_href    = href;
    data.cta_section = sectionId;
    window.dataLayer.push(data);
  }, true);

  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!f || f.tagName !== 'FORM') return;
    var data = base('form_submit');
    data.form_id     = f.id || '';
    data.form_name   = f.getAttribute('name') || f.getAttribute('data-form') || f.id || '';
    data.form_action = f.getAttribute('action') || location.pathname;
    data.form_method = (f.getAttribute('method') || 'get').toLowerCase();
    window.dataLayer.push(data);
  }, true);
})();


  // Lightweight reveal-on-scroll (graceful: .reveal is visible by default in noscript).
  (function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('in'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -10% 0px' });
    document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
  })();


  // Mega-menu: only one dropdown open at a time; close on outside-click or Esc.
  // Built on native <details> so it still opens/closes with no JS and via keyboard.
  (function () {
    var menus = Array.prototype.slice.call(document.querySelectorAll('.nav details.mega'));
    menus.forEach(function (d) {
      d.addEventListener('toggle', function () {
        if (d.open) { menus.forEach(function (o) { if (o !== d) o.open = false; }); }
      });
    });
    document.addEventListener('click', function (e) {
      menus.forEach(function (d) { if (d.open && !d.contains(e.target)) d.open = false; });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') menus.forEach(function (d) { d.open = false; });
    });
    // Mobile hamburger: toggle the primary nav.
    var btn = document.querySelector('.nav-toggle'), nav = document.getElementById('primary-nav');
    if (btn && nav) {
      btn.addEventListener('click', function () {
        var open = nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }
  })();


  // Auto-running trust-bar carousel (mobile only, one item per view). Runs SITE-WIDE from here
  // so every page carrying the .tbar-f band behaves like the home page. CSS (ukv.css) does the
  // one-per-view layout under 760px; this advances it every 2.6s and loops, pausing on touch.
  (function () {
    var row = document.querySelector('.tbar-f .row');
    if (!row) return;
    var mq = window.matchMedia('(max-width:760px)');
    var timer = null, paused = false, i = 0;
    function items(){ return row.children.length; }
    function go(){
      if (paused || !mq.matches || items() < 2) return;
      i = (i + 1) % items();
      var el = row.children[i];
      if (el) row.scrollTo({ left: el.offsetLeft, behavior: 'smooth' });
    }
    function start(){ stop(); if (mq.matches) timer = setInterval(go, 2600); }
    function stop(){ if (timer) { clearInterval(timer); timer = null; } }
    var sync;
    row.addEventListener('scroll', function () {
      clearTimeout(sync);
      sync = setTimeout(function () {
        var best = 0, min = Infinity;
        for (var k = 0; k < items(); k++) {
          var d = Math.abs(row.children[k].offsetLeft - row.scrollLeft);
          if (d < min) { min = d; best = k; }
        }
        i = best;
      }, 120);
    }, { passive: true });
    ['touchstart','pointerdown'].forEach(function (e) { row.addEventListener(e, function () { paused = true; }, { passive: true }); });
    ['touchend','pointerup','mouseleave'].forEach(function (e) { row.addEventListener(e, function () { paused = false; }, { passive: true }); });
    (mq.addEventListener ? mq.addEventListener('change', start) : mq.addListener(start));
    start();
  })();


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

(function(){function c(){var b=a.contentDocument||(a.contentWindow&&a.contentWindow.document);if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'a36c93599bcaf884',t:'MTc4ODY4ODI5MA=='};var a=document.createElement('script');a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();

(function () {
  window.dataLayer = window.dataLayer || [];
  var body = document.body;
  var PAGE = {
    page_key:  (body && body.dataset.page) || 'unknown',
    page_path: location.pathname
  };
  function base(ev) {
    return { event: ev, page_key: PAGE.page_key, page_path: PAGE.page_path, page_title: document.title };
  }
  function clean(s) { return (s || '').replace(/\s+/g, ' ').trim().slice(0, 120); }
  function ctaKind(el) {
    if (el.hasAttribute('data-cta')) return 'custom';
    var href = (el.getAttribute && el.getAttribute('href')) || '';
    if (/wa\.me|api\.whatsapp|web\.whatsapp/i.test(href)) return 'whatsapp';
    if (/^tel:/i.test(href))   return 'phone';
    if (/^mailto:/i.test(href)) return 'email';
    var cls = el.className && el.className.baseVal !== undefined ? el.className.baseVal : (el.className || '');
    if (/\b(btn|cta)[\w-]*/i.test(cls)) return el.type === 'submit' ? 'submit' : 'button';
    if (el.type === 'submit') return 'submit';
    return null;
  }
  document.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('a,button,[data-cta]');
    if (!el) return;
    var kind = ctaKind(el);
    if (!kind) return;
    var section = el.closest('section,[data-section]');
    var sectionId = section ? (section.id || section.getAttribute('data-section') || '') : '';
    var href = (el.getAttribute && el.getAttribute('href')) || '';
    if (kind === 'email') {
      var addr = href.replace(/^mailto:/i, '').split('?')[0];
      try { addr = decodeURIComponent(addr); } catch (_) {}
      var em = base('email_click');
      em.email_address = addr;
      em.email_text    = clean(el.getAttribute('data-cta') || el.innerText || el.getAttribute('aria-label'));
      em.email_id      = el.id || '';
      em.email_href    = href;
      em.email_section = sectionId;
      window.dataLayer.push(em);
      return;
    }
    var data = base('cta_click');
    data.cta_kind    = kind;
    data.cta_text    = clean(el.getAttribute('data-cta') || el.innerText || el.getAttribute('aria-label') || el.value);
    data.cta_id      = el.id || '';
    data.cta_href    = href;
    data.cta_section = sectionId;
    window.dataLayer.push(data);
  }, true);
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!f || f.tagName !== 'FORM') return;
    var data = base('form_submit');
    data.form_id     = f.id || '';
    data.form_name   = f.getAttribute('name') || f.getAttribute('data-form') || f.id || '';
    data.form_action = f.getAttribute('action') || location.pathname;
    data.form_method = (f.getAttribute('method') || 'get').toLowerCase();
    window.dataLayer.push(data);
  }, true);
})();


var STATE={country:null,cc:'',stopper:null,timing:null},CUR=1,TOTAL=4,WA='447882747584';
var STOP={
 notsure:{badge:'First-time file',bc:'s',v:"The documents you need depend on the UK visa you're on. Most guides online are written for a different visa, not yours.",cta:'Send my {C} checklist'},
 second:{badge:'Ready to check',bc:'s',v:"Most files people think are finished are missing two or three small things. Almost never the passport or the flights, usually the small details.",cta:'Check my file'},
 noappt:{badge:'Appointment hunt',bc:'s',vDef:"Appointments open with little warning and get taken by people who are already ready. Getting one is about having your file finished, not checking more often.",vHigh:"{C} is one of the busiest countries to apply for from the UK. The few appointments that open go to people who are already ready, not whoever checks the most.",vFast:"{C} is quicker than most, but appointments still go to people who are ready first. Having your file finished is what gets you in.",cta:'Get me the next {C} slot'},
 refused:{badge:'Refused before',bc:'r',v:"The real reason for a refusal is one short coded line on the letter. Most people can't tell which of the ticked reasons actually caused it.",cta:'Explain my letter'},
 handled:{badge:'Full service',bc:'s',v:"It's one list of documents, one appointment, and one folder in the right order. Not difficult, just easy to get a small thing wrong.",cta:'Get me a consultant'}
};
var STOPLBL={notsure:'Not sure what I need',second:'Want a second look',noappt:"Can't get an appointment",refused:'Refused before',handled:'Just want it handled'};
var TIMLBL={within2:'2-3 weeks','2to4':'In a month',m13:'In 1 to 3 months',none:'No date fixed'};
var URG={within2:'This one has to start today.','2to4':'You have room, but not much.'};
function bars(){for(var i=1;i<=TOTAL;i++){var b=document.getElementById('bar'+i);if(b)b.classList.toggle('on',i<=CUR);}}
function show(n){
  CUR=n;
  document.querySelectorAll('.step').forEach(function(s){s.classList.toggle('active',+s.dataset.n===n);});
  var backBtn=document.getElementById('back');if(backBtn)backBtn.classList.toggle('show',n>1);
  var pill=document.querySelector('.pill');if(pill){pill.style.display=(n<=3)?'':'none';pill.textContent='Step '+n+' of 3';}
  bars();
  var hf=document.getElementById('htopflag');
  if(hf){if(STATE.country&&STATE.cc&&n>1){hf.innerHTML='<img src="https://flagcdn.com/w80/'+STATE.cc+'.png" width="20" height="15" alt="">'+STATE.country;hf.hidden=false;}else{hf.hidden=true;hf.innerHTML='';}}
  if(n===2){var q=document.getElementById('s2q');if(q)q.textContent="What's stopping you from going to "+(STATE.country||'your destination')+"?";}
  if(n===4){renderVerdict();try{window.dataLayer=window.dataLayer||[];window.dataLayer.push({'event':'quiz_completed','page_key':(document.body&&document.body.dataset.page)||'unknown','page_path':location.pathname,'page_title':document.title,'quiz_flow':'main'});}catch(e){}}
  var c=document.querySelector('.card');if(c)c.scrollIntoView({behavior:'smooth',block:'nearest'});
}
function selIn(el){if(!el)return;var g=el.parentNode;g.querySelectorAll('.tile').forEach(function(t){t.classList.remove('sel');});el.classList.add('sel');}
function pickCountry(el,name,iso){selIn(el);STATE.country=name;STATE.cc=iso||'';hCclose();setTimeout(function(){show(2);},200);}
function pickStopper(el,key){selIn(el);STATE.stopper=key;setTimeout(function(){show(3);},200);}
function pickTiming(el,key){selIn(el);STATE.timing=key;setTimeout(function(){show(4);},200);}
window.HISO='';
var HSC=[["Austria","at"],["Belgium","be"],["Bulgaria","bg"],["Croatia","hr"],["Czechia","cz"],["Denmark","dk"],["Estonia","ee"],["Finland","fi"],["France","fr"],["Germany","de"],["Greece","gr"],["Hungary","hu"],["Iceland","is"],["Italy","it"],["Latvia","lv"],["Liechtenstein","li"],["Lithuania","lt"],["Luxembourg","lu"],["Malta","mt"],["Netherlands","nl"],["Norway","no"],["Poland","pl"],["Portugal","pt"],["Romania","ro"],["Slovakia","sk"],["Slovenia","si"],["Spain","es"],["Sweden","se"],["Switzerland","ch"]];
function pickC(el,name,iso){pickCountry(el,name,iso);}
function hCsearch(){document.getElementById('hcgrid').style.display='none';document.getElementById('hcwrap').classList.remove('mhide');hCrender('');document.getElementById('hcq').focus();}
function hCclose(){var w=document.getElementById('hcwrap');if(w)w.classList.add('mhide');var g=document.getElementById('hcgrid');if(g)g.style.display='';var q=document.getElementById('hcq');if(q)q.value='';}
var HSLOT={fr:['open','Open'],de:['open','Open'],gr:['open','Open'],es:['lim','Filling'],it:['lim','Limited']};
function hCrender(q){q=(q||'').toLowerCase();var ul=document.getElementById('hclist');if(!ul)return;ul.innerHTML='';var m=HSC.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1;});if(!m.length){ul.innerHTML='<li class=nomatch>No match. Try another spelling.</li>';return;}m.forEach(function(c){var st=HSLOT[c[1]]||['chk','Check'];var li=document.createElement('li');li.innerHTML='<img class=flag src="https://flagcdn.com/w80/'+c[1]+'.png" width="24" height="17" alt=""><span class=cn2>'+c[0]+'</span><span class="cst2 '+st[0]+'"><span class=cdot2></span>'+st[1]+'</span>';li.onclick=function(){STATE.country=c[0];STATE.cc=c[1];show(2);hCclose();};ul.appendChild(li);});}
function restoreSel(step){
  var val=step===1?STATE.country:step===2?STATE.stopper:step===3?STATE.timing:null;
  if(val==null)return;
  document.querySelectorAll('.step[data-n="'+step+'"] .tile').forEach(function(t){
    t.classList.toggle('sel',t.dataset.val===val||t.dataset.k===val);
  });
}
function goBack(){ if(CUR>1){show(CUR-1);restoreSel(CUR-1);} }
var BUCKET={es:'high',fr:'high',it:'high',de:'fast',nl:'fast'};
function verdictText(k,cc,c){var s=STOP[k];if(!s)return '';if(k==='noappt'){var b=BUCKET[cc]||'def';var t=b==='high'?s.vHigh:(b==='fast'?s.vFast:s.vDef);return t.replace(/\{C\}/g,c);}return (s.v||'').replace(/\{C\}/g,c);}
function renderVerdict(){
  var s=STOP[STATE.stopper];if(!s)return;
  var c=STATE.country||'your destination';
  var badge=document.getElementById('s4badge');
  if(badge){badge.className='v-badge '+s.bc;badge.textContent=s.badge;}
  var sub=document.getElementById('s4sub');if(sub)sub.innerHTML=verdictText(STATE.stopper,STATE.cc,c);
  var u=URG[STATE.timing]||'',urg=document.getElementById('s4urg');
  if(urg){if(u){document.getElementById('s4urgt').textContent=u;urg.style.display='flex';}else{urg.style.display='none';}}
  var cta=document.getElementById('s4cta');if(cta)cta.textContent=s.cta.replace('{C}',c);
}
function buildMsg(){
  var s=STOP[STATE.stopper]||{cta:'help me'};
  var phone=(window.pcFull&&window.pcFull())||'';
  var lines=['Hi Beyond Passports.'];
  if(STATE.country)lines.push('Destination: '+STATE.country);
  if(STATE.stopper)lines.push('My situation: '+(STOPLBL[STATE.stopper]||''));
  if(STATE.timing)lines.push('Timing: '+(TIMLBL[STATE.timing]||''));
  lines.push('Please '+s.cta.replace('{C}',STATE.country||'my destination').toLowerCase()+'.');
  if(phone)lines.push('Number: '+phone);
  return lines.join('\n');
}
function postLead(name,phone,dest,intent){try{window.dataLayer=window.dataLayer||[];window.dataLayer.push({event:'generate_lead',method:'whatsapp',quiz_flow:intent||'case',destination:dest||'',page_key:(document.body&&document.body.dataset.page)||'',page_path:location.pathname,value:39,currency:'GBP'});}catch(e){}try{fetch('/schengen-visa-consultancy/lead',{method:'POST',keepalive:true,headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({name:name||'',phone:phone||'',dest:dest||'',intent:intent||'case',source:'lp-v2 (money page)',website:'',utm:(window.bpUtm&&window.bpUtm())||null})});}catch(e){}}
function send(){
  var ps=(window.pcStatus&&window.pcStatus())||'bad';
  var combo=document.getElementById('pc'),eph=document.getElementById('e_phone');
  if(ps!=='ok'){combo.classList.add('pc-invalid');eph.textContent=(ps==='req'?'Please enter your WhatsApp number':'Enter a valid '+((window.pcCountry&&window.pcCountry())||'')+' number');eph.style.display='block';return;}
  combo.classList.remove('pc-invalid');eph.style.display='none';
  postLead('',(window.pcFull&&window.pcFull())||'',STATE.country||'','lp-v2 hero');
  var text=encodeURIComponent(buildMsg());
  var waApp='whatsapp://send?phone='+WA+'&text='+text;
  var waWeb=(window.bpWaUrl||String)('https://wa.me/'+WA+'?text='+text);
  var isMobile=/Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  document.querySelectorAll('.step').forEach(function(s){s.classList.remove('active');});
  var backBtn2=document.getElementById('back');if(backBtn2)backBtn2.classList.remove('show');
  var pill=document.querySelector('.pill');if(pill){pill.style.display='';pill.textContent='All set';}
  document.querySelectorAll('.bar').forEach(function(b){b.classList.add('on');});
  document.getElementById('sntHead').textContent="You're all set.";
  document.getElementById('sntWa').href=waWeb;
  var sh='';
  if(STATE.country)sh+='<div class=row><span class=k>Destination</span><span class=v>'+STATE.country+'</span></div>';
  if(STATE.stopper)sh+='<div class=row><span class=k>Situation</span><span class=v>'+(STOPLBL[STATE.stopper]||'')+'</span></div>';
  if(STATE.timing)sh+='<div class=row><span class=k>Timing</span><span class=v>'+(TIMLBL[STATE.timing]||'')+'</span></div>';
  document.getElementById('sntSummary').innerHTML=sh;
  document.getElementById('sent').classList.add('active');
  var c=3,el=document.getElementById('sntCount'),went=false;
  function go(){ if(went)return; went=true;
    if(isMobile){ try{window.location.href=waApp;}catch(e){} setTimeout(function(){window.location.href=waWeb;},1500); }
    else{ window.open(waWeb,'_blank'); }
  }
  var t=setInterval(function(){c--;if(el)el.textContent=c<0?0:c;if(c<=0){clearInterval(t);go();}},1000);
  setTimeout(go,3300);
}
var backBtn3=document.getElementById('back');if(backBtn3)backBtn3.addEventListener('click',goBack);
bars();
// ---- dial-code phone combo (port of partials.phone-country) ----
(function(){
 var FB='https://beyondpassports.co.uk/assets/flags/';
 var C=[
  ["United Kingdom","GB","+44",[10,10]],["Ireland","IE","+353",[7,9]],["France","FR","+33",[9,9]],
  ["Germany","DE","+49",[10,11]],["Spain","ES","+34",[9,9]],["Italy","IT","+39",[9,10]],
  ["Netherlands","NL","+31",[9,9]],["Belgium","BE","+32",[8,9]],["Portugal","PT","+351",[9,9]],
  ["Poland","PL","+48",[9,9]],["Greece","GR","+30",[10,10]],["Austria","AT","+43",[9,13]],
  ["Czechia","CZ","+420",[9,9]],["Switzerland","CH","+41",[9,9]],["Sweden","SE","+46",[7,9]],
  ["Denmark","DK","+45",[8,8]],["Norway","NO","+47",[8,8]],["United States","US","+1",[10,10]],
  ["Canada","CA","+1",[10,10]],["India","IN","+91",[10,10]],["Pakistan","PK","+92",[10,10]],
  ["Bangladesh","BD","+880",[10,10]],["Nigeria","NG","+234",[8,10]],["Kenya","KE","+254",[9,9]],
  ["South Africa","ZA","+27",[9,9]],["UAE","AE","+971",[9,9]],["Saudi Arabia","SA","+966",[9,9]],
  ["Qatar","QA","+974",[8,8]],["Australia","AU","+61",[9,9]],["New Zealand","NZ","+64",[8,10]],
  ["Turkey","TR","+90",[10,10]],["China","CN","+86",[11,11]]
 ];
 var root=document.getElementById('pc');if(!root)return;
 var btn=document.getElementById('pcBtn'),pop=document.getElementById('pcPop'),
     flag=document.getElementById('pcFlag'),dc=document.getElementById('pcDc'),
     input=document.getElementById('f_phone');
 var cur=C[0];
 pop.innerHTML='<input class=pc-search placeholder="Search country or code" aria-label="Search country"><ul class=pc-list></ul>';
 var search=pop.querySelector('.pc-search'),ul=pop.querySelector('.pc-list');
 function flimg(c){return '<img class=pc-fl-img src="'+FB+c[1].toLowerCase()+'.svg" width=20 height=15 alt="">';}
 function paint(c){cur=c;flag.src=FB+c[1].toLowerCase()+'.svg';dc.textContent=c[2];}
 function render(q){q=(q||'').toLowerCase().trim();ul.innerHTML='';
  C.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1||c[2].indexOf(q)>-1||c[1].toLowerCase()===q;})
   .forEach(function(c){var li=document.createElement('li');li.className='pc-opt';li.setAttribute('role','option');
    li.innerHTML='<span>'+flimg(c)+'</span><span class=pc-nm>'+c[0]+'</span><span class=pc-dc>'+c[2]+'</span>';
    li.addEventListener('mousedown',function(e){e.preventDefault();paint(c);close();input.focus();});ul.appendChild(li);});}
 function open(){render('');pop.hidden=false;btn.setAttribute('aria-expanded','true');setTimeout(function(){search.focus();},20);}
 function close(){pop.hidden=true;btn.setAttribute('aria-expanded','false');}
 btn.addEventListener('click',function(e){e.stopPropagation();pop.hidden?open():close();});
 search.addEventListener('input',function(){render(this.value);});
 search.addEventListener('keydown',function(e){if(e.key==='Escape'){close();btn.focus();}});
 document.addEventListener('click',function(e){if(!root.contains(e.target))close();});
 paint(cur);
 input.addEventListener('input',function(){root.classList.remove('pc-invalid');document.getElementById('e_phone').style.display='none';});
 window.pcCountry=function(){return cur[0];};
 window.pcStatus=function(){
   var raw=(input.value||'').trim();if(!raw)return 'req';
   var d=raw.replace(/[^0-9]/g,'').replace(/^0+/,''),r=cur[3]||[6,15];
   return (d.length>=r[0]&&d.length<=r[1])?'ok':'bad';};
 window.pcFull=function(){var v=(input.value||'').trim();if(!v)return '';
   if(v.charAt(0)==='+')return v;return cur[2]+' '+v.replace(/[^0-9]/g,'').replace(/^0+/,'');};
})();

(function(){function h(n){n=(n*2654435761)>>>0;n^=n>>>13;n=(n*2246822519)>>>0;n^=n>>>16;return n>>>0;}
function tgt(w){return 21+(h(w)%8);}
function dayInc(d){var w=Math.floor(d/7),dow=d%7,t=tgt(w),b=Math.floor(t/7),r=t-b*7,off=h(w*31+99)%7,v=b,k;for(k=0;k<r;k++){if(((off+k)%7)===dow)v++;}return v;}
var start=Date.UTC(2026,7,24),base=243,days=Math.floor((Date.now()-start)/86400000);if(days<0)days=0;
var total=base;for(var d=0;d<days;d++)total+=dayInc(d);
var els=document.querySelectorAll('.bpcount');for(var i=0;i<els.length;i++)els[i].textContent=total.toLocaleString('en-GB');})();

(function(){
 var CT={es:['filling','standard'],fr:['open','straightforward'],it:['limited','strict'],de:['open','standard'],gr:['open','straightforward'],nl:['filling','strict'],pt:['open','straightforward'],be:['limited','high'],at:['check','standard'],ch:['waitlist','strict']};
 window.CTRY=CT;
 var S={
  open:['linear-gradient(100deg,rgba(197,150,58,.1),rgba(197,150,58,.03))','rgba(197,150,58,.28)','#C5963A','#5c410f','#8a621f','<svg viewBox="0 0 24 24" fill=none stroke=#fff stroke-width=2.6 stroke-linecap=round stroke-linejoin=round style="width:10px;height:10px"><path d="M20 6L9 17l-5-5"/></svg>',': good availability now','Best slots go early. Start while dates are open.'],
  filling:['linear-gradient(100deg,rgba(245,158,11,.12),rgba(245,158,11,.04))','rgba(245,158,11,.28)','#B45309','#7c3d06','#b45309','<svg viewBox="0 0 24 24" fill=none stroke=#fff stroke-width=2.2 stroke-linecap=round stroke-linejoin=round style="width:10px;height:10px"><path d="M6 3h12M6 21h12M7 3c0 5 10 5 10 0M7 21c0-5 10-5 10 0"/></svg>',': appointments filling fast','If your trip is in the next 8 weeks, do not wait.'],
  limited:['linear-gradient(100deg,rgba(245,158,11,.12),rgba(245,158,11,.04))','rgba(245,158,11,.28)','#B45309','#7c3d06','#b45309','<svg viewBox="0 0 24 24" fill=#fff style="width:10px;height:10px"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>',': limited slots left','If your trip is in the next 8 weeks, do not wait.'],
  check:['#f6f9fb','#E2E8F0','#64748b','#0B1528','#5b6b7d','<svg viewBox="0 0 24 24" fill=none stroke=#fff stroke-width=2.4 stroke-linecap=round stroke-linejoin=round style="width:10px;height:10px"><circle cx=11 cy=11 r=7 /><path d="M21 21l-4.3-4.3"/></svg>',': live availability check','Tell us your dates and we confirm fast.'],
  waitlist:['rgba(11,21,40,.04)','rgba(11,21,40,.12)','#0B1528','#0B1528','#5b6b7d','<svg viewBox="0 0 24 24" fill=none stroke=#fff stroke-width=2.2 stroke-linecap=round stroke-linejoin=round style="width:10px;height:10px"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v4h4"/></svg>',': currently by waitlist','We alert you the moment a slot opens.']
 };
 var D={
  straightforward:['rgba(22,163,74,.1)','rgba(22,163,74,.25)','#166534','<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px"><path d="M7 10v10H3V10h4zm3.5 0 3-6.5c.7-1.5 3-1 3 .8V8h3.3c1.3 0 2.2 1.2 1.9 2.4l-1.4 6.2c-.2 1-1.1 1.6-2.1 1.6H10.5V10z"/></svg>','Usually smooth when documents are in order'],
  standard:['#f1f5f9','#E2E8F0','#5b6b7d','<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px"><path fill-rule=evenodd d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5H8.25Z" clip-rule=evenodd/><path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z"/></svg>','Standard checks. Details matter'],
  strict:['rgba(180,83,9,.1)','rgba(180,83,9,.28)','#b45309','<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px"><path fill-rule=evenodd d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule=evenodd/></svg>','Strict on funds and documents. Common refusal points'],
  high:['rgba(220,38,38,.09)','rgba(220,38,38,.28)','#b91c1c','<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px"><path fill-rule=evenodd d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule=evenodd/></svg>','Higher refusal risk. Expert prep advised']
 };
 window.urgHTML=function(name,iso){
  iso=(iso||'').toLowerCase();var c=CT[iso]||['check','standard'];var s=S[c[0]]||S.check;
  return '<div style="display:flex;gap:11px;align-items:center;border-radius:13px;padding:11px 13px;background:'+s[0]+';border:1px solid '+s[1]+'">'
   +'<span style="position:relative;flex:none"><img src="https://flagcdn.com/w80/'+iso+'.png" alt="" style="width:36px;height:36px;border-radius:10px;object-fit:cover;box-shadow:0 6px 14px -6px rgba(11,21,40,.5),0 0 0 1px rgba(0,0,0,.08)"><span style="position:absolute;bottom:-4px;right:-4px;width:18px;height:18px;border-radius:50%;border:2px solid #fff;display:flex;align-items:center;justify-content:center;background:'+s[2]+'">'+s[5]+'</span></span>'
   +'<span><b style="display:block;font:800 12px Inter,sans-serif;color:'+s[3]+'">'+name+s[6]+'</b><span style="display:block;font:600 11px Inter,sans-serif;color:'+s[4]+';margin-top:2px;line-height:1.4">'+s[7]+'</span></span></div>';
 };
 window.diffHTML=function(iso){
  iso=(iso||'').toLowerCase();var c=CT[iso]||['check','standard'];var d=D[c[1]]||D.standard;
  return '<span style="display:inline-flex;align-items:center;gap:6px;font:700 10.5px Inter,sans-serif;border-radius:999px;padding:5px 10px;background:'+d[0]+';border:1px solid '+d[1]+';color:'+d[2]+'">'+d[3]+d[4]+'</span>';
 };
 var VIC={
  ok:'<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=3 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg>',
  warn:'<svg viewBox="0 0 24 24" fill=currentColor><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>',
  bad:'<svg viewBox="0 0 24 24" fill=currentColor><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 5h2v6h-2V7Zm0 8h2v2h-2v-2Z"/></svg>',
  neutral:'<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.2 stroke-linecap=round stroke-linejoin=round><circle cx=11 cy=11 r="7"></circle><path d="M21 21l-4.3-4.3"/></svg>'
 };
 window.vChip=function(c){return c&&c[1]?'<span class="v-chip '+c[0]+'">'+(VIC[c[0]]||'')+c[1]+'</span>':'';};
 window.vSlot=function(iso){var c=CT[(iso||'').toLowerCase()];var s=c?c[0]:'check';
  if(s==='open')return['ok','Appointments open'];
  if(s==='filling'||s==='limited')return['warn','Book soon'];
  if(s==='waitlist')return['neutral','Waitlist'];
  return['neutral','Live check'];};
 window.vTime=function(t){t=t||'';
  if(/(within 2 weeks|2-3 weeks)/i.test(t))return['bad','Very tight'];
  if(/(2 to 4 weeks|in a month)/i.test(t))return['warn','Tight, act now'];
  if(/1 to 3 months/i.test(t))return['ok','Good runway'];
  if(/planning/i.test(t))return['ok','Plenty of time'];
  return['neutral',''];};
 window.vAmber=function(sit,tim){return /refused|complex|4 weeks/i.test(sit||'')||/(within 2 weeks|2-3 weeks)/i.test(tim||'');};
 window.vSnap=function(rows,title){var h='<div class=v-snaphd>'+(title||'Your eligibility snapshot')+' <small>provisional</small></div>';
  rows.forEach(function(r){if(!r[1])return;h+='<div class=v-row><span class=kv><span class=k>'+r[0]+'</span><span class=v>'+r[1]+'</span></span>'+window.vChip(r[2])+'</div>';});
  return h;};
 window.vBadgeInner=function(amber){return amber
   ? '<svg viewBox="0 0 24 24" fill=currentColor><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>Needs a human look'
   : '<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.6 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg>Eligibility check';};
 window.vVerdictInner=function(amber){return amber
   ? '<svg viewBox="0 0 24 24" fill=none stroke="#b45309" stroke-width=2 stroke-linecap=round stroke-linejoin=round><circle cx=12 cy=12 r="9"><path d="M12 8v4l3 2"/></svg><span><b>We can still help.</b> A consultant reviews complex and urgent cases first, subject to a quick review.</span>'
   : '<svg viewBox="0 0 24 24" fill=none stroke="#15803d" stroke-width=2 stroke-linecap=round stroke-linejoin=round><circle cx=12 cy=12 r="9"><path d="M12 8v4l3 2"/></svg><span><b>Looks eligible.</b> Final check takes 2 minutes with your consultant, subject to a quick review.</span>';};
})();
(function(){var t=document.querySelector('.dtoggle'),n=document.getElementById('dprimary');if(t&&n){t.addEventListener('click',function(){var o=n.classList.toggle('open');t.setAttribute('aria-expanded',o?'true':'false');});n.addEventListener('click',function(e){if(e.target.closest('a'))n.classList.remove('open');});}})();
(function(){var h=document.getElementById('htrust');if(!h||h.querySelector('.htrust-track'))return;var t=document.createElement('div');t.className='htrust-track';while(h.firstChild)t.appendChild(h.firstChild);h.appendChild(t);try{if(window.matchMedia('(max-width:640px)').matches)t.innerHTML+=t.innerHTML;}catch(e){}})();
(function(){var row=document.getElementById('howrow'),dots=document.getElementById('howdots');if(!row||!dots)return;var cards=row.children;function step(){var c=cards[0];return c?c.getBoundingClientRect().width+14:356;}for(var i=0;i<cards.length;i++){var s=document.createElement('span');dots.appendChild(s);}function upd(){var a=Math.round(row.scrollLeft/step());if(a<0)a=0;if(a>dots.children.length-1)a=dots.children.length-1;for(var i=0;i<dots.children.length;i++)dots.children[i].className=i===a?'on':'';}row.addEventListener('scroll',upd);window.addEventListener('resize',upd);upd();})();
(function(){var row=document.querySelector('.tscroll'),dots=document.getElementById('tdots');if(!row||!dots)return;var cards=row.children;function step(){var c=cards[0];return c?c.getBoundingClientRect().width+16:356;}for(var i=0;i<cards.length;i++){(function(i){var b=document.createElement('button');b.type='button';b.setAttribute('aria-label','Go to testimonial '+(i+1));b.addEventListener('click',function(){row.scrollTo({left:i*step(),behavior:'smooth'});});dots.appendChild(b);})(i);}function upd(){var a=Math.round(row.scrollLeft/step());if(a<0)a=0;if(a>dots.children.length-1)a=dots.children.length-1;for(var i=0;i<dots.children.length;i++)dots.children[i].classList.toggle('on',i===a);}row.addEventListener('scroll',upd);window.addEventListener('resize',upd);upd();})();


(function(){
 var WA='447882747584';
 var STOP={
  notsure:{badge:'First-time file',bc:'s',v:"The documents you need depend on the UK visa you're on. Most guides online are written for a different visa, not yours.",cta:'Send my {C} checklist'},
  second:{badge:'Ready to check',bc:'s',v:"Most files people think are finished are missing two or three small things. Almost never the passport or the flights, usually the small details.",cta:'Check my file'},
  noappt:{badge:'Appointment hunt',bc:'s',vDef:"Appointments open with little warning and get taken by people who are already ready. Getting one is about having your file finished, not checking more often.",vHigh:"{C} is one of the busiest countries to apply for from the UK. The few appointments that open go to people who are already ready, not whoever checks the most.",vFast:"{C} is quicker than most, but appointments still go to people who are ready first. Having your file finished is what gets you in.",cta:'Get me the next {C} slot'},
  refused:{badge:'Refused before',bc:'r',v:"The real reason for a refusal is one short coded line on the letter. Most people can't tell which of the ticked reasons actually caused it.",cta:'Explain my letter'},
  handled:{badge:'Full service',bc:'s',v:"It's one list of documents, one appointment, and one folder in the right order. Not difficult, just easy to get a small thing wrong.",cta:'Get me a consultant'}
 };
 var STOPLBL={notsure:'Not sure what I need',second:'Want a second look',noappt:"Can't get an appointment",refused:'Refused before',handled:'Just want it handled'};
 var TIMLBL={within2:'2-3 weeks','2to4':'In a month',m13:'In 1 to 3 months',none:'No date fixed'};
 var URG={within2:'This one has to start today.','2to4':'You have room, but not much.'};
 var S={country:null,cc:'',stopper:null,timing:null},CUR=1;
 var ov=document.getElementById('faPop');if(!ov)return;
 function $(id){return document.getElementById(id);}
 function bars(){for(var i=1;i<=4;i++){var b=$('faBar'+i);if(b)b.classList.toggle('on',i<=CUR);}}
 function faShow(n){CUR=n;ov.querySelectorAll('.fa-step').forEach(function(s){s.classList.toggle('on',+s.dataset.n===n);});$('faBack').style.display=(n>1&&n<5)?'inline-flex':'none';var p=$('faPill');p.style.display=(n<=3)?'':'none';p.textContent='Step '+n+' of 3';bars();var f=$('faFlag');if(S.country&&S.cc&&n>1&&n<5){f.innerHTML='<img src="https://flagcdn.com/w80/'+S.cc+'.png" width="20" height="15" alt="">'+S.country;f.hidden=false;}else{f.hidden=true;f.innerHTML='';}if(n===2){$('faSq').textContent="What's stopping you from going to "+(S.country||'your destination')+"?";}if(n===4){renderV();try{window.dataLayer=window.dataLayer||[];window.dataLayer.push({'event':'quiz_completed','page_key':(document.body&&document.body.dataset.page)||'unknown','page_path':location.pathname,'page_title':document.title,'quiz_flow':'fa'});}catch(e){}}}
 function selIn(el){if(!el)return;el.parentNode.querySelectorAll('.tile').forEach(function(t){t.classList.remove('sel');});el.classList.add('sel');}
 window.faOpen=function(){S={country:null,cc:'',stopper:null,timing:null};ov.querySelectorAll('.tile').forEach(function(t){t.classList.remove('sel');});$('faPhone').value='';$('faErr').style.display='none';if(window.faCclose)faCclose();faShow(1);ov.classList.add('open');ov.setAttribute('aria-hidden','false');};
 window.faClose=function(){ov.classList.remove('open');ov.setAttribute('aria-hidden','true');};
 window.faCsearch=function(){$('facgrid').style.display='none';$('facwrap').classList.remove('mhide');faCrender('');$('facq').focus();};
 window.faCclose=function(){var w=$('facwrap');if(w)w.classList.add('mhide');var g=$('facgrid');if(g)g.style.display='';var q=$('facq');if(q)q.value='';};
 window.faCrender=function(q){q=(q||'').toLowerCase();var ul=$('faclist');if(!ul)return;ul.innerHTML='';var list=(typeof HSC!=='undefined'&&HSC)||[];var SL=(typeof HSLOT!=='undefined'&&HSLOT)||{};var m=list.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1;});if(!m.length){ul.innerHTML='<li class=nomatch>No match. Try another spelling.</li>';return;}m.forEach(function(c){var st=SL[c[1]]||['chk','Check'];var li=document.createElement('li');li.innerHTML='<img class=flag src="https://flagcdn.com/w80/'+c[1]+'.png" width="24" height="17" alt=""><span class=cn2>'+c[0]+'</span><span class="cst2 '+st[0]+'"><span class=cdot2></span>'+st[1]+'</span>';li.onclick=function(){S.country=c[0];S.cc=c[1];faShow(2);faCclose();};ul.appendChild(li);});};
 window.faBackFn=function(){if(CUR>1&&CUR<5)faShow(CUR-1);};
 window.faPickCountry=function(el,name,iso){selIn(el);S.country=name;S.cc=iso||'';setTimeout(function(){faShow(2);},180);};
 window.faPickStopper=function(el,k){selIn(el);S.stopper=k;setTimeout(function(){faShow(3);},180);};
 window.faPickTiming=function(el,k){selIn(el);S.timing=k;setTimeout(function(){faShow(4);},180);};
 var BUCKET={es:'high',fr:'high',it:'high',de:'fast',nl:'fast'};
 function verdictText(k,cc,c){var s=STOP[k];if(!s)return '';if(k==='noappt'){var b=BUCKET[cc]||'def';var t=b==='high'?s.vHigh:(b==='fast'?s.vFast:s.vDef);return t.replace(/\{C\}/g,c);}return (s.v||'').replace(/\{C\}/g,c);}
 function renderV(){var s=STOP[S.stopper];if(!s)return;var c=S.country||'your destination';var b=$('faBadge');b.className='v-badge '+s.bc;b.textContent=s.badge;$('faSub').innerHTML=verdictText(S.stopper,S.cc,c);var u=URG[S.timing]||'',ug=$('faUrg');if(u){$('faUrgt').textContent=u;ug.style.display='flex';}else{ug.style.display='none';}$('faCta').textContent=s.cta.replace('{C}',c);}
 window.faSend=function(){var ph=$('faPhone').value.trim();if(ph.replace(/\D/g,'').length<7){$('faErr').style.display='block';return;}$('faErr').style.display='none';var s=STOP[S.stopper]||{cta:'help me'};if(window.postLead)postLead('',ph,S.country||'','lp-v2 whats-at-stake');var lines=['Hi Beyond Passports.'];if(S.country)lines.push('Destination: '+S.country);if(S.stopper)lines.push('My situation: '+(STOPLBL[S.stopper]||''));if(S.timing)lines.push('Timing: '+(TIMLBL[S.timing]||''));lines.push('Please '+s.cta.replace('{C}',S.country||'my destination').toLowerCase()+'.');lines.push('Number: '+ph);var url=(window.bpWaUrl||String)('https://wa.me/'+WA+'?text='+encodeURIComponent(lines.join('\n')));faShow(5);window.open(url,'_blank');};
 ov.addEventListener('click',function(e){if(e.target===ov)faClose();});
})();


(function(){
 var WA='447882747584';
 var READY={
  yes:{badge:'Ready to book',bc:'g',v:"Then we can start today. It's £49 to start, and if you don't get a slot, you get it back.",cta:'Talk with a consultant'},
  almost:{badge:'Almost there',bc:'a',v:"Almost is enough to start. We'll tell you what's missing and get on with the booking at the same time. £49 to start, refunded if we can't get you a date.",cta:'Talk with a consultant'},
  no:{badge:'Not started',bc:'s',v:"Most people haven't. We'll send you exactly what {C} asks for from someone on your visa, free. Booking starts at £49 when you're ready.",cta:'Send me what {C} asks for'}
 };
 var READYLBL={yes:'Documents ready',almost:'Almost ready',no:'Not started yet'};
 var TIMLBL={within2:'2-3 weeks','2to4':'2 to 4 weeks',m12:'1 to 2 months',gt2:'More than 2 months'};
 var URG={within2:'Two weeks is tight. This has to start today.','2to4':'Three weeks goes fast. Best to get moving now.'};
 var S={country:null,cc:'',timing:null,ready:null},CUR=1,START=1;
 var ov=document.getElementById('fbPop');if(!ov)return;
 function $(id){return document.getElementById(id);}
 function bars(){for(var i=1;i<=3;i++){var b=$('fbBar'+i);if(b)b.classList.toggle('on',i<=CUR);}}
 function fbShow(n){CUR=n;ov.querySelectorAll('.fa-step').forEach(function(s){s.classList.toggle('on',+s.dataset.n===n);});$('fbBack').style.display=(n>START&&n<4)?'inline-flex':'none';var p=$('fbPill');if(n>=1&&n<=2){p.style.display='';p.textContent='Step '+n+' of '+(window.UKV_SKIP_READINESS?1:2);}else{p.style.display='none';}bars();var f=$('fbFlag');if(S.country&&S.cc&&n>=1&&n<4){f.innerHTML='<img src="https://flagcdn.com/w80/'+S.cc+'.png" width="20" height="15" alt="">'+S.country;f.hidden=false;}else{f.hidden=true;f.innerHTML='';}if(n===1){$('fbTq').textContent="When do you need to be in "+(S.country||'the country')+"?";$('fbHdr').innerHTML='<b>'+(S.country||'This')+'. Here is the part you can control.</b>';}if(n===3){renderV();try{window.dataLayer=window.dataLayer||[];window.dataLayer.push({'event':'quiz_completed','page_key':(document.body&&document.body.dataset.page)||'unknown','page_path':location.pathname,'page_title':document.title,'quiz_flow':'fb'});}catch(e){}}}
 function selIn(el){if(!el)return;el.parentNode.querySelectorAll('.tile').forEach(function(t){t.classList.remove('sel');});el.classList.add('sel');}
 window.fbOpen=function(name,iso){S={country:name||null,cc:iso||'',timing:null,ready:null};ov.querySelectorAll('.tile').forEach(function(t){t.classList.remove('sel');});$('fbPhone').value='';$('fbErr').style.display='none';START=name?1:0;if(window.fbCclose)fbCclose();fbShow(START);ov.classList.add('open');ov.setAttribute('aria-hidden','false');};
 window.fbClose=function(){ov.classList.remove('open');ov.setAttribute('aria-hidden','true');};
 window.fbCsearch=function(){$('fbcgrid').style.display='none';$('fbcwrap').classList.remove('mhide');fbCrender('');$('fbcq').focus();};
 window.fbOpenSearch=function(){fbOpen();fbCsearch();};
 window.fbCclose=function(){var w=$('fbcwrap');if(w)w.classList.add('mhide');var g=$('fbcgrid');if(g)g.style.display='';var q=$('fbcq');if(q)q.value='';};
 window.fbCrender=function(q){q=(q||'').toLowerCase();var ul=$('fbclist');if(!ul)return;ul.innerHTML='';var list=(typeof HSC!=='undefined'&&HSC)||[];var SL=(typeof HSLOT!=='undefined'&&HSLOT)||{};var m=list.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1;});if(!m.length){ul.innerHTML='<li class=nomatch>No match. Try another spelling.</li>';return;}m.forEach(function(c){var st=SL[c[1]]||['chk','Check'];var li=document.createElement('li');li.innerHTML='<img class=flag src="https://flagcdn.com/w80/'+c[1]+'.png" width="24" height="17" alt=""><span class=cn2>'+c[0]+'</span><span class="cst2 '+st[0]+'"><span class=cdot2></span>'+st[1]+'</span>';li.onclick=function(){S.country=c[0];S.cc=c[1];fbShow(1);fbCclose();};ul.appendChild(li);});};
 window.fbBackFn=function(){if(CUR>START&&CUR<4)fbShow((window.UKV_SKIP_READINESS&&CUR===3)?1:CUR-1);};
 window.fbPickCountry=function(el,name,iso){selIn(el);S.country=name;S.cc=iso||'';setTimeout(function(){fbShow(1);},180);};
 window.fbPickTiming=function(el,k){selIn(el);S.timing=k;setTimeout(function(){if(window.UKV_SKIP_READINESS){S.ready='almost';fbShow(3);}else{fbShow(2);}},180);};
 window.fbPickReady=function(el,k){selIn(el);S.ready=k;setTimeout(function(){fbShow(3);},180);};
 function renderV(){var r=READY[S.ready];if(!r)return;var c=S.country||'your destination';var b=$('fbBadge');b.className='v-badge '+r.bc;b.textContent=r.badge;$('fbSub').innerHTML=r.v.replace(/\{C\}/g,c);var u=URG[S.timing]||'',ug=$('fbUrg');if(u){$('fbUrgt').textContent=u;ug.style.display='flex';}else{ug.style.display='none';}$('fbCta').textContent=r.cta.replace(/\{C\}/g,c);}
 window.fbSend=function(){var ph=$('fbPhone').value.trim();if(ph.replace(/\D/g,'').length<7){$('fbErr').style.display='block';return;}$('fbErr').style.display='none';var r=READY[S.ready]||{cta:'help me'};if(window.postLead)postLead('',ph,S.country||'','lp-v2 appointment');var lines=['Hi Beyond Passports.'];if(S.country)lines.push('Destination: '+S.country);if(S.timing)lines.push('Travelling: '+(TIMLBL[S.timing]||''));if(S.ready)lines.push('Documents: '+(READYLBL[S.ready]||''));lines.push('Please '+r.cta.replace(/\{C\}/g,S.country||'my destination').toLowerCase()+'.');lines.push('Number: '+ph);var url=(window.bpWaUrl||String)('https://wa.me/'+WA+'?text='+encodeURIComponent(lines.join('\n')));fbShow(4);window.open(url,'_blank');};
 ov.addEventListener('click',function(e){if(e.target===ov)fbClose();});
})();


(function(){
 var WA='447882747584',TXT='Hi%20Beyond%20Passports%2C%20I%20would%20like%20help%20with%20my%20Schengen%20visa.';
 var TEAM=[
  {n:'Sarah, consultant',i:'https://beyondpassports.co.uk/assets/img/team/lena-vogel-germany-lead.jpg?v=3'},
  {n:'James, specialist',i:'https://beyondpassports.co.uk/assets/img/team/james-whitfield-operations.jpg'},
  {n:'Zayla, coordinator',i:'https://beyondpassports.co.uk/assets/img/team/zayla-ferreira-appointments.jpg?v=3'}
 ];
 var el=document.getElementById('wafab');if(!el)return;
 var av=document.getElementById('wafabav'),nm=document.getElementById('wafabnm'),stt=document.getElementById('wafabstt'),bubble=document.getElementById('wafabbubble'),card=document.getElementById('wafabcard');
 card.href=(window.bpWaUrl||String)('https://wa.me/'+WA+'?text='+TXT);
 function render(){
  var d=new Date(),idx=Math.floor(d.getTime()/1000/60/30)%TEAM.length,t=TEAM[idx];
  if(av.getAttribute('data-i')!==t.i){av.src=t.i;av.setAttribute('data-i',t.i);av.alt=t.n;}
  nm.textContent=t.n;
  var first=t.n.split(',')[0].trim();
  bubble.innerHTML='<b>Hi, I\'m '+first+'.</b> How can I assist you?';
  var hr,dayName;try{hr=parseInt(new Intl.DateTimeFormat('en-GB',{timeZone:'Europe/London',hour:'2-digit',hour12:false}).format(d),10)%24;dayName=new Intl.DateTimeFormat('en-GB',{timeZone:'Europe/London',weekday:'short'}).format(d);}catch(e){hr=d.getHours();dayName=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][d.getDay()];}
  var open=(hr>=7&&hr<23);
  if(open){el.classList.remove('off');stt.textContent='Online now';}
  else{el.classList.add('off');stt.textContent='Away';}
 }
 try{if(localStorage.getItem('wafab_min')==='1')el.classList.add('mini');}catch(e){}
 document.getElementById('wafabx').addEventListener('click',function(e){e.preventDefault();e.stopPropagation();el.classList.add('mini');try{localStorage.setItem('wafab_min','1');}catch(e){}});
 card.addEventListener('click',function(e){if(el.classList.contains('mini')){e.preventDefault();el.classList.remove('mini');try{localStorage.setItem('wafab_min','0');}catch(e){}}});
 function tick(){el.classList.toggle('hide',!!document.querySelector('.mov.open'));render();}
 el.hidden=false;tick();setInterval(tick,20000);
 var mov=document.getElementById('mov');if(mov){new MutationObserver(tick).observe(mov,{attributes:true,attributeFilter:['class']});}
})();


(function(){
  var dform=document.querySelector('#fhero .dform');if(!dform)return;
  var card=dform.querySelector('.card');
  var mq=window.matchMedia('(max-width:760px)');
  var close=document.createElement('button');
  close.className='mx df-close';close.setAttribute('type','button');close.setAttribute('aria-label','Close');
  close.textContent='✕';
  var htr=dform.querySelector('.htoprow');(htr||card).appendChild(close);
  function activeStep(){var s=dform.querySelector('.step.active');return s?parseInt(s.getAttribute('data-n'),10):1;}
  function sync(){
    if(!mq.matches){dform.classList.remove('as-modal');document.body.classList.remove('mlock');return;}
    if(activeStep()>=2){dform.classList.add('as-modal');document.body.classList.add('mlock');}
    else{dform.classList.remove('as-modal');document.body.classList.remove('mlock');}
  }
  close.addEventListener('click',function(){
    var back=dform.querySelector('.back');var guard=0;
    (function step(){if(activeStep()>1&&back&&guard<8){guard++;back.click();setTimeout(step,10);}else{sync();}})();
  });
  var mo=new MutationObserver(function(){setTimeout(sync,0)});
  dform.querySelectorAll('.step').forEach(function(s){mo.observe(s,{attributes:true,attributeFilter:['class']})});
  dform.addEventListener('click',function(){setTimeout(sync,30)},true);
  (mq.addEventListener?mq.addEventListener('change',sync):mq.addListener(sync));
  sync();
})();
