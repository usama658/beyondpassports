<?php
// T14: publish IDP hub + 3 driving-in-country pages. Idempotent by slug. Run via wp eval-file.
$disc = '<p style="font-size:13px;color:#5a6577;border-top:1px solid #e3e8f0;padding-top:12px;margin-top:24px">Independent service &mdash; <strong>not a government website</strong>. We provide guidance only; UK IDPs are issued by PayPoint.</p>';

$pages = [];

$pages['international-driving-permit'] = [ 'International Driving Permit (IDP): Do You Need One & How to Get It', <<<HTML
<p><strong>You can only get a UK International Driving Permit (IDP) in person at a PayPoint shop &mdash; it costs &pound;5.50, and you cannot apply online or by post.</strong> Take your full UK driving licence, a passport-sized photo and a valid passport, and you can usually walk out with the permit the same day. If you hold a <strong>photocard</strong> UK licence you do <strong>not</strong> need an IDP for the EU, EEA, Switzerland, Norway, Iceland or Liechtenstein &mdash; your licence is enough. You only need an IDP for certain countries outside Europe.</p>
<h2>What an IDP is</h2>
<p>An IDP is an official translation of your UK driving licence recognised under international road-traffic conventions. It is not a standalone licence &mdash; it is only valid alongside your full, in-date UK licence. Carry both and present both if asked.</p>
<h2>The three conventions</h2>
<table border="1" cellpadding="8" style="border-collapse:collapse">
<tr><th>Convention</th><th>Validity</th><th>Examples</th></tr>
<tr><td>1949 Geneva</td><td>1 year</td><td>USA, Canada, Japan, Australia, New Zealand, India</td></tr>
<tr><td>1968 Vienna</td><td>3 years (or until licence expires)</td><td>Turkey, Morocco, UAE, Egypt, Mexico, Thailand</td></tr>
<tr><td>1926 Paris</td><td>1 year</td><td>A small number of countries</td></tr>
</table>
<h2>The photocard exemption</h2>
<p>With a <strong>UK photocard licence</strong> you do <strong>not</strong> need an IDP in the EU/EEA, Switzerland, Norway, Iceland or Liechtenstein. You only need one for Europe if you hold a <strong>paper</strong> licence or a Crown Dependency licence (Guernsey, Jersey, Isle of Man, Gibraltar). For the USA, Turkey, Morocco, the UAE, Japan, Australia and most non-EEA countries you may need an IDP regardless.</p>
<h2>How and where to get one</h2>
<p>UK IDPs are issued <strong>in person only</strong> at selected <strong>PayPoint</strong> stores (they moved from the Post Office in April 2024). There is <strong>no online or postal service</strong>. Bring your full UK licence, a passport-sized photo, your passport, and &pound;5.50. You must be a GB resident aged 18+. Apply up to 3 months before travel.</p>
<h3>FAQ</h3>
<p><strong>Can I get a UK IDP online or by post?</strong> No &mdash; in person only at PayPoint. Beware sites claiming to post "official" IDPs.<br>
<strong>How much?</strong> &pound;5.50 each (you may need more than one type for a multi-country trip).<br>
<strong>France/Spain on a photocard?</strong> No IDP needed.<br>
<strong>Do I still carry my normal licence?</strong> Yes &mdash; the IDP is only valid alongside it.</p>
<p>Related: <a href="/ukvisa/driving-in-france/">Driving in France</a> &middot; <a href="/ukvisa/driving-in-usa/">Driving in the USA</a> &middot; <a href="/ukvisa/driving-in-turkey/">Driving in Turkey</a></p>
$disc
HTML ];

