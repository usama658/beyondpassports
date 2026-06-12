<?php
/**
 * Plugin Name: UKV Appointment Booking (Production Line Phase 5, #81)
 * Desc: WE book the customer's biometrics/visa appointment for them. Adds the booking workflow
 *       (centre + status) around the existing appointment ref/date fields, a journey-logged save,
 *       and the customer "what to bring" appointment pack. Independent service, not a government website.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Workflow statuses
 * ------------------------------------------------------------------------- */

/** Appointment workflow statuses, slug => label. */
const UKV_APPOINTMENT_STATUSES = [
	'not_required' => 'Not required',
	'to_book'      => 'To book',
	'booked'       => 'Booked',
	'attended'     => 'Attended',
	'completed'    => 'Completed',
];

/* ---------------------------------------------------------------------------
 * Helpers
 * ------------------------------------------------------------------------- */

/** Current appointment status for an order (defaults to 'not_required'). */
function ukv_appointment_status( int $order_id ): string {
	$s = (string) get_post_meta( $order_id, 'ukv_appointment_status', true );
	return isset( UKV_APPOINTMENT_STATUSES[ $s ] ) ? $s : 'not_required';
}

/**
 * Set/extend an order's appointment booking and log a journey note.
 * $data keys (all optional): centre, ref, date, status.
 * Writes ukv_appointment_centre / ukv_appointment_ref / ukv_appointment_at / ukv_appointment_status
 * (the ref/date keys are the SAME fields owned by ukv-govt-fields.php — extended, not duplicated).
 */
function ukv_set_appointment( int $order_id, array $data ): void {
	if ( $order_id <= 0 ) { return; }

	if ( isset( $data['centre'] ) ) {
		update_post_meta( $order_id, 'ukv_appointment_centre', sanitize_text_field( (string) $data['centre'] ) );
	}
	if ( isset( $data['ref'] ) ) {
		update_post_meta( $order_id, 'ukv_appointment_ref', sanitize_text_field( (string) $data['ref'] ) );
	}
	if ( isset( $data['date'] ) ) {
		update_post_meta( $order_id, 'ukv_appointment_at', sanitize_text_field( (string) $data['date'] ) );
	}

	$status = isset( $data['status'] ) && isset( UKV_APPOINTMENT_STATUSES[ $data['status'] ] )
		? (string) $data['status']
		: ukv_appointment_status( $order_id );
	update_post_meta( $order_id, 'ukv_appointment_status', $status );

	$centre = (string) get_post_meta( $order_id, 'ukv_appointment_centre', true );
	$date   = (string) get_post_meta( $order_id, 'ukv_appointment_at', true );
	$label  = UKV_APPOINTMENT_STATUSES[ $status ] ?? $status;
	ukv_appointment_log_note( $order_id, sprintf( 'Appointment %s: %s %s', $label, $centre, $date ) );
}

/** Append a note to the order's shared journey timeline (matches ukv-orders.php shape). */
function ukv_appointment_log_note( int $order_id, string $text ): void {
	$j = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$u = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
	$j[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => ( $u && $u->display_name ) ? $u->display_name : 'system',
		'channel' => 'internal',
		'text'    => trim( $text ),
	];
	update_post_meta( $order_id, 'ukv_journey', $j );
}

/* ---------------------------------------------------------------------------
 * Appointment pack — customer "what to bring"
 * ------------------------------------------------------------------------- */

/**
 * Build the customer appointment pack as escaped HTML.
 * Greets by name; shows centre/date/ref; checklist; arrive-early; contact line.
 * Compliance: independent service, not a government website.
 */
