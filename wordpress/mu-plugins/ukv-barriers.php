<?php
/**
 * Plugin Name: UKV Barriers (Smart Stories spine)
 * Desc: ukv_barrier CPT — single source of truth for case + destination-wide barriers.
 *       Stored ONCE, surfaced LIVE by query (no copies). Feeds client updates + content engine.
 * Spec: docs/superpowers/specs/2026-06-12-smart-stories-design.md (P11)
 */
defined( 'ABSPATH' ) || exit;

const UKV_BARRIER_NATURE = [ 'temporary' => 'Temporary', 'permanent' => 'Permanent' ];
const UKV_BARRIER_SCOPE  = [ 'case' => 'This case', 'destination' => 'Destination-wide', 'all' => 'All clients' ];
// Statuses on an order that mean it is no longer "open" work (no barrier fan-out / auto-detect).
const UKV_ORDER_CLOSED   = [ 'delivered', 'won', 'rejected', 'refunded' ];

/** Normalise any destination value (slug or "Egypt") to a slug for matching. */
function ukv_dest_slug( $v ) { return sanitize_title( (string) $v ); }

/** Register the barrier CPT (admin only). */
add_action( 'init', function () {
	register_post_type( 'ukv_barrier', [
		'label'        => 'Barriers',
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-warning',
		'supports'     => [ 'title' ],
		'capability_type' => 'post',
		'map_meta_cap' => true,
	] );
} );

/**
 * Create a barrier. Returns post ID, or an existing open barrier's ID when rule_key already open (idempotent).
 * $d: nature, scope, destination, order_ref, guidance, status(open), detected_by(agent), rule_key(optional).
 */
function ukv_barrier_create( array $d ) {
	$d = array_merge( [
		'nature' => 'temporary', 'scope' => 'case', 'destination' => '', 'order_ref' => '',
		'guidance' => '', 'status' => 'open', 'detected_by' => 'agent', 'rule_key' => '',
	], $d );

	// Idempotency: if a rule_key is given and an OPEN barrier already has it, return that one.
	if ( '' !== $d['rule_key'] ) {
		$dup = get_posts( [
			'post_type' => 'ukv_barrier', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => 1,
			'meta_query' => [
				[ 'key' => 'rule_key', 'value' => $d['rule_key'] ],
				[ 'key' => 'status', 'value' => 'open' ],
			],
		] );
		if ( $dup ) { return (int) $dup[0]; }
	}

	$title = ucfirst( $d['scope'] ) . ' barrier'
		. ( $d['destination'] ? ' — ' . ucwords( str_replace( '-', ' ', $d['destination'] ) ) : '' )
		. ( $d['order_ref'] ? ' (' . $d['order_ref'] . ')' : '' );
	$pid = wp_insert_post( [ 'post_type' => 'ukv_barrier', 'post_status' => 'publish', 'post_title' => $title ] );
	if ( ! $pid || is_wp_error( $pid ) ) { return 0; }
	foreach ( [ 'nature', 'scope', 'destination', 'order_ref', 'guidance', 'status', 'detected_by', 'rule_key' ] as $k ) {
		update_post_meta( $pid, $k, $d[ $k ] );
	}
	return (int) $pid;
}

/** All open barriers (IDs). */
function ukv_open_barriers() {
	return get_posts( [
		'post_type' => 'ukv_barrier', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1,
		'meta_query' => [ [ 'key' => 'status', 'value' => 'open' ] ],
	] );
}

/**
 * LIVE surface for one order: open barriers that apply to it =
 *   case barriers whose order_ref matches  +  destination/all barriers whose destination matches.
 * No copies — computed at call time. Returns barrier IDs.
 */
function ukv_barriers_for_order( $order_id ) {
	$ref  = (string) get_post_meta( $order_id, 'ukv_order_ref', true );
	$slug = ukv_dest_slug( get_post_meta( $order_id, 'ukv_destination', true ) );
	$out  = [];
	foreach ( ukv_open_barriers() as $bid ) {
		$scope = get_post_meta( $bid, 'scope', true );
		if ( 'case' === $scope ) {
			if ( $ref && (string) get_post_meta( $bid, 'order_ref', true ) === $ref ) { $out[] = $bid; }
		} else { // destination | all
			$bslug = ukv_dest_slug( get_post_meta( $bid, 'destination', true ) );
			if ( 'all' === $scope || ( $bslug && $bslug === $slug ) ) { $out[] = $bid; }
		}
	}
	return $out;
}

