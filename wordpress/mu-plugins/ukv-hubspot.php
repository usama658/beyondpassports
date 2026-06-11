<?php
/**
 * Plugin Name: UKV HubSpot CRM (Forminator -> HubSpot)
 * Desc: On Apply-form submission, create a HubSpot Contact + Deal (Paid stage). Token in DB option ukv_hubspot_token.
 */
defined( 'ABSPATH' ) || exit;

const UKV_HS_PIPELINE   = 'default';
const UKV_HS_STAGE_PAID = '3823157986';
const UKV_HS_APPLY_FORM = 299;

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

add_action( 'forminator_form_after_save_entry', function ( $form_id, $response ) {
	if ( (int) $form_id !== UKV_HS_APPLY_FORM || ! ukv_hs_token() ) { return; }
	$entry_id = is_array( $response ) ? (int) ( $response['entry_id'] ?? 0 ) : 0;
	if ( ! $entry_id || ! class_exists( 'Forminator_Form_Entry_Model' ) ) { return; }

	$entry = new Forminator_Form_Entry_Model( $entry_id );
	$get = function ( $k ) use ( $entry ) {
		$v = $entry->meta_data[ $k ]['value'] ?? '';
		return is_array( $v ) ? trim( implode( ' ', array_filter( $v, 'is_scalar' ) ) ) : trim( (string) $v );
	};

	$dest  = $get( 'select-1' );
	$tierP = (float) $get( 'radio-1' );                 // tier price 29/49/79
	$email = $get( 'email-1' );
	$nameV = $entry->meta_data['name-1']['value'] ?? '';
	$first = is_array( $nameV ) ? ( $nameV['first-name'] ?? '' ) : $nameV;
	$last  = is_array( $nameV ) ? ( $nameV['last-name'] ?? '' ) : '';
	$pass  = $get( 'text-1' );
	$govt  = function_exists( 'ukv_dest_value' ) ? (float) ukv_dest_value( $dest, 'govt_fee_gbp' ) : 0;
	$total = $tierP + $govt;
	$tierName = [ 29 => 'Standard', 49 => 'Express', 79 => 'Premium' ][ (int) $tierP ] ?? (string) $tierP;
	$ref   = 'UKV-' . gmdate( 'Y' ) . '-' . str_pad( $entry_id, 5, '0', STR_PAD_LEFT );
	$destName = ucwords( str_replace( '-', ' ', $dest ) );

	// 1) Contact (create; on 409 reuse existing id)
	$contactId = 0;
	$c = ukv_hs_post( '/crm/v3/objects/contacts', [ 'properties' => array_filter( [
		'email' => $email, 'firstname' => $first, 'lastname' => $last,
	] ) ] );
	if ( $c && $c['code'] >= 200 && $c['code'] < 300 ) { $contactId = $c['data']['id'] ?? 0; }
	elseif ( $c && 409 === $c['code'] && preg_match( '/Existing ID:\s*(\d+)/', $c['data']['message'] ?? '', $m ) ) { $contactId = $m[1]; }

	// 2) Deal
	$d = ukv_hs_post( '/crm/v3/objects/deals', [ 'properties' => [
		'dealname'  => "Visa application - {$destName} ({$tierName})",
		'amount'    => (string) $total,
		'pipeline'  => UKV_HS_PIPELINE,
		'dealstage' => UKV_HS_STAGE_PAID,
		'description' => "Order {$ref} | Destination: {$destName} | Tier: {$tierName} | Passport: {$pass} | Service: GBP{$tierP} | Govt fee: GBP{$govt} | Total: GBP{$total} | Email: {$email}",
	] ] );
	$dealId = ( $d && $d['code'] >= 200 && $d['code'] < 300 ) ? ( $d['data']['id'] ?? 0 ) : 0;

	// 3) Associate deal <-> contact (best effort, v4 default association)
	if ( $dealId && $contactId ) {
		wp_remote_request( "https://api.hubapi.com/crm/v4/objects/deal/{$dealId}/associations/default/contact/{$contactId}", [
			'method' => 'PUT',
			'headers' => [ 'Authorization' => 'Bearer ' . ukv_hs_token(), 'Content-Type' => 'application/json' ],
			'timeout' => 15,
		] );
	}
}, 20, 2 );
