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
