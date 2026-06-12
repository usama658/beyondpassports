<?php
// Publish customer-facing delivery pages from the delivery framework: how-it-works + per-stage guides.
// Idempotent upsert by slug. Run: wp eval-file automation/publish-process-pages.php
$foot = '<p style="font-size:.9em;color:#666;border-top:1px solid #eee;padding-top:10px;margin-top:20px">Independent service — not a government website. Our service fee is separate from any government fee. Express tiers speed up our handling, not the government\'s decision. We never guarantee a government decision.</p>';

$pages = [
'how-it-works' => [ 'How It Works — Your Visa, Step by Step',
 '<p style="font-size:18px;font-weight:500;background:#EEF3FA;padding:14px 18px;border-radius:8px">From the moment you apply to the day your visa arrives, here is exactly what happens — and what we need from you at each step.</p>'
 .'<h2>1. You apply &amp; pay</h2><p>Choose your destination, answer a few questions and pay securely. You get an instant confirmation with your order reference.</p>'
 .'<h2>2. We confirm what\'s needed</h2><p>We send you the exact list of documents for your destination — usually your passport bio page and a passport-style photo.</p>'
 .'<h2>3. You send your documents</h2><p>Upload or email them using our simple guide. Clear photos are fine.</p>'
 .'<h2>4. We check &amp; submit</h2><p>Our team reviews everything for errors before submitting to the official system and paying the government fee on your behalf.</p>'
 .'<h2>5. We track the decision</h2><p>We monitor your application and respond to any queries. You can check progress any time on our <a href="/ukvisa/track/">tracking page</a>.</p>'
 .'<h2>6. We deliver your visa</h2><p>Once granted, we send it to you with simple guidance on using it at the border.</p>'
 .'<p><a href="/ukvisa/apply/">Start my application →</a></p>',
 'How our UK visa service works — the 6 steps from applying to delivery, and what we need from you at each one.','how it works visa service'],

'how-to-send-documents' => [ 'How to Send Your Documents',
 '<p style="font-size:18px;font-weight:500;background:#EEF3FA;padding:14px 18px;border-radius:8px">Most visas need just two things: a clear photo of your passport bio page and a passport-style photo. Here\'s how to get them right first time.</p>'
 .'<h2>Your passport bio page</h2><p>Lay your passport flat in good light. Capture the whole page — name, photo, number and dates — with no glare or fingers covering text. A phone photo or scan is fine.</p>'
 .'<h2>Your photo</h2><p>Plain light background, face centred, no hat or sunglasses, neutral expression. Recent (within 6 months).</p>'
 .'<h2>Check before you send</h2><p>Passport valid at least 6 months beyond your travel date (some countries require more — we\'ll tell you). Name matches your booking.</p>'
 .'<h2>Sending</h2><p>Reply to your confirmation email with the files attached, or use the upload link we send. We\'ll confirm receipt and flag anything that needs redoing.</p>',
 'How to photograph and send your passport and photo for a visa application — simple tips to avoid delays.','how to send visa documents'],

'using-your-visa-on-arrival' => [ 'Using Your Visa or Authorisation on Arrival',
 '<p style="font-size:18px;font-weight:500;background:#EEF3FA;padding:14px 18px;border-radius:8px">Your visa is approved — here\'s how to use it smoothly at the border.</p>'
 .'<h2>e-Visas</h2><p>Print your approved e-visa and carry it with your passport. Some countries also accept a digital copy, but a printout is safest.</p>'
 .'<h2>ETAs / ESTA / eTA</h2><p>These are an authorisation linked to your passport — there is no separate document. Travel on the same passport you applied with. Carrying a printout of the confirmation can help at check-in.</p>'
 .'<h2>At the border</h2><p>Have your passport, your approval, and your onward/return ticket and accommodation details ready. The final decision to admit you is always made by a border officer.</p>'
 .'<h2>If anything\'s wrong</h2><p>Contact us before you travel and we\'ll help.</p>',
 'How to use your approved e-visa or ETA at the border — what to carry and what to expect from immigration.','using visa on arrival'],

'idp-paypoint-checklist' => [ 'International Driving Permit (IDP): PayPoint Checklist',
 '<p style="font-size:18px;font-weight:500;background:#EEF3FA;padding:14px 18px;border-radius:8px">An IDP is issued in person at a PayPoint in the UK — it can\'t be bought online. We tell you the right permit for your destination and exactly what to bring. This is a guided self-service: you collect it yourself.</p>'
 .'<h2>Which permit you need</h2><p>Countries recognise different conventions (1926, 1949 or 1968). Some trips need more than one. We confirm the correct type for your destination before you go.</p>'
 .'<h2>What to bring to PayPoint</h2><p>Your full UK driving licence, a passport-style photo, and the fee. If you hold a paper licence you may also need ID.</p>'
 .'<h2>Photocard exemption</h2><p>For some countries a UK photocard licence alone is enough and no IDP is required — we\'ll tell you if that applies, so you don\'t pay for something you don\'t need.</p>'
 .'<h2>Get the right advice first</h2><p><a href="/ukvisa/idp-checker/">Use our IDP checker</a> or message us, then visit any PayPoint store.</p>',
 'A clear checklist for getting your International Driving Permit at PayPoint — which permit, what to bring, and the photocard exemption.','international driving permit paypoint'],
];

foreach ( $pages as $slug => $d ) {
	$content = $d[1] . $foot;
	$e = get_page_by_path( $slug, OBJECT, 'page' );
	$arr = [ 'post_type' => 'page', 'post_name' => $slug, 'post_title' => $d[0], 'post_content' => $content, 'post_status' => 'publish' ];
	if ( $e ) { $arr['ID'] = $e->ID; $id = $e->ID; wp_update_post( $arr ); } else { $id = wp_insert_post( $arr ); }
	update_post_meta( $id, 'rank_math_title', $d[0] );
	update_post_meta( $id, 'rank_math_description', $d[2] );
	update_post_meta( $id, 'rank_math_focus_keyword', $d[3] );
	echo "$slug -> #$id\n";
}
echo "done\n";
