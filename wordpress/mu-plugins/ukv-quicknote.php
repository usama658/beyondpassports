<?php
/**
 * Plugin Name: UKV Quick Note + HubSpot timeline sync (Orders P8)
 * Desc: Add a journey note from the Orders list (without opening the order), and push every
 *       journey note to the linked HubSpot deal timeline. Non-fatal when no HubSpot token.
 * Spec: docs/superpowers/specs/2026-06-12-smart-orders-hub-design.md (P8)
 */
defined( 'ABSPATH' ) || exit;

/** Core: append a journey note to an order + sync to HubSpot. Returns the new note count. */
function ukv_quicknote_add( int $order_id, string $text, string $channel = 'call' ) {
	$text = trim( $text );
	if ( '' === $text || 'ukv_order' !== get_post_type( $order_id ) ) { return 0; }
	$j = get_post_meta( $order_id, 'ukv_journey', true );
	$j = is_array( $j ) ? $j : [];
	$u = wp_get_current_user();
	$j[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => ( $u && $u->display_name ) ? $u->display_name : 'agent',
		'channel' => sanitize_text_field( $channel ),
		'text'    => sanitize_textarea_field( $text ),
	];
	update_post_meta( $order_id, 'ukv_journey', $j );
	ukv_hs_push_note( $order_id, $text );
	return count( $j );
}

/** Push a note to the linked HubSpot deal timeline. No token / no helper -> no-op (false). */
function ukv_hs_push_note( int $order_id, string $text ): bool {
	if ( ! function_exists( 'ukv_hs_post' ) || ! function_exists( 'ukv_hs_token' ) || ! ukv_hs_token() ) { return false; }
	$deal = (int) get_post_meta( $order_id, 'ukv_hubspot_deal', true );
	$ref  = (string) get_post_meta( $order_id, 'ukv_order_ref', true );
	$body = ( $ref ? "[$ref] " : '' ) . $text;
	// hs_timestamp must be epoch milliseconds.
	$res = ukv_hs_post( '/crm/v3/objects/notes', [ 'properties' => [ 'hs_note_body' => $body, 'hs_timestamp' => (string) ( time() * 1000 ) ] ] );
	$noteId = ( $res && $res['code'] >= 200 && $res['code'] < 300 ) ? ( $res['data']['id'] ?? 0 ) : 0;
	if ( $noteId && $deal ) {
		// Associate note -> deal (v4 default association). Non-fatal.
		wp_remote_request( "https://api.hubapi.com/crm/v4/objects/note/{$noteId}/associations/default/deal/{$deal}", [
			'method'  => 'PUT',
			'headers' => [ 'Authorization' => 'Bearer ' . ukv_hs_token(), 'Content-Type' => 'application/json' ],
			'timeout' => 15,
		] );
	}
	return (bool) $noteId;
}

/** Row action "Quick note" on the Orders list. */
add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( 'ukv_order' === $post->post_type ) {
		$url = wp_nonce_url( admin_url( 'admin.php?page=ukv-quicknote&order=' . $post->ID ), 'ukv_quicknote_' . $post->ID );
		$actions['ukv_quicknote'] = '<a href="' . esc_url( $url ) . '">Quick note</a>';
	}
	return $actions;
}, 10, 2 );

/** Hidden admin page hosting the quick-note form. */
add_action( 'admin_menu', function () {
	add_submenu_page( null, 'Quick note', 'Quick note', 'edit_posts', 'ukv-quicknote', 'ukv_quicknote_page' );
} );
function ukv_quicknote_page() {
	$oid = isset( $_GET['order'] ) ? (int) $_GET['order'] : 0;
	if ( ! $oid || ! check_admin_referer( 'ukv_quicknote_' . $oid ) ) { wp_die( 'Invalid order.' ); }
	$ref = get_post_meta( $oid, 'ukv_order_ref', true );
	echo '<div class="wrap"><h1>Quick note — ' . esc_html( $ref ?: ( '#' . $oid ) ) . '</h1>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'ukv_quicknote_save' );
	echo '<input type="hidden" name="action" value="ukv_quicknote_save"><input type="hidden" name="order" value="' . (int) $oid . '">';
	echo '<p><textarea name="note" rows="3" class="large-text" placeholder="What happened on this call/chat..." required></textarea></p>';
	echo '<p>Channel: <select name="channel"><option value="call">Call</option><option value="whatsapp">WhatsApp</option><option value="email">Email</option><option value="internal">Internal</option></select></p>';
	submit_button( 'Add note' );
	echo '</form></div>';
}
add_action( 'admin_post_ukv_quicknote_save', function () {
	check_admin_referer( 'ukv_quicknote_save' );
	$oid = isset( $_POST['order'] ) ? (int) $_POST['order'] : 0;
	if ( $oid && current_user_can( 'edit_post', $oid ) ) {
		ukv_quicknote_add( $oid, wp_unslash( $_POST['note'] ?? '' ), sanitize_text_field( wp_unslash( $_POST['channel'] ?? 'call' ) ) );
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=ukv_order&ukv_note=added' ) );
	exit;
} );
add_action( 'admin_notices', function () {
	if ( isset( $_GET['ukv_note'] ) && 'added' === $_GET['ukv_note'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>Note added' . ( ( function_exists( 'ukv_hs_token' ) && ukv_hs_token() ) ? ' + synced to HubSpot.' : '.' ) . '</p></div>';
	}
} );

/** Also sync notes added via the P7 Journey meta box ($_POST['ukv_new_note']) to HubSpot. */
add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	// Only act on a genuine P7 Journey meta-box submit: verify its nonce + capability before any outbound push.
	if ( ! isset( $_POST['ukv_journey_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_journey_nonce'], 'ukv_journey' ) ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }
	$note = isset( $_POST['ukv_new_note'] ) ? trim( wp_unslash( $_POST['ukv_new_note'] ) ) : '';
	if ( '' !== $note ) { ukv_hs_push_note( $pid, $note ); }
}, 12 );
