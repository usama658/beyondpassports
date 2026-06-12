<?php
/**
 * Plugin Name: UKV Client Updates (Smart Stories P12)
 * Desc: Proactive, plain-English client updates fanned out from an open barrier.
 *       Drafts (pure) + sends (wp_mail + journey audit) + an "Affected clients" meta box
 *       on the ukv_barrier edit screen to approve & send to all affected orders.
 * Spec: docs/superpowers/specs/2026-06-12-smart-stories-design.md (P12)
 * Depends on: ukv-barriers.php (ukv_affected_orders, ukv_dest_slug, UKV_BARRIER_*).
 */
defined( 'ABSPATH' ) || exit;

const UKV_UPDATE_COMPLIANCE = 'Independent service — not a government website. Express tiers speed up our handling, not the government\'s decision.';

/**
 * Build a proactive client update for one (barrier, order). PURE — no side effects.
 * Returns [ subject, body, wa_link, call_task ].
 */
function ukv_draft_client_update( $barrier_id, $order_id ) {
	$o = fn( $k ) => (string) get_post_meta( $order_id, $k, true );
	$b = fn( $k ) => (string) get_post_meta( $barrier_id, $k, true );

	$name     = $o( 'ukv_name' ) ?: 'there';
	$dest     = $o( 'ukv_destination' ) ?: 'visa';
	$ref      = $o( 'ukv_order_ref' );
	$guidance = $b( 'guidance' );
	// A destination/all barrier fans this guidance out to many clients — redact any PII a staff member typed in.
	if ( function_exists( 'ukv_redact_pii' ) ) { $guidance = ukv_redact_pii( $guidance ); }
	$nature   = $b( 'nature' ) === 'permanent' ? 'permanent' : 'temporary';

	$subject = sprintf( 'Update on your %s visa application (%s)', $dest, $ref );

	// "What's changed" framing depends on the barrier nature.
	$changed = ( 'permanent' === $nature )
		? "What's changed: there's a change to your application that you'll need to act on. " . $guidance
		: "What's changed: we've spotted a temporary issue that we're already handling for you. " . $guidance;

	$todo = ( 'permanent' === $nature )
		? 'What you need to do: please review the note above and reply to this email (or message us on WhatsApp) so we can keep your application moving.'
		: 'What you need to do: nothing for now — there\'s no action needed from your side. We\'ll let you know if that changes.';

	$next = 'Our next step: our team is on this and will update you as soon as there\'s progress.';

	$body = implode( "\n\n", [
		"Hi {$name},",
		$changed,
		$todo,
		$next,
		"Your reference: {$ref}",
		UKV_UPDATE_COMPLIANCE,
		'UKVisaCo',
	] );

	$digits  = preg_replace( '/[^0-9]/', '', (string) get_option( 'ukv_whatsapp_number', '' ) );
	$wa_text = sprintf( 'Hi, I have a question about my visa application %s', $ref );
	$wa_link = $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( $wa_text ) : '';

	$call_task = sprintf( 'Call %s re: %s — %s', $name, $dest, $guidance );

	return [
		'subject'   => $subject,
		'body'      => $body,
		'wa_link'   => $wa_link,
		'call_task' => $call_task,
	];
}

/**
 * Send a proactive update for one (barrier, order): wp_mail + append an audit note to ukv_journey.
 * Local mail may not deliver — that's expected; we still log and return true.
 */
function ukv_send_client_update( $barrier_id, $order_id ) {
	$d     = ukv_draft_client_update( $barrier_id, $order_id );
	$email = (string) get_post_meta( $order_id, 'ukv_email', true );

	if ( $email ) {
		wp_mail( $email, $d['subject'], $d['body'], [ 'Content-Type: text/plain; charset=UTF-8' ] );
	}

	$j   = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$j[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'email',
		'text'    => 'Proactive update sent: ' . $d['subject'],
	];
	update_post_meta( $order_id, 'ukv_journey', $j );

	return true;
}

