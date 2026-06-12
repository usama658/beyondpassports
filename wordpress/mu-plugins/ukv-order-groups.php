<?php
/**
 * Plugin Name: UKV Order Groups (Group / linked orders — Gap #82)
 * Desc: Policy: every traveller on the same trip gets their OWN order (per-person
 *       docs/fees, clean tracking). This plugin LINKS those orders as a group via a
 *       deterministic group id, exposes helpers to query members/siblings, adds a
 *       "Trip group" meta box to set/clear the link, and an admin list column.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Generate a deterministic group id from a set of order ids.
 *
 * Derived from the sorted, de-duplicated ids (NOT from Date/rand) so that linking
 * the same travellers always yields the same id regardless of call order.
 */
function ukv_group_id_for( array $order_ids ): string {
	$ids = array_values( array_unique( array_map( 'intval', $order_ids ) ) );
	$ids = array_filter( $ids, fn( $i ) => $i > 0 );
	sort( $ids, SORT_NUMERIC );
	$hash = substr( md5( implode( '-', $ids ) ), 0, 8 );
	return 'GRP-' . strtoupper( $hash );
}

/**
 * Link a set of orders into one trip group.
 *
 * Generates a deterministic group id from the (sorted, de-duplicated) ids, stores
 * it as ukv_group_id meta on each order, and appends a journey audit note to each.
 *
 * @param int[] $order_ids
 * @return string The group id ('' when no valid order ids were supplied).
 */
function ukv_link_orders( array $order_ids ): string {
	$ids = array_values( array_unique( array_map( 'intval', $order_ids ) ) );
	$ids = array_values( array_filter( $ids, fn( $i ) => $i > 0 ) );
	if ( ! $ids ) {
		return '';
	}

	$gid = ukv_group_id_for( $ids );

	foreach ( $ids as $oid ) {
		update_post_meta( $oid, 'ukv_group_id', $gid );

		// Journey audit note (matches ukv-orders journey schema: date/agent/channel/text).
		$journey   = get_post_meta( $oid, 'ukv_journey', true );
		$journey   = is_array( $journey ) ? $journey : [];
		$journey[] = [
			'date'    => gmdate( 'Y-m-d H:i' ),
			'agent'   => 'system',
			'channel' => 'internal',
			'text'    => sprintf( 'Linked to group %s', $gid ),
		];
		update_post_meta( $oid, 'ukv_journey', $journey );
	}

	return $gid;
}

/**
 * The group id for an order ('' when solo / unlinked).
 */
function ukv_group_id( int $order_id ): string {
	return (string) get_post_meta( $order_id, 'ukv_group_id', true );
}

/**
 * All order ids in a group, sorted ascending. Empty for an empty/invalid group id.
 *
 * @return int[]
 */
function ukv_group_orders( string $group_id ): array {
	$group_id = trim( $group_id );
	if ( '' === $group_id ) {
		return [];
	}

	$ids = get_posts( [
		'post_type'      => 'ukv_order',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
		'meta_query'     => [
			[ 'key' => 'ukv_group_id', 'value' => $group_id ],
		],
	] );

	$ids = array_map( 'intval', $ids );
	sort( $ids, SORT_NUMERIC );
	return $ids;
}

/**
 * Other orders in the same group, sorted, excluding the given order.
 * Empty array when the order is solo / unlinked.
 *
 * @return int[]
 */
function ukv_order_group_siblings( int $order_id ): array {
	$gid = ukv_group_id( $order_id );
	if ( '' === $gid ) {
		return [];
	}
	return array_values( array_filter( ukv_group_orders( $gid ), fn( $i ) => $i !== $order_id ) );
}

