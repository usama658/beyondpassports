<?php
/**
 * Plugin Name: UKV Pre-launch Hardening + Readiness Checker
 * Desc: Adds baseline security headers (front-end only), disables in-dashboard file
 *       editing, and exposes a "Pre-launch" readiness checklist (Tools menu) plus an
 *       admin notice counting unresolved fail items. All checks are defensive (no fatals).
 */
defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * 1. Security headers — FRONT-END ONLY (guard is_admin()), on send_headers.
 *    Never override a header already set upstream (nginx/Apache/other plugin).
 * ---------------------------------------------------------------------- */
add_action( 'send_headers', function () {
	if ( is_admin() || headers_sent() ) {
		return; // admin requests + already-flushed output are out of scope.
	}
	$wanted = [
		'X-Content-Type-Options' => 'nosniff',
		'X-Frame-Options'        => 'SAMEORIGIN',
		'Referrer-Policy'        => 'strict-origin-when-cross-origin',
	];
	// Collect header names already present (case-insensitive) so we don't override.
	$present = [];
	foreach ( headers_list() as $h ) {
		$name = strtolower( trim( strstr( $h, ':', true ) ) );
		if ( '' !== $name ) { $present[ $name ] = true; }
	}
	foreach ( $wanted as $name => $value ) {
		if ( empty( $present[ strtolower( $name ) ] ) ) {
			header( $name . ': ' . $value, false );
		}
	}
} );

/* -------------------------------------------------------------------------
 * 2. Disable the in-dashboard plugin/theme file editor.
 *    mu-plugins load before the editor screens, so defining it here is effective.
 *    NOTE: wp-config.php is the canonical place for this in production — define it
 *    there too so it survives even if mu-plugins are ever cleared.
 * ---------------------------------------------------------------------- */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/* -------------------------------------------------------------------------
 * 3. Readiness checker.
 *    Returns: array of [ 'key', 'label', 'status' (ok|warn|fail), 'detail', 'fix' ].
 *    Defensive throughout — never fatals, even if options are missing/odd types.
 * ---------------------------------------------------------------------- */