function ukv_appointment_pack( int $order_id ): string {
	$name   = (string) get_post_meta( $order_id, 'ukv_name', true );
	$centre = (string) get_post_meta( $order_id, 'ukv_appointment_centre', true );
	$ref    = (string) get_post_meta( $order_id, 'ukv_appointment_ref', true );
	$date   = (string) get_post_meta( $order_id, 'ukv_appointment_at', true );
	$dest   = (string) get_post_meta( $order_id, 'ukv_destination', true );

	$name   = $name !== '' ? $name : 'there';
	$centre = $centre !== '' ? $centre : 'your appointment centre';
	$ref    = $ref !== '' ? $ref : 'to be confirmed';
	$date   = $date !== '' ? $date : 'to be confirmed';

	$contact = function_exists( 'ukv_contact_email' ) ? (string) ukv_contact_email() : 'support@ukvisaco.com';

	$checklist = [
		'Your passport (valid, plus any previous passports if you have them)',
		'Your supporting documents' . ( $dest !== '' ? ' for ' . ucfirst( $dest ) : '' ) . ' (originals and copies)',
		'A printout of your appointment confirmation',
		'Biometrics note: you may be asked to give your fingerprints and a photograph at the centre',
	];

	$h  = '<div class="ukv-appointment-pack">';
	$h .= '<h2>Your appointment pack</h2>';
	$h .= '<p>Hi ' . esc_html( $name ) . ',</p>';
	$h .= '<p>We have arranged your appointment. Please read this pack carefully and bring everything listed below.</p>';

	$h .= '<table class="ukv-appointment-details"><tbody>';
	$h .= '<tr><th>Centre</th><td>' . esc_html( $centre ) . '</td></tr>';
	$h .= '<tr><th>Date</th><td>' . esc_html( $date ) . '</td></tr>';
	$h .= '<tr><th>Reference</th><td>' . esc_html( $ref ) . '</td></tr>';
	$h .= '</tbody></table>';

	$h .= '<h3>What to bring</h3><ul>';
	foreach ( $checklist as $item ) {
		$h .= '<li>' . esc_html( $item ) . '</li>';
	}
	$h .= '</ul>';

	$h .= '<p><strong>Please arrive at least 15 minutes early.</strong> Late arrivals may be turned away and the appointment may have to be rebooked.</p>';

	$h .= '<p>Questions? Contact us at ' . esc_html( $contact ) . ' and quote your reference.</p>';

	$h .= '<p class="ukv-compliance"><em>UKVisaCo is an independent visa support service. We are not affiliated with, endorsed by, or part of any government department, and this is not a government website. Official government fees are payable separately.</em></p>';
	$h .= '</div>';

	return $h;
}

/* ---------------------------------------------------------------------------
 * Meta box — Appointment workflow
 * ------------------------------------------------------------------------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_appointment', 'Appointment', 'ukv_appointment_metabox', 'ukv_order', 'normal', 'default' );
} );

function ukv_appointment_metabox( $post ) {
	$pid = $post->ID;
	wp_nonce_field( 'ukv_appointment_save', 'ukv_appointment_nonce' );
	$g      = fn( $k, $d = '' ) => get_post_meta( $pid, $k, true ) ?: $d;
	$status = ukv_appointment_status( $pid );

	echo '<style>.ukva label{display:block;font-weight:600;margin:8px 0 2px}.ukva input,.ukva select{width:100%}.ukva-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}</style><div class="ukva">';
	echo '<div class="ukva-grid">';
	echo '<div><label>Status</label><select name="ukv_appointment_status">';
	foreach ( UKV_APPOINTMENT_STATUSES as $k => $v ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $status, $k, false ) . '>' . esc_html( $v ) . '</option>';
	}
	echo '</select></div>';
	echo '<div><label>Centre (e.g. VFS London)</label><input name="ukv_appointment_centre" value="' . esc_attr( $g( 'ukv_appointment_centre' ) ) . '"></div>';
	echo '<div><label>Appointment reference</label><input name="ukv_appointment_ref" value="' . esc_attr( $g( 'ukv_appointment_ref' ) ) . '"></div>';
	echo '<div><label>Appointment date</label><input type="date" name="ukv_appointment_at" value="' . esc_attr( $g( 'ukv_appointment_at' ) ) . '"></div>';
	echo '</div>';
	echo '<p style="color:#666;font-size:11px;margin-top:8px">Changing the status logs a note to the Lead Journey timeline.</p>';
	echo '</div>';
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_appointment_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_appointment_nonce'], 'ukv_appointment_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }

	$old_status = ukv_appointment_status( $pid );

	$centre = isset( $_POST['ukv_appointment_centre'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_appointment_centre'] ) ) : '';
	update_post_meta( $pid, 'ukv_appointment_centre', $centre );

	// ref/date are shared with ukv-govt-fields.php; both save handlers write the same sanitized value.
	if ( isset( $_POST['ukv_appointment_ref'] ) ) {
		update_post_meta( $pid, 'ukv_appointment_ref', sanitize_text_field( wp_unslash( $_POST['ukv_appointment_ref'] ) ) );
	}
	if ( isset( $_POST['ukv_appointment_at'] ) ) {
		update_post_meta( $pid, 'ukv_appointment_at', sanitize_text_field( wp_unslash( $_POST['ukv_appointment_at'] ) ) );
	}

	$new_status = isset( $_POST['ukv_appointment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_appointment_status'] ) ) : $old_status;
	if ( ! isset( UKV_APPOINTMENT_STATUSES[ $new_status ] ) ) { $new_status = 'not_required'; }
	update_post_meta( $pid, 'ukv_appointment_status', $new_status );

	// Audit: only log when the status actually changes.
	if ( $new_status !== $old_status ) {
		$date  = (string) get_post_meta( $pid, 'ukv_appointment_at', true );
		$label = UKV_APPOINTMENT_STATUSES[ $new_status ] ?? $new_status;
		ukv_appointment_log_note( $pid, sprintf( 'Appointment %s: %s %s', $label, $centre, $date ) );
	}
}, 20 );