/* -------------------------------------------------------------------------
 * Admin: "Trip group" meta box on the order edit screen.
 * ---------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_trip_group', 'Trip group', 'ukv_trip_group_metabox', 'ukv_order', 'side', 'default' );
} );

function ukv_trip_group_metabox( $post ) {
	$pid = (int) $post->ID;
	wp_nonce_field( 'ukv_trip_group_' . $pid, 'ukv_trip_group_nonce' );

	$gid = ukv_group_id( $pid );

	if ( '' !== $gid ) {
		echo '<p><strong>Group:</strong> <code>' . esc_html( $gid ) . '</code></p>';
		$siblings = ukv_order_group_siblings( $pid );
		if ( $siblings ) {
			echo '<p style="margin-bottom:2px"><strong>Other travellers in this trip</strong></p><ul style="margin:0 0 8px 1em">';
			foreach ( $siblings as $sid ) {
				$ref    = (string) get_post_meta( $sid, 'ukv_order_ref', true );
				$name   = (string) get_post_meta( $sid, 'ukv_name', true );
				$status = (string) get_post_meta( $sid, 'ukv_status', true );
				$label  = defined( 'UKV_ORDER_STATUSES' ) ? ( UKV_ORDER_STATUSES[ $status ] ?? $status ) : $status;
				$link   = get_edit_post_link( $sid );
				echo '<li><a href="' . esc_url( (string) $link ) . '">' . esc_html( $ref ?: ( '#' . $sid ) ) . '</a> — '
					. esc_html( $name ) . ' <em>(' . esc_html( (string) $label ) . ')</em></li>';
			}
			echo '</ul>';
		} else {
			echo '<p style="color:#555;font-size:11px">No other travellers linked yet.</p>';
		}
	} else {
		echo '<p style="color:#555;font-size:11px">This order is not linked to a trip group.</p>';
	}

	echo '<p><label for="ukv_group_id_input"><strong>Set / clear group id</strong></label><br>';
	echo '<input type="text" id="ukv_group_id_input" name="ukv_group_id_input" value="' . esc_attr( $gid ) . '" '
		. 'style="width:100%" placeholder="GRP-XXXXXXXX (leave blank to unlink)"></p>';

	echo '<p style="color:#555;font-size:11px">Or link to another order by its reference:</p>';
	echo '<p><input type="text" name="ukv_group_link_ref" value="" style="width:100%" '
		. 'placeholder="Link to order ref… (e.g. UKV-2026-000123)"></p>';
	echo '<p style="color:#555;font-size:11px">Linking by ref derives a shared deterministic group id for both orders and logs an audit note.</p>';
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_trip_group_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_trip_group_nonce'] ) ), 'ukv_trip_group_' . $pid )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }

	// 1) Link-by-reference takes priority: derive a shared deterministic id for both.
	$link_ref = isset( $_POST['ukv_group_link_ref'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_group_link_ref'] ) ) : '';
	if ( '' !== $link_ref ) {
		$matches = get_posts( [
			'post_type'      => 'ukv_order',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'post_status'    => 'publish',
			'meta_query'     => [
				[ 'key' => 'ukv_order_ref', 'value' => $link_ref ],
			],
		] );
		$other = $matches ? (int) $matches[0] : 0;
		if ( $other > 0 && $other !== $pid ) {
			ukv_link_orders( [ $pid, $other ] );
			return;
		}
	}

	// 2) Otherwise honour the explicit group id field (set or clear).
	$new = isset( $_POST['ukv_group_id_input'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_group_id_input'] ) ) : '';
	$old = ukv_group_id( $pid );
	if ( $new === $old ) { return; }

	if ( '' === $new ) {
		delete_post_meta( $pid, 'ukv_group_id' );
		return;
	}

	update_post_meta( $pid, 'ukv_group_id', $new );
	$journey   = get_post_meta( $pid, 'ukv_journey', true );
	$journey   = is_array( $journey ) ? $journey : [];
	$journey[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'internal',
		'text'    => sprintf( 'Linked to group %s', $new ),
	];
	update_post_meta( $pid, 'ukv_journey', $journey );
} );

/* -------------------------------------------------------------------------
 * Admin list: "Group" column.
 * ---------------------------------------------------------------------- */
add_filter( 'manage_ukv_order_posts_columns', function ( $cols ) {
	$new = [];
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'ukv_status' === $k ) { $new['ukv_group'] = 'Group'; }
	}
	if ( ! isset( $new['ukv_group'] ) ) { $new['ukv_group'] = 'Group'; }
	return $new;
} );

add_action( 'manage_ukv_order_posts_custom_column', function ( $col, $pid ) {
	if ( 'ukv_group' === $col ) {
		$gid = ukv_group_id( (int) $pid );
		echo $gid ? '<code>' . esc_html( $gid ) . '</code>' : '&mdash;';
	}
}, 10, 2 );
