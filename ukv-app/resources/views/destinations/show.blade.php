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
            'a' => "It depends on your nationality, passport and trip. Tell us your details and we'll confirm whether you need one before you pay, and we'll tell you honestly if you don't.",
        ],
        [
            'q' => 'How long does it take?',
            'a' => "We prioritise Express cases within our team, but the government sets its own processing time, which we can't control or shorten. Express speeds our handling only, not the official decision.",
        ],
        [
            'q' => 'Is this the official government website?',
            'a' => "No. Beyond Passports is an independent service. We are not a government website and not affiliated with any government. We prepare, check and submit your application on your behalf for a separate service fee.",
        ],
        [
            'q' => 'What does your fee cover, and is the government fee included?',
            'a' => "Our fee covers our preparation, checking, submission and support. The {$name} government charges its own separate fee".(config('ukv.show_prices') && $gbp($govtFee) ? " ({$gbp($govtFee)})" : '').", which is shown clearly before you pay. The two are always kept separate.",
        ],
        [
            'q' => 'What happens if my application is refused?',
            'a' => "The outcome of any application is decided solely by the {$name} authorities, so we cannot guarantee approval. We focus on getting your application accurate and complete to give it the best chance, and we're upfront with you throughout.",
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
            'name'  => 'Beyond Passports',
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

@section('title', $visaLabel.' for UK travellers: Prepared & Checked | Beyond Passports')
@section('description', 'Apply for your '.$visaLabel.' with an independent UK team that prepares and checks every application. Clear fixed service fees, fast handling, every step tracked. Not a government website.')
@section('canonical', url('/visa/'.$destination->slug))

@push('head')
{{-- Page-scoped styles lifted verbatim from the coded reference (frontend/destination.html).
     Reuses the shared CSS vars/components from ukv.css; no palette redefinition. --}}
<style>
  .dhero{background:var(--navy);color:#eef0f1;padding:64px 0 0;position:relative;overflow:hidden}
  .dhero h1{color:#fff;font-size:clamp(34px,4.8vw,54px);letter-spacing:-.015em;max-width:18ch}
  .dhero p.lede{font-size:19px;max-width:46ch;color:#d3d7da}
  .dhero .eyebrow{color:#A9CCDA}
  .dhero .btn{margin-top:8px}
  .dhero .skyband{margin-top:36px}
  /* HERO + AT A GLANCE — full photo with welded navy fact strip (option C) */
  .dmoney-hero{position:relative;color:#fff;padding:0}
  .dmoney-hero .stage{position:relative;min-height:400px;display:flex;align-items:flex-end;background:var(--navy)}
  .dmoney-hero .stage .img{position:absolute;inset:0;background-size:cover;background-position:center}
  .dmoney-hero .stage::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(20,22,25,.25) 28%,rgba(20,22,25,.85))}
  .dmoney-hero .inner{position:relative;z-index:2;padding:60px 0 32px}
  .dmoney-hero .eyebrow{color:var(--soft)}
  .dmoney-hero h1{color:#fff;font:700 clamp(32px,4.6vw,52px)/1.04 var(--display);letter-spacing:-.02em;max-width:none;margin:0 0 14px}
  .dmoney-hero .lede{color:#dfe3e6;font-size:18px;line-height:1.5;max-width:62ch;margin:0 0 20px}
  .dmoney-facts{background:var(--navy);color:#fff;border-top:1px solid rgba(255,255,255,.12)}
  .dmoney-facts .wrap{display:grid;grid-template-columns:repeat(4,1fr)}
  .dmoney-facts .wrap>div{padding:18px 22px;border-left:1px solid rgba(255,255,255,.12)}
  .dmoney-facts .wrap>div:first-child{border-left:0;padding-left:0}
  .dmoney-facts .k{font:700 10px var(--mono);letter-spacing:.1em;text-transform:uppercase;color:var(--soft);margin:0 0 5px}
  .dmoney-facts .v{font:700 17px var(--display);line-height:1.25}
  .dmoney-facts .v small{display:block;font:400 11px var(--mono);color:#aab0b5;margin-top:3px}
  @media (max-width:820px){.dmoney-facts .wrap{grid-template-columns:1fr 1fr}.dmoney-facts .wrap>div:nth-child(odd){border-left:0;padding-left:0}.dmoney-facts .wrap>div:nth-child(even){padding-right:0}}
  @media (max-width:520px){.dmoney-facts .wrap{grid-template-columns:1fr}.dmoney-facts .wrap>div{border-left:0;border-top:1px solid rgba(255,255,255,.12);padding-left:0;padding-right:0}}
  .tiers{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;align-items:stretch}
  .tier{display:flex;flex-direction:column;background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;padding:26px 24px;position:relative}
  /* Featured tier rendered dark (option C) */
  .tier.feat{background:var(--navy);border-color:var(--navy);box-shadow:var(--shadow)}
  .tier.feat .name{color:var(--soft)}
  .tier.feat .price{color:#fff}
  .tier.feat .price small{color:#aab0b5}
  .tier.feat .sub{color:#cfd4d8}
  .tier.feat li{color:#e4e7ea}
  .tier.feat li .chk{color:var(--soft)}
  .tier .badge{position:absolute;top:-12px;left:24px;background:var(--soft);color:var(--navy);font-family:var(--mono);font-size:11px;letter-spacing:.08em;text-transform:uppercase;font-weight:700;padding:4px 10px;border-radius:20px}
  .tier .name{font-family:var(--display);font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 8px}
  .tier .price{font-family:var(--display);font-size:42px;font-weight:800;letter-spacing:-.02em;color:var(--navy);line-height:1}
  .tier .price small{font-family:var(--mono);font-size:13px;color:var(--muted);font-weight:400;letter-spacing:.04em}
  /* prices-off: "fee on request" line in place of the amount */
  .tier .qline{font-family:var(--display);font-size:20px;font-weight:800;color:var(--navy);line-height:1.15;letter-spacing:-.01em}
  .tier .qline small{display:block;font-family:var(--mono);font-size:12.5px;font-weight:400;color:var(--muted);margin-top:5px;letter-spacing:.02em}
  .tier.feat .qline{color:#fff}.tier.feat .qline small{color:#aab0b5}
  .tier .sub{font-size:14px;color:var(--muted);margin:6px 0 18px}
  .tier ul{list-style:none;padding:0;margin:0 0 22px;flex:1}
  .tier li{position:relative;padding-left:26px;margin-bottom:10px;font-size:15px;color:var(--ink)}
  .tier li .chk{position:absolute;left:3px;top:1px;color:var(--stamp);font-weight:700;font-size:13px}
  .tier .btn{width:100%;text-align:center}
  .pricenote{background:#f7fafb;border:1px solid var(--paper-edge);border-left:3px solid var(--gold);border-radius:8px;padding:16px 20px;margin-top:26px;font-size:14px;color:#3a4b55}
  .pricenote strong{color:var(--navy)}
  .pricenote p{margin:0 0 6px}
  .pricenote p:last-child{margin-bottom:0}
  /* Requirements — sticky heading left, list right (option E) */
  .reqs-split{display:grid;grid-template-columns:.72fr 1.28fr;gap:44px;align-items:start}
  .reqs-intro .lede{font-size:16px;color:var(--muted);margin:14px 0 0;max-width:32ch}
  .reqs{display:grid;gap:0}
  .req{display:grid;grid-template-columns:auto 1fr;gap:16px;align-items:start;padding:18px 0;border-top:1px solid var(--paper-edge)}
  .req:first-child{border-top:0;padding-top:0}
  .req svg{flex:none;color:var(--cta)}
  .req h3{font-size:16px;font-family:var(--body);font-weight:700;margin:0 0 2px}
  .req p{margin:0;font-size:14px;color:var(--muted);line-height:1.5}
  @media (max-width:820px){.reqs-split{grid-template-columns:1fr;gap:24px}}
  .faqd{max-width:78ch}
  .faqd details{border-bottom:1px solid var(--paper-edge);padding:18px 0}
  .faqd summary{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:500;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:16px}
  .faqd summary::-webkit-details-marker{display:none}
  .faqd summary::after{content:"+";font-family:var(--mono);font-size:22px;color:var(--gold);flex:0 0 auto;transition:transform .15s ease}
  .faqd details[open] summary::after{content:"–"}
  .faqd p{margin:12px 0 0;color:#3a4b55;font-size:16px}
  /* FAQ — tinted panel accordion (option E) */
  .faq-e{background:var(--paper)}
  .faq-e .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  .faq-e .faq-panel{background:var(--white);border:1px solid var(--paper-edge);border-radius:18px;padding:6px 30px;max-width:80ch;margin:0 auto;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  .faq-e .faqd{max-width:none}
  .faq-e .faqd details:last-child{border-bottom:0}
  @media (max-width:860px){
    .facts,.tiers,.reqs{grid-template-columns:1fr}
  }
  /* reviewer credit — the named UK person who checks this application */
  .revcred{background:linear-gradient(180deg,#f3f9f7,#fff);border-bottom:1px solid var(--paper-edge)}
  .revcred .wrap{display:flex;align-items:center;gap:14px;padding:16px 0}
  .revcred img{width:50px;height:50px;border-radius:50%;object-fit:cover;flex:none;border:2px solid #fff;box-shadow:0 6px 16px -8px rgba(30,40,60,.5)}
  .revcred .t b{font:700 15px var(--display);color:var(--navy)}
  .revcred .t span{display:block;font-size:13px;color:var(--muted);margin-top:2px}
  .revcred .chk{margin-left:auto;font:800 12px var(--display);color:var(--stamp-text);background:#e7f3ef;border-radius:999px;padding:6px 12px;white-space:nowrap}
  @media (max-width:560px){.revcred .chk{display:none}}
</style>
<script type="application/ld+json">{!! json_encode($serviceLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

{{-- 1. HERO + AT A GLANCE — full photo with welded navy fact strip (C) --}}
<section class="dmoney-hero">
  <div class="stage">
    @if($destination->image_path)<div class="img" style="background-image:url('{{ asset(ltrim($destination->image_path, '/')) }}')"></div>@endif
    <div class="wrap"><div class="inner">
      <p class="eyebrow">{{ $name }} · {{ $visaType }}</p>
      <h1>{{ $visaLabel }}, prepared and checked by our UK team</h1>
      <p class="lede">Skip the guesswork. We confirm exactly what you need, check every detail before submission, and keep you updated until it's done.</p>
      <a href="#pricing" class="btn">Start my {{ $name }} application →</a>
      @include('partials.trustpilot', ['template' => 'micro', 'align' => 'left', 'margin' => '16px 0 0'])
    </div></div>
  </div>
  <div class="dmoney-facts"><div class="wrap">
    <div><p class="k">Validity</p><div class="v">{{ $maxStay ? 'Up to '.$maxStay.' days' : 'Varies by trip' }}</div></div>
    <div><p class="k">Type</p><div class="v">{{ $visaType }}</div></div>
    <div><p class="k">Typical processing</p><div class="v">A few business days<small>our handling</small></div></div>
    @if(config('ukv.show_prices') && $standard !== null && (float)$standard > 0)
      <div><p class="k">Service fee</p><div class="v">from {{ $gbp($standard) }}<small>separate from govt fee</small></div></div>
    @else
      <div><p class="k">Passport</p><div class="v">{{ $passport ? $passport.' months validity' : 'Valid passport' }}</div></div>
    @endif
  </div></div>
</section>

{{-- Reviewer credit: the named UK lead who personally checks each application --}}
@php $revLead = collect(config('ukv.team', []))->firstWhere('lead', true) ?? collect(config('ukv.team', []))->first(); @endphp
@if ($revLead)
<section class="revcred"><div class="wrap">
  @if (!empty($revLead['photo']))<img src="{{ asset(ltrim($revLead['photo'], '/')) }}" alt="{{ $revLead['name'] }}">@endif
  <div class="t"><b>Checked by {{ $revLead['name'] }}</b><span>{{ $revLead['role'] ?? 'UK Case Lead' }} · personally reviews every application before it's submitted</span></div>
  <span class="chk">✓ Human-checked</span>
</div></section>
@endif

@if (\Illuminate\Support\Str::contains(strtolower((string) $visaType), 'etias'))
{{-- ETIAS pre-launch honesty banner — ETIAS is not live until late 2026; UK citizens
     currently travel visa-free. Keeps the page truthful before the scheme starts. --}}
<section style="padding:0"><div class="wrap" style="padding-top:22px">
  <div style="display:flex;gap:14px;align-items:flex-start;background:linear-gradient(180deg,#fff8ef,#fffdf9);border:1px solid var(--paper-edge);border-left:4px solid #c8923a;border-radius:14px;padding:18px 22px;box-shadow:0 10px 30px -26px rgba(40,50,70,.4)">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#c8923a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:1px" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
    <p style="margin:0;font-size:15px;line-height:1.6;color:#3a4b55">
      <strong style="color:var(--navy)">ETIAS isn't required yet.</strong> Right now UK citizens travel to {{ $name }} <strong>visa-free</strong> for short stays, with no ETIAS and no fee. ETIAS launches in late 2026 (€20, valid 3 years, 90 days in any 180). When it opens we'll prepare and check yours, and confirm the rules before you pay. <span style="color:var(--muted)">Not a government website · the decision is the authority's.</span>
    </p>
  </div>
</div></section>
@endif

{{-- 3. PRICING --}}
<section id="pricing" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">{{ config('ukv.show_prices') ? 'Clear fixed service fees' : 'Choose your level of service' }}</p><h2>{{ config('ukv.show_prices') ? 'Choose your level of service' : 'Three service levels' }}</h2></div>
  <div class="tiers">
    {{-- Standard --}}
    <div class="tier reveal">
      <p class="name">Standard</p>
      @if (config('ukv.show_prices'))
        <div class="price">{{ $gbp($standard) ?? '—' }} <small>service fee</small></div>
      @else
        <div class="qline">Standard fee: on request<small>our base service · separate from the govt fee</small></div>
      @endif
      <p class="sub">Everything you need, done right.</p>
      <ul>
        <li><span class="chk">✓</span>Document &amp; eligibility check</li>
        <li><span class="chk">✓</span>Application prepared by our UK team</li>
        <li><span class="chk">✓</span>Submitted &amp; tracked to completion</li>
        <li><span class="chk">✓</span>Email support</li>
      </ul>
      <a href="{{ $applyUrl }}&tier=standard" class="btn btn--ghost">{{ config('ukv.show_prices') ? 'Choose Standard' : 'Get my Standard quote →' }}</a>
    </div>
    {{-- Express (featured) --}}
    <div class="tier feat reveal">
      <span class="badge">Most popular</span>
      <p class="name">Express</p>
      @if (config('ukv.show_prices'))
        <div class="price">{{ $gbp($express) ?? '—' }} <small>service fee</small></div>
      @else
        <div class="qline">Express fee: on request<small>priority handling · separate from the govt fee</small></div>
      @endif
      <p class="sub">When you're short on time.</p>
      <ul>
        <li><span class="chk">✓</span>Everything in Standard</li>
        <li><span class="chk">✓</span>Priority handling by our team</li>
        <li><span class="chk">✓</span>Faster preparation &amp; submission</li>
        <li><span class="chk">✓</span>WhatsApp + email support</li>
      </ul>
      <a href="{{ $applyUrl }}&tier=express" class="btn">{{ config('ukv.show_prices') ? 'Choose Express' : 'Get my Express quote →' }}</a>
    </div>
    {{-- Premium --}}
    <div class="tier reveal">
      <p class="name">Premium</p>
      @if (config('ukv.show_prices'))
        <div class="price">{{ $gbp($premium) ?? '—' }} <small>service fee</small></div>
      @else
        <div class="qline">Premium fee: on request<small>full hands-on support · separate from the govt fee</small></div>
      @endif
      <p class="sub">Full hands-on support.</p>
      <ul>
        <li><span class="chk">✓</span>Everything in Express</li>
        <li><span class="chk">✓</span>Dedicated case handler</li>
        <li><span class="chk">✓</span>Phone support &amp; document help</li>
        <li><span class="chk">✓</span>Re-check &amp; re-submit if needed</li>
      </ul>
      <a href="{{ $applyUrl }}&tier=premium" class="btn btn--ghost">{{ config('ukv.show_prices') ? 'Choose Premium' : 'Get my Premium quote →' }}</a>
    </div>
  </div>
  <div class="pricenote reveal">
    <p><strong>Our service fee is separate from the {{ $name }} government fee.</strong> The government charges its own fee{{ config('ukv.show_prices') && $gbp($govtFee) ? ' (currently '.$gbp($govtFee).')' : '' }} for the {{ $visaType }}, which you'll see clearly before you pay anything.</p>
    <p><strong>Express speeds our handling. It does not change the government's decision or its processing time.</strong> We cannot guarantee approval; the outcome is always decided by the {{ $name }} authorities.</p>
  </div>
</div></section>

{{-- 4. DOCUMENT CHECKLIST PREVIEW (Document Requirements Engine) — the canonical requirements section --}}
{{-- $docItems is computed in DestinationController::show via RequirementService::preview().
     Generic, destination-scoped preview — no order context. Renders nothing if empty. --}}
@if (! empty($docItems))
<section><div class="wrap">
  <div class="reveal">
    @include('partials.doc-checklist', ['items' => $docItems, 'personalised' => false])
  </div>
</div></section>
@endif

{{-- 4b. PREVENTION — "built to prevent refusals" (country-templated) --}}
@include('partials.money-prevention')

{{-- 5. HOW IT WORKS --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What you get</p><h2>Three simple steps</h2></div>
  <div class="steps">
    <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>Tell us your trip</h3><p>Share your travel dates and passport details. We confirm exactly what your {{ $visaLabel }} needs.</p></div>
    <div class="step reveal"><div class="num">02</div><div class="rule"></div><h3>We prepare &amp; check</h3><p>Our UK team reviews every detail for errors before anything is submitted to the authorities.</p></div>
    <div class="step reveal"><div class="num">03</div><div class="rule"></div><h3>Submit &amp; track</h3><p>We handle the submission and keep you updated until your {{ $visaType }} is delivered.</p></div>
  </div>
</div></section>

{{-- 6. FAQ — tinted panel accordion (E) --}}
<section class="faq-e"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Questions</p><h2>{{ $visaLabel }} FAQ</h2></div>
  <div class="faq-panel reveal">
    <div class="faqd">
    @foreach ($faqs as $f)
      <details>
        <summary>{{ $f['q'] }}</summary>
        <p>{{ $f['a'] }}</p>
      </details>
    @endforeach
    </div>
  </div>
</div></section>

{{-- 6b. GUIDES FOR {COUNTRY} — published guide cluster (Guide engine, hub-and-spoke) --}}
@if (isset($guideCluster) && $guideCluster->isNotEmpty())
<section class="alt"><div class="wrap">
  @include('partials.guide-cluster', [
    'cluster' => $guideCluster,
    'country' => $name,
    'heading' => 'Guides for '.$name,
  ])
</div></section>
@endif

{{-- 7. CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Ready for {{ $name }}? Let's get it sorted.</h2>
  <p style="max-width:48ch;color:#eef0f1">Start your {{ $visaLabel }} now, or message our UK team with any question first.</p>
  <div class="row">
    <a href="#pricing" class="btn">Start my {{ $name }} application →</a>
    @include('partials.wa-cta', [
        'message' => "Hi Beyond Passports, I'd like help with my documents for {$name}.",
        'label' => 'Ask about '.$name.' on WhatsApp',
        'variant' => 'ghost',
    ])
  </div>
</div></section>

@endsection
