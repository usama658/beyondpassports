<?php
/**
 * Plugin Name: UKV Zapier (Order events webhook feed)
 * Desc: Posts order events (created + status changes) to a user-configured Zapier Catch Hook
 *       as JSON — powers Drive/Sheet auto-archive via the user's own Zap. Hook URL in DB
 *       option ukv_zapier_hook_url (not in code/git). No-op when no URL is set.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Build the webhook payload for an order.
 *
 * @param int    $order_id Order CPT post ID.
 * @param string $event    Event name to embed in the payload (default 'update').
 * @return array{ref:string,name:string,email:string,destination:string,tier:string,status:string,total:string,created:string,event:string}
 */
function ukv_zapier_payload( int $order_id, string $event = 'update' ): array {
	$g = fn( $k ) => (string) get_post_meta( $order_id, $k, true );

	// ukv_created is stored as a unix timestamp; render as Y-m-d (fall back to today).
	$created_raw = get_post_meta( $order_id, 'ukv_created', true );
	$created     = $created_raw ? gmdate( 'Y-m-d', (int) $created_raw ) : gmdate( 'Y-m-d' );

	return [
		'ref'         => $g( 'ukv_order_ref' ),
		'name'        => $g( 'ukv_name' ),
		'email'       => $g( 'ukv_email' ),
		'destination' => $g( 'ukv_destination' ),
		'tier'        => $g( 'ukv_tier' ),
		'status'      => $g( 'ukv_status' ),
		'total'       => $g( 'ukv_total' ),
		'created'     => $created,
		'event'       => $event,
	];
}

/**
 * Send one order event to the configured Zapier hook as JSON.
 *
 * Returns true on a 2xx response, false otherwise OR if no URL is set (no-op).
 *
 * Testability: a 'ukv_zapier_pre_send' filter runs FIRST (before the URL check). If it
 * returns non-null the (bool) value is returned and NO HTTP happens — tests use this to
 * capture the payload and force a result without ever hitting the network.
 *
 * @param int    $order_id Order CPT post ID.
 * @param string $event    Event name (e.g. 'created', 'status_submitted').
 * @return bool
 */
function ukv_zapier_send( int $order_id, string $event ): bool {
	$payload = ukv_zapier_payload( $order_id, $event );

	// Test/override hook — placed FIRST so tests work with no URL set.
	$pre = apply_filters( 'ukv_zapier_pre_send', null, $order_id, $event, $payload );
	if ( null !== $pre ) {
		return (bool) $pre;
	}

	$url = get_option( 'ukv_zapier_hook_url', '' );
	if ( '' === trim( (string) $url ) ) {
		return false; // No-op when no hook configured.
	}

	$res = wp_remote_post( $url, [
		'body'    => wp_json_encode( $payload ),
		'headers' => [ 'Content-Type' => 'application/json' ],
		'timeout' => 15,
	] );

	if ( is_wp_error( $res ) ) {
		return false;
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	return $code >= 200 && $code < 300;
}

/**
 * Trigger: fire 'created' once for a brand-new order. Others may call this directly,
 * and it is also wired to the optional 'ukv_order_created' action below.
 */
function ukv_zapier_on_created( int $order_id ): bool {
	return ukv_zapier_send( $order_id, 'created' );
}
// If the orders module ever fires a dedicated creation action, hook it.
add_action( 'ukv_order_created', 'ukv_zapier_on_created', 10, 1 );

/**
 * Triggers on save:
 *  - First-ever save of an order with no recorded last-status -> fire 'created'.
 *  - Any subsequent ukv_status transition -> fire 'status_<new>' once per transition.
 * Idempotency is enforced via the ukv_zap_status_last meta (separate from other modules).
 * Guards autosave/revisions.
 */
add_action( 'save_post_ukv_order', function ( $order_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $order_id ) ) { return; }

	$new  = (string) get_post_meta( $order_id, 'ukv_status', true );
	$last = get_post_meta( $order_id, 'ukv_zap_status_last', true );

	// Brand-new order: no last-status recorded yet -> creation event.
	if ( '' === (string) $last && '' !== $new ) {
		ukv_zapier_send( (int) $order_id, 'created' );
		update_post_meta( $order_id, 'ukv_zap_status_last', $new );
		return;
	}

	// Subsequent status transition -> one event per change.
	if ( '' !== $new && $new !== (string) $last ) {
		ukv_zapier_send( (int) $order_id, 'status_' . $new );
		update_post_meta( $order_id, 'ukv_zap_status_last', $new );
	}
}, 13 );

/* ------------------------------------------------------------------------- *
 * Settings page (capability manage_options)
 * ------------------------------------------------------------------------- */