/** Open orders (IDs), optionally filtered to a destination slug. The fan-out target set. */
function ukv_open_orders( $dest_slug = '' ) {
	$ids = get_posts( [ 'post_type' => 'ukv_order', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1 ] );
	$out = [];
	foreach ( $ids as $oid ) {
		if ( in_array( get_post_meta( $oid, 'ukv_status', true ), UKV_ORDER_CLOSED, true ) ) { continue; }
		if ( $dest_slug && ukv_dest_slug( get_post_meta( $oid, 'ukv_destination', true ) ) !== $dest_slug ) { continue; }
		$out[] = $oid;
	}
	return $out;
}

/** Orders affected by a barrier (fan-out). case -> its one order; destination/all -> open orders for that dest/all. */
function ukv_affected_orders( $barrier_id ) {
	$scope = get_post_meta( $barrier_id, 'scope', true );
	if ( 'case' === $scope ) {
		$ref = (string) get_post_meta( $barrier_id, 'order_ref', true );
		if ( ! $ref ) { return []; }
		foreach ( ukv_open_orders() as $oid ) {
			if ( (string) get_post_meta( $oid, 'ukv_order_ref', true ) === $ref ) { return [ $oid ]; }
		}
		return [];
	}
	$slug = ( 'all' === $scope ) ? '' : ukv_dest_slug( get_post_meta( $barrier_id, 'destination', true ) );
	return ukv_open_orders( $slug );
}

/** Rejection rate for a destination slug, from resolved orders. Returns [rate, resolved_count]. */
function ukv_dest_rejection_rate( $dest_slug ) {
	$ids = get_posts( [ 'post_type' => 'ukv_order', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1 ] );
	$resolved = 0; $rejected = 0;
	foreach ( $ids as $oid ) {
		if ( ukv_dest_slug( get_post_meta( $oid, 'ukv_destination', true ) ) !== $dest_slug ) { continue; }
		$s = get_post_meta( $oid, 'ukv_status', true );
		if ( in_array( $s, UKV_ORDER_CLOSED, true ) ) {
			$resolved++;
			if ( 'rejected' === $s ) { $rejected++; }
		}
	}
	return [ $resolved ? $rejected / $resolved : 0.0, $resolved ];
}

/**
 * Auto-detect barriers (cron). IDEMPOTENT: each (order, rule) carries a stable rule_key, so a second
 * run finds the existing open barrier and creates nothing new.
 */
function ukv_auto_detect_barriers() {
	$now = time();
	foreach ( ukv_open_orders() as $oid ) {
		$ref   = (string) get_post_meta( $oid, 'ukv_order_ref', true );
		$slug  = ukv_dest_slug( get_post_meta( $oid, 'ukv_destination', true ) );
		$tier  = (string) get_post_meta( $oid, 'ukv_tier', true );
		$status= get_post_meta( $oid, 'ukv_status', true );

		// Rule 1: SLA breach.
		if ( function_exists( 'ukv_order_sla_hours' ) ) {
			$due = get_post_time( 'U', true, $oid ) + ukv_order_sla_hours( $tier ) * 3600;
			if ( $now > $due ) {
				ukv_barrier_create( [
					'nature' => 'temporary', 'scope' => 'case', 'destination' => $slug, 'order_ref' => $ref,
					'guidance' => 'Your application is taking longer than our target time. Our team is chasing it and will update you shortly.',
					'detected_by' => 'auto', 'rule_key' => "$ref:sla_breach",
				] );
			}
		}

		// Rule 2: passport validity short of destination requirement (only if both data points exist).
		$expiry = (string) get_post_meta( $oid, 'passport_expiry', true ); // YYYY-MM-DD if set
		$req_months = function_exists( 'ukv_dest_value' ) ? (int) ukv_dest_value( $slug, 'passport_validity_months' ) : 0;
		$travel = (string) get_post_meta( $oid, 'ukv_travel_date', true );
		if ( $expiry && $req_months && $travel ) {
			$need = strtotime( $travel ) + $req_months * 2629800; // ~months in seconds
			if ( strtotime( $expiry ) < $need ) {
				ukv_barrier_create( [
					'nature' => 'permanent', 'scope' => 'case', 'destination' => $slug, 'order_ref' => $ref,
					'guidance' => "Your passport may not have the {$req_months} months' validity this destination requires beyond your travel date. Please check and consider renewing before we submit.",
					'detected_by' => 'auto', 'rule_key' => "$ref:passport_validity",
				] );
			}
		}

		// Rule 3: high-rejection destination + open blocker + near travel.
		$blocker = get_post_meta( $oid, 'ukv_blocker', true );
		list( $rate, $resolved ) = ukv_dest_rejection_rate( $slug );
		$near = $travel && ( strtotime( $travel ) - $now ) < 14 * 86400;
		if ( $resolved >= 3 && $rate >= 0.30 && $blocker && 'none' !== $blocker && $near ) {
			ukv_barrier_create( [
				'nature' => 'temporary', 'scope' => 'case', 'destination' => $slug, 'order_ref' => $ref,
				'guidance' => 'This application has an open issue and a near travel date for a destination with a higher refusal rate. Prioritise resolving the outstanding item before submission.',
				'detected_by' => 'auto', 'rule_key' => "$ref:high_rejection_blocker",
			] );
		}
	}
}
add_action( 'ukv_barriers_detect', 'ukv_auto_detect_barriers' );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ukv_barriers_detect' ) ) {
		wp_schedule_event( time() + 300, 'daily', 'ukv_barriers_detect' );
	}
} );

