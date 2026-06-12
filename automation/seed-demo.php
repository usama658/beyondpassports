<?php
/**
 * Idempotent demo-data seeder for the UK visa WordPress site.
 *
 * Run via: wp eval-file automation/seed-demo.php
 *
 * Every object created carries meta ukv_demo='1' so a re-run wipes prior
 * demo data first (clean, no duplication) and so it's easy to remove later.
 *
 * This is LIVE demo data left in the DB on purpose so the user can view the
 * dashboards populated. It is NOT a test leak.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ------------------------------------------------------------------ helpers */

/** Delete all prior demo objects (orders, barriers, testimonials) tagged ukv_demo=1. */
function ukvseed_wipe(): array {
	$wiped = [ 'orders' => 0, 'barriers' => 0, 'testimonials' => 0 ];

	foreach ( [ 'ukv_order' => 'orders', 'ukv_barrier' => 'barriers' ] as $pt => $bucket ) {
		$ids = get_posts( [
			'post_type'   => $pt,
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => [ [ 'key' => 'ukv_demo', 'value' => '1' ] ],
		] );
		foreach ( $ids as $id ) {
			if ( wp_delete_post( $id, true ) ) { $wiped[ $bucket ]++; }
		}
	}

	// Testimonials: posts in category 'testimonial' with ukv_demo=1.
	$tcat = get_category_by_slug( 'testimonial' );
	$tids = get_posts( [
		'post_type'   => 'post',
		'post_status' => 'any',
		'numberposts' => -1,
		'fields'      => 'ids',
		'meta_query'  => [ [ 'key' => 'ukv_demo', 'value' => '1' ] ],
	] );
	foreach ( $tids as $id ) {
		if ( wp_delete_post( $id, true ) ) { $wiped['testimonials']++; }
	}

	return $wiped;
}

/** Force the post_date (and gmt) of a post to N days ago — drives SLA breach displays. */
function ukvseed_backdate( int $pid, int $days_ago ): void {
	$ts  = time() - ( $days_ago * DAY_IN_SECONDS );
	$loc = gmdate( 'Y-m-d H:i:s', $ts );
	$gmt = gmdate( 'Y-m-d H:i:s', $ts );
	wp_update_post( [
		'ID'            => $pid,
		'post_date'     => $loc,
		'post_date_gmt' => $gmt,
	] );
	// Keep ukv_created consistent with the backdated post date.
	update_post_meta( $pid, 'ukv_created', $ts );
}

/* ------------------------------------------------------------------ seed */

$report = [ 'orders' => 0, 'barriers' => 0, 'testimonials' => 0, 'consent' => 0, 'order_ids' => [] ];

$wiped = ukvseed_wipe();

// Order blueprints. Covers all 6 destinations and all 9 statuses, varied tiers/totals.
// Travel dates: a few set, some within 10 days. Blockers: a few non-none.
// Journeys: 4-5 orders carry 1-2 notes with fake names + channels.
$today = time();
$d     = fn( $offset ) => gmdate( 'Y-m-d', $today + ( $offset * DAY_IN_SECONDS ) );
$dt    = fn( $offset ) => gmdate( 'Y-m-d H:i', $today + ( $offset * DAY_IN_SECONDS ) );

