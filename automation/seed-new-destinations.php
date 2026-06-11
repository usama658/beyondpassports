<?php
// Seed 6 new destinations (Thailand, Vietnam, Kenya, Tanzania, Sri Lanka, Brazil). Run via wp eval-file.
// Data researched vs gov.uk 2026. VERIFY before launch (Thailand 60-day under review; Sri Lanka ETA free from 25 May 2026).
$dest = [
	[ 'thailand','Thailand', 0,'visa-free',0,60,0,'single',0,0,0,
		["Passport valid 6+ months with 1 blank page","Complete the Thailand Digital Arrival Card (TDAC) online","Proof of onward/return travel may be requested"],
		["Check 60-day visa-free eligibility","Complete the free TDAC within 3 days of arrival","Present passport and TDAC QR at immigration"],
		29,49,79, 1,'1949',1,1,'Visa-free 60 days (TDAC mandatory). 60-day rule under review in 2026 — verify before travel.' ],
	[ 'vietnam','Vietnam', 0,'visa-free',0,45,0,'single',0,0,0,
		["Passport valid 6+ months","Onward/return travel","Accommodation details"],
		["Visa-free up to 45 days","For longer/multi-entry, apply at the official eVisa portal","Travel on the passport you applied with"],
		29,49,79, 0,'1968',0,0,'Visa-free 45 days; eVisa needed only for longer/multiple entry. IDPs are not legally recognised in Vietnam.' ],
	[ 'kenya','Kenya', 1,'eTA',1,90,90,'single',24,3,0,
		["Passport valid 6+ months with 2 blank pages","Confirmed accommodation + return ticket","Yellow fever certificate if arriving from a risk country"],
		["Apply online for the eTA before travel","Upload passport bio page and photo, pay the fee","Receive your approved eTA by email"],
		29,49,79, 1,'1968',1,1,'eTA replaced the visa; mandatory for all visitors. Govt fee ~£24.' ],
	[ 'tanzania','Tanzania', 1,'eVisa',1,90,90,'single',39,5,0,
		["Passport valid 6+ months with 1 blank page","Proof of return/onward travel","Zanzibar requires mandatory inbound travel insurance"],
		["Apply at the official eVisa portal","Complete the form, upload passport + photo, pay the fee","Await approval (4-6 working days) and print"],
		35,55,85, 1,'1949',1,1,'Tourist eVisa ~£39, single entry, up to 90 days.' ],
	[ 'sri-lanka','Sri Lanka', 1,'eTA',1,30,30,'multiple',0,3,0,
		["Passport valid 6+ months","Onward/return ticket and accommodation","Sufficient funds"],
		["Apply online for the ETA before departure","Complete the form with your passport details","Receive ETA approval by email"],
		29,49,79, 1,'1949',1,1,'ETA mandatory; free for UK passport holders for 30 days (double entry) from 25 May 2026.' ],
	[ 'brazil','Brazil', 0,'visa-free',0,90,0,'multiple',0,0,0,
		["Passport valid 6+ months from arrival","Onward/return travel and accommodation may be requested","Ensure your entry stamp is obtained"],
		["Confirm visa-free eligibility","Travel with a valid passport","Get your passport stamped on arrival"],
		29,49,79, 1,'1926',1,1,'Visa-free for UK up to 90 days (extendable). Brazil eVisa rollout does NOT apply to the UK.' ],
];
$created = 0; $updated = 0;
foreach ( $dest as $d ) {
	list($slug,$name,$req,$type,$evisa,$stay,$valid,$entry,$gfee,$pstd,$pexp,$reqs,$steps,$ts,$te,$tp,$idprec,$idptype,$idppc,$idppaper,$notes) = $d;
	$existing = get_page_by_path( $slug, OBJECT, 'destination' );
	$postarr = [ 'post_type' => 'destination', 'post_name' => $slug, 'post_title' => $name, 'post_status' => 'publish' ];
	if ( $existing ) { $postarr['ID'] = $existing->ID; $pid = wp_update_post( $postarr ); $updated++; }
	else { $pid = wp_insert_post( $postarr ); $created++; }
	$meta = [
		'required_for_uk' => $req, 'visa_type' => $type, 'evisa_available' => $evisa, 'max_stay_days' => $stay,
		'validity_days' => $valid, 'entry' => $entry, 'govt_fee_gbp' => $gfee, 'processing_standard_days' => $pstd,
		'processing_express_hours' => $pexp, 'requirements' => implode( "\n", $reqs ), 'how_to_steps' => implode( "\n", $steps ),
		'tier_standard_gbp' => $ts, 'tier_express_gbp' => $te, 'tier_premium_gbp' => $tp, 'idp_recommended' => $idprec,
		'idp_permit_type' => $idptype, 'idp_required_photocard' => $idppc, 'idp_required_paper' => $idppaper, 'notes' => $notes,
	];
	foreach ( $meta as $k => $v ) { update_post_meta( $pid, $k, $v ); }
	echo "$slug -> #$pid\n";
}
echo "created $created, updated $updated\n";
