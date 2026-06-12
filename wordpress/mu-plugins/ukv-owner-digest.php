<?php
/**
 * Plugin Name: UKV Owner Digest (Gap #94)
 * Desc: Per-owner daily digest of pending actions. For each user who owns OPEN orders
 *       (meta ukv_owner == user id, status NOT in UKV_ORDER_CLOSED) summarise open count,
 *       SLA breaches, items due today and high-risk orders. Best-effort daily wp-cron mail
 *       to each owner + a "My digest" admin dashboard widget for the current user.
 *       Reuses ukv_get_owner / ukv_order_sla_breached / UKV_ORDER_CLOSED.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * 1. Digest data for one owner.
 * ------------------------------------------------------------------------- */

/**
 * Build the pending-actions digest for a single owner.
 *
 * @param int $user_id WP user id (matched against meta ukv_owner).
 * @return array{open:int,sla_breaches:int[],due_today:int[],high_risk:int[],orders:array<int,array<string,string|int>>}
 */
function ukv_owner_digest( int $user_id ): array {
	$digest = [
		'open'         => 0,
		'sla_breaches' => [],
		'due_today'    => [],
		'high_risk'    => [],
		'orders'       => [],
	];
	if ( $user_id <= 0 ) { return $digest; }

	$closed = defined( 'UKV_ORDER_CLOSED' ) ? UKV_ORDER_CLOSED : [ 'delivered', 'won', 'rejected', 'refunded' ];
	$today  = current_time( 'Y-m-d' );

	$ids = get_posts( [
		'post_type'   => 'ukv_order',
		'post_status' => 'publish',
		'fields'      => 'ids',
		'numberposts' => -1,
		'meta_key'    => 'ukv_owner',
		'meta_value'  => $user_id,
	] );

	foreach ( $ids as $oid ) {
		$oid = (int) $oid;
		// Defensive double-check ownership (helper preferred if present).
		$owner = function_exists( 'ukv_get_owner' ) ? ukv_get_owner( $oid ) : (int) get_post_meta( $oid, 'ukv_owner', true );
		if ( $owner !== $user_id ) { continue; }

		$status = (string) get_post_meta( $oid, 'ukv_status', true );
		if ( in_array( $status, $closed, true ) ) { continue; }

		$digest['open']++;

		if ( function_exists( 'ukv_order_sla_breached' ) && ukv_order_sla_breached( $oid ) ) {
			$digest['sla_breaches'][] = $oid;
		}

		$due = (string) get_post_meta( $oid, 'ukv_next_due', true );
		if ( '' !== $due && $due <= $today ) {
			$digest['due_today'][] = $oid;
		}

		if ( '1' === (string) get_post_meta( $oid, 'ukv_risk_flag', true ) ) {
			$digest['high_risk'][] = $oid;
		}

		$digest['orders'][] = [
			'id'          => $oid,
			'ref'         => (string) get_post_meta( $oid, 'ukv_order_ref', true ),
			'name'        => (string) get_post_meta( $oid, 'ukv_name', true ),
			'destination' => (string) get_post_meta( $oid, 'ukv_destination', true ),
			'status'      => $status,
			'next_action' => (string) get_post_meta( $oid, 'ukv_next_action', true ),
			'due'         => $due,
		];
	}

	return $digest;
}

/* ---------------------------------------------------------------------------
 * 2. Plain-text email body for one owner.
 * ------------------------------------------------------------------------- */

/**
 * Render a clean plain-text digest body for emailing an owner.
 *
 * @param int $user_id WP user id.
 * @return string Plain-text summary (empty string if the owner has no open orders).
 */
function ukv_owner_digest_text( int $user_id ): string {
	$d = ukv_owner_digest( $user_id );
	if ( $d['open'] < 1 ) { return ''; }

	$lines   = [];
	$lines[] = sprintf(
		'You have %d open orders. %d SLA breaches, %d due today, %d high-risk.',
		$d['open'],
		count( $d['sla_breaches'] ),
		count( $d['due_today'] ),
		count( $d['high_risk'] )
	);
	$lines[] = '';

	foreach ( $d['orders'] as $o ) {
		$status = UKV_ORDER_STATUSES[ $o['status'] ] ?? $o['status'];
		$parts  = [
			$o['ref'] !== '' ? $o['ref'] : ( '#' . $o['id'] ),
			$o['name'] !== '' ? $o['name'] : '(no name)',
			$status !== '' ? $status : '(no status)',
			$o['next_action'] !== '' ? $o['next_action'] : 'no next action',
		];
		// Collapse any newlines from stored values so each order is one clean line.
		$lines[] = '- ' . preg_replace( '/\s+/', ' ', implode( ' · ', $parts ) );
	}

	return implode( "\n", $lines ) . "\n";
}

