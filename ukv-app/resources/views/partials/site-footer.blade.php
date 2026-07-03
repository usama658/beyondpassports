{{-- Canonical site footer. Single source of truth — included by layouts.public
     AND by the standalone pages (track, checklist-result). Styling lives in
     assets/ukv.css (.ft-main / .cols / .ft-bottom etc.). --}}
<footer style="padding:0">
  {{-- (CTA band removed — every page already ends with a .cta-band above the footer) --}}
  <div class="ft-main"><div class="wrap">
    <div class="cols">
      <div>
        <a href="{{ url('/') }}" class="brand" style="display:inline-block" aria-label="Beyond Passports home"><img src="{{ asset('assets/brand/bp-logo-v2-reversed.svg') }}" alt="Beyond Passports" width="170" height="44" style="display:block;height:44px;width:auto"></a>
        <p style="max-width:30ch">Independent UK-based visa &amp; eVisa facilitation for travel abroad, since {{ App\Support\SiteStats::foundedYear() }}. Not a government website.</p>
        @include('partials.trustpilot-cta', ['align' => 'left', 'theme' => 'dark', 'margin' => '14px 0 4px'])
        @php
          $social = array_filter(config('ukv.social', []));
          $socialIcons = [
            'facebook'  => '<path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.7l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/>',
            'instagram' => '<path d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.5-.1 4.8c-.1 3.2-1.6 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.9-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.1 15.5 2.1 15.2 2.1 12s0-3.5.1-4.8C2.3 4 3.8 2.4 7.1 2.3 8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 1 0 18.6 12 6.6 6.6 0 0 0 12 5.4zm0 10.9A4.3 4.3 0 1 1 16.3 12 4.3 4.3 0 0 1 12 16.3zm6.8-11.1a1.5 1.5 0 1 0 1.5 1.5 1.5 1.5 0 0 0-1.5-1.5z"/>',
            'tiktok'    => '<path d="M16.5 3c.3 2 1.5 3.6 3.5 3.9v2.6c-1.3 0-2.5-.4-3.5-1v6.1a5.6 5.6 0 1 1-5.6-5.6c.3 0 .6 0 .9.1v2.7a2.9 2.9 0 1 0 2 2.8V3z"/>',
            'youtube'   => '<path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 0 0-1.7-1.7C19.3 5.2 12 5.2 12 5.2s-7.3 0-8.9.4A2.5 2.5 0 0 0 1.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 0 0 1.7 1.7c1.6.4 8.9.4 8.9.4s7.3 0 8.9-.4a2.5 2.5 0 0 0 1.7-1.7C23 15.2 23 12 23 12zM9.8 15V9l5.2 3z"/>',
            'linkedin'  => '<path d="M6.9 8.5v11.6H3.2V8.5zM5 3.1a2.1 2.1 0 1 1 0 4.3 2.1 2.1 0 0 1 0-4.3zM9.2 8.5h3.5v1.6h.1a3.9 3.9 0 0 1 3.5-1.9c3.7 0 4.4 2.4 4.4 5.6v6.3h-3.7v-5.6c0-1.3 0-3-1.9-3s-2.1 1.4-2.1 2.9v5.7H9.2z"/>',
            'pinterest' => '<path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.4 1.8-2.4.9 0 1.3.7 1.3 1.5 0 .9-.6 2.2-.9 3.5-.2 1 .5 1.9 1.6 1.9 1.9 0 3.2-2.4 3.2-5.3 0-2.2-1.5-3.8-4.1-3.8a4.7 4.7 0 0 0-4.9 4.7c0 .9.3 1.5.7 2 .2.2.2.3.1.5l-.2.9c-.1.3-.3.4-.5.2-1.1-.5-1.7-1.9-1.7-3.1 0-2.5 2.1-5.5 6.3-5.5 3.4 0 5.6 2.4 5.6 5 0 3.4-1.9 6-4.7 6a2.5 2.5 0 0 1-2.1-1.1l-.6 2.2c-.2.7-.6 1.5-1 2.1A10 10 0 1 0 12 2z"/>',
            'reddit'    => '<path d="M22 12.1a2.1 2.1 0 0 0-3.6-1.5 10.3 10.3 0 0 0-5.3-1.7l.9-4.2 2.9.6a1.5 1.5 0 1 0 .2-1l-3.3-.7a.5.5 0 0 0-.6.4l-1 4.6a10.4 10.4 0 0 0-5.4 1.7A2.1 2.1 0 1 0 3.6 14a4 4 0 0 0 0 .6c0 3 3.6 5.5 8.1 5.5s8.1-2.5 8.1-5.5a4 4 0 0 0 0-.6 2.1 2.1 0 0 0 1.2-1.9zM7.5 13.5a1.3 1.3 0 1 1 2.6 0 1.3 1.3 0 0 1-2.6 0zm7.2 3.6a4.6 4.6 0 0 1-5.4 0 .4.4 0 1 1 .5-.6 3.8 3.8 0 0 0 4.4 0 .4.4 0 0 1 .6.6zm-.3-2.3a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6z"/>',
            'quora'     => '<path d="M13.6 18.36c-.5.12-1.02.18-1.56.18-3.09 0-6.26-2.48-6.26-6.61 0-4.16 3.17-6.65 6.26-6.65 3.13 0 6.29 2.47 6.29 6.65 0 2.33-.99 4.18-2.49 5.32.48.72.98 1.2 1.69 1.2.78 0 1.1-.6 1.15-1.07h1.38c.06.63-.25 3.09-2.91 3.09-1.61 0-2.46-.93-3.09-2.11zm-.86-1.77c-.46-.91-1-1.83-2.1-1.83-.21 0-.42.03-.62.1l-.39-.79c.47-.4 1.24-.71 2.23-.71 1.54 0 2.33.74 2.96 1.69.39-.85.57-1.95.57-3.32 0-3.43-1.07-5.18-3.57-5.18-2.46 0-3.52 1.75-3.52 5.18 0 3.41 1.06 5.15 3.52 5.15.31 0 .6-.03.87-.09z"/>',
          ];
        @endphp
        @php $addr = config('ukv.address'); @endphp
        @if ($addr && !empty($addr['line1']))
        <p style="max-width:30ch;font-size:13px;line-height:1.55;color:#c7cfd6;margin-top:12px">
          {{ $addr['company'] ?? 'Beyond Passports Ltd' }}<br>
          {{ $addr['line1'] }}@if(!empty($addr['line2'])), {{ $addr['line2'] }}@endif<br>
          {{ $addr['city'] ?? '' }} {{ $addr['postcode'] ?? '' }}@if(!empty($addr['company_no']))<br>Company no. {{ $addr['company_no'] }}@endif
        </p>
        @endif
        {{-- Consent-gated newsletter opt-in (works with no JS; flashes status on return) --}}
        <form method="POST" action="{{ route('subscribe.store') }}">
          @csrf
          <div class="ft-cap">
            <label for="sub-email" class="mrz">Your email</label>
            <input id="sub-email" type="email" name="email" placeholder="Get visa-rule updates by email" value="{{ old('email') }}" required>
            <button class="btn" type="submit" style="padding:11px 16px">Join</button>
          </div>
          <label class="ft-consent"><input type="checkbox" name="consent" value="1" required> <span>I agree to receive occasional email updates. <a href="{{ url('/legal') }}#privacy">Privacy notice</a>. Unsubscribe any time.</span></label>
          @if (session('subscribe_status'))<p class="ft-ok">{{ session('subscribe_status') }}</p>@include('partials.track-event', ['teEvent' => 'Subscribe', 'teGa' => 'sign_up'])@endif
          @error('email')<p class="ft-err">{{ $message }}</p>@enderror
          @error('consent')<p class="ft-err">{{ $message }}</p>@enderror
        </form>
        @if (! empty($social))
        <p class="ft-social-lab">@if (session('subscribe_status'))You're subscribed — follow us too, that's where time-sensitive updates land first.@else Follow us @endif</p>
        <div class="ft-social">
          @foreach ($social as $k => $url)
            <a class="ft-soc" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($k) }}">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">{!! $socialIcons[$k] ?? '' !!}</svg>
            </a>
          @endforeach
        </div>
        @once
        <style>
          .ft-social-lab{font-size:13px;color:#c7cfd6;margin:18px 0 8px;max-width:34ch;line-height:1.5}
          .ft-main .cols .ft-social{display:flex;flex-wrap:wrap;gap:10px;margin:0 0 4px}
          /* higher specificity than ".ft-main .cols a{display:block;padding}" in ukv.css so icons stay flex-centered boxes */
          .ft-main .cols a.ft-soc{display:inline-flex;width:36px;height:36px;padding:0;align-items:center;justify-content:center;border-radius:9px;
            background:rgba(255,255,255,.1);color:#fff;transition:background .18s ease,transform .18s ease,box-shadow .18s ease}
          .ft-main .cols a.ft-soc:hover{background:var(--cta);color:#fff;transform:translateY(-2px);box-shadow:0 10px 22px -10px rgba(21,94,122,.8)}
          .ft-main .cols a.ft-soc:focus-visible{outline:2px solid var(--soft);outline-offset:2px}
          .ft-main .cols a.ft-soc svg{display:block}
        </style>
        @endonce
        @endif
      </div>
      <div>
        <strong>Service</strong>
        <a href="{{ url('/services') }}">All services</a>
        <a href="{{ url('/tour-packages') }}">{{ config('ukv.tours.nav_label', 'Plan a trip') }}</a>
        <a href="{{ url('/destinations') }}">Schengen visa</a>
        <a href="{{ App\Support\SiteStats::chatUrl() }}" target="_blank" rel="noopener">Check eligibility →</a>
        <a href="{{ url('/track') }}">Track application</a>
      </div>
      <div>
        <strong>Free tools &amp; guides</strong>
        <a href="{{ url('/tools') }}">Visa checker</a>
        <a href="{{ url('/document-checklist') }}">Document checker</a>
        <a href="{{ url('/find-a-centre') }}">Find a centre</a>
        <a href="{{ url('/guides') }}">Visa guides &amp; stories</a>
        <a href="{{ url('/reviews') }}">Traveller reviews</a>
        <a href="{{ url('/compare') }}">Apply yourself vs us</a>
      </div>
      <div>
        <strong>Company &amp; legal</strong>
        <a href="{{ url('/about') }}">Who we are</a>
        <a href="{{ url('/contact') }}">Contact</a>
        <a href="{{ url('/legal') }}#privacy">Privacy</a>
        <a href="{{ url('/legal') }}#terms">Terms</a>
        <a href="{{ url('/legal') }}#complaints">Complaints</a>
        <a href="{{ url('/legal') }}#disclaimer">Disclaimer</a>
      </div>
    </div>
    <div class="ft-bottom">
      <span>© Beyond Passports. Service fee separate from any government fee. Express speeds our handling, not the government’s decision. No approval guarantee.</span>
      <span>Registered in UK &amp; Germany</span>
    </div>
  </div></div>
</footer>
@include('partials.cookie-consent')
