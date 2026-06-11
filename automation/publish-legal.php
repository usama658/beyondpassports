<?php
// T15: publish/update legal + trust pages. Idempotent by slug. Run via wp eval-file.
$disc = '<p style="background:#EEF3FA;border-left:4px solid #1456B8;padding:10px 14px;margin:0 0 18px"><strong>Independent service &mdash; not a government website.</strong> Our service fee is in addition to any official government fees.</p>';
$p = [];

$p['how-it-works'] = [ 'How It Works', $disc . <<<HTML
<p>Getting your visa, eVisa or ETA sorted should be simple. Here is how we help British travellers from start to finish.</p>
<h2>1. Check the requirements (free)</h2><p>Use our free checker to confirm whether your destination needs a visa, eVisa, ETA, or nothing at all. No payment, no obligation.</p>
<h2>2. Start your application</h2><p>Begin through our guided online form. We translate confusing official questions into plain English and flag the documents you'll need.</p>
<h2>3. We review your documents before submission</h2><p>Our team checks your application for errors and common rejection triggers <em>before</em> anything is sent &mdash; this is where we earn our fee.</p>
<h2>4. We submit to the authority</h2><p>Once correct and complete, we submit to the official system on your behalf and pay the official fee at cost.</p>
<h2>5. You receive your visa</h2><p>Your eVisa arrives by email; an ETA is confirmed as electronically linked to your passport. We keep you updated throughout.</p>
<h2>About express tiers</h2><p>Express and Premium speed up <strong>our</strong> handling and queue position. They do <strong>not</strong> change the government's decision or its official processing time.</p>
HTML ];

$p['refunds'] = [ 'Refund Policy', $disc . <<<HTML
<p>Every order has two parts &mdash; the official government fee and our service fee &mdash; refunded differently.</p>
<h2>Government / official fees</h2><p>Once an application is <strong>submitted</strong> to the authority, the official fee is <strong>non-refundable</strong> (set by the authority; passed on at cost).</p>
<h2>Our service fee &mdash; sliding scale</h2>
<table border="1" cellpadding="8" style="border-collapse:collapse"><tr><th>Stage</th><th>Service-fee refund</th></tr>
<tr><td>Before we begin / before submission</td><td><strong>100%</strong></td></tr>
<tr><td>During document review</td><td><strong>~75%</strong></td></tr>
<tr><td>Once filed with the authority</td><td><strong>0%</strong></td></tr></table>
<h2>24-hour cancellation</h2><p>Cancel for a full service-fee refund within 24 hours of ordering, provided we have not yet submitted your application.</p>
<h2>How to request</h2><p>Email refunds@[yourdomain] with your order reference. We aim to acknowledge within one working day; eligible refunds are processed to your original payment method within 5&ndash;10 working days.</p>
HTML ];

$p['terms'] = [ 'Terms of Service', $disc . <<<HTML
<h2>1. Who we are</h2><p>We are an independent visa-facilitation service. We assist with preparing and submitting visa/eVisa/ETA applications and provide IDP guidance. We do <strong>not</strong> issue visas, ETAs, IDPs or any official document &mdash; these are issued solely by the relevant authorities.</p>
<h2>2. Fees</h2><p>Our service fee is separate from and additional to any official government fee. Government fees are shown at cost. Paying our fee secures our assistance; it does not guarantee approval.</p>
<h2>3. Accuracy of your information</h2><p>You are responsible for the accuracy of the information and documents you provide. We are not liable for delays or rejections caused by inaccurate or incomplete information.</p>
<h2>4. No guarantee of outcome</h2><p>All decisions rest with the issuing authority. We cannot influence, predict or guarantee any decision or processing time.</p>
<h2>5. Liability</h2><p>To the fullest extent permitted by law, our total liability is limited to the service fee you paid. Nothing excludes liability that cannot lawfully be excluded.</p>
<h2>6. International Driving Permits</h2><p>IDP support is guided self-service. Permits are issued only by authorised bodies; we do not issue IDPs.</p>
<h2>7. Governing law</h2><p>These terms are governed by the laws of England and Wales.</p>
HTML ];

