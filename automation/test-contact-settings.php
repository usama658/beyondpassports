<?php
/**
 * Test: UKV Contact Settings (ukv-contact-settings.php).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-contact-settings.php"
 *
 * Saves + restores ukv_phone_number / ukv_whatsapp_number so real config is never clobbered.
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required public functions present.
$check( function_exists( 'ukv_contact_numbers_set' ), 'ukv_contact_numbers_set() is defined' );
$check( function_exists( 'ukv_contact_consent_html' ), 'ukv_contact_consent_html() is defined' );
$check( function_exists( 'ukv_contact_consent_filter' ), 'ukv_contact_consent_filter() is defined' );

// 1. Save current option values so we can restore them.
$orig_phone = get_option( 'ukv_phone_number', '' );
$orig_wa    = get_option( 'ukv_whatsapp_number', '' );
echo "INFO — saved originals: phone='{$orig_phone}', whatsapp='{$orig_wa}'\n";

// 2. Real-looking values -> helper true.
update_option( 'ukv_phone_number', '+44 20 1234 5678' );
update_option( 'ukv_whatsapp_number', '447700900123' );
$check( ukv_contact_numbers_set() === true, 'both real-looking numbers set -> ukv_contact_numbers_set() true' );

// 3. Empty / sample phone -> helper false.
update_option( 'ukv_phone_number', '' );
$check( ukv_contact_numbers_set() === false, 'empty phone -> ukv_contact_numbers_set() false' );

update_option( 'ukv_phone_number', 'sample' );
$check( ukv_contact_numbers_set() === false, "phone='sample' -> ukv_contact_numbers_set() false" );

// Restore a good phone, then blank the whatsapp -> still false (needs BOTH).
update_option( 'ukv_phone_number', '+44 20 1234 5678' );
update_option( 'ukv_whatsapp_number', '0000' );
$check( ukv_contact_numbers_set() === false, "whatsapp='0000' (sample) -> ukv_contact_numbers_set() false" );

update_option( 'ukv_whatsapp_number', '447700900123' );
$check( ukv_contact_numbers_set() === true, 'both restored to real values -> true again' );

// 4. Consent HTML helper + filter callback.
$html = ukv_contact_consent_html();
$check( stripos( $html, 'consent' ) !== false, 'ukv_contact_consent_html() contains "consent"' );
$check( stripos( $html, 'Privacy Policy' ) !== false, 'consent HTML mentions Privacy Policy' );

// Filter on the callback page appends the consent block; elsewhere it is unchanged.
$dummy = '<p>Original page body.</p>';
$on_callback  = ukv_contact_consent_filter( $dummy, 'request-a-callback' );
$check( strpos( $on_callback, 'Original page body.' ) !== false, 'filter preserves original content on callback page' );
$check( stripos( $on_callback, 'consent' ) !== false, 'filter appends consent text on the callback page' );

$on_other = ukv_contact_consent_filter( $dummy, 'some-other-page' );
$check( $on_other === $dummy, 'filter leaves content untouched on non-callback pages' );

// 5. Restore original option values.
update_option( 'ukv_phone_number', $orig_phone );
update_option( 'ukv_whatsapp_number', $orig_wa );
$check( get_option( 'ukv_phone_number', '' ) === $orig_phone, 'ukv_phone_number restored' );
$check( get_option( 'ukv_whatsapp_number', '' ) === $orig_wa, 'ukv_whatsapp_number restored' );

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
