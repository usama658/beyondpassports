<?php
/**
 * Plugin Name: UKV Emails (Email Lifecycle Engine)
 * Desc: Lean event→template email engine. Secondary channel (calls/WhatsApp are primary).
 *       Logs every attempt, idempotent per (order,event), pluggable transport (wp_mail|hubspot).
 *       Runs in log mode on XAMPP; flip to real delivery at launch.
 */
defined( 'ABSPATH' ) || exit;

const UKV_EMAIL_FOOTER = "Independent service — not a government website. Our service fee is separate from any government fee. Express tiers speed up our handling, not the government's decision.";

/**
 * Build the email for an event + order.
 * @return array{ subject:string, body:string }
 */
function ukv_email_template( string $event, int $order_id ): array {
	$g     = fn( $k ) => (string) get_post_meta( $order_id, $k, true );
	$name  = $g( 'ukv_name' ) ?: 'there';
	$dest  = $g( 'ukv_destination' ) ?: 'your destination';
	$ref   = $g( 'ukv_order_ref' ) ?: '—';

	$subjects = [
		'order_paid'      => "Your {$dest} visa order is confirmed ({$ref})",
		'docs_needed'     => "Action needed: documents for your {$dest} visa ({$ref})",
		'submitted'       => "Your {$dest} visa application has been submitted ({$ref})",
		'decision'        => "Update on your {$dest} visa decision ({$ref})",
		'delivered'       => "Your {$dest} visa is ready ({$ref})",
		'review_request'  => "How did we do? Your {$dest} visa ({$ref})",
		'checker_abandon' => "Finish your {$dest} visa check — we can help",
	];

	$bodies = [
		'order_paid' => "Hi {$name},\n\nThanks — we've received your order for your {$dest} visa. Your order reference is {$ref}.\n\nOur team will be in touch shortly (usually by phone or WhatsApp) to guide you through the next steps. There's nothing you need to do right now.\n",
		'docs_needed' => "Hi {$name},\n\nTo move your {$dest} visa application forward (order {$ref}), we need a few documents from you. The quickest way is to reply here or to our WhatsApp message, and we'll confirm exactly what's required.\n\nWe'll keep this moving as soon as we have what we need.\n",
		'submitted' => "Hi {$name},\n\nGood news — your {$dest} visa application (order {$ref}) has now been submitted. We'll let you know as soon as there's a decision.\n",
		'decision' => "Hi {$name},\n\nThere's an update on your {$dest} visa application (order {$ref}). A member of our team will be in touch by phone or WhatsApp with the details and any next steps.\n",
		'delivered' => "Hi {$name},\n\nYour {$dest} visa is ready (order {$ref}). Please check the details carefully and keep a copy with your travel documents.\n\nIf anything doesn't look right, contact us straight away and we'll help.\n",
		'review_request' => "Hi {$name},\n\nWe hope your {$dest} visa (order {$ref}) made your trip planning easier. If you have a moment, we'd really appreciate a short review of how we did — it helps other travellers know what to expect.\n",
		'checker_abandon' => "Hi {$name},\n\nIt looks like you started a visa check for {$dest} but didn't finish. If you'd like a hand, we can guide you through it — just reply and we'll pick up where you left off.\n",
	];

	$subject = $subjects[ $event ] ?? "Update on your {$dest} visa ({$ref})";
	$body    = $bodies[ $event ] ?? "Hi {$name},\n\nThere's an update on your {$dest} visa (order {$ref}).\n";

	// Optional P15 AI polish — only if helper exists and returns non-null.
	// Note: ukv_ai_polish_content expects already-anonymised text; if our body contains PII
	// its leak gate returns null and we keep the static template (safe fallback).
	if ( function_exists( 'ukv_ai_polish_content' ) ) {
		$polished = ukv_ai_polish_content( $body, 'transactional email' );
		if ( null !== $polished && '' !== trim( (string) $polished ) ) {
			$body = (string) $polished;
		}
	}

	$body .= "\n" . UKV_EMAIL_FOOTER . "\n";

	return [ 'subject' => $subject, 'body' => $body ];
}

/**
 * Send (or log) one email for an (order, event). Idempotent + always logged.
 */
