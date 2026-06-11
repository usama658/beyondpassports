<?php
// T5: seed 8 Destination CPT entries with Pods field values. Run via wp eval-file. Idempotent by slug.
// NOTE: values are best-known starting data — VERIFY each against the official source before launch.
$dest = [
	[ 'turkey','Turkey', 1,'eVisa',1,90,180,'single',0,3,24,
		["Passport valid 150+ days from entry","Return or onward ticket","Accommodation details"],
		["Check your passport validity","Complete the application","Pay and receive your eVisa by email"],
		29,49,79, 1,'1949',1,1,'Passport must have a blank page.' ],
	[ 'egypt','Egypt', 1,'eVisa',1,30,90,'single',20,7,72,
		["Passport valid 6+ months from entry","Confirmed accommodation","Return ticket"],
		["Check passport validity","Complete the application","Pay and receive your eVisa by email"],
		29,49,79, 1,'1949',1,1,'' ],
	[ 'india','India', 1,'eVisa',1,30,365,'multiple',22,4,48,
		["Passport valid 6+ months with 2 blank pages","Passport-style photo","Return or onward ticket"],
		["Check passport validity","Complete the e-Visa application","Pay and receive your e-Visa by email"],
		29,49,79, 1,'1949',1,1,'' ],
	[ 'morocco','Morocco', 0,'visa-free',0,90,0,'single',0,0,0,
		["Passport valid 6+ months from entry"],
		["Check passport validity","Travel — no visa required for stays up to 90 days"],
		29,49,79, 1,'1968',1,1,'Visa-free for UK citizens up to 90 days.' ],
	[ 'uae','United Arab Emirates', 0,'visa-free',0,90,180,'multiple',0,0,0,
		["Passport valid 6+ months from entry"],
		["Check passport validity","Receive a free visa-on-arrival stamp for up to 90 days"],
		29,49,79, 1,'1968',1,1,'UK citizens get a free 90-day entry on arrival.' ],
	[ 'australia','Australia', 1,'eTA',1,90,365,'multiple',10,2,24,
		["Passport valid for the duration of stay","No serious criminal record"],
		["Check passport eligibility","Complete the ETA/eVisitor application","Receive approval linked to your passport by email"],
		29,49,79, 1,'1949',1,1,'ETA is electronically linked to your passport; no document is issued.' ],
	[ 'usa','United States', 1,'eTA',1,90,730,'multiple',17,3,24,
		["Passport valid for the duration of stay","ESTA-eligible (Visa Waiver Program) traveller"],
		["Check ESTA eligibility","Complete the ESTA application","Receive approval linked to your passport by email"],
		29,49,79, 1,'1949',1,1,'ESTA is electronically linked to your passport; no document is issued. Rules vary by state.' ],
	[ 'schengen','Schengen Area', 0,'visa-free',0,90,180,'multiple',0,0,0,
		["Passport valid 3+ months beyond departure, issued within last 10 years"],
		["Check passport validity","Travel visa-free for up to 90 days in any 180-day period (ETIAS expected in future)"],
		99,129,159, 0,'1968',0,1,'Visa-free for UK citizens; ETIAS travel authorisation expected later.' ],
];

$created = 0; $updated = 0;
foreach ( $dest as $d ) {
	list($slug,$name,$req,$type,$evisa,$stay,$valid,$entry,$gfee,$pstd,$pexp,$reqs,$steps,$ts,$te,$tp,$idprec,$idptype,$idppc,$idppaper,$notes) = $d;
	$existing = get_page_by_path( $slug, OBJECT, 'destination' );
	$postarr = [ 'post_type' => 'destination', 'post_name' => $slug, 'post_title' => $name, 'post_status' => 'publish' ];
	if ( $existing ) { $postarr['ID'] = $existing->ID; $pid = wp_update_post( $postarr ); $updated++; }
	else { $pid = wp_insert_post( $postarr ); $created++; }

	$meta = [
		'required_for_uk' => $req, 'visa_type' => $type, 'evisa_available' => $evisa,
		'max_stay_days' => $stay, 'validity_days' => $valid, 'entry' => $entry, 'govt_fee_gbp' => $gfee,
		'processing_standard_days' => $pstd, 'processing_express_hours' => $pexp,
		'requirements' => implode( "\n", $reqs ), 'how_to_steps' => implode( "\n", $steps ),
		'tier_standard_gbp' => $ts, 'tier_express_gbp' => $te, 'tier_premium_gbp' => $tp,
		'idp_recommended' => $idprec, 'idp_permit_type' => $idptype,
		'idp_required_photocard' => $idppc, 'idp_required_paper' => $idppaper, 'notes' => $notes,
	];
	foreach ( $meta as $k => $v ) { update_post_meta( $pid, $k, $v ); }
	echo "$slug -> #$pid\n";
}
echo "created: $created, updated: $updated\n";