/* ---------------------------------------------------------------------------
 * 3. Daily cron: best-effort mail each owner of >=1 open order.
 * ------------------------------------------------------------------------- */

/** Mail every eligible owner (edit_posts) who owns at least one open order. Best-effort, non-fatal. */
function ukv_owner_digest_cron(): void {
	$users = get_users( [ 'capability__in' => [ 'edit_posts' ], 'fields' => [ 'ID', 'user_email' ] ] );
	foreach ( $users as $u ) {
		$uid  = (int) $u->ID;
		$body = ukv_owner_digest_text( $uid );
		if ( '' === $body || empty( $u->user_email ) ) { continue; }

		$subject = '[UKVisaCo] Your daily order digest';
		// Swallow any mail-layer error so one bad recipient never blocks the rest.
		try { wp_mail( $u->user_email, $subject, $body ); }
		catch ( \Throwable $e ) { /* non-fatal */ }
	}
}
add_action( 'ukv_owner_digest_cron', 'ukv_owner_digest_cron' );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ukv_owner_digest_cron' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ukv_owner_digest_cron' );
	}
} );

/* ---------------------------------------------------------------------------
 * 4. "My digest" admin dashboard widget for the current user.
 * ------------------------------------------------------------------------- */
add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'edit_posts' ) ) { return; }
	wp_add_dashboard_widget( 'ukv_owner_digest', 'My digest — pending actions', 'ukv_owner_digest_widget' );
} );

/** Render the current user's digest inside the dashboard widget. */
function ukv_owner_digest_widget(): void {
	$d = ukv_owner_digest( get_current_user_id() );

	if ( $d['open'] < 1 ) {
		echo '<p><em>' . esc_html__( 'You have no open orders assigned to you. Nice and clear.', 'ukv' ) . '</em></p>';
		return;
	}

	echo '<p><strong>' . (int) $d['open'] . '</strong> open order(s) &middot; '
		. '<span style="color:#c00">' . count( $d['sla_breaches'] ) . ' SLA breach(es)</span> &middot; '
		. count( $d['due_today'] ) . ' due today &middot; '
		. '<span style="color:#b8860b">' . count( $d['high_risk'] ) . ' high-risk</span></p>';

	echo '<ul style="margin-left:1em;max-height:260px;overflow:auto">';
	foreach ( $d['orders'] as $o ) {
		$status   = UKV_ORDER_STATUSES[ $o['status'] ] ?? $o['status'];
		$breached = in_array( $o['id'], $d['sla_breaches'], true );
		$risk     = in_array( $o['id'], $d['high_risk'], true );
		$flags    = [];
		if ( $breached ) { $flags[] = 'SLA'; }
		if ( $risk ) { $flags[] = 'risk'; }
		if ( in_array( $o['id'], $d['due_today'], true ) ) { $flags[] = 'due'; }

		$label = ( $o['ref'] !== '' ? $o['ref'] : ( '#' . $o['id'] ) ) . ' · ' . ( $o['name'] !== '' ? $o['name'] : '(no name)' );
		echo '<li style="border-bottom:1px solid #eee;padding:4px 0">'
			. '<a href="' . esc_url( admin_url( 'post.php?post=' . $o['id'] . '&action=edit' ) ) . '">' . esc_html( $label ) . '</a>'
			. ' &middot; <em>' . esc_html( $status ) . '</em>'
			. ( $o['next_action'] !== '' ? ' &middot; ' . esc_html( $o['next_action'] ) : '' )
			. ( $flags ? ' <strong style="color:#c00">[' . esc_html( implode( ',', $flags ) ) . ']</strong>' : '' )
			. '</li>';
	}
	echo '</ul>';
}
