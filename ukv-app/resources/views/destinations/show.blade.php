@extends('layouts.public')

@php
    use Illuminate\Support\Str;

    /** @var \App\Models\Destination $destination */

    // --- Derived display values (all from DB fields, with safe fallbacks) ---------------
    $name      = $destination->name;
    $visaType  = $destination->visa_type ? Str::of($destination->visa_type)->replace(['-', '_'], ' ')->title() : 'Visa';
    $visaLabel = $name.' '.$visaType;

    // Money helper: render a GBP decimal without trailing ".00", null-safe.
    $gbp = function ($v): ?string {
        if (is_null($v)) {
            return null;
        }
        return '£'.rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    };

    $standard = $destination->tier_standard_gbp;
    $express  = $destination->tier_express_gbp;
    $premium  = $destination->tier_premium_gbp;
    $govtFee  = $destination->govt_fee_gbp;

    $maxStay  = $destination->max_stay_days;
    $passport = $destination->passport_validity_months;

    // required_docs is cast to array on the model; normalise to a clean list of strings.
    $docs = collect($destination->required_docs ?? [])
        ->map(fn ($d) => is_string($d) ? trim($d) : null)
        ->filter()
        ->values();

    $applyUrl = url('/apply').'?destination='.urlencode($destination->slug);

    // --- FAQ content (compliance-first, parameterised by destination) -------------------
    $faqs = [
        [
            'q' => "Do I actually need a {$visaLabel}?",
            'a' => "It depends on your nationality, passport and trip. Tell us your details and we'll confirm whether you need one before you pay — and we'll tell you honestly if you don't.",
        ],
        [
            'q' => 'How long does it take?',
            'a' => "We prioritise Express cases within our team, but the government sets its own processing time, which we can't control or shorten. Express speeds our handling only — not the official decision.",
        ],
        [
            'q' => 'Is this the official government website?',
            'a' => "No. UKVisaCo is an independent service — we are not a government website and not affiliated with any government. We prepare, check and submit your application on your behalf for a separate service fee.",
        ],
        [
            'q' => 'What does your fee cover, and is the government fee included?',
            'a' => "Our fee covers our preparation, checking, submission and support. The {$name} government charges its own separate fee".($gbp($govtFee) ? " ({$gbp($govtFee)})" : '').", which is shown clearly before you pay. The two are always kept separate.",
        ],
        [
            'q' => 'What happens if my application is refused?',
            'a' => "The outcome of any application is decided solely by the {$name} authorities — we cannot guarantee approval. We focus on getting your application accurate and complete to give it the best chance, and we're upfront with you throughout.",
        ],
    ];

    // --- JSON-LD: Service + FAQPage -----------------------------------------------------
    $serviceLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'Service',
        'name'     => "{$visaLabel} preparation & checking",
        'serviceType' => "{$visaType} application facilitation",
        'description' => "Independent UK team that prepares, checks and submits your {$visaLabel} application. Service fee separate from the government fee. Not a government website.",
        'areaServed'  => $name,
        'provider' => [
            '@type' => 'Organization',
            'name'  => 'UKVisaCo',
            'url'   => url('/'),
        ],
        'url' => url('/visa/'.$destination->slug),
    ];
    if (! is_null($standard)) {
        $serviceLd['offers'] = [
            '@type' => 'Offer',
            'price' => number_format((float) $standard, 2, '.', ''),
            'priceCurrency' => 'GBP',
            'description' => 'Standard service fee (separate from the government fee).',
        ];
    }

    $faqLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => collect($faqs)->map(fn ($f) => [
            '@type' => 'Question',
            'name'  => $f['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $f['a'],
            ],
        ])->all(),
    ];
@endphp

@section('title', $visaLabel.' for UK travellers — Prepared & Checked | UKVisaCo')
@section('description', 'Apply for your '.$visaLabel.' with an independent UK team that prepares and checks every application. Clear fixed service fees, fast handling, every step tracked. Not a government website.')
@section('canonical', url('/visa/'.$destination->slug))

@push('head')
{{-- Page-scoped styles lifted verbatim from the coded reference (frontend/destination.html).
     Reuses the shared CSS vars/components from ukv.css; no palette redefinition. --}}
