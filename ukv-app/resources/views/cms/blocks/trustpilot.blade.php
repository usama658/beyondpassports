{{-- Trustpilot block. Renders the existing trustpilot-cta partial verbatim (rating from config,
     consent-gated). Only theme + alignment are selectable; internals stay coded. --}}
<section style="padding:14px 0"><div class="wrap" style="text-align:{{ ($data['align'] ?? 'center') === 'left' ? 'left' : ((($data['align'] ?? '') === 'right') ? 'right' : 'center') }}">
  @include('partials.trustpilot-cta', [
    'align' => $data['align'] ?? 'center',
    'theme' => ($data['theme'] ?? 'light') === 'dark' ? 'dark' : 'light',
    'margin' => '0',
  ])
</div></section>