add_action( 'admin_init', function () {
	register_setting( 'general', 'ukv_zapier_hook_url', [
		'type'              => 'string',
		'sanitize_callback' => 'esc_url_raw',
		'default'           => '',
	] );
} );

add_action( 'admin_menu', function () {
	add_options_page(
		'UKV Zapier feed',
		'UKV Zapier',
		'manage_options',
		'ukv-zapier',
		'ukv_zapier_settings_page'
	);
} );

function ukv_zapier_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$url = get_option( 'ukv_zapier_hook_url', '' );
	echo '<div class="wrap"><h1>UKV Zapier — order events feed</h1>';
	echo '<p>Paste your Zapier <strong>Catch Hook</strong> URL below. On each order event (created / status change) we POST a small JSON payload your Zap can archive to Drive or a Sheet.</p>';

	echo '<form method="post" action="options.php">';
	settings_fields( 'general' );
	echo '<table class="form-table"><tr><th scope="row"><label for="ukv_zapier_hook_url">Zapier hook URL</label></th><td>';
	echo '<input type="url" id="ukv_zapier_hook_url" name="ukv_zapier_hook_url" class="regular-text" value="' . esc_attr( $url ) . '" placeholder="https://hooks.zapier.com/hooks/catch/...">';
	echo '<p class="description">Leave blank to disable the feed (no requests are sent).</p>';
	echo '</td></tr></table>';
	submit_button( 'Save hook URL' );
	echo '</form>';

	// Send-test-ping button — only meaningful once a URL is set.
	echo '<hr><h2>Test</h2>';
	if ( '' === trim( (string) $url ) ) {
		echo '<p><em>Set and save a hook URL above to enable the test ping.</em></p>';
	} else {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'ukv_zapier_test', 'ukv_zapier_test_nonce' );
		echo '<input type="hidden" name="action" value="ukv_zapier_test">';
		echo '<button type="submit" class="button button-secondary">Send test ping</button>';
		echo '<p class="description">Sends the most recent order (or a sample payload if none exist) to your hook.</p>';
		echo '</form>';
	}
	echo '</div>';
}

/** Admin-post handler: fire a test ping for the most recent order. Nonce + capability gated. */
add_action( 'admin_post_ukv_zapier_test', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Insufficient permissions.' ); }
	if ( ! isset( $_POST['ukv_zapier_test_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_zapier_test_nonce'], 'ukv_zapier_test' ) ) {
		wp_die( 'Invalid request.' );
	}

	$url = get_option( 'ukv_zapier_hook_url', '' );
	if ( '' === trim( (string) $url ) ) {
		wp_safe_redirect( add_query_arg( 'ukv_zapier_test', 'nourl', admin_url( 'options-general.php?page=ukv-zapier' ) ) );
		exit;
	}

	$recent = get_posts( [ 'post_type' => 'ukv_order', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'publish' ] );
	if ( $recent ) {
		$ok = ukv_zapier_send( (int) $recent[0], 'test' );
	} else {
		// No orders yet — POST a sample payload directly so the user can still verify wiring.
		$payload = [
			'ref' => 'UKV-TEST', 'name' => 'Test Order', 'email' => 'test@example.com',
			'destination' => 'Test', 'tier' => 'Standard', 'status' => 'paid',
			'total' => '0', 'created' => gmdate( 'Y-m-d' ), 'event' => 'test',
		];
		$res = wp_remote_post( $url, [
			'body'    => wp_json_encode( $payload ),
			'headers' => [ 'Content-Type' => 'application/json' ],
			'timeout' => 15,
		] );
		$code = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
		$ok   = $code >= 200 && $code < 300;
	}

	wp_safe_redirect( add_query_arg( 'ukv_zapier_test', $ok ? 'ok' : 'fail', admin_url( 'options-general.php?page=ukv-zapier' ) ) );
	exit;
} );

/** Admin notice after a test ping. */
add_action( 'admin_notices', function () {
	if ( empty( $_GET['ukv_zapier_test'] ) ) { return; }
	$state = sanitize_key( wp_unslash( $_GET['ukv_zapier_test'] ) );
	$map   = [
		'ok'    => [ 'success', 'Zapier test ping sent — a 2xx response was received.' ],
		'fail'  => [ 'error', 'Zapier test ping failed (no 2xx response). Check the hook URL.' ],
		'nourl' => [ 'warning', 'No Zapier hook URL is set — nothing was sent.' ],
	];
	if ( ! isset( $map[ $state ] ) ) { return; }
	echo '<div class="notice notice-' . esc_attr( $map[ $state ][0] ) . ' is-dismissible"><p>' . esc_html( $map[ $state ][1] ) . '</p></div>';
} );
