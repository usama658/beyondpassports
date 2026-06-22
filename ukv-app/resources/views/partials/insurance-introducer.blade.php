{{--
    Travel-insurance INTRODUCER (FCA-safe signpost, NOT a product Beyond Passports sells/arranges).

    Beyond Passports is not an insurer and takes no charge here. This is an optional introduction to an
    FCA-authorised travel-insurance partner. If no partner is configured, we show a neutral
    "ask us" note instead of a dead link. Never invent a provider or imply we arrange cover.

    Styling is self-contained (scoped, inline) so this renders correctly both inside the
    standalone confirmation.blade.php HTML document and within the design-system guides page.

    Optional variable:
      $compact  bool  slightly tighter spacing for in-article placement (default false)
--}}
@php
    $partnerName = trim((string) config('ukv.insurance_partner_name', ''));
    $partnerUrl  = trim((string) config('ukv.insurance_partner_url', ''));
    $hasPartner  = $partnerName !== '' && $partnerUrl !== '';
    // Used only in the disclosure sentence; falls back to a neutral phrase when unconfigured.
    $partnerLabel = $partnerName !== '' ? $partnerName : 'an FCA-authorised travel-insurance provider';
    $compact = $compact ?? false;
@endphp
<aside class="ukv-ins" role="complementary" aria-label="Travel insurance introduction"
       style="border:1px solid #dde3ec;border-left:4px solid #155E7A;border-radius:12px;
              background:#ffffff;padding:{{ $compact ? '20px 22px' : '24px 26px' }};
              margin:{{ $compact ? '1.6em 0' : '28px 0 0' }};
              box-shadow:0 8px 30px rgba(40,50,70,.08);
              font-family:"Outfit",system-ui,sans-serif;
              color:#16222E;line-height:1.6">
    <p style="font-family:"Outfit",system-ui,sans-serif;font-weight:700;font-size:11px;letter-spacing:.12em;
              text-transform:uppercase;color:#9c4a26;margin:0 0 8px">Optional · before you travel</p>
    <h2 style="font-size:19px;font-weight:600;color:#16222E;margin:0 0 8px;line-height:1.3">
        Travelling soon? Consider travel insurance
    </h2>
    <p style="font-size:15px;color:#33454f;margin:0 0 14px;max-width:60ch">
        A visa or ETA covers permission to travel; it isn't insurance. Many travellers choose a
        travel-insurance policy for medical cover, cancellations and lost baggage. It's entirely
        your choice, and not required for your application.
    </p>

    @if($hasPartner)
        <a href="{{ $partnerUrl }}" target="_blank" rel="noopener nofollow sponsored"
           style="display:inline-block;background:#155E7A;color:#ffffff;text-decoration:none;
                  font-weight:600;font-size:15px;padding:12px 20px;border-radius:8px;margin:0 0 4px">
            See cover from {{ $partnerName }} &rarr;
        </a>
    @else
        <p style="font-size:15px;color:#33454f;margin:0 0 4px">
            Want a recommendation? <strong>Just ask our team</strong> and we'll point you to an
            FCA-authorised travel-insurance provider.
        </p>
    @endif

    {{-- FCA-required disclosure: keep verbatim; do not soften. --}}
    <p style="font-size:12.5px;color:#5d6f79;margin:14px 0 0;line-height:1.6">
        Beyond Passports is not an insurer and does not provide insurance advice. This is an introduction
        to {{ $partnerLabel }}, an FCA-authorised provider; we may receive a referral fee. Cover
        and eligibility are arranged solely between you and {{ $partnerLabel }}.
    </p>
</aside>