function ukv_email_send( string $to, string $subject, string $body, string $event, int $order_id ): bool {
	$meta = fn( $k ) => is_array( $m = get_post_meta( $order_id, $k, true ) ) ? $m : [];

	$sent = $meta( 'ukv_email_sent' );
	if ( in_array( $event, $sent, true ) ) {
		return false; // already sent — no resend.
	}

	// Always log (audit) + journey note, regardless of transport outcome.
	$log   = $meta( 'ukv_email_log' );
	$log[] = [ 'event' => $event, 'to' => $to, 'subject' => $subject, 'time' => gmdate( 'Y-m-d H:i' ) ];
	update_post_meta( $order_id, 'ukv_email_log', $log );

	$journey   = $meta( 'ukv_journey' );
	$journey[] = [ 'date' => gmdate( 'Y-m-d H:i' ), 'agent' => 'system', 'channel' => 'email', 'text' => "Email sent: {$event}" ];
	update_post_meta( $order_id, 'ukv_journey', $journey );

	// Transport (best-effort; never fatal when delivery can't happen on XAMPP).
	$transport = get_option( 'ukv_email_transport', 'wp_mail' );
	if ( 'hubspot' === $transport && function_exists( 'ukv_hs_post' ) ) {
		// Real engagement push can be added later; log-only for now (do not fail).
	} else {
		// Default wp_mail. On XAMPP this returns but won't deliver — fine.
		wp_mail( $to, $subject, $body );
	}

	$sent[] = $event;
	update_post_meta( $order_id, 'ukv_email_sent', $sent );
	return true;
}

/**
 * Convenience: build template, read the order email, send.
 */
function ukv_email_fire( string $event, int $order_id ): bool {
	$to = (string) get_post_meta( $order_id, 'ukv_email', true );
	if ( '' === trim( $to ) ) {
		return false;
	}
	$tpl = ukv_email_template( $event, $order_id );
	return ukv_email_send( $to, $tpl['subject'], $tpl['body'], $event, $order_id );
}

/**
 * Testable core for status-change triggers. Fires the matching event(s) once each.
 */
function ukv_email_on_status_change( int $order_id, string $new_status ): void {
	switch ( $new_status ) {
		case 'submitted':
			ukv_email_fire( 'submitted', $order_id );
			break;
		case 'awaiting_decision':
			ukv_email_fire( 'decision', $order_id );
			break;
		case 'delivered':
		case 'won': // approved/delivered — both are a successful completion
			ukv_email_fire( 'delivered', $order_id );
			ukv_email_fire( 'review_request', $order_id );
			break;
	}
}

/**
 * Hook: on order save, detect a status transition and fire the matching email.
 * Meta compare only — no nonce needed; guard autosave/revisions.
 */
add_action( 'save_post_ukv_order', function ( $order_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $order_id ) ) { return; }
	$new  = (string) get_post_meta( $order_id, 'ukv_status', true );
	$last = (string) get_post_meta( $order_id, 'ukv_status_last', true );
	if ( $new === $last ) { return; }
	update_post_meta( $order_id, 'ukv_status_last', $new );
	if ( '' !== $new ) {
		ukv_email_on_status_change( (int) $order_id, $new );
	}
}, 12 );

/**
 * checker_abandon cron — SAFE no-op placeholder.
 * No checker-email store exists yet, so this is a documented stub: when a visa-checker
 * submission captures an email and no Apply/order follows within 24h, iterate captured
 * leads here and fire 'checker_abandon' once each (idempotent). Wired at launch.
 */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ukv_email_checker_abandon' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ukv_email_checker_abandon' );
	}
} );
add_action( 'ukv_email_checker_abandon', function () {
	$leads = []; // Placeholder: no checker-email data source yet. Populate at launch.
	foreach ( $leads as $lead ) {
		// ukv_email_send( $lead['email'], ... 'checker_abandon', $lead['order_id'] );
	}
} );

/**
 * Admin: settings field for the email transport.
 */
add_action( 'admin_init', function () {
	register_setting( 'general', 'ukv_email_transport', [
		'type'              => 'string',
		'sanitize_callback' => fn( $v ) => in_array( $v, [ 'wp_mail', 'hubspot' ], true ) ? $v : 'wp_mail',
		'default'           => 'wp_mail',
	] );
	add_settings_field( 'ukv_email_transport', 'UKV email transport', function () {
		$cur = get_option( 'ukv_email_transport', 'wp_mail' );
		echo '<select name="ukv_email_transport">';
		foreach ( [ 'wp_mail' => 'wp_mail (default)', 'hubspot' => 'HubSpot engagement' ] as $k => $label ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $cur, $k, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	}, 'general' );
} );
