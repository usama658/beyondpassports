{{-- Progressive enhancement: on mobile, upgrade wa.me link clicks to the native
     whatsapp:// app scheme (which opens the app inside ad in-app browsers where the
     https handoff silently fails), with the original wa.me link as fallback.
     Pure enhancement: desktop is untouched, and if anything cannot be parsed the
     default link fires unchanged. Cannot make existing behaviour worse. --}}
@once
<script>
(function () {
  var ua = navigator.userAgent || '';
  var isMobile = /Android|iPhone|iPad|iPod/i.test(ua);
  if (!isMobile) return; // desktop keeps the normal wa.me / web.whatsapp flow

  document.addEventListener('click', function (e) {
    var a = e.target && e.target.closest ? e.target.closest('a[href*="wa.me/"]') : null;
    if (!a) return;

    var href = a.getAttribute('href') || '';
    var m = href.match(/wa\.me\/(\d+)(?:\?text=([^#]*))?/i);
    if (!m) return; // unparseable: let the default link fire

    var phone = m[1];
    var text = '';
    if (m[2]) { try { text = decodeURIComponent(m[2].replace(/\+/g, '%20')); } catch (_) { text = ''; } }
    var deep = 'whatsapp://send?phone=' + phone + (text ? '&text=' + encodeURIComponent(text) : '');

    e.preventDefault();

    // Fall back to the original https link if the app does not take over.
    var fell = false;
    var timer = setTimeout(function () { fell = true; window.location.href = href; }, 1400);
    // If the app opens, the page is backgrounded: cancel the fallback.
    var onHide = function () { if (!fell) { clearTimeout(timer); } };
    document.addEventListener('visibilitychange', onHide, { once: true });
    window.addEventListener('pagehide', onHide, { once: true });

    try { window.location.href = deep; } catch (_) { clearTimeout(timer); window.location.href = href; }
  }, false);
})();
</script>
@endonce
