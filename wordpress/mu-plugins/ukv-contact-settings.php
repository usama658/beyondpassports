<?php
/**
 * Plugin Name: UKV Contact Settings
 * Desc: Staff-editable contact details (phone / WhatsApp / hours) written to the EXISTING option
 *       names the rest of the site already reads (ukv_phone_number, ukv_whatsapp_number) so call
 *       bars, tel: links and wa.me links pick up real numbers immediately. Also adds a GDPR/consent
 *       line to the request-a-callback page. Public surface:
 *         ukv_contact_numbers_set(), ukv_contact_consent_html(), ukv_contact_consent_filter().
 */
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------------- sanitisers */

/** WhatsApp: digits and a single leading + only (wa.me wants digits). */
function ukv_contact_sanitize_whatsapp( $value ): string {
	$value = is_string( $value ) ? trim( $value ) : '';
	$plus  = ( '' !== $value && '+' === $value[0] ) ? '+' : '';
	return $plus . preg_replace( '/[^0-9]/', '', $value );
}

/* ----------------------------------------------------------------- readiness helper */

/**
 * True only when BOTH phone and WhatsApp are set AND not obviously-sample.
 * Treats empty, '0000', '123', 'sample' (and digit-stripped equivalents) as not-set.
 * Used by the hardening readiness checker.
 */
function ukv_contact_numbers_set(): bool {
	$phone = trim( (string) get_option( 'ukv_phone_number', '' ) );
	$wa    = trim( (string) get_option( 'ukv_whatsapp_number', '' ) );

	$real = static function ( string $v ): bool {
		if ( '' === $v ) { return false; }
		$lower = strtolower( $v );
		if ( in_array( $lower, [ '0000', '123', 'sample' ], true ) ) { return false; }
		// Digit form: reject obvious placeholders and anything too short to be a real number.
		$digits = preg_replace( '/[^0-9]/', '', $v );
		if ( in_array( $digits, [ '', '0000', '123' ], true ) ) { return false; }
		if ( strlen( $digits ) < 6 ) { return false; }
		return true;
	};

	return $real( $phone ) && $real( $wa );
}

/* ----------------------------------------------------------------- consent line */

/** The GDPR/consent paragraph appended to the callback page. Escaped, links to Privacy Policy if present. */
function ukv_contact_consent_html(): string {
	$text = 'By submitting, you consent to us contacting you about your enquiry. We never share your details.';

	$pp = get_page_by_path( 'privacy-policy' );
	if ( $pp instanceof WP_Post ) {
		$link = '<a href="' . esc_url( get_permalink( $pp ) ) . '">' . esc_html__( 'Privacy Policy', 'ukv' ) . '</a>';
		$tail = sprintf( /* translators: %s = Privacy Policy link */ 'See our %s.', $link );
	} else {
		$tail = esc_html__( 'See our Privacy Policy.', 'ukv' );
	}

	return '<p class="ukv-consent-note" style="font-size:.85em;color:#555;margin-top:1em;">'
		. esc_html( $text ) . ' ' . $tail . '</p>';
}

/**
 * Append the consent line to the callback page content.
 * @param string $content Page content.
 * @param string|null $slug Page slug to test against; defaults to the current queried page.
 */
function ukv_contact_consent_filter( string $content, ?string $slug = null ): string {
	if ( null === $slug ) {
		if ( ! is_page() ) { return $content; }
		$post = get_queried_object();
		$slug = ( $post instanceof WP_Post ) ? (string) $post->post_name : '';
	}
	if ( 'request-a-callback' !== $slug ) { return $content; }

	return $content . ukv_contact_consent_html();
}

add_filter( 'the_content', static function ( $content ) {
	return ukv_contact_consent_filter( (string) $content );
}, 20 );

/* ----------------------------------------------------------------- admin settings page */

add_action( 'admin_menu', static function () {
	add_options_page(
		'UKV Contact',
		'UKV Contact',
		'manage_options',
		'ukv-contact',
		'ukv_contact_settings_page'
	);
} );

/** Render + handle the settings form (nonce-protected, saves to existing option names). */
function ukv_contact_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'ukv' ) );
	}

	$saved = false;
	if ( isset( $_POST['ukv_contact_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_contact_nonce'] ) ), 'ukv_contact_save' ) ) {

		update_option( 'ukv_phone_number',    sanitize_text_field( wp_unslash( $_POST['ukv_phone_number'] ?? '' ) ) );
		update_option( 'ukv_whatsapp_number', ukv_contact_sanitize_whatsapp( wp_unslash( $_POST['ukv_whatsapp_number'] ?? '' ) ) );
		update_option( 'ukv_contact_hours',   sanitize_text_field( wp_unslash( $_POST['ukv_contact_hours'] ?? '' ) ) );
		$saved = true;
	}

	$phone = (string) get_option( 'ukv_phone_number', '' );
	$wa    = (string) get_option( 'ukv_whatsapp_number', '' );
	$hours = (string) get_option( 'ukv_contact_hours', '' );
	$ready = ukv_contact_numbers_set();

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'UKV Contact', 'ukv' ) . '</h1>';

	if ( $saved ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Contact details saved.', 'ukv' ) . '</p></div>';
	}

	echo '<p>' . esc_html__( 'These numbers feed the site call bar, tel: links and WhatsApp (wa.me) links.', 'ukv' ) . '</p>';
	echo '<p><strong>' . esc_html__( 'Status:', 'ukv' ) . '</strong> '
		. ( $ready
			? '<span style="color:#1a7f37;">' . esc_html__( 'Live numbers set.', 'ukv' ) . '</span>'
			: '<span style="color:#b32d2e;">' . esc_html__( 'Sample/placeholder — paste real numbers below.', 'ukv' ) . '</span>' )
		. '</p>';

	echo '<form method="post" action="">';
	wp_nonce_field( 'ukv_contact_save', 'ukv_contact_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';

	echo '<tr><th scope="row"><label for="ukv_phone_number">' . esc_html__( 'Phone number', 'ukv' ) . '</label></th>'
		. '<td><input name="ukv_phone_number" id="ukv_phone_number" type="text" class="regular-text" value="' . esc_attr( $phone ) . '">'
		. '<p class="description">' . esc_html__( 'Shown as a clickable tel: link, e.g. +44 20 1234 5678.', 'ukv' ) . '</p></td></tr>';

	echo '<tr><th scope="row"><label for="ukv_whatsapp_number">' . esc_html__( 'WhatsApp number', 'ukv' ) . '</label></th>'
		. '<td><input name="ukv_whatsapp_number" id="ukv_whatsapp_number" type="text" class="regular-text" value="' . esc_attr( $wa ) . '">'
		. '<p class="description">' . esc_html__( 'Digits only (a leading + is allowed), e.g. 447700900123.', 'ukv' ) . '</p></td></tr>';

	echo '<tr><th scope="row"><label for="ukv_contact_hours">' . esc_html__( 'Contact hours', 'ukv' ) . '</label></th>'
		. '<td><input name="ukv_contact_hours" id="ukv_contact_hours" type="text" class="regular-text" value="' . esc_attr( $hours ) . '">'
		. '<p class="description">' . esc_html__( 'Optional, e.g. Mon–Fri 9–6.', 'ukv' ) . '</p></td></tr>';

	echo '</tbody></table>';
	submit_button( __( 'Save contact details', 'ukv' ) );
	echo '</form></div>';
}
