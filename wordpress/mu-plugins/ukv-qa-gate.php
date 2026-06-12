<?php
/**
 * Plugin Name: UKV Pre-submission QA Gate (Gap #75)
 * Desc: Blocks moving an order to 'submitted' until it is complete (docs present) and a
 *       human has signed off. This protects success rate by preventing incomplete /
 *       error-prone submissions from reaching the government with missing evidence.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Evaluate whether an order is allowed to be submitted.
 * Floor requirements:
 *   - At least one document present (ukv_documents non-empty). If a per-destination
 *     required-docs COUNT becomes available later it can be compared here; until then
 *     the floor is ">= 1 document".
 *   - A human sign-off recorded: meta ukv_qa_signed_off === '1'.
 *
 * @return array{ ok: bool, reasons: string[] }
 */
function ukv_qa_can_submit( $order_id ) {
	$order_id = (int) $order_id;
	$reasons  = [];

	if ( 'ukv_order' !== get_post_type( $order_id ) ) {
		return [ 'ok' => false, 'reasons' => [ 'Not a valid order.' ] ];
	}

	$docs = array_filter( (array) get_post_meta( $order_id, 'ukv_documents', true ) );
	$have = count( $docs );

	// Optional per-destination required count (forward-compatible; absent today).
	$required = (int) get_post_meta( $order_id, 'ukv_required_docs', true );
	if ( $required > 0 ) {
		if ( $have < $required ) {
			$reasons[] = sprintf( 'Only %d of %d required document(s) attached.', $have, $required );
		}
	} elseif ( $have < 1 ) {
		$reasons[] = 'No documents attached — at least one is required.';
	}

	if ( '1' !== (string) get_post_meta( $order_id, 'ukv_qa_signed_off', true ) ) {
		$reasons[] = 'QA sign-off not recorded — an agent must confirm documents are checked.';
	}

	return [ 'ok' => empty( $reasons ), 'reasons' => $reasons ];
}

/**
 * Core gate. Called directly (tests) and from the save hook below.
 * If the attempted status is 'submitted' and the order is not OK to submit, it REVERTS
 * ukv_status to the previous value, records a blocking journey note, and sets an
 * admin-notice transient. Returns TRUE when the submission was blocked.
 */
function ukv_qa_gate_enforce( $order_id, $attempted_status ) {
	$order_id = (int) $order_id;
	if ( 'submitted' !== (string) $attempted_status ) {
		return false; // gate only governs the move to 'submitted'
	}

	$check = ukv_qa_can_submit( $order_id );
	if ( $check['ok'] ) {
		return false; // complete + signed off -> allow
	}

	// REVERT: roll ukv_status back so the transition does not take effect (and so the
	// email status-change hook at priority 12 sees no change and stays quiet).
	$prev = (string) get_post_meta( $order_id, 'ukv_status_last', true );
	if ( '' === $prev || 'submitted' === $prev ) { $prev = 'doc_review'; }
	update_post_meta( $order_id, 'ukv_status', $prev );

	$reasons_txt = implode( '; ', $check['reasons'] );

	// Admin notice (rendered escaped on the next admin page load).
	$uid = get_current_user_id();
	if ( $uid ) {
		set_transient( 'ukv_qa_block_' . $uid, $check['reasons'], 60 );
	}

	// Journey audit note.
	$journey   = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$journey[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'internal',
		'text'    => 'Submission blocked by QA gate: ' . $reasons_txt,
	];
	update_post_meta( $order_id, 'ukv_journey', $journey );

	return true;
}

/**
 * The gate, wired BEFORE the email status-change hook (priority 12) so a reverted
 * status never triggers a "submitted" email. Guards autosave / revisions.
 */
add_action( 'save_post_ukv_order', function ( $order_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $order_id ) ) { return; }
	$new = (string) get_post_meta( $order_id, 'ukv_status', true );
	if ( 'submitted' === $new ) {
		ukv_qa_gate_enforce( (int) $order_id, 'submitted' );
	}
}, 9 );

/**
 * Sign-off meta box: checkbox bound to ukv_qa_signed_off + live readiness display.
 */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_qa_gate', 'Pre-submission QA gate', 'ukv_qa_gate_metabox', 'ukv_order', 'side', 'high' );
} );

function ukv_qa_gate_metabox( $post ) {
	$pid = (int) $post->ID;
	wp_nonce_field( 'ukv_qa_gate', 'ukv_qa_gate_nonce' );
	$signed  = '1' === (string) get_post_meta( $pid, 'ukv_qa_signed_off', true );
	$check   = ukv_qa_can_submit( $pid );

	echo '<p><label><input type="checkbox" name="ukv_qa_signed_off" value="1" ' . checked( $signed, true, false ) . '> ';
	echo esc_html__( 'QA sign-off — documents checked, ready to submit', 'ukv' ) . '</label></p>';

	if ( $check['ok'] ) {
		echo '<p style="color:#0f7b3f;font-weight:600">' . esc_html__( 'Ready to submit — all checks pass.', 'ukv' ) . '</p>';
	} else {
		echo '<p style="color:#c00;font-weight:600">' . esc_html__( 'Cannot submit yet:', 'ukv' ) . '</p><ul style="margin-left:1em;list-style:disc">';
		foreach ( $check['reasons'] as $r ) {
			echo '<li style="color:#c00">' . esc_html( $r ) . '</li>';
		}
		echo '</ul>';
	}
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $pid ) ) { return; }
	if ( ! isset( $_POST['ukv_qa_gate_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_qa_gate_nonce'], 'ukv_qa_gate' ) ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }
	update_post_meta( $pid, 'ukv_qa_signed_off', isset( $_POST['ukv_qa_signed_off'] ) ? '1' : '' );
}, 8 ); // run before the gate (9) so a fresh sign-off counts in the same save

/**
 * Render the block reason to the agent as a dismissible admin notice.
 */
add_action( 'admin_notices', function () {
	$uid     = get_current_user_id();
	$reasons = $uid ? get_transient( 'ukv_qa_block_' . $uid ) : false;
	if ( empty( $reasons ) || ! is_array( $reasons ) ) { return; }
	delete_transient( 'ukv_qa_block_' . $uid );
	echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Submission blocked by QA gate.', 'ukv' ) . '</strong> ';
	echo esc_html__( 'The order was kept at its previous stage because:', 'ukv' ) . '</p><ul style="margin-left:1.5em;list-style:disc">';
	foreach ( $reasons as $r ) {
		echo '<li>' . esc_html( $r ) . '</li>';
	}
	echo '</ul></div>';
} );