$orders = [
	// High-risk: OPEN Egypt order with a blocker + within-10-days travel.
	[
		'order_ref' => 'UKV-DEMO-001', 'name' => 'Demo Alice', 'email' => 'demo.alice@example.test',
		'destination' => 'Egypt', 'tier' => 'Express', 'total' => 79,
		'status' => 'awaiting_docs', 'blocker' => 'docs_missing', 'travel_date' => $d( 6 ),
		'passport_number' => 'DEMO000001',
		'journey' => [
			[ 'date' => $dt( -3 ) . '', 'agent' => 'Sam', 'channel' => 'call',     'text' => 'Welcome call, explained the e-visa steps.' ],
			[ 'date' => $dt( -1 ) . '', 'agent' => 'Sam', 'channel' => 'whatsapp', 'text' => 'Chased passport scan; client said it will follow today.' ],
		],
	],
	// Egypt rejected x3 (push Egypt rejection rate high).
	[ 'order_ref' => 'UKV-DEMO-002', 'name' => 'Demo Bob',    'email' => 'demo.bob@example.test',   'destination' => 'Egypt', 'tier' => 'Standard', 'total' => 49, 'status' => 'rejected', 'blocker' => 'none', 'passport_number' => 'DEMO000002' ],
	[ 'order_ref' => 'UKV-DEMO-003', 'name' => 'Demo Carol',  'email' => 'demo.carol@example.test', 'destination' => 'Egypt', 'tier' => 'Standard', 'total' => 49, 'status' => 'rejected', 'blocker' => 'eligibility', 'passport_number' => 'DEMO000003' ],
	[ 'order_ref' => 'UKV-DEMO-004', 'name' => 'Demo Dan',    'email' => 'demo.dan@example.test',   'destination' => 'Egypt', 'tier' => 'Express',  'total' => 79, 'status' => 'rejected', 'blocker' => 'none', 'passport_number' => 'DEMO000004' ],
	// Egypt won (one success for contrast).
	[
		'order_ref' => 'UKV-DEMO-005', 'name' => 'Demo Erin', 'email' => 'demo.erin@example.test',
		'destination' => 'Egypt', 'tier' => 'Premium', 'total' => 129, 'status' => 'won', 'blocker' => 'none',
		'passport_number' => 'DEMO000005', 'travel_date' => $d( 21 ),
		'journey' => [
			[ 'date' => $dt( -10 ), 'agent' => 'Priya', 'channel' => 'email', 'text' => 'Confirmation email sent; e-visa delivered.' ],
		],
	],
	// Other destinations, remaining statuses.
	[
		'order_ref' => 'UKV-DEMO-006', 'name' => 'Demo Frank', 'email' => 'demo.frank@example.test',
		'destination' => 'Turkey', 'tier' => 'Standard', 'total' => 39, 'status' => 'paid', 'blocker' => 'none',
		'travel_date' => $d( 4 ),
		'journey' => [
			[ 'date' => $dt( -1 ), 'agent' => 'Sam', 'channel' => 'email', 'text' => 'Payment received, intake form sent.' ],
		],
	],
	[ 'order_ref' => 'UKV-DEMO-007', 'name' => 'Demo Grace',  'email' => 'demo.grace@example.test',  'destination' => 'India',     'tier' => 'Express',  'total' => 89,  'status' => 'doc_review',        'blocker' => 'customer_deciding' ],
	[
		'order_ref' => 'UKV-DEMO-008', 'name' => 'Demo Henry', 'email' => 'demo.henry@example.test',
		'destination' => 'Thailand', 'tier' => 'Standard', 'total' => 45, 'status' => 'submitted', 'blocker' => 'none',
		'travel_date' => $d( 18 ),
		'journey' => [
			[ 'date' => $dt( -2 ), 'agent' => 'Priya', 'channel' => 'whatsapp', 'text' => 'Application submitted to portal; awaiting reference.' ],
			[ 'date' => $dt( -1 ), 'agent' => 'Priya', 'channel' => 'email',    'text' => 'Shared submission receipt with client.' ],
		],
	],
	[ 'order_ref' => 'UKV-DEMO-009', 'name' => 'Demo Ivy',    'email' => 'demo.ivy@example.test',    'destination' => 'USA',       'tier' => 'Premium',  'total' => 149, 'status' => 'awaiting_decision', 'blocker' => 'payment_pending' ],
	[
		'order_ref' => 'UKV-DEMO-010', 'name' => 'Demo Jack', 'email' => 'demo.jack@example.test',
		'destination' => 'Australia', 'tier' => 'Standard', 'total' => 59, 'status' => 'delivered', 'blocker' => 'none',
		'consent' => true,
		'journey' => [
			[ 'date' => $dt( -5 ), 'agent' => 'Sam', 'channel' => 'call', 'text' => 'Delivered ETA confirmation; client very happy.' ],
		],
	],
	[ 'order_ref' => 'UKV-DEMO-011', 'name' => 'Demo Kate',   'email' => 'demo.kate@example.test',   'destination' => 'India',     'tier' => 'Standard', 'total' => 49,  'status' => 'refunded',  'blocker' => 'none' ],
	// Second high-risk-ish: Turkey awaiting_docs with blocker, backdated for SLA breach.
	[
		'order_ref' => 'UKV-DEMO-012', 'name' => 'Demo Liam', 'email' => 'demo.liam@example.test',
		'destination' => 'Turkey', 'tier' => 'Express', 'total' => 75, 'status' => 'awaiting_docs', 'blocker' => 'docs_missing',
		'travel_date' => $d( 9 ), 'backdate' => 5,
		'journey' => [
			[ 'date' => $dt( -5 ), 'agent' => 'Priya', 'channel' => 'call', 'text' => 'Could not reach client; left voicemail.' ],
		],
	],
];

// One more backdated order for a second SLA breach (Egypt rejected #002 above stays as-is;
// backdate the first awaiting_decision-style one). We backdate DEMO-009 too.
$backdate_extra = 'UKV-DEMO-009';

