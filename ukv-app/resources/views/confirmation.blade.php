{{--
    Order confirmation — renders the order reference in MRZ style (UKV-YYYY-NNNNNN) plus the
    lane-specific next steps. Intentionally minimal/self-contained styling so it works before
    the coded design system is wired in; it can later adopt assets/ukv.css.

    Expected variables:
      $order  App\Models\Order
--}}
@php
    /** @var \App\Models\Order $order */
    $lane = $order->eligibility instanceof \App\Enums\EligibilityLane
        ? $order->eligibility->value
        : (string) $order->eligibility;
    $isStandard = $lane === \App\Enums\EligibilityLane::Standard->value;
    // MRZ-style padded ref line (e.g. UKV<2026<000042<<<<<<<<<<<<<<<<<<).
    $mrz = strtoupper(str_replace('-', '<', $order->order_ref));
    $mrz = str_pad($mrz, 32, '<');
@endphp
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application received — {{ $order->order_ref }} | UKVisaCo</title>
    <style>
        :root{
            --navy:#0f2747; --gold:#c8a24a; --ink:#1c2b33; --muted:#5d6f79;
            --paper:#ffffff; --edge:#dfe6ea; --bg:#f3f6f8;
        }
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--ink);
            font-family:"Inter",system-ui,-apple-system,Segoe UI,Roboto,sans-serif;line-height:1.55}
        .wrap{max-width:640px;margin:0 auto;padding:48px 20px}
        .card{background:var(--paper);border:1px solid var(--edge);border-radius:14px;
            box-shadow:0 8px 30px rgba(15,39,71,.08);overflow:hidden}
        .head{background:var(--navy);color:#fff;padding:26px 28px}
        .head .tag{font-family:"Space Mono",ui-monospace,monospace;font-size:11px;
            letter-spacing:.16em;text-transform:uppercase;color:var(--gold);margin:0 0 6px}
        .head h1{font-size:24px;margin:0;font-weight:600}
        .body{padding:28px}
        .body p{color:#33454f;margin:0 0 14px}
        .ref{margin:4px 0 22px;text-align:center}
        .ref .label{font-family:"Space Mono",monospace;font-size:11px;letter-spacing:.14em;
            text-transform:uppercase;color:var(--muted);margin:0 0 8px}
        .ref .value{font-family:"Space Mono",monospace;font-size:28px;font-weight:700;
            color:var(--navy);letter-spacing:.04em}
        .mrz{margin-top:14px;background:var(--navy);color:#e7eef6;
            font-family:"Space Mono",monospace;font-size:13px;letter-spacing:.14em;
            padding:10px 14px;border-radius:8px;overflow-x:auto;white-space:nowrap;text-align:center}
        .steps{list-style:none;margin:18px 0 0;padding:0;counter-reset:step}
        .steps li{position:relative;padding:12px 0 12px 42px;border-top:1px dashed var(--edge)}
        .steps li:first-child{border-top:0}
        .steps li::before{counter-increment:step;content:counter(step);position:absolute;left:0;top:11px;
            width:26px;height:26px;border-radius:50%;background:var(--gold);color:var(--navy);
            font-family:"Space Mono",monospace;font-weight:700;font-size:13px;
            display:flex;align-items:center;justify-content:center}
        .badge{display:inline-block;font-family:"Space Mono",monospace;font-size:11px;
            letter-spacing:.1em;text-transform:uppercase;border-radius:99px;padding:4px 12px;margin:0 0 16px}
        .badge.standard{background:#e7f3ec;color:#1f6b3b}
        .badge.review{background:#fbf1dc;color:#8a6516}
        .cta{display:inline-block;margin-top:20px;background:var(--navy);color:#fff;
            text-decoration:none;font-weight:600;padding:13px 22px;border-radius:8px}
        .note{font-family:"Space Mono",monospace;font-size:11px;color:var(--muted);
            letter-spacing:.03em;margin-top:22px}
        .compliance{font-size:12.5px;color:var(--muted);margin-top:18px;line-height:1.6}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="head">
            <p class="tag">Application received</p>
            <h1>Thank you — we have your details.</h1>
        </div>
        <div class="body">

            @if($isStandard)
                <span class="badge standard">Standard service · eligible</span>
            @else
                <span class="badge review">Manual review · human check</span>
            @endif

            <div class="ref">
                <p class="label">Your reference</p>
                <div class="value">{{ $order->order_ref }}</div>
                <div class="mrz">{{ $mrz }}</div>
            </div>

            <p>Keep this reference safe — you'll need it to track your application or talk to our team.</p>

            @if($isStandard)
                <p><strong>Next steps</strong></p>
                <ol class="steps">
                    <li>Complete secure payment for your chosen service tier
                        @if($order->total !== null)(total <strong>£{{ number_format((float) $order->total, 2) }}</strong>, service fee + government fee)@endif.</li>
                    <li>Upload your documents — we'll tell you exactly what's needed.</li>
                    <li>Our UK team checks everything and submits on your behalf, keeping every step tracked.</li>
                </ol>
                @php
                    // CheckoutController owns this route; fall back gracefully if not yet registered.
                    $checkoutUrl = \Illuminate\Support\Facades\Route::has('checkout.show')
                        ? route('checkout.show', $order->order_ref)
                        : '#';
                @endphp
                <a class="cta" href="{{ $checkoutUrl }}">Continue to secure payment →</a>
            @else
                <p><strong>Next steps</strong></p>
                <ol class="steps">
                    <li>A UK-based adviser reviews your answers — usually within one business day.</li>
                    <li>We confirm exactly what your case requires and send a <strong>personalised quote</strong>.</li>
                    <li>Nothing is charged until you approve that quote.</li>
                </ol>
                <p>No payment is taken yet. We'll be in touch using the contact details you gave us.</p>
            @endif

            {{-- Optional FCA-safe travel-insurance introducer (no charge taken here). --}}
            @include('partials.insurance-introducer')

            <p class="compliance">
                UKVisaCo is an independent service and is not a government website. Our service
                fee is separate from, and additional to, any government or scheme fee. Express
                speeds our handling — it does not speed up or change the government's decision,
                and we cannot guarantee approval.
            </p>

            <p class="note">REF {{ $order->order_ref }} · STATUS {{ strtoupper((string) ($order->status->value ?? $order->status)) }}</p>
        </div>
    </div>
</div>
</body>
</html>
