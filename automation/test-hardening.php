<?php
/**
 * Test: UKV Pre-launch Hardening + Readiness checker (ukv-hardening.php).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-hardening.php"
 *
 * Saves + restores ukv_hubspot_token so it never clobbers the real option.
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// 0. Sanity: function present.
$check( function_exists( 'ukv_hardening_checks' ), 'ukv_hardening_checks() is defined' );

// 1. Returns a non-empty, well-formed array (no fatal).
$err = '';
set_error_handler( function ( $no, $str ) use ( &$err ) { $err .= $str . "\n"; return true; } );
$items = ukv_hardening_checks();
restore_error_handler();
$check( $err === '', 'no PHP error/warning calling ukv_hardening_checks()' . ( $err ? " (got: " . trim( $err ) . ")" : '' ) );
$check( is_array( $items ) && count( $items ) > 0, 'returns a non-empty array (' . ( is_array( $items ) ? count( $items ) : 'not array' ) . ' items)' );

$valid_status = [ 'ok', 'warn', 'fail' ];
$well_formed  = true;
foreach ( (array) $items as $it ) {
	if ( ! is_array( $it )
		|| ! isset( $it['key'], $it['label'], $it['status'], $it['detail'] )
		|| ! in_array( $it['status'], $valid_status, true ) ) {
		$well_formed = false;
		break;
	}
}
$check( $well_formed, 'every item has keys key/label/status/detail and status in [ok,warn,fail]' );

// Helper: fetch a single check by key (re-runs checks each time).
$status_of = function ( $key ) {
	foreach ( ukv_hardening_checks() as $it ) {
		if ( isset( $it['key'] ) && $it['key'] === $key ) { return $it['status']; }
	}
	return null;
};

// 2. HubSpot token check flips fail <-> not-fail. Save + restore the real value.
$tk = get_option( 'ukv_hubspot_token', '' );

update_option( 'ukv_hubspot_token', 'pat-na2-EXAMPLE' );
$check( $status_of( 'hubspot_token' ) === 'fail', "exposed token (pat-na2-...) => hubspot check is 'fail'" );

update_option( 'ukv_hubspot_token', '' );
$check( $status_of( 'hubspot_token' ) !== 'fail', "empty/rotated token => hubspot check is NOT 'fail' (got '" . $status_of( 'hubspot_token' ) . "')" );

update_option( 'ukv_hubspot_token', $tk ); // restore original
$check( get_option( 'ukv_hubspot_token', '' ) === $tk, 'original ukv_hubspot_token restored' );

// 3. DISALLOW_FILE_EDIT defined + true after the plugin loads.
$check( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT === true, 'DISALLOW_FILE_EDIT is defined and true' );

// 4. No fatal calling the checks repeatedly (re-entrancy / idempotent).
$err2 = '';
set_error_handler( function ( $no, $str ) use ( &$err2 ) { $err2 .= $str . "\n"; return true; } );
ukv_hardening_checks();
ukv_hardening_checks();
restore_error_handler();
$check( $err2 === '', 'no fatal/warning on repeated calls' . ( $err2 ? " (got: " . trim( $err2 ) . ")" : '' ) );

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