foreach ( $orders as $o ) {
	$pid = ukv_create_order( [
		'order_ref'       => $o['order_ref'],
		'name'            => $o['name'],
		'email'           => $o['email'],
		'destination'     => $o['destination'],
		'tier'            => $o['tier'],
		'total'           => $o['total'],
		'passport_number' => $o['passport_number'] ?? '',
	] );
	if ( ! $pid ) { continue; }

	update_post_meta( $pid, 'ukv_demo', '1' );
	update_post_meta( $pid, 'ukv_status', $o['status'] );
	update_post_meta( $pid, 'ukv_blocker', $o['blocker'] ?? 'none' );

	if ( ! empty( $o['travel_date'] ) ) { update_post_meta( $pid, 'ukv_travel_date', $o['travel_date'] ); }
	if ( ! empty( $o['journey'] ) )     { update_post_meta( $pid, 'ukv_journey', $o['journey'] ); }

	if ( ! empty( $o['backdate'] ) ) { ukvseed_backdate( $pid, (int) $o['backdate'] ); }
	if ( $o['order_ref'] === $backdate_extra ) { ukvseed_backdate( $pid, 5 ); }

	if ( ! empty( $o['consent'] ) && function_exists( 'ukv_set_story_consent' ) ) {
		ukv_set_story_consent( $pid, true );
		$report['consent']++;
	}

	$report['orders']++;
	$report['order_ids'][ $o['order_ref'] ] = $pid;
}

/* ------------------------------------------------------------------ barriers */

if ( function_exists( 'ukv_barrier_create' ) ) {
	// 1) destination-scope OPEN Egypt barrier.
	$b1 = ukv_barrier_create( [
		'nature'      => 'temporary',
		'scope'       => 'destination',
		'destination' => 'egypt',
		'guidance'    => 'Egypt e-visa portal briefly down; no action needed.',
		'status'      => 'open',
	] );
	if ( $b1 ) { update_post_meta( $b1, 'ukv_demo', '1' ); $report['barriers']++; }

	// 2) case-scope OPEN barrier on a specific order (the high-risk Egypt one).
	$b2 = ukv_barrier_create( [
		'nature'      => 'temporary',
		'scope'       => 'case',
		'destination' => 'egypt',
		'order_ref'   => 'UKV-DEMO-001',
		'guidance'    => 'Client passport scan missing; chase before submission.',
		'status'      => 'open',
	] );
	if ( $b2 ) { update_post_meta( $b2, 'ukv_demo', '1' ); $report['barriers']++; }

	// 3) resolved barrier (so dashboards show resolution history).
	$b3 = ukv_barrier_create( [
		'nature'      => 'permanent',
		'scope'       => 'destination',
		'destination' => 'india',
		'guidance'    => 'India now requires a returning-flight booking at submission.',
		'status'      => 'resolved',
	] );
	if ( $b3 ) { update_post_meta( $b3, 'ukv_demo', '1' ); $report['barriers']++; }
}

/* ------------------------------------------------------------------ testimonial */

$cat = get_category_by_slug( 'testimonial' );
if ( ! $cat ) {
	$res = wp_insert_term( 'Testimonial', 'category', [ 'slug' => 'testimonial' ] );
	$cat_id = is_wp_error( $res ) ? 0 : (int) $res['term_id'];
} else {
	$cat_id = (int) $cat->term_id;
}

if ( $cat_id ) {
	$tid = wp_insert_post( [
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => 'Smooth Egypt visa experience',
		'post_excerpt' => 'The team handled my Egypt e-visa start to finish. Clear steps, quick replies, and I had everything sorted well before travel. Highly recommend the guided service.',
		'post_content' => 'I used the guided service for my Egypt e-visa and it could not have been easier. They walked me through each step, chased anything outstanding, and kept me updated the whole way. Stress-free and well ahead of my trip.',
		'post_category'=> [ $cat_id ],
	] );
	if ( $tid && ! is_wp_error( $tid ) ) {
		update_post_meta( $tid, 'ukv_demo', '1' );
		$report['testimonials']++;
	}
}

/* ------------------------------------------------------------------ output */

echo "=== DEMO SEEDER ===\n";
echo "Wiped prior demo: " . wp_json_encode( $wiped ) . "\n";
echo "Orders created:       {$report['orders']}\n";
echo "Barriers created:     {$report['barriers']}\n";
echo "Testimonials created: {$report['testimonials']}\n";
echo "Consent flags set:    {$report['consent']}\n";
echo "All objects carry ukv_demo='1' for clean re-run / cleanup.\n";
echo "DONE\n";
