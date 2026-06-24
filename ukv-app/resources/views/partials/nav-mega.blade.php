{{--
  Reusable nav mega-menu (dropdown panel of titled links with one-line descriptions).
  Use for any top-nav dropdown so the markup + behaviour stay consistent.

  Params:
    $label (string)  — the nav item label, e.g. 'Tools'
    $items (array)   — list of ['title' => ..., 'desc' => ..., 'url' => ...]

  Example:
    @include('partials.nav-mega', ['label' => 'Tools', 'items' => [
      ['title' => 'Visa checker', 'desc' => 'Tell us your trip and we confirm what you need.', 'url' => '/tools'],
    ]])
--}}
<details class="mega">
  <summary class="mlink" role="button" aria-haspopup="true">{{ $label }} <span class="ch" aria-hidden="true">▾</span></summary>
  <div class="mega-panel"><div class="wrap">
    <div class="mega-list">
      @foreach ($items as $it)
        <a class="mega-item" href="{{ url($it['url']) }}"><b>{{ $it['title'] }}</b><span>{{ $it['desc'] }}</span></a>
      @endforeach
    </div>
  </div></div>
</details>