function ukv_hardening_checks(): array {
	$items = [];

	// -- HubSpot token: fail if still the known-exposed pat-na2- token. --
	$hs = (string) get_option( 'ukv_hubspot_token', '' );
	if ( '' === $hs ) {
		$items[] = [
			'key'    => 'hubspot_token',
			'label'  => 'HubSpot token',
			'status' => 'warn',
			'detail' => 'No HubSpot token set — CRM sync is inert.',
			'fix'    => 'Set a fresh Private App token in the HubSpot settings once CRM sync is needed.',
		];
	} elseif ( 0 === strpos( $hs, 'pat-na2-' ) ) {
		$items[] = [
			'key'    => 'hubspot_token',
			'label'  => 'HubSpot token',
			'status' => 'fail',
			'detail' => 'The known-exposed token (pat-na2-…) is still in the database.',
			'fix'    => 'ROTATE NOW: revoke the leaked Private App in HubSpot, generate a new token, and update ukv_hubspot_token.',
		];
	} else {
		$items[] = [
			'key'    => 'hubspot_token',
			'label'  => 'HubSpot token',
			'status' => 'ok',
			'detail' => 'Token is set and is not the known-exposed value.',
			'fix'    => '',
		];
	}

	// -- Anthropic key: warn if empty (AI features inert). --
	$an = (string) get_option( 'ukv_anthropic_key', '' );
	$items[] = ( '' === trim( $an ) )
		? [
			'key'    => 'anthropic_key',
			'label'  => 'Anthropic API key',
			'status' => 'warn',
			'detail' => 'No Anthropic key set — AI features will be inert.',
			'fix'    => 'Add a valid Anthropic API key in settings to enable AI features.',
		]
		: [
			'key'    => 'anthropic_key',
			'label'  => 'Anthropic API key',
			'status' => 'ok',
			'detail' => 'Anthropic key is set.',
			'fix'    => '',
		];

	// -- Phone number: fail if empty or looks like a sample/placeholder. --
	$items[] = ukv_hardening_check_contact(
		'phone_number',
		'Phone number',
		(string) get_option( 'ukv_phone_number', '' ),
		'Set the real public phone number in settings.'
	);

	// -- WhatsApp number: same treatment. --
	$items[] = ukv_hardening_check_contact(
		'whatsapp_number',
		'WhatsApp number',
		(string) get_option( 'ukv_whatsapp_number', '' ),
		'Set the real WhatsApp number in settings.'
	);

	// -- Email transport: SMTP must be configured before launch. --
	$transport = strtolower( trim( (string) get_option( 'ukv_email_transport', '' ) ) );
	$is_smtp   = ( 'smtp' === $transport );
	$is_local  = ukv_hardening_is_local();
	if ( $is_smtp ) {
		$items[] = [
			'key'    => 'email_transport',
			'label'  => 'Email transport',
			'status' => 'ok',
			'detail' => 'Transport is set to SMTP.',
			'fix'    => 'Confirm SMTP credentials send a live test email before launch.',
		];
	} else {
		$items[] = [
			'key'    => 'email_transport',
			'label'  => 'Email transport',
			'status' => 'warn',
			'detail' => $is_local
				? 'Local environment — SMTP is not configured here. SMTP MUST be configured at launch so transactional email is delivered.'
				: 'Transport is "' . ( $transport ?: 'unset' ) . '". SMTP MUST be configured at launch for reliable transactional email.',
			'fix'    => 'Configure an SMTP provider (e.g. transactional service) and set ukv_email_transport to "smtp".',
		];
	}

	// -- DISALLOW_FILE_EDIT defined (ok/fail). --
	$file_edit_off = defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT;
	$items[] = [
		'key'    => 'disallow_file_edit',
		'label'  => 'Dashboard file editing',
		'status' => $file_edit_off ? 'ok' : 'fail',
		'detail' => $file_edit_off
			? 'DISALLOW_FILE_EDIT is defined and true — the in-dashboard editor is off.'
			: 'DISALLOW_FILE_EDIT is not enforced — plugin/theme files are editable from the dashboard.',
		'fix'    => $file_edit_off ? '' : "Add define('DISALLOW_FILE_EDIT', true); to wp-config.php (canonical) or keep this mu-plugin active.",
	];

	// -- Pretty permalinks (warn if plain). --
	$structure = '';
	if ( function_exists( 'get_option' ) ) {
		$structure = (string) get_option( 'permalink_structure', '' );
	}
	$items[] = ( '' !== $structure )
		? [
			'key'    => 'permalinks',
			'label'  => 'Pretty permalinks',
			'status' => 'ok',
			'detail' => 'Pretty permalinks are enabled (' . $structure . ').',
			'fix'    => '',
		]
		: [
			'key'    => 'permalinks',
			'label'  => 'Pretty permalinks',
			'status' => 'warn',
			'detail' => 'Permalinks are set to "Plain" — SEO-friendly URLs and some rewrite-based features need pretty permalinks.',
			'fix'    => 'Settings → Permalinks → choose Post name (or any non-plain structure) and save.',
		];

	return $items;
}

/**
 * Shared helper: validate a public contact number.
 * fail if empty or it looks like a sample/placeholder; ok otherwise.
 */
function ukv_hardening_check_contact( string $key, string $label, string $value, string $fix ): array {
	$value = trim( $value );
	if ( '' === $value || ukv_hardening_looks_like_sample( $value ) ) {
		return [
			'key'    => $key,
			'label'  => $label,
			'status' => 'fail',
			'detail' => '' === $value
				? "No {$label} set — public contact details are required at launch."
				: "The {$label} (\"{$value}\") looks like a sample/placeholder.",
			'fix'    => $fix,
		];
	}
	return [
		'key'    => $key,
		'label'  => $label,
		'status' => 'ok',
		'detail' => "Real {$label} is set.",
		'fix'    => '',
	];
}

/** Heuristic: does a phone-ish string look like a placeholder/sample? */
function ukv_hardening_looks_like_sample( string $value ): bool {
	$v     = strtolower( $value );
	$words = [ 'sample', 'example', 'placeholder', 'changeme', 'change me', 'your number', 'xxx', 'tbd', 'n/a', '000000', '123456', '1234567' ];
	foreach ( $words as $w ) {
		if ( false !== strpos( $v, $w ) ) { return true; }
	}
	// All-same-digit (e.g. 0000000000) once stripped to digits.
	$digits = preg_replace( '/\D+/', '', $value );
	if ( strlen( $digits ) >= 6 && preg_match( '/^(\d)\1+$/', $digits ) ) { return true; }
	return false;
}