$pages['driving-in-france'] = [ 'Driving in France: What UK Drivers Need (No IDP Required)', <<<HTML
<p><strong>If you hold a UK photocard driving licence, you do NOT need an International Driving Permit to drive in France.</strong> Your photocard licence is accepted on its own. You will need your licence, valid insurance, a UK identifier (sticker or number plate), and to drive on the <strong>right</strong>. Only drivers with an old <strong>paper</strong> licence or a Crown Dependency licence need a 1968 IDP for France.</p>
<h2>What you need</h2>
<ul><li>UK photocard licence &mdash; no IDP needed</li><li>A <strong>UK</strong> identifier (the old GB sticker is no longer valid)</li><li>Valid insurance (no green card needed for the EU/EEA &mdash; confirm with your insurer)</li><li>Drive on the right; carry a reflective jacket + warning triangle</li></ul>
<h2>Do you need an IDP?</h2>
<p>For most UK drivers, <strong>no</strong>. You only need a <strong>1968</strong> IDP for France if you hold a paper licence or a Guernsey/Jersey/Isle of Man/Gibraltar licence.</p>
<h2>How to get one (if needed)</h2>
<p>Get a 1968 IDP <strong>in person</strong> at a <strong>PayPoint</strong> store &mdash; in person only, &pound;5.50. See the <a href="/ukvisa/international-driving-permit/">IDP hub</a>.</p>
<p>Related: <a href="/ukvisa/international-driving-permit/">IDP hub</a></p>
$disc
HTML ];

$pages['driving-in-usa'] = [ 'Driving in the USA: Why UK Drivers Need a 1949 IDP', <<<HTML
<p><strong>To drive in the USA, carry a 1949 Geneva Convention IDP alongside your UK driving licence.</strong> It is recognised across US states, often required by car-hire firms, and useful if stopped by police. Get your <strong>1949</strong> IDP in person at a <strong>PayPoint</strong> store for <strong>&pound;5.50</strong> before you fly &mdash; the USA drives on the <strong>right</strong>.</p>
<h2>What you need</h2>
<ul><li>Your full UK driving licence</li><li>A <strong>1949</strong> Geneva Convention IDP (recommended for all UK visitors; often required by hire firms)</li><li>Drive on the right; check car-hire insurance (CDW/liability) and minimum-age surcharges</li></ul>
<h2>Do you need an IDP?</h2>
<p>The USA is not covered by the EU photocard exemption. The <strong>1949</strong> IDP avoids problems at the hire desk and roadside, so it is recommended for every UK driver. Valid 1 year.</p>
<h2>How to get a 1949 IDP</h2>
<p>In person only at PayPoint &mdash; no online/postal option. Bring your licence, a passport photo and passport; pay &pound;5.50; ask for the 1949 permit. See the <a href="/ukvisa/international-driving-permit/">IDP hub</a>.</p>
<p>Related: <a href="/ukvisa/international-driving-permit/">IDP hub</a> &middot; <a href="/ukvisa/usa/">USA visa guide</a></p>
$disc
HTML ];

$pages['driving-in-turkey'] = [ 'Driving in Turkey: Why UK Drivers Need a 1968 IDP', <<<HTML
<p><strong>To drive in Turkey, carry a 1968 Vienna Convention IDP alongside your UK driving licence.</strong> Turkey recognises the 1968 IDP (valid 3 years). Get yours in person at a <strong>PayPoint</strong> store for <strong>&pound;5.50</strong>, display a <strong>UK</strong> identifier, arrange insurance (you will likely need a <strong>green card</strong>), and drive on the <strong>right</strong>.</p>
<h2>What you need</h2>
<ul><li>Your full UK driving licence</li><li>A <strong>1968</strong> Vienna Convention IDP</li><li>A <strong>green card</strong> (Turkey is outside the EU/EEA waiver)</li><li>A UK identifier; drive on the right</li></ul>
<h2>Do you need an IDP?</h2>
<p>Yes &mdash; Turkey is not covered by the photocard exemption. Carry a <strong>1968</strong> IDP with your UK licence. Valid 3 years (or until your licence expires).</p>
<h2>How to get a 1968 IDP</h2>
<p>In person only at PayPoint &mdash; no online/postal option. Bring your licence, a passport photo and passport; pay &pound;5.50; ask for the 1968 permit. See the <a href="/ukvisa/international-driving-permit/">IDP hub</a>.</p>
<p>Related: <a href="/ukvisa/international-driving-permit/">IDP hub</a> &middot; <a href="/ukvisa/turkey/">Turkey visa guide</a></p>
$disc
HTML ];

foreach ( $pages as $slug => $pd ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$arr = [ 'post_type' => 'page', 'post_name' => $slug, 'post_title' => $pd[0], 'post_content' => $pd[1], 'post_status' => 'publish' ];
	if ( $existing ) { $arr['ID'] = $existing->ID; wp_update_post( $arr ); echo "updated $slug\n"; }
	else { wp_insert_post( $arr ); echo "created $slug\n"; }
}
