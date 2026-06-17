{{--
    Order confirmation — renders the plain order reference (UKV-YYYY-NNNNNN) plus the
    lane-specific next steps. Self-contained warm-light styling (terracotta/sage, Plus
    Jakarta Sans) mirroring assets/ukv.css.

    Expected variables:
      $order  App\Models\Order
--}}
@php
    /** @var \App\Models\Order $order */
    // Document Requirements Engine: personalised checklist items, computed in the
    // confirmation route closure via RequirementService::for($order). Guarded for safety.
    $docItems = $docItems ?? [];
    $lane = $order->eligibility instanceof \App\Enums\EligibilityLane
        ? $order->eligibility->value
        : (string) $order->eligibility;
    $isStandard = $lane === \App\Enums\EligibilityLane::Standard->value;
@endphp
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application received — {{ $order->order_ref }} | Beyond Passports</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── Confirmation page — self-contained premium warm-light design ──────────── */
        :root {
            --ink: #22282b; --navy: #22282b; --gold: #C75D38; --cta: #C75D38;
            --sage: #5C9A7B; --stamp-text: #3f7259;
            --paper: #F4F5F6; --paper-edge: #e6e8ea; --white: #fff; --muted: #697079;
            --soft: #F2C2AC;
            --shadow: 0 18px 44px -26px rgba(40,50,70,.30);
            --lift-2: 0 26px 56px -28px rgba(40,50,70,.40);
            --display: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif;
            --body: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: linear-gradient(180deg, #EAF1F4 0%, #F2F5F6 40%, var(--paper) 100%);
            background-attachment: fixed;
            color: var(--ink);
            font-family: var(--body);
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        a { color: var(--cta); text-decoration: none; }
        a:hover { text-decoration: underline; }
        a:focus-visible,
        button:focus-visible { outline: 3px solid var(--cta); outline-offset: 2px; }

        /* ── Page wrapper ───────────────────────────────────────────────────────── */
        .cnf-outer {
            max-width: 680px;
            margin: 0 auto;
            padding: 48px 20px 72px;
        }

        /* ── Brand bar ──────────────────────────────────────────────────────────── */
        .cnf-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .cnf-brand a {
            font-family: var(--display);
            font-weight: 800;
            font-size: 22px;
            color: var(--ink);
            text-decoration: none;
            letter-spacing: -.01em;
        }
        .cnf-brand a b { color: var(--cta); }
        .cnf-brand .cnf-brand-note {
            display: block;
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 4px;
            letter-spacing: .02em;
        }

        /* ── Main card ──────────────────────────────────────────────────────────── */
        .cnf-card {
            background: var(--white);
            border: 1px solid var(--paper-edge);
            border-radius: 20px;
            box-shadow: var(--lift-2);
            overflow: hidden;
        }

        /* ── Hero banner (navy mesh) ────────────────────────────────────────────── */
        .cnf-banner {
            position: relative;
            overflow: hidden;
            padding: 30px 32px 28px;
            background:
                radial-gradient(520px 200px at 0% 50%, rgba(199,93,56,.36), transparent 60%),
                radial-gradient(480px 200px at 100% 50%, rgba(92,154,123,.28), transparent 60%),
                var(--navy);
            color: #fff;
        }
        .cnf-banner .cnf-eyebrow {
            font-family: var(--body);
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--soft);
            margin: 0 0 10px;
        }
        .cnf-banner h1 {
            font-family: var(--display);
            font-weight: 700;
            font-size: clamp(22px, 3.6vw, 30px);
            line-height: 1.1;
            letter-spacing: -.025em;
            color: #fff;
            margin: 0 0 6px;
        }
        .cnf-banner .cnf-sub {
            font-size: 15px;
            color: rgba(255,255,255,.78);
            margin: 0;
            max-width: 42ch;
        }
        /* decorative stamp mark */
        .cnf-banner-check {
            position: absolute;
            right: 28px;
            top: 50%;
            transform: translateY(-50%);
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255,255,255,.14);
            border: 2px solid rgba(255,255,255,.28);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #fff;
        }

        /* ── Card body ──────────────────────────────────────────────────────────── */
        .cnf-body { padding: 30px 32px 28px; }

        /* ── Lane badge ─────────────────────────────────────────────────────────── */
        .cnf-lane-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-family: var(--body);
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
            border-radius: 999px;
            padding: 6px 14px;
            margin: 0 0 24px;
        }
        .cnf-lane-badge::before {
            content: "";
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }
        .cnf-lane-badge.standard {
            background: #e6f3ec;
            color: #1c6535;
        }
        .cnf-lane-badge.standard::before { background: #27ae60; }
        .cnf-lane-badge.review {
            background: #fdf3e0;
            color: #7a5210;
        }
        .cnf-lane-badge.review::before { background: #e6a817; }

        /* ── Reference display ──────────────────────────────────────────────────── */
        .cnf-ref-block {
            background: var(--paper);
            border: 1.5px dashed var(--paper-edge);
            border-radius: 14px;
            padding: 18px 22px;
            text-align: center;
            margin: 0 0 24px;
        }
        .cnf-ref-block .cnf-ref-label {
            font-family: var(--body);
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--muted);
            margin: 0 0 8px;
        }
        .cnf-ref-block .cnf-ref-val {
            font-family: var(--display);
            font-weight: 800;
            font-size: clamp(24px, 4.5vw, 34px);
            color: var(--ink);
            letter-spacing: .03em;
            margin: 0;
            word-break: break-all;
        }
        .cnf-ref-block .cnf-ref-note {
            font-size: 13px;
            color: var(--muted);
            margin: 8px 0 0;
        }

        /* ── Body text ──────────────────────────────────────────────────────────── */
        .cnf-body p {
            color: #33454f;
            margin: 0 0 14px;
        }
        .cnf-body p:last-child { margin-bottom: 0; }

        /* ── Next steps list ────────────────────────────────────────────────────── */
        .cnf-steps-head {
            font-family: var(--body);
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: var(--stamp-text);
            margin: 24px 0 12px;
        }
        .cnf-steps {
            list-style: none;
            margin: 0 0 20px;
            padding: 0;
            counter-reset: cnf-step;
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .cnf-steps li {
            position: relative;
            padding: 16px 16px 16px 58px;
            border-top: 1px solid var(--paper-edge);
            counter-increment: cnf-step;
            font-size: 15px;
            color: #33454f;
            line-height: 1.55;
        }
        .cnf-steps li:first-child { border-top: 0; }
        .cnf-steps li::before {
            content: counter(cnf-step);
            position: absolute;
            left: 14px;
            top: 14px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--cta);
            color: #fff;
            font-family: var(--display);
            font-weight: 800;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cnf-steps-wrap {
            background: var(--paper);
            border: 1px solid var(--paper-edge);
            border-radius: 14px;
            overflow: hidden;
            margin: 0 0 24px;
        }

        /* ── Primary CTA button ─────────────────────────────────────────────────── */
        .cnf-cta {
            display: block;
            text-align: center;
            background: var(--cta);
            color: #fff;
            font-family: var(--display);
            font-weight: 700;
            font-size: 16px;
            padding: 16px 28px;
            border-radius: 14px;
            text-decoration: none;
            margin: 0 0 24px;
            transition: background .15s ease, transform .08s ease, box-shadow .15s ease;
        }
        .cnf-cta:hover {
            background: #b04e2c;
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 0 0 3px rgba(199,93,56,.18);
        }
        .cnf-cta:active { transform: translateY(1px); }

        /* ── Doc checklist section divider ──────────────────────────────────────── */
        .cnf-section-divider {
            border: 0;
            border-top: 1px dashed var(--paper-edge);
            margin: 26px 0;
        }

        /* ── Compliance / meta footnote ─────────────────────────────────────────── */
        .cnf-compliance {
            font-size: 12.5px;
            color: var(--muted);
            line-height: 1.65;
            margin: 22px 0 0;
            padding: 18px 20px;
            background: var(--paper);
            border: 1px solid var(--paper-edge);
            border-radius: 12px;
        }
        .cnf-meta {
            font-size: 11px;
            color: var(--muted);
            letter-spacing: .04em;
            text-transform: uppercase;
            margin: 14px 0 0;
            text-align: center;
        }

        /* ── Track link footer ──────────────────────────────────────────────────── */
        .cnf-footer-links {
            text-align: center;
            margin-top: 28px;
            font-size: 14px;
            color: var(--muted);
        }
        .cnf-footer-links a { color: var(--cta); font-weight: 600; }

        /* ── Responsive ─────────────────────────────────────────────────────────── */
        @media (max-width: 520px) {
            .cnf-outer { padding: 24px 12px 56px; }
            .cnf-banner { padding: 24px 20px 22px; }
            .cnf-body { padding: 22px 20px 20px; }
            .cnf-banner-check { display: none; }
            .cnf-ref-block .cnf-ref-val { font-size: 26px; }
        }
    </style>
</head>
<body>

<div class="cnf-outer">

    {{-- Brand bar --}}
    <div class="cnf-brand">
        <a href="/">Beyond <b>Passports</b></a>
        <span class="cnf-brand-note">Independent service — not a government website</span>
    </div>

    {{-- Main card --}}
    <div class="cnf-card">

        {{-- Hero banner --}}
        <div class="cnf-banner">
            <p class="cnf-eyebrow">Application received</p>
            <h1>Thank you — we have your&nbsp;details.</h1>
            <p class="cnf-sub">We'll be in touch at every step. Keep the reference below safe.</p>
            <div class="cnf-banner-check" aria-hidden="true">&#x2714;</div>
        </div>

        <div class="cnf-body">

            {{-- Lane badge --}}
            @if($isStandard)
                <span class="cnf-lane-badge standard">Standard service &middot; eligible</span>
            @else
                <span class="cnf-lane-badge review">Manual review &middot; human check</span>
            @endif

            {{-- Reference display --}}
            <div class="cnf-ref-block">
                <p class="cnf-ref-label">Your reference</p>
                <div class="cnf-ref-val">{{ $order->order_ref }}</div>
                <p class="cnf-ref-note">Keep this safe — you'll need it to track your application or talk to our team.</p>
            </div>

            {{-- Next steps --}}
            @if($isStandard)
                <p class="cnf-steps-head">Next steps</p>
                <div class="cnf-steps-wrap">
                    <ol class="cnf-steps">
                        <li>Complete secure payment for your chosen service tier
                            @if($order->total !== null)(total <strong>£{{ number_format((float) $order->total, 2) }}</strong>, service fee + government fee)@endif.</li>
                        <li>Upload your documents — we'll tell you exactly what's needed.</li>
                        <li>Our UK team checks everything and submits on your behalf, keeping every step tracked.</li>
                    </ol>
                </div>
                @php
                    // CheckoutController owns this route; fall back gracefully if not yet registered.
                    $checkoutUrl = \Illuminate\Support\Facades\Route::has('checkout.show')
                        ? route('checkout.show', $order->order_ref)
                        : '#';
                @endphp
                <a class="cnf-cta" href="{{ $checkoutUrl }}">Continue to secure payment &rarr;</a>
            @else
                <p class="cnf-steps-head">Next steps</p>
                <div class="cnf-steps-wrap">
                    <ol class="cnf-steps">
                        <li>A UK-based adviser reviews your answers — usually within one business day.</li>
                        <li>We confirm exactly what your case requires and send a <strong>personalised quote</strong>.</li>
                        <li>Nothing is charged until you approve that quote.</li>
                    </ol>
                </div>
                <p>No payment is taken yet. We'll be in touch using the contact details you gave us.</p>
            @endif

            {{-- Personalised document checklist (Document Requirements Engine).
                 $docItems comes from RequirementService::for($order) in the confirmation route.
                 Renders nothing heavy when empty — the partial handles its own empty state. --}}
            @if (! empty($docItems))
                <hr class="cnf-section-divider">
                @include('partials.doc-checklist', ['items' => $docItems, 'personalised' => true])
            @endif

            {{-- Optional FCA-safe travel-insurance introducer (no charge taken here). --}}
            @include('partials.insurance-introducer')

            {{-- Compliance copy — verbatim --}}
            <p class="cnf-compliance">
                Beyond Passports is an independent service and is not a government website. Our service
                fee is separate from, and additional to, any government or scheme fee. Express
                speeds our handling — it does not speed up or change the government's decision,
                and we cannot guarantee approval.
            </p>

            <p class="cnf-meta">REF {{ $order->order_ref }} &middot; STATUS {{ strtoupper((string) ($order->status->value ?? $order->status)) }}</p>

        </div>{{-- /.cnf-body --}}
    </div>{{-- /.cnf-card --}}

    {{-- Track link --}}
    <p class="cnf-footer-links">
        You can <a href="/track">track this application</a> any time using your reference.
        &nbsp;&middot;&nbsp;
        <a href="/">Back to Beyond Passports</a>
    </p>

</div>{{-- /.cnf-outer --}}

</body>
</html>