/** Best-effort: are we on a local/dev environment? Defensive (no fatal). */
function ukv_hardening_is_local(): bool {
	if ( function_exists( 'wp_get_environment_type' ) ) {
		$env = wp_get_environment_type();
		if ( 'local' === $env || 'development' === $env ) { return true; }
	}
	$host = '';
	if ( function_exists( 'wp_parse_url' ) ) {
		$host = (string) wp_parse_url( (string) get_option( 'siteurl', '' ), PHP_URL_HOST );
	}
	$host = strtolower( $host );
	return ( 'localhost' === $host
		|| '127.0.0.1' === $host
		|| '' !== $host && ( false !== strpos( $host, '.local' ) || false !== strpos( $host, '.test' ) ) );
}

/* -------------------------------------------------------------------------
 * 4a. Admin page: "Pre-launch" under Tools (manage_options).
 * ---------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_management_page(
		'Pre-launch readiness',
		'Pre-launch',
		'manage_options',
		'ukv-prelaunch',
		'ukv_hardening_render_page'
	);
} );

function ukv_hardening_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.' ) );
	}
	$items  = ukv_hardening_checks();
	$counts = [ 'ok' => 0, 'warn' => 0, 'fail' => 0 ];
	foreach ( $items as $it ) {
		$s = isset( $it['status'] ) && isset( $counts[ $it['status'] ] ) ? $it['status'] : 'warn';
		$counts[ $s ]++;
	}
	$colour = [ 'ok' => '#1a7f37', 'warn' => '#bf8700', 'fail' => '#b32d2e' ];
	$badge  = [ 'ok' => 'OK', 'warn' => 'WARN', 'fail' => 'FAIL' ];

	echo '<div class="wrap"><h1>Pre-launch readiness</h1>';
	echo '<p>Resolve every <strong style="color:' . esc_attr( $colour['fail'] ) . '">FAIL</strong> before going live. '
		. esc_html( sprintf( '%d ok · %d warn · %d fail', $counts['ok'], $counts['warn'], $counts['fail'] ) ) . '</p>';

	echo '<table class="widefat striped" style="max-width:920px"><thead><tr>'
		. '<th style="width:90px">Status</th><th style="width:200px">Check</th><th>Detail &amp; fix</th>'
		. '</tr></thead><tbody>';
	foreach ( $items as $it ) {
		$status = isset( $it['status'], $colour[ $it['status'] ] ) ? $it['status'] : 'warn';
		echo '<tr>';
		echo '<td><span style="display:inline-block;padding:2px 8px;border-radius:3px;color:#fff;font-weight:600;background:'
			. esc_attr( $colour[ $status ] ) . '">' . esc_html( $badge[ $status ] ) . '</span></td>';
		echo '<td><strong>' . esc_html( (string) ( $it['label'] ?? '' ) ) . '</strong></td>';
		echo '<td>' . esc_html( (string) ( $it['detail'] ?? '' ) );
		if ( ! empty( $it['fix'] ) ) {
			echo '<br><em style="color:#555">Fix: ' . esc_html( (string) $it['fix'] ) . '</em>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	echo '</div>';
}

/* -------------------------------------------------------------------------
 * 4b. Dashboard admin notice — fail count, admins only.
 * ---------------------------------------------------------------------- */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$fails = 0;
	foreach ( ukv_hardening_checks() as $it ) {
		if ( isset( $it['status'] ) && 'fail' === $it['status'] ) { $fails++; }
	}
	if ( $fails < 1 ) {
		return;
	}
	$url = esc_url( admin_url( 'tools.php?page=ukv-prelaunch' ) );
	echo '<div class="notice notice-error"><p><strong>Pre-launch:</strong> '
		. esc_html( sprintf( '%d critical readiness check%s failing.', $fails, 1 === $fails ? '' : 's' ) )
		. ' <a href="' . $url . '">Review the checklist</a>.</p></div>';
} );
