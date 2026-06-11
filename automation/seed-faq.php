<?php
// Add a Pods 'faq' field + seed per-destination FAQ (JSON of {q,a}). Run via wp eval-file.
if ( function_exists( 'pods_api' ) ) {
	$api = pods_api();
	if ( empty( $api->load_field( [ 'pod' => 'destination', 'name' => 'faq' ] ) ) ) {
		$api->save_field( [ 'pod' => 'destination', 'name' => 'faq', 'type' => 'paragraph', 'label' => 'FAQ (JSON)' ] );
		echo "faq field created\n";
	}
}

$faq = [
'turkey' => [
	['Do UK citizens need a visa for Turkey?','No. British citizens can visit Turkey visa-free for up to 90 days, so there is no visa or eVisa to buy. Make sure your passport has at least 150 days\' validity from entry.'],
	['How long can I stay in Turkey without a visa?','Up to 90 days within any 180-day period. To stay longer you must apply for a residence permit in Turkey before your 90 days run out.'],
	['Does it cost anything to enter Turkey?','Nothing for entry — Turkey is visa-free for UK passport holders. You only need a valid passport with a blank page.'],
	['Do I need anything special to drive in Turkey?','Yes — alongside your full UK licence carry a 1968 International Driving Permit, available in person at PayPoint for £5.50.'],
],
'egypt' => [
	['Do UK citizens need a visa for Egypt?','Yes, British citizens need a visa, and the simplest route is the official Egypt eVisa applied for online before you travel. We handle the application for you.'],
	['How long can I stay in Egypt on the eVisa?','The standard tourist eVisa allows up to 30 days. Multiple-entry options are available for trips with re-entry.'],
	['How much does the Egypt eVisa cost?','The government fee is around £20. Our service fee is on top and covers checking and submitting your application.'],
	['How long does the Egypt eVisa take?','Usually a few business days; apply at least a week ahead. We review every application before submission and keep you updated.'],
],
'india' => [
	['Do UK citizens need a visa for India?','Yes, British citizens need a visa; most tourists use the India eVisa applied for online. We manage the application including the photo and passport-page requirements.'],
	['How long can I stay in India on the eVisa?','The 30-day tourist eVisa allows up to 30 days with double entry. 1-year and 5-year options also exist.'],
	['How much does the India eVisa cost?','The government fee for the 30-day eVisa is around £8 (it is seasonal). Our service fee is added on top.'],
	['How long does the India eVisa take?','Usually a few business days; apply at least four days before you fly. We check documents before submission to reduce rejections.'],
],
'morocco' => [
	['Do UK citizens need a visa for Morocco?','No. British citizens travel visa-free for up to 90 days, so there is no visa or eVisa to apply for.'],
	['How long can I stay in Morocco without a visa?','Up to 90 days. To stay longer, apply to the local authorities before your 90 days expire.'],
	['Does entering Morocco cost anything?','No entry or visa cost for UK passport holders for stays up to 90 days. Just a valid passport.'],
	['What do I need to enter Morocco?','A passport valid for your stay, plus a return/onward ticket and accommodation details may be requested on arrival.'],
],
'uae' => [
	['Do UK citizens need a visa for the UAE?','No. British citizens get a free visa-on-arrival, granted automatically at the border for tourism and short visits.'],
	['How long can I stay in the UAE?','Typically up to 30 days, usually extendable once for a further 30 days for a fee while in the country.'],
	['How much does a UAE visa cost for UK citizens?','Nothing for entry — the visa-on-arrival is free. You only pay if you choose to extend.'],
	['What do I need to enter the UAE?','A UK passport valid at least six months from entry, plus a return or onward ticket.'],
],
'australia' => [
	['Do UK citizens need a visa for Australia?','Yes, a visa or travel authorisation is required before flying. Most UK passport holders use the free eVisitor (subclass 651); we handle the application.'],
	['How long can I stay in Australia on an eVisitor?','Up to 90 days per entry within a 12-month period. It is electronically linked to your passport — no label or stamp.'],
	['How much does the Australia eVisitor cost?','The eVisitor itself is free of any Australian government charge. Our service fee covers completing and submitting it accurately.'],
	['How long does the Australia eVisitor take?','Often a few days, sometimes longer if checks are required, so apply well ahead. We submit and keep you updated.'],
],
'usa' => [
	['Do UK citizens need a visa for the USA?','For short trips under the Visa Waiver Program you need an approved ESTA, not a full visa. We handle the ESTA application and check every detail.'],
	['How long can I stay in the USA on an ESTA?','Up to 90 days per visit. The ESTA is linked to your passport and valid for two years (or until your passport expires).'],
	['How much does a US ESTA cost?','The official government fee is around £32 ($40). Our service fee is in addition and covers reviewing and submitting your application.'],
	['How does the ESTA work?','You apply online; many approvals arrive within minutes to 72 hours. Once approved it is tied to your passport for two years — nothing to print.'],
],
'schengen' => [
	['Do UK citizens need a visa for Europe (Schengen)?','No. British citizens travel visa-free for short stays. ETIAS is expected later but is not yet live, so there is currently nothing to apply for.'],
	['How long can UK citizens stay in the Schengen Area?','Up to 90 days in any rolling 180-day period, shared across all Schengen countries combined.'],
	['Does it cost anything to visit Schengen countries?','No visa fee for visa-free stays under 90 days. ETIAS will carry a small fee when it launches.'],
	['What do I need to travel to the Schengen Area?','A UK passport less than 10 years old on entry and valid at least three months after departure.'],
],
];
$n = 0;
foreach ( $faq as $slug => $items ) {
	$p = get_page_by_path( $slug, OBJECT, 'destination' );
	if ( ! $p ) { continue; }
	$json = wp_json_encode( array_map( fn( $i ) => [ 'q' => $i[0], 'a' => $i[1] ], $items ) );
	update_post_meta( $p->ID, 'faq', $json );
	$n++;
}
echo "seeded faq for $n destinations\n";