/** Order meta box: live barriers + quick "log barrier". */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_order_barriers', 'Barriers (live)', 'ukv_order_barriers_metabox', 'ukv_order', 'side', 'high' );
} );
function ukv_order_barriers_metabox( $post ) {
	wp_nonce_field( 'ukv_log_barrier', 'ukv_log_barrier_nonce' );
	$bids = ukv_barriers_for_order( $post->ID );
	echo '<ul style="margin:0 0 10px">';
	if ( ! $bids ) { echo '<li><em>No open barriers.</em></li>'; }
	foreach ( $bids as $bid ) {
		$nat = get_post_meta( $bid, 'nature', true ); $sc = get_post_meta( $bid, 'scope', true );
		echo '<li style="border-bottom:1px solid #eee;padding:4px 0"><strong>' . esc_html( UKV_BARRIER_NATURE[ $nat ] ?? $nat ) . '</strong> · ' . esc_html( UKV_BARRIER_SCOPE[ $sc ] ?? $sc ) . '<br>' . esc_html( get_post_meta( $bid, 'guidance', true ) ) . '</li>';
	}
	echo '</ul>';
	echo '<label style="font-weight:600">Log a barrier on this case</label>';
	echo '<select name="ukv_log_barrier_nature" style="width:100%"><option value="temporary">Temporary</option><option value="permanent">Permanent</option></select>';
	echo '<textarea name="ukv_log_barrier_guidance" rows="2" style="width:100%" placeholder="What is the barrier + what the client should do..."></textarea>';
}
add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_log_barrier_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_log_barrier_nonce'], 'ukv_log_barrier' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	$g = isset( $_POST['ukv_log_barrier_guidance'] ) ? trim( wp_unslash( $_POST['ukv_log_barrier_guidance'] ) ) : '';
	if ( '' === $g ) { return; }
	ukv_barrier_create( [
		'nature'      => 'permanent' === ( $_POST['ukv_log_barrier_nature'] ?? '' ) ? 'permanent' : 'temporary',
		'scope'       => 'case',
		'destination' => ukv_dest_slug( get_post_meta( $pid, 'ukv_destination', true ) ),
		'order_ref'   => (string) get_post_meta( $pid, 'ukv_order_ref', true ),
		'guidance'    => sanitize_textarea_field( $g ),
		'detected_by' => 'agent',
	] );
}, 11 );

/** Dashboard: top open barriers by destination. */
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'ukv_barriers_widget', 'UKV — Open barriers', function () {
		$by = [];
		foreach ( ukv_open_barriers() as $bid ) {
			$slug = ukv_dest_slug( get_post_meta( $bid, 'destination', true ) ) ?: '(unspecified)';
			$by[ $slug ] = ( $by[ $slug ] ?? 0 ) + 1;
		}
		if ( ! $by ) { echo '<p>No open barriers.</p>'; return; }
		arsort( $by );
		echo '<ul>';
		foreach ( $by as $slug => $n ) { echo '<li><strong>' . esc_html( ucwords( str_replace( '-', ' ', $slug ) ) ) . '</strong>: ' . (int) $n . ' open</li>'; }
		echo '</ul>';
	} );
} );
