{{-- Canonical site footer. Single source of truth — included by layouts.public
     AND by the standalone pages (track, checklist-result). Styling lives in
     assets/ukv.css (.ft-main / .cols / .ft-bottom etc.). --}}
<footer style="padding:0">
  {{-- (CTA band removed — every page already ends with a .cta-band above the footer) --}}
  <div class="ft-main"><div class="wrap">
    <div class="cols">
      <div>
        <div class="brand" style="color:#fff">Beyond <b>Passports</b></div>
        <p style="max-width:30ch">Independent UK-based visa &amp; eVisa facilitation for travel abroad. Not a government website.</p>
        @include('partials.trustpilot', ['template' => 'micro', 'align' => 'left', 'margin' => '14px 0 4px'])
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
          ];
        @endphp
        @if (! empty($social))
        <div class="ft-social" style="display:flex;gap:12px;margin:14px 0 4px">
          @foreach ($social as $k => $url)
            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($k) }}" style="display:inline-flex;width:34px;height:34px;align-items:center;justify-content:center;border-radius:9px;background:rgba(255,255,255,.1);color:#fff">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">{!! $socialIcons[$k] ?? '' !!}</svg>
            </a>
          @endforeach
        </div>
        @endif
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
          @if (session('subscribe_status'))<p class="ft-ok">{{ session('subscribe_status') }}</p>@endif
          @error('email')<p class="ft-err">{{ $message }}</p>@enderror
          @error('consent')<p class="ft-err">{{ $message }}</p>@enderror
        </form>
      </div>
      <div>
        <strong>Service</strong>
        <a href="{{ url('/destinations') }}">Destinations</a>
        <a href="{{ url('/tools') }}">Visa checker</a>
        <a href="{{ url('/apply') }}">Start an application</a>
        <a href="{{ url('/track') }}">Track application</a>
      </div>
      <div>
        <strong>Guides</strong>
        <a href="{{ url('/guides') }}">Visa guides &amp; stories</a>
        <a href="{{ url('/reviews') }}">Traveller reviews</a>
        <a href="{{ url('/driving-abroad') }}">Driving abroad (IDP)</a>
        <a href="{{ url('/compare') }}">Apply yourself vs us</a>
      </div>
      <div>
        <strong>Company &amp; legal</strong>
        <a href="{{ url('/about') }}">About us</a>
        <a href="{{ url('/contact') }}">Contact</a>
        <a href="{{ url('/legal') }}#privacy">Privacy</a>
        <a href="{{ url('/legal') }}#terms">Terms</a>
        <a href="{{ url('/legal') }}#complaints">Complaints</a>
        <a href="{{ url('/legal') }}#disclaimer">Disclaimer</a>
      </div>
    </div>
    <div class="ft-bottom">
      <span>© Beyond Passports. Service fee separate from any government fee. Express speeds our handling, not the government’s decision. No approval guarantee.</span>
      <span>UK-based team</span>
    </div>
  </div></div>
</footer>
@include('partials.cookie-consent')