<style>
  .dhero{background:var(--navy);color:#eaf0f4;padding:64px 0 0;position:relative;overflow:hidden}
  .dhero h1{color:#fff;font-size:clamp(34px,4.8vw,54px);letter-spacing:-.015em;max-width:18ch}
  .dhero p.lede{font-size:19px;max-width:46ch;color:#cdd9e1}
  .dhero .eyebrow{color:var(--gold)}
  .dhero .btn{margin-top:8px}
  .dhero .skyband{margin-top:36px}
  .facts{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
  .fact{background:var(--white);border:1px solid var(--paper-edge);border-radius:10px;padding:18px 18px}
  .fact .k{font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp);margin:0 0 6px}
  .fact .v{font-family:var(--display);font-size:20px;color:var(--navy);font-weight:600;line-height:1.2}
  .tiers{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;align-items:stretch}
  .tier{display:flex;flex-direction:column;background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;padding:26px 24px;position:relative}
  .tier.feat{border-color:var(--gold);box-shadow:var(--shadow)}
  .tier .badge{position:absolute;top:-12px;left:24px;background:var(--gold);color:var(--navy);font-family:var(--mono);font-size:11px;letter-spacing:.08em;text-transform:uppercase;font-weight:700;padding:4px 10px;border-radius:20px}
  .tier .name{font-family:var(--mono);font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp);margin:0 0 8px}
  .tier .price{font-family:var(--display);font-size:42px;font-weight:600;color:var(--navy);line-height:1}
  .tier .price small{font-family:var(--mono);font-size:13px;color:var(--muted);font-weight:400;letter-spacing:.04em}
  .tier .sub{font-size:14px;color:var(--muted);margin:6px 0 18px}
  .tier ul{list-style:none;padding:0;margin:0 0 22px;flex:1}
  .tier li{position:relative;padding-left:26px;margin-bottom:10px;font-size:15px;color:var(--ink)}
  .tier li .chk{position:absolute;left:3px;top:1px;color:var(--stamp);font-weight:700;font-size:13px}
  .tier .btn{width:100%;text-align:center}
  .pricenote{background:#f7fafb;border:1px solid var(--paper-edge);border-left:3px solid var(--gold);border-radius:8px;padding:16px 20px;margin-top:26px;font-size:14px;color:#3a4b55}
  .pricenote strong{color:var(--navy)}
  .pricenote p{margin:0 0 6px}
  .pricenote p:last-child{margin-bottom:0}
  .reqs{display:grid;grid-template-columns:repeat(2,1fr);gap:18px 36px}
  .req{display:flex;gap:14px;align-items:flex-start}
  .req svg{flex:0 0 28px}
  .req h3{font-size:16px;font-family:var(--body);font-weight:600;margin:2px 0 2px}
  .req p{margin:0;font-size:14px;color:var(--muted)}
  .faqd{max-width:78ch}
  .faqd details{border-bottom:1px solid var(--paper-edge);padding:18px 0}
  .faqd summary{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:500;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:16px}
  .faqd summary::-webkit-details-marker{display:none}
  .faqd summary::after{content:"+";font-family:var(--mono);font-size:22px;color:var(--gold);flex:0 0 auto;transition:transform .15s ease}
  .faqd details[open] summary::after{content:"–"}
  .faqd p{margin:12px 0 0;color:#3a4b55;font-size:16px}
  @media (max-width:860px){
    .facts,.tiers,.reqs{grid-template-columns:1fr}
  }
</style>
<script type="application/ld+json">{!! json_encode($serviceLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

{{-- 1. HERO --}}
<section class="dhero"><div class="wrap">
  <p class="eyebrow">{{ $name }} · {{ $visaType }}</p>
  <h1>{{ $visaLabel }}, prepared and checked by our UK team</h1>
  <p class="lede">Skip the guesswork. We confirm exactly what you need, check every detail before submission, and keep you updated until it's done.</p>
  <a href="#pricing" class="btn">Start my {{ $name }} application →</a>
  <div class="skyband">
    <svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" style="width:100%;height:120px;opacity:.55" aria-hidden="true"><use href="#ukv-skyline"></use></svg>
  </div>
</div></section>
<div class="mrz"><div class="wrap"><span>P&lt;GBR&lt;TRAVELLER&lt;&lt;{{ Str::upper(Str::slug($name, '<')) }}&lt;{{ Str::upper(Str::slug($visaType, '<')) }}&lt;READY&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- 2. AT A GLANCE --}}
<section><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">At a glance</p><h2>The {{ $visaLabel }}, in short</h2></div>
  <div class="facts">
    <div class="fact reveal">
      <p class="k">Validity</p>
      <div class="v">{{ $maxStay ? 'Up to '.$maxStay.' days' : 'Varies by trip' }}</div>
    </div>
    <div class="fact reveal">
      <p class="k">Type</p>
      <div class="v">{{ $visaType }}</div>
    </div>
    <div class="fact reveal">
      <p class="k">Typical processing</p>
      <div class="v">A few business days <small style="font-family:var(--mono);font-size:11px;color:var(--muted);display:block;margin-top:4px">our handling</small></div>
    </div>
    <div class="fact reveal">
      <p class="k">Passport</p>
      <div class="v">{{ $passport ? $passport.' months validity recommended' : 'Valid passport required' }}</div>
    </div>
  </div>
</div></section>

{{-- 3. PRICING --}}
<section id="pricing" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Clear fixed service fees</p><h2>Choose your level of service</h2></div>
  <div class="tiers">
    {{-- Standard --}}
    <div class="tier reveal">
      <p class="name">Standard</p>
      <div class="price">{{ $gbp($standard) ?? '—' }} <small>service fee</small></div>
      <p class="sub">Everything you need, done right.</p>
      <ul>
        <li><span class="chk">✓</span>Document &amp; eligibility check</li>
        <li><span class="chk">✓</span>Application prepared by our UK team</li>
        <li><span class="chk">✓</span>Submitted &amp; tracked to completion</li>
        <li><span class="chk">✓</span>Email support</li>
      </ul>
      <a href="{{ $applyUrl }}&tier=standard" class="btn btn--ghost">Choose Standard</a>
    </div>
    {{-- Express (featured) --}}
    <div class="tier feat reveal">
      <span class="badge">Most popular</span>
      <p class="name">Express</p>
      <div class="price">{{ $gbp($express) ?? '—' }} <small>service fee</small></div>
      <p class="sub">When you're short on time.</p>
      <ul>
        <li><span class="chk">✓</span>Everything in Standard</li>
        <li><span class="chk">✓</span>Priority handling by our team</li>
        <li><span class="chk">✓</span>Faster preparation &amp; submission</li>
        <li><span class="chk">✓</span>WhatsApp + email support</li>
      </ul>
      <a href="{{ $applyUrl }}&tier=express" class="btn">Choose Express</a>
    </div>
    {{-- Premium --}}
    <div class="tier reveal">
      <p class="name">Premium</p>
      <div class="price">{{ $gbp($premium) ?? '—' }} <small>service fee</small></div>
      <p class="sub">Full hands-on support.</p>
      <ul>
        <li><span class="chk">✓</span>Everything in Express</li>
        <li><span class="chk">✓</span>Dedicated case handler</li>
        <li><span class="chk">✓</span>Phone support &amp; document help</li>
        <li><span class="chk">✓</span>Re-check &amp; re-submit if needed</li>
      </ul>
      <a href="{{ $applyUrl }}&tier=premium" class="btn btn--ghost">Choose Premium</a>
    </div>
  </div>
  <div class="pricenote reveal">
    <p><strong>Our service fee is separate from the {{ $name }} government fee.</strong> The government charges its own fee{{ $gbp($govtFee) ? ' (currently '.$gbp($govtFee).')' : '' }} for the {{ $visaType }}, which you'll see clearly before you pay anything.</p>
    <p><strong>Express speeds our handling — it does not change the government's decision or its processing time.</strong> We cannot guarantee approval; the outcome is always decided by the {{ $name }} authorities.</p>
  </div>
</div></section>

{{-- 4. HOW IT WORKS --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What you get</p><h2>Three simple steps</h2></div>
  <div class="steps">
    <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>Tell us your trip</h3><p>Share your travel dates and passport details. We confirm exactly what your {{ $visaLabel }} needs.</p></div>
    <div class="step reveal"><div class="num">02</div><div class="rule"></div><h3>We prepare &amp; check</h3><p>Our UK team reviews every detail for errors before anything is submitted to the authorities.</p></div>
    <div class="step reveal"><div class="num">03</div><div class="rule"></div><h3>Submit &amp; track</h3><p>We handle the submission and keep you updated until your {{ $visaType }} is delivered.</p></div>
  </div>
</div></section>

{{-- 5. REQUIREMENTS --}}
<section class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Before you start</p><h2>What you'll need</h2></div>
  <div class="reqs">
    @forelse ($docs as $doc)
      <div class="req reveal">
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <div><h3>{{ $doc }}</h3></div>
      </div>
    @empty
      {{-- Fallback when required_docs is empty: sensible generic checklist. --}}
      <div class="req reveal">
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <div><h3>A valid passport</h3><p>{{ $passport ? 'Recommended at least '.$passport.' months\' validity beyond your travel dates.' : 'Valid for your full trip.' }}</p></div>
      </div>
      <div class="req reveal">
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <div><h3>Your travel dates</h3><p>Intended arrival and departure dates for your trip to {{ $name }}.</p></div>
      </div>
      <div class="req reveal">
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <div><h3>A digital passport photo</h3><p>A clear, recent photo where required — we'll tell you the exact spec.</p></div>
      </div>
      <div class="req reveal">
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <div><h3>Contact &amp; payment details</h3><p>An email for updates and a card to cover the service and government fees.</p></div>
      </div>
    @endforelse
  </div>
</div></section>

{{-- 6. FAQ --}}
<section><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Questions</p><h2>{{ $visaLabel }} FAQ</h2></div>
  <div class="faqd reveal">
    @foreach ($faqs as $f)
      <details>
        <summary>{{ $f['q'] }}</summary>
        <p>{{ $f['a'] }}</p>
      </details>
    @endforeach
  </div>
</div></section>

{{-- 7. CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Ready for {{ $name }}? Let's get it sorted.</h2>
  <p style="max-width:48ch;color:#cdd9e1">Start your {{ $visaLabel }} now, or message our UK team with any question first.</p>
  <div class="row"><a href="#pricing" class="btn">Start my {{ $name }} application →</a><a href="https://wa.me/440000000000" class="btn btn--wa">Chat on WhatsApp</a></div>
</div></section>

@endsection
