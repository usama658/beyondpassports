{{-- "Registered in UK & Europe" → Companies House verify link. Single source for the
     URL so every placement (badges + inline prose) points to the same record.
     Usage: <x-reg-verify class="ti">…badge…</x-reg-verify>  or  <x-reg-verify>UK &amp; Europe registered</x-reg-verify> --}}
<a href="https://find-and-update.company-information.service.gov.uk/company/{{ config('ukv.company_no') ?: '17331903' }}"
   target="_blank" rel="noopener" title="Verify our UK registration on Companies House"
   {{ $attributes->merge(['style' => 'color:inherit']) }}>{{ $slot }}</a>