$p['privacy'] = [ 'Privacy Policy', $disc . <<<HTML
<p>We comply with the UK GDPR and the Data Protection Act 2018 as an independent UK data controller.</p>
<h2>What we collect</h2><p>Name, contact details, passport/travel-document details, nationality, date of birth, trip details, supporting documents and payment information &mdash; only what is necessary to deliver your service.</p>
<h2>How we handle documents</h2><p>Your data and documents are transmitted and stored using encryption in transit and at rest. Access is restricted to staff who need it.</p>
<h2>Retention and deletion</h2><p>We retain application data only as long as needed to deliver your service and meet legal obligations. After delivery, personal documents are securely deleted per our retention schedule.</p>
<h2>Your rights</h2><p>You may access, rectify, erase, restrict or port your data, and object to certain processing. Email privacy@[yourdomain]. You may also complain to the ICO.</p>
<h2>ICO registration</h2><p>We are registered with the Information Commissioner's Office. Registration number: [ICO NUMBER].</p>
<h2>Cookies and analytics</h2><p>We use essential cookies to run the site and, only with your consent, analytics cookies to improve it. Manage non-essential cookies any time via our banner.</p>
HTML ];

$p['about'] = [ 'About Us', $disc . <<<HTML
<h2>Who we are</h2><p>An independent, UK-based visa-facilitation service built for British travellers heading abroad &mdash; visas, eVisas, ETAs and International Driving Permit guidance.</p>
<h2>Our mission</h2><p>To make outbound travel paperwork simple, transparent and reliable, so you can focus on the trip, not the bureaucracy.</p>
<h2>Why use us</h2><ul><li>Free requirements checker</li><li>Human document review before submission</li><li>Transparent pricing &mdash; service fee shown separately from official fees</li><li>UK-based support</li><li>Secure handling &mdash; encrypted documents, deleted after delivery</li></ul>
<h2>We are not a government service</h2><p>We are a private company, not affiliated with or endorsed by any government or immigration authority. You can apply directly with the authority yourself, often for the official fee alone. What you pay us is a service fee for our checking, guidance and submission support.</p>
HTML ];

$p['pricing'] = [ 'Pricing', $disc . <<<HTML
<p>Our pricing is always split into two clear parts.</p>
<h2>1. Our service fee</h2>
<table border="1" cellpadding="8" style="border-collapse:collapse"><tr><th>Tier</th><th>What you get</th><th>Service fee</th></tr>
<tr><td><strong>Standard</strong></td><td>Guided application, document review, submission, email updates</td><td>from &pound;29</td></tr>
<tr><td><strong>Express</strong></td><td>Standard + priority placement in our review queue + faster handling</td><td>from &pound;49</td></tr>
<tr><td><strong>Premium</strong></td><td>Express + fastest our-side handling + dedicated support</td><td>from &pound;79</td></tr></table>
<p>Express and Premium speed up <strong>our</strong> handling and queue only &mdash; not the government's processing time or decision.</p>
<h2>2. The official government fee</h2><p>Set by the issuing authority, shown at cost, separately, and passed on unchanged. We add nothing to it.</p>
<p><em>Example: Service fee (Standard) &pound;29 + Government fee &pound;20 = Total &pound;49.</em></p>
HTML ];

foreach ( $p as $slug => $pd ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$arr = [ 'post_type' => 'page', 'post_name' => $slug, 'post_title' => $pd[0], 'post_content' => $pd[1], 'post_status' => 'publish' ];
	if ( $existing ) { $arr['ID'] = $existing->ID; wp_update_post( $arr ); echo "updated $slug\n"; }
	else { wp_insert_post( $arr ); echo "created $slug\n"; }
}
