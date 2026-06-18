{{-- Canonical front-of-site behaviour: reveal-on-scroll + mega-menu/mobile nav.
     Shared by layouts.public and the standalone pages so the header dropdowns and
     .reveal content animate identically everywhere. --}}
<script>
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
</script>

<script>
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
</script>
