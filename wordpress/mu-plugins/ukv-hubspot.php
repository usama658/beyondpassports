<?php
/**
 * Plugin Name: UKV HubSpot CRM (Forminator -> HubSpot)
 * Desc: On a SUCCESSFUL Stripe charge from the Apply form, create a HubSpot Contact + Deal in the Paid stage.
 * Token in DB option ukv_hubspot_token (not in code/git).
 */
defined( 'ABSPATH' ) || exit;

const UKV_HS_PIPELINE   = 'default';
const UKV_HS_STAGE_PAID = '3823157986';

function ukv_hs_token() { return get_option( 'ukv_hubspot_token', '' ); }

function ukv_hs_post( $path, $body ) {
	$token = ukv_hs_token();
	if ( ! $token ) { return null; }
	$res = wp_remote_post( 'https://api.hubapi.com' . $path, [
		'headers' => [ 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ],
		'body'    => wp_json_encode( $body ),
		'timeout' => 20,
	] );
	if ( is_wp_error( $res ) ) { return null; }
	return [ 'code' => wp_remote_retrieve_response_code( $res ), 'data' => json_decode( wp_remote_retrieve_body( $res ), true ) ];
}

// Fires only after a successful Stripe payment on a custom form.
add_action( 'forminator_custom_form_after_stripe_charge', function ( $module, $field, $stripe_entry_data, $prepared_data, $field_data_array ) {
	if ( ! ukv_hs_token() || ! is_array( $prepared_data ) ) { return; }
	// Guard: only our Apply form has a destination select + tier radio.
	if ( empty( $prepared_data['select-1'] ) || ! isset( $prepared_data['radio-1'] ) ) { return; }

	$dest  = (string) $prepared_data['select-1'];
	$tierP = (float) $prepared_data['radio-1'];
	$email = (string) ( $prepared_data['email-1'] ?? '' );
	$pass  = (string) ( $prepared_data['text-1'] ?? '' );
	$nameV = $prepared_data['name-1'] ?? '';
	if ( is_array( $nameV ) ) { $first = $nameV['first-name'] ?? ''; $last = $nameV['last-name'] ?? ''; }
	else { $parts = explode( ' ', trim( (string) $nameV ), 2 ); $first = $parts[0] ?? ''; $last = $parts[1] ?? ''; }

	$govt  = function_exists( 'ukv_dest_value' ) ? (float) ukv_dest_value( $dest, 'govt_fee_gbp' ) : 0;
	$total = $tierP + $govt;
	$tierName = [ 29 => 'Standard', 49 => 'Express', 79 => 'Premium', 25 => 'Standard', 39 => 'Express', 59 => 'Premium', 35 => 'Standard', 55 => 'Express', 85 => 'Premium' ][ (int) $tierP ] ?? (string) $tierP;
	$destName = ucwords( str_replace( '-', ' ', $dest ) );
	$ref   = 'UKV-' . gmdate( 'Y' ) . '-' . substr( (string) time(), -6 );

	// 1) Contact
	$contactId = 0;
	$c = ukv_hs_post( '/crm/v3/objects/contacts', [ 'properties' => array_filter( [ 'email' => $email, 'firstname' => $first, 'lastname' => $last ] ) ] );
	if ( $c && $c['code'] >= 200 && $c['code'] < 300 ) { $contactId = $c['data']['id'] ?? 0; }
	elseif ( $c && 409 === $c['code'] && preg_match( '/Existing ID:\s*(\d+)/', $c['data']['message'] ?? '', $m ) ) { $contactId = $m[1]; }

	// 2) Deal (Paid)
	$d = ukv_hs_post( '/crm/v3/objects/deals', [ 'properties' => [
		'dealname'    => "Visa application - {$destName} ({$tierName})",
		'amount'      => (string) $total,
		'pipeline'    => UKV_HS_PIPELINE,
		'dealstage'   => UKV_HS_STAGE_PAID,
		'description' => "Order {$ref} | Destination: {$destName} | Tier: {$tierName} | Passport: {$pass} | Service: GBP{$tierP} | Govt fee: GBP{$govt} | Total: GBP{$total} | Email: {$email}",
	] ] );
	$dealId = ( $d && $d['code'] >= 200 && $d['code'] < 300 ) ? ( $d['data']['id'] ?? 0 ) : 0;

	// 3) Associate
	if ( $dealId && $contactId ) {
		wp_remote_request( "https://api.hubapi.com/crm/v4/objects/deal/{$dealId}/associations/default/contact/{$contactId}", [
			'method' => 'PUT', 'headers' => [ 'Authorization' => 'Bearer ' . ukv_hs_token(), 'Content-Type' => 'application/json' ], 'timeout' => 15,
		] );
	}
}, 20, 5 );
