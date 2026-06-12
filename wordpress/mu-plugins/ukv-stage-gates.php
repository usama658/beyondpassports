<?php
/**
 * Plugin Name: UKV Stage-transition Gates (Production Line #90)
 * Desc: Generalized flow control for the order pipeline. An order can only ADVANCE to a
 *       stage when that stage's entry criteria are met. This is the production line's
 *       flow control — it stops orders jumping ahead of the work that must happen first
 *       (e.g. can't enter doc_review with no documents; can't deliver what was never
 *       submitted to the government).
 *
 *       The move to 'submitted' is NOT handled here — it is owned by the existing
 *       Pre-submission QA Gate (ukv-qa-gate.php, priority 9). This engine runs at
 *       priority 10 (AFTER the QA gate) and explicitly DELEGATES 'submitted' to it.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Map of target_status => list of requirement checks.
 *
 * Each check is [ 'label' => string, 'test' => callable(int $order_id):bool ].
 * A target status absent from this map has no entry requirements (always enterable).
 * 'submitted' is intentionally absent — the QA gate owns it.
 *
 * @return array<string, array<int, array{label:string, test:callable}>>
 */
function ukv_stage_entry_requirements(): array {
	return [
		// Doc review can only begin once there is something to review.
		'doc_review' => [
			[
				'label' => 'At least one document must be uploaded before doc review.',
				'test'  => static function ( int $order_id ): bool {
					$docs = array_filter( (array) get_post_meta( $order_id, 'ukv_documents', true ) );
					return count( $docs ) >= 1;
				},
			],
		],
		// Awaiting a decision only makes sense once a submission was actually made to govt.
		'awaiting_decision' => [
			[
				'label' => 'A government reference (ukv_govt_ref) must be recorded — the order must have been submitted.',
				'test'  => static function ( int $order_id ): bool {
					return '' !== trim( (string) get_post_meta( $order_id, 'ukv_govt_ref', true ) );
				},
			],
		],
		// Can't deliver an outcome for something that was never submitted to the government.
		'delivered' => [
			[
				'label' => 'A government reference (ukv_govt_ref) must be recorded — cannot deliver an order that was never submitted.',
				'test'  => static function ( int $order_id ): bool {
					return '' !== trim( (string) get_post_meta( $order_id, 'ukv_govt_ref', true ) );
				},
			],
		],
	];
}

/**
 * Can this order enter the given target stage right now?
 *
 * - Statuses with no defined requirements -> ok=true.
 * - 'submitted' -> ok=true (the QA gate owns that transition; this engine never blocks it).
 *
 * @param int    $order_id
 * @param string $target_status
 * @return array{ ok: bool, reasons: string[] }
 */
function ukv_stage_can_enter( int $order_id, string $target_status ): array {
	$target_status = (string) $target_status;

	// Delegated: the QA gate is the sole authority on entering 'submitted'.
	if ( 'submitted' === $target_status ) {
		return [ 'ok' => true, 'reasons' => [] ];
	}

	$map = ukv_stage_entry_requirements();
	if ( empty( $map[ $target_status ] ) ) {
		return [ 'ok' => true, 'reasons' => [] ]; // no entry criteria for this stage
	}

	$reasons = [];
	foreach ( $map[ $target_status ] as $req ) {
		if ( ! is_callable( $req['test'] ) || ! $req['test']( $order_id ) ) {
			$reasons[] = (string) $req['label'];
		}
	}

	return [ 'ok' => empty( $reasons ), 'reasons' => $reasons ];
}

/**
 * Core gate. Called directly (tests) and from the save hook below.
 *
 * If the attempted status is a real change, is NOT 'submitted', and the order does not
 * meet the target stage's entry criteria, this REVERTS ukv_status to the previous valid
 * stage, records a blocking journey note, and sets an admin-notice transient.
 *
 * @param int    $order_id
 * @param string $attempted_status  the value ukv_status was just set to.
 * @return bool TRUE when the transition was blocked (and reverted), FALSE otherwise.
 */
function ukv_stage_gate_enforce( int $order_id, string $attempted_status ): bool {
	$order_id         = (int) $order_id;
	$attempted_status = (string) $attempted_status;

	// 'submitted' is delegated to the QA gate — never block it here.
	if ( 'submitted' === $attempted_status ) {
		return false;
	}

	$prev = (string) get_post_meta( $order_id, 'ukv_status_last', true );

	// No real transition (status unchanged) -> nothing to gate.
	if ( $attempted_status === $prev ) {
		return false;
	}

	$check = ukv_stage_can_enter( $order_id, $attempted_status );
	if ( $check['ok'] ) {
		return false; // criteria met -> allow the move
	}

	// REVERT: roll ukv_status back to the previous valid stage so the move does not take
	// effect. Fall back to 'paid' (pipeline start) if we have no recorded prior stage.
	$revert_to = ( '' !== $prev && $prev !== $attempted_status ) ? $prev : 'paid';
	update_post_meta( $order_id, 'ukv_status', $revert_to );

	$reasons_txt = implode( '; ', $check['reasons'] );

	// Admin notice (rendered escaped on the next admin page load).
	$uid = get_current_user_id();
	if ( $uid ) {
		set_transient( 'ukv_stage_block_' . $uid, $check['reasons'], 60 );
	}

	// Journey audit note.
	$journey   = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$journey[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'internal',
		'text'    => sprintf( 'Stage move to %s blocked: %s', $attempted_status, $reasons_txt ),
	];
	update_post_meta( $order_id, 'ukv_journey', $journey );

	return true;
}

/**
 * The gate hook. Runs at priority 10 — AFTER the QA gate (priority 9) and BEFORE the
 * email status-change hook (priority 12), so a reverted status never fires a stage email.
 * Guards autosave / revisions. Skips 'submitted' (delegated to the QA gate).
 */
add_action( 'save_post_ukv_order', function ( $order_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $order_id ) ) { return; }

	$new  = (string) get_post_meta( $order_id, 'ukv_status', true );
	$last = (string) get_post_meta( $order_id, 'ukv_status_last', true );

	if ( '' === $new || $new === $last ) { return; } // no real transition
	if ( 'submitted' === $new ) { return; }          // delegated to the QA gate

	ukv_stage_gate_enforce( (int) $order_id, $new );
}, 10 );

/**
 * Render the block reason to the agent as a dismissible admin notice.
 */
add_action( 'admin_notices', function () {
	$uid     = get_current_user_id();
	$reasons = $uid ? get_transient( 'ukv_stage_block_' . $uid ) : false;
	if ( empty( $reasons ) || ! is_array( $reasons ) ) { return; }
	delete_transient( 'ukv_stage_block_' . $uid );
	echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Stage move blocked.', 'ukv' ) . '</strong> ';
	echo esc_html__( 'The order was kept at its previous stage because:', 'ukv' ) . '</p><ul style="margin-left:1.5em;list-style:disc">';
	foreach ( $reasons as $r ) {
		echo '<li>' . esc_html( $r ) . '</li>';
	}
	echo '</ul></div>';
} );
