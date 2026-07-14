{{--
  Site-wide JSON-LD: Organization + WebSite.

  @include this once in the public layout's <head> (after partials.seo-meta).
  These two blocks belong on EVERY public page.

  Per-page schema (Service, FAQPage, BreadcrumbList) should be emitted by the
  individual page template, NOT here — see the worked example at the bottom.
--}}
@php
    $siteName = config('app.name', 'Beyond Passports');
    $base     = rtrim(config('app.url'), '/');
    $addr     = config('ukv.address', []);

    // PostalAddress from config, only when a street + postcode are set (real registered office).
    $postal = (! empty($addr['line1']) && ! empty($addr['postcode'])) ? array_filter([
        '@type'           => 'PostalAddress',
        'streetAddress'   => trim(($addr['line1'] ?? '') . (! empty($addr['line2']) ? ', ' . $addr['line2'] : '')),
        'addressLocality' => $addr['city'] ?? null,
        'postalCode'      => $addr['postcode'] ?? null,
        'addressCountry'  => 'GB',
    ]) : null;

    $org = array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        '@id'         => $base . '/#organization',
        'name'        => $siteName,
        'legalName'   => $addr['company'] ?? null,
        'url'         => $base . '/',
        'logo'        => asset('images/logo.png'),
        'description' => 'Independent UK service that prepares and checks travel document applications for people travelling abroad. Not a government website.',
        'email'       => config('ukv.email') ?: null,
        'telephone'   => config('ukv.phone_e164') ?: null,
        'address'     => $postal,
        'sameAs'      => array_values(array_filter(config('ukv.social', []))),
    ], fn ($v) => $v !== null && $v !== '' && $v !== []);
@endphp

<script type="application/ld+json">
{!! json_encode($org, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
</script>

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    '@id'      => $base . '/#website',
    'name'     => $siteName,
    'url'      => $base . '/',
    'publisher' => ['@id' => $base . '/#organization'],
    'inLanguage' => 'en-GB',
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
</script>

{{--
  ───────────────────────────────────────────────────────────────────────────
  HOW A DESTINATION PAGE ADDS Service + FAQPage SCHEMA
  ───────────────────────────────────────────────────────────────────────────
  Do NOT add this here. Put it in the per-destination view (e.g.
  resources/views/schengen-visa/show.blade.php), inside a @push('schema') stack
  that the layout @stacks right after this include. Example:

  @push('schema')
  <script type="application/ld+json">
  {!! json_encode([
      '@context'    => 'https://schema.org',
      '@type'       => 'Service',
      'serviceType' => $destination->visa_type . ' application preparation',
      'name'        => $destination->name . ' Visa Preparation & Checking',
      'provider'    => ['@id' => rtrim(config('app.url'), '/') . '/#organization'],
      'areaServed'  => 'GB',
      'offers'      => array_filter([
          '@type'         => 'Offer',
          // Omit price when marketing prices are hidden (config('ukv.show_prices') = false)
          // so structured data never advertises a price the page itself doesn't show.
          'price'         => config('ukv.show_prices') ? (string) $destination->tier_standard_gbp : null,
          'priceCurrency' => config('ukv.show_prices') ? 'GBP' : null,
          'url'           => url('/visa/' . $destination->slug),
      ], fn ($v) => $v !== null),
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
  </script>

  <script type="application/ld+json">
  {!! json_encode([
      '@context'   => 'https://schema.org',
      '@type'      => 'FAQPage',
      'mainEntity' => collect($faqs)->map(fn ($faq) => [
          '@type'          => 'Question',
          'name'           => $faq['q'],
          'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
      ])->all(),
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
  </script>
  @endpush

  Then in the layout, after this include, add: @stack('schema')
--}}
