<?php
/**
 * Plugin Name: UKV Order Ownership + SLA escalation (Gap #70)
 * Desc: Assign an owner (WP user) to each order — meta box + round-robin auto-assign — and
 *       escalate OPEN orders that breach their SLA exactly once (journey note + best-effort owner mail).
 *       Reuses ukv_create_order / ukv_order_sla_hours / UKV_ORDER_CLOSED / ukv_open_orders.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * 1. Owner field (meta ukv_owner = WP user ID; 0 = unassigned / shared queue).
 * ------------------------------------------------------------------------- */

/** Set the owner (WP user ID) of an order. */
function ukv_set_owner( int $order_id, int $user_id ): void {
	update_post_meta( $order_id, 'ukv_owner', max( 0, $user_id ) );
}

/** Get the owner (WP user ID) of an order. 0 = unassigned. */
function ukv_get_owner( int $order_id ): int {
	return (int) get_post_meta( $order_id, 'ukv_owner', true );
}

/** Eligible owners = users who can edit_posts. Returns WP_User[] (id-keyed by get_users). */
function ukv_eligible_owners(): array {
	return get_users( [ 'capability__in' => [ 'edit_posts' ], 'orderby' => 'ID', 'order' => 'ASC' ] );
}

/* ---------------------------------------------------------------------------
 * 2. Meta box "Owner" on the ukv_order edit screen.
 * ------------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_owner', 'Owner', 'ukv_owner_metabox', 'ukv_order', 'side', 'high' );
} );

function ukv_owner_metabox( $post ) {
	wp_nonce_field( 'ukv_owner_save', 'ukv_owner_nonce' );
	$current = ukv_get_owner( $post->ID );
	echo '<label for="ukv_owner" style="display:block;font-weight:600;margin-bottom:4px">Assigned owner</label>';
	echo '<select name="ukv_owner" id="ukv_owner" style="width:100%">';
	echo '<option value="0" ' . selected( $current, 0, false ) . '>— Unassigned (shared queue) —</option>';
	foreach ( ukv_eligible_owners() as $u ) {
		echo '<option value="' . esc_attr( $u->ID ) . '" ' . selected( $current, (int) $u->ID, false ) . '>'
			. esc_html( $u->display_name . ' (' . $u->user_login . ')' ) . '</option>';
	}
	echo '</select>';
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_owner_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_owner_nonce'], 'ukv_owner_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }
	if ( isset( $_POST['ukv_owner'] ) ) {
		ukv_set_owner( $pid, (int) wp_unslash( $_POST['ukv_owner'] ) );
	}
} );

/* ---------------------------------------------------------------------------
 * 3. Round-robin auto-assign (exposed; not wired destructively to creation).
 *    Picks the next eligible owner after the last-assigned (option ukv_owner_rota_last).
 * ------------------------------------------------------------------------- */
function ukv_auto_assign( int $order_id ): int {
	// If already owned, leave it alone.
	$existing = ukv_get_owner( $order_id );
	if ( $existing > 0 ) { return $existing; }

	$owners = ukv_eligible_owners();
	if ( ! $owners ) { return 0; }
	$ids = array_map( static fn( $u ) => (int) $u->ID, $owners );

	$last = (int) get_option( 'ukv_owner_rota_last', 0 );
	$pos  = array_search( $last, $ids, true );
	$next = ( false === $pos ) ? $ids[0] : $ids[ ( $pos + 1 ) % count( $ids ) ];

	ukv_set_owner( $order_id, $next );
	update_option( 'ukv_owner_rota_last', $next );
	return $next;
}

/* ---------------------------------------------------------------------------
 * 4. SLA-breach escalation (fire-once via guard meta ukv_sla_escalated).
 * ------------------------------------------------------------------------- */

/** True when an open order is past its SLA due time. */
function ukv_order_sla_breached( int $order_id ): bool {
	$status = get_post_meta( $order_id, 'ukv_status', true );
	if ( in_array( $status, UKV_ORDER_CLOSED, true ) ) { return false; }
	if ( ! function_exists( 'ukv_order_sla_hours' ) ) { return false; }
	$tier = (string) get_post_meta( $order_id, 'ukv_tier', true );
	$due  = get_post_time( 'U', true, $order_id ) + ukv_order_sla_hours( $tier ) * 3600;
	return time() > $due;
}

/** Count of currently-breached OPEN orders (for the admin notice). */
function ukv_sla_breach_count(): int {
	$n = 0;
	foreach ( ukv_open_orders() as $oid ) { if ( ukv_order_sla_breached( $oid ) ) { $n++; } }
	return $n;
}

/**
 * Escalate OPEN orders past SLA that are not yet escalated. Fires once per order
 * (guard meta ukv_sla_escalated='1'): sets the guard, appends a 'system'/'internal'
 * journey note, and best-effort mails the owner. Returns escalated order IDs.
 */
function ukv_sla_escalate(): array {
	$escalated = [];
	foreach ( ukv_open_orders() as $oid ) {
		if ( ! ukv_order_sla_breached( $oid ) ) { continue; }
		if ( '1' === (string) get_post_meta( $oid, 'ukv_sla_escalated', true ) ) { continue; }

		// Fire-once guard.
		update_post_meta( $oid, 'ukv_sla_escalated', '1' );

		// Journey note (system / internal).
		$j = get_post_meta( $oid, 'ukv_journey', true );
		$j = is_array( $j ) ? $j : [];
		$j[] = [
			'date'    => gmdate( 'Y-m-d H:i' ),
			'agent'   => 'system',
			'channel' => 'internal',
			'text'    => 'SLA breached — escalated to owner',
		];
		update_post_meta( $oid, 'ukv_journey', $j );

		// Best-effort owner mail (non-fatal).
		$owner_id = ukv_get_owner( $oid );
		if ( $owner_id > 0 ) {
			$owner = get_userdata( $owner_id );
			if ( $owner && ! empty( $owner->user_email ) ) {
				$ref = (string) get_post_meta( $oid, 'ukv_order_ref', true );
				$subject = '[UKVisaCo] SLA breached — ' . ( $ref ?: ( 'order #' . $oid ) );
				$body    = "Order {$ref} (#{$oid}) has breached its SLA target and needs attention.\n\n"
					. admin_url( 'post.php?post=' . $oid . '&action=edit' );
				// Swallow any mail-layer error so escalation is never blocked.
				try { wp_mail( $owner->user_email, $subject, $body ); }
				catch ( \Throwable $e ) { /* non-fatal */ }
			}
		}

		$escalated[] = (int) $oid;
	}
	return $escalated;
}

/* ---------------------------------------------------------------------------
 * Daily cron + admin notice.
 * ------------------------------------------------------------------------- */
add_action( 'ukv_sla_escalate_cron', 'ukv_sla_escalate' );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ukv_sla_escalate_cron' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ukv_sla_escalate_cron' );
	}
} );

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$n = ukv_sla_breach_count();
	if ( $n < 1 ) { return; }
	echo '<div class="notice notice-error"><p><strong>UKVisaCo:</strong> '
		. (int) $n . ' open order(s) have breached their SLA and need escalation.</p></div>';
} );
