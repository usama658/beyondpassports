{{-- Self-contained site footer for the STANDALONE landing pages (lp-*.blade.php). Mirrors the
     canonical partials.site-footer exactly (columns, Trustpilot, newsletter opt-in, social row,
     legal bottom) but every class is prefixed `bpc-` so it never collides with ukv.css or an lp
     page's own inline styles. See partials.lp-chrome for the matching topbar+header. --}}
@php
  $bpcSocial = array_filter(config('ukv.social', []));
  $bpcSocialIcons = [
    'facebook'  => '<path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.7l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/>',
    'instagram' => '<path d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.5-.1 4.8c-.1 3.2-1.6 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.9-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.1 15.5 2.1 15.2 2.1 12s0-3.5.1-4.8C2.3 4 3.8 2.4 7.1 2.3 8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 1 0 18.6 12 6.6 6.6 0 0 0 12 5.4zm0 10.9A4.3 4.3 0 1 1 16.3 12 4.3 4.3 0 0 1 12 16.3zm6.8-11.1a1.5 1.5 0 1 0 1.5 1.5 1.5 1.5 0 0 0-1.5-1.5z"/>',
    'tiktok'    => '<path d="M16.5 3c.3 2 1.5 3.6 3.5 3.9v2.6c-1.3 0-2.5-.4-3.5-1v6.1a5.6 5.6 0 1 1-5.6-5.6c.3 0 .6 0 .9.1v2.7a2.9 2.9 0 1 0 2 2.8V3z"/>',
    'youtube'   => '<path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 0 0-1.7-1.7C19.3 5.2 12 5.2 12 5.2s-7.3 0-8.9.4A2.5 2.5 0 0 0 1.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 0 0 1.7 1.7c1.6.4 8.9.4 8.9.4s7.3 0 8.9-.4a2.5 2.5 0 0 0 1.7-1.7C23 15.2 23 12 23 12zM9.8 15V9l5.2 3z"/>',
    'linkedin'  => '<path d="M6.9 8.5v11.6H3.2V8.5zM5 3.1a2.1 2.1 0 1 1 0 4.3 2.1 2.1 0 0 1 0-4.3zM9.2 8.5h3.5v1.6h.1a3.9 3.9 0 0 1 3.5-1.9c3.7 0 4.4 2.4 4.4 5.6v6.3h-3.7v-5.6c0-1.3 0-3-1.9-3s-2.1 1.4-2.1 2.9v5.7H9.2z"/>',
    'pinterest' => '<path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.4 1.8-2.4.9 0 1.3.7 1.3 1.5 0 .9-.6 2.2-.9 3.5-.2 1 .5 1.9 1.6 1.9 1.9 0 3.2-2.4 3.2-5.3 0-2.2-1.5-3.8-4.1-3.8a4.7 4.7 0 0 0-4.9 4.7c0 .9.3 1.5.7 2 .2.2.2.3.1.5l-.2.9c-.1.3-.3.4-.5.2-1.1-.5-1.7-1.9-1.7-3.1 0-2.5 2.1-5.5 6.3-5.5 3.4 0 5.6 2.4 5.6 5 0 3.4-1.9 6-4.7 6a2.5 2.5 0 0 1-2.1-1.1l-.6 2.2c-.2.7-.6 1.5-1 2.1A10 10 0 1 0 12 2z"/>',
    'reddit'    => '<path d="M22 12.1a2.1 2.1 0 0 0-3.6-1.5 10.3 10.3 0 0 0-5.3-1.7l.9-4.2 2.9.6a1.5 1.5 0 1 0 .2-1l-3.3-.7a.5.5 0 0 0-.6.4l-1 4.6a10.4 10.4 0 0 0-5.4 1.7A2.1 2.1 0 1 0 3.6 14a4 4 0 0 0 0 .6c0 3 3.6 5.5 8.1 5.5s8.1-2.5 8.1-5.5a4 4 0 0 0 0-.6 2.1 2.1 0 0 0 1.2-1.9zM7.5 13.5a1.3 1.3 0 1 1 2.6 0 1.3 1.3 0 0 1-2.6 0zm7.2 3.6a4.6 4.6 0 0 1-5.4 0 .4.4 0 1 1 .5-.6 3.8 3.8 0 0 0 4.4 0 .4.4 0 0 1 .6.6zm-.3-2.3a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6z"/>',
    'quora'     => '<path d="M13.6 18.36c-.5.12-1.02.18-1.56.18-3.09 0-6.26-2.48-6.26-6.61 0-4.16 3.17-6.65 6.26-6.65 3.13 0 6.29 2.47 6.29 6.65 0 2.33-.99 4.18-2.49 5.32.48.72.98 1.2 1.69 1.2.78 0 1.1-.6 1.15-1.07h1.38c.06.63-.25 3.09-2.91 3.09-1.61 0-2.46-.93-3.09-2.11zm-.86-1.77c-.46-.91-1-1.83-2.1-1.83-.21 0-.42.03-.62.1l-.39-.79c.47-.4 1.24-.71 2.23-.71 1.54 0 2.33.74 2.96 1.69.39-.85.57-1.95.57-3.32 0-3.43-1.07-5.18-3.57-5.18-2.46 0-3.52 1.75-3.52 5.18 0 3.41 1.06 5.15 3.52 5.15.31 0 .6-.03.87-.09z"/>',
  ];
  $bpcAddr = config('ukv.address');
@endphp
<footer class="bpc-ft">
  <div class="bpc-consultband"><div class="bpc-wrap">
    <span>Prefer to talk it through first? It's free, no obligation.</span>
    <a href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my free consultation.') }}" target="_blank" rel="noopener" class="bpc-consultbtn">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>Book a free consultation</a>
  </div></div>
  <div class="bpc-ftmain"><div class="bpc-wrap">
    <div class="bpc-cols">
      <div class="bpc-col-brand">
        <a href="{{ url('/') }}" class="bpc-ftbrand" aria-label="Beyond Passports home"><img src="{{ asset('assets/brand/bp-logo-v2-reversed.svg') }}" alt="Beyond Passports" width="170" height="44"></a>
        <p class="bpc-ftlede">Independent UK-based visa &amp; eVisa facilitation for travel abroad, since {{ App\Support\SiteStats::foundedYear() }}. Not a government website.</p>
        @include('partials.trustpilot-cta', ['align' => 'left', 'theme' => 'dark', 'margin' => '14px 0 4px'])
        @if ($bpcAddr && !empty($bpcAddr['line1']))
        <p class="bpc-ftaddr">
          {{ $bpcAddr['company'] ?? 'Beyond Passports Ltd' }}<br>
          {{ $bpcAddr['line1'] }}@if(!empty($bpcAddr['line2'])), {{ $bpcAddr['line2'] }}@endif<br>
          {{ $bpcAddr['city'] ?? '' }} {{ $bpcAddr['postcode'] ?? '' }}@if(!empty($bpcAddr['company_no']))<br>Company no. {{ $bpcAddr['company_no'] }}@endif
        </p>
        @endif
        <form method="POST" action="{{ route('subscribe.store') }}">
          @csrf
          <div class="bpc-ftcap">
            <label for="bpc-sub-email" class="bpc-mrz">Your email</label>
            <input id="bpc-sub-email" type="email" name="email" placeholder="Get visa-rule updates by email" value="{{ old('email') }}" required>
            <button class="bpc-btn" type="submit">Join</button>
          </div>
          <label class="bpc-ftconsent"><input type="checkbox" name="consent" value="1" required> <span>I agree to receive occasional email updates. <a href="{{ url('/legal') }}#privacy">Privacy notice</a>. Unsubscribe any time.</span></label>
          @if (session('subscribe_status'))<p class="bpc-ftok">{{ session('subscribe_status') }}</p>@endif
          @error('email')<p class="bpc-fterr">{{ $message }}</p>@enderror
          @error('consent')<p class="bpc-fterr">{{ $message }}</p>@enderror
        </form>
        @if (! empty($bpcSocial))
        <p class="bpc-ftsoclab">Follow us</p>
        <div class="bpc-ftsoc">
          @foreach ($bpcSocial as $k => $url)
            <a class="bpc-soc" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($k) }}">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">{!! $bpcSocialIcons[$k] ?? '' !!}</svg>
            </a>
          @endforeach
        </div>
        @endif
      </div>
      <div class="bpc-col">
        <strong>Service</strong>
        <a href="{{ url('/services') }}">All services</a>
        <a href="{{ url('/destinations') }}">Schengen visa</a>
        <a href="{{ App\Support\SiteStats::chatUrl() }}" target="_blank" rel="noopener">Check eligibility →</a>
        <a href="{{ url('/track') }}">Track application</a>
      </div>
      <div class="bpc-col">
        <strong>Free tools &amp; guides</strong>
        <a href="{{ url('/tools') }}">Visa checker</a>
        <a href="{{ url('/document-checklist') }}">Document checker</a>
        <a href="{{ url('/find-a-centre') }}">Find a centre</a>
        <a href="{{ url('/guides') }}">Visa guides &amp; stories</a>
        <a href="{{ url('/reviews') }}">Traveller reviews</a>
        <a href="{{ url('/compare') }}">Apply yourself vs us</a>
      </div>
      <div class="bpc-col">
        <strong>Company &amp; legal</strong>
        <a href="{{ url('/about') }}">Who we are</a>
        <a href="{{ url('/contact') }}">Contact</a>
        <a href="{{ url('/legal') }}#privacy">Privacy</a>
        <a href="{{ url('/legal') }}#terms">Terms</a>
        <a href="{{ url('/legal') }}#complaints">Complaints</a>
        <a href="{{ url('/legal') }}#disclaimer">Disclaimer</a>
      </div>
    </div>
    <div class="bpc-ftbottom">
      <span>© Beyond Passports. Independent, not the government. Our fee is separate from the visa fee. Approval is never guaranteed.</span>
      @include('partials.uk-eu-flags',['size'=>14]) <span>Registered in UK &amp; Europe</span>
    </div>
  </div></div>
