{{-- GTM interaction tracking: pushes a structured event to dataLayer for every CTA
     click and form submit, tagged with the current page. GTM builds triggers/tags off
     these custom events (event names: 'cta_click', 'form_submit').

     dataLayer is a plain JS array — pushing to it makes NO network call. GTM itself only
     loads after cookie consent (see cookie-consent partial), so nothing leaves the browser
     until consent is granted; pre-consent pushes simply buffer and are read (or discarded)
     by GTM when/if it loads. PECR-safe by construction — no markup changes needed on CTAs.

     Page context on every event:
       page_key    route name (e.g. 'home', 'destinations.show') or path fallback
       page_path   pathname
       page_title  document.title

     A CTA is any <a>/<button> matching a btn*/cta* class, a WhatsApp/tel/mailto link, or
     an element carrying data-cta. To label a CTA explicitly, add data-cta="name" in markup;
     otherwise the trimmed text is used. --}}
@once
<script>
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
    var data = base('cta_click');
    data.cta_kind    = kind;
    data.cta_text    = clean(el.getAttribute('data-cta') || el.innerText || el.getAttribute('aria-label') || el.value);
    data.cta_id      = el.id || '';
    data.cta_href    = (el.getAttribute && el.getAttribute('href')) || '';
    data.cta_section = section ? (section.id || section.getAttribute('data-section') || '') : '';
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
</script>
@endonce