/** Meta box on the barrier edit screen: list affected clients + approve & send. */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_barrier_updates', 'Affected clients — send updates', 'ukv_barrier_updates_metabox', 'ukv_barrier', 'normal', 'high' );
} );

function ukv_barrier_updates_metabox( $post ) {
	$bid     = $post->ID;
	$orders  = function_exists( 'ukv_affected_orders' ) ? ukv_affected_orders( $bid ) : [];
	$sent    = (array) get_post_meta( $bid, 'ukv_update_sent', true );

	echo '<p style="margin-top:0">These open orders are affected by this barrier. Sending posts a plain-English update by email and logs it to each order\'s journey.</p>';

	if ( ! $orders ) {
		echo '<p><em>No affected open orders right now.</em></p>';
		return;
	}

	echo '<ul style="margin:0 0 12px">';
	foreach ( $orders as $oid ) {
		$d    = ukv_draft_client_update( $bid, $oid );
		$name = (string) get_post_meta( $oid, 'ukv_name', true );
		$mail = (string) get_post_meta( $oid, 'ukv_email', true );
		$flag = in_array( (int) $oid, array_map( 'intval', $sent ), true ) ? ' <span style="color:#0f7b3f">· sent</span>' : '';
		echo '<li style="border-bottom:1px solid #eee;padding:6px 0">'
			. '<strong>' . esc_html( $name ) . '</strong> · ' . esc_html( $mail ) . $flag . '<br>'
			. '<em>' . esc_html( $d['subject'] ) . '</em></li>';
	}
	echo '</ul>';

	$url = admin_url( 'admin-post.php' );
	echo '<form method="post" action="' . esc_url( $url ) . '">';
	wp_nonce_field( 'ukv_send_updates_' . $bid, 'ukv_send_updates_nonce' );
	echo '<input type="hidden" name="action" value="ukv_send_client_updates">';
	echo '<input type="hidden" name="barrier_id" value="' . esc_attr( $bid ) . '">';
	echo '<button type="submit" class="button button-primary">Approve &amp; send to all affected (' . count( $orders ) . ')</button>';
	echo '</form>';
}

/** admin-post handler: loop ukv_send_client_update over each affected order. */
add_action( 'admin_post_ukv_send_client_updates', function () {
	$bid = isset( $_POST['barrier_id'] ) ? (int) $_POST['barrier_id'] : 0;
	if ( ! $bid || ! current_user_can( 'edit_post', $bid ) ) { wp_die( 'Not allowed.' ); }
	if ( ! isset( $_POST['ukv_send_updates_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_send_updates_nonce'], 'ukv_send_updates_' . $bid ) ) {
		wp_die( 'Invalid request.' );
	}

	$orders = function_exists( 'ukv_affected_orders' ) ? ukv_affected_orders( $bid ) : [];
	$prev   = get_post_meta( $bid, 'ukv_update_sent', true );
	$prev   = is_array( $prev ) ? array_map( 'intval', $prev ) : [];
	$sent_now = 0;
	foreach ( $orders as $oid ) {
		$oid = (int) $oid;
		if ( in_array( $oid, $prev, true ) ) { continue; } // already emailed for this barrier — don't re-send
		if ( ukv_send_client_update( $bid, $oid ) ) { $prev[] = $oid; $sent_now++; }
	}
	update_post_meta( $bid, 'ukv_update_sent', array_values( array_unique( $prev ) ) );

	wp_safe_redirect( add_query_arg(
		[ 'ukv_sent' => $sent_now ],
		get_edit_post_link( $bid, 'url' )
	) );
	exit;
} );

/** Admin notice confirming how many updates were sent. */
add_action( 'admin_notices', function () {
	if ( isset( $_GET['ukv_sent'] ) && get_current_screen() && 'ukv_barrier' === get_current_screen()->post_type ) {
		echo '<div class="notice notice-success is-dismissible"><p>Sent proactive updates to ' . (int) $_GET['ukv_sent'] . ' client(s).</p></div>';
	}
} );