</footer>
@once
<style>
  /* --- Beyond Passports chrome footer (self-contained; mirrors home footer in ukv.css) --- */
  .bpc-ft{--bpc-cta:#155E7A;--display:"Outfit",system-ui,sans-serif;--ink:#16222E;--ink-soft:#5d6b76;--paper-edge:#dde3ec;
    font-family:"Outfit",system-ui,sans-serif;background:#1b2024;color:#aab0b5;font-size:14px;line-height:1.6;box-sizing:border-box}
  .bpc-ft *{box-sizing:border-box}
  .bpc-ft .bpc-wrap{max-width:1100px;margin:0 auto;padding:0 24px}
  .bpc-cols{display:grid;grid-template-columns:2.4fr 1fr 1fr 1fr;gap:40px;padding:40px 0 26px;align-items:start}
  .bpc-ftbrand{display:inline-block;line-height:0;margin-bottom:12px}
  .bpc-ftbrand img{display:block;height:44px;width:auto}
  .bpc-ftlede{max-width:30ch;margin:0}
  .bpc-ftaddr{max-width:30ch;font-size:13px;line-height:1.55;color:#c7cfd6;margin-top:12px}
  .bpc-col strong,.bpc-col-brand strong{display:block;color:#fff;margin-bottom:6px;font-size:15px}
  .bpc-col a{display:block;padding:4px 0;color:#d3d7da;text-decoration:none}
  .bpc-col a:hover{color:#fff}
  /* newsletter opt-in */
  .bpc-mrz{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}
  .bpc-ftcap{display:flex;gap:8px;margin-top:14px;max-width:340px}
  .bpc-ftcap input[type=email]{flex:1;padding:11px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.06);color:#fff;font:inherit;font-size:14px}
  .bpc-ftcap input::placeholder{color:#8d949a}
  .bpc-ftcap .bpc-btn{display:inline-block;background:var(--bpc-cta);color:#fff;font-weight:700;padding:11px 16px;border-radius:10px;border:0;cursor:pointer;font-size:14px;font-family:inherit;white-space:nowrap}
  .bpc-ftconsent{display:flex;gap:8px;align-items:flex-start;margin-top:10px;font-size:12px;color:#9aa0a5;max-width:480px}
  .bpc-ftconsent input{margin-top:2px}
  .bpc-ftconsent a{color:#d3d7da;text-decoration:underline}
  .bpc-ftok{margin-top:10px;font-size:13px;color:#9fe0c0;font-weight:700}
  .bpc-fterr{margin-top:8px;font-size:12.5px;color:#f0b3a0}
  /* social */
  .bpc-ftsoclab{font-size:13px;color:#c7cfd6;margin:18px 0 8px;max-width:34ch;line-height:1.5}
  .bpc-ftsoc{display:flex;flex-wrap:wrap;gap:10px;margin:0 0 4px}
  .bpc-soc{display:inline-flex;width:36px;height:36px;align-items:center;justify-content:center;border-radius:9px;
    background:rgba(255,255,255,.1);color:#fff;transition:background .18s ease,transform .18s ease}
  .bpc-soc:hover{background:var(--bpc-cta);color:#fff;transform:translateY(-2px)}
  .bpc-soc svg{display:block}
  /* bottom bar */
  .bpc-ftbottom{border-top:1px solid rgba(255,255,255,.12);padding:16px 0;display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;font-size:12.5px;color:#8d949a}
  @media (max-width:860px){.bpc-cols{grid-template-columns:1fr 1fr}}
  @media (max-width:520px){.bpc-cols{grid-template-columns:1fr}}
  /* free-consultation band */
  .bpc-consultband{background:#141a1e;border-bottom:1px solid rgba(255,255,255,.08);padding:16px 0}
  .bpc-consultband .bpc-wrap{display:flex;gap:14px;align-items:center;justify-content:center;flex-wrap:wrap}
  .bpc-consultband span{color:#c7cfd6;font-size:15px}
  .bpc-consultbtn{display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:11px 18px;border-radius:11px;transition:background .15s,transform .08s}
  .bpc-consultbtn:hover{background:#1da851;transform:translateY(-1px)}
  .bpc-consultbtn svg{width:18px;height:18px;fill:currentColor;flex:none}
</style>
@endonce
