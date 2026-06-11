<?php
// Create the "Request a callback" Forminator form + page. Stores form ID in option ukv_callback_form_id. Run via wp eval-file.
if ( ! class_exists( 'Forminator_API' ) ) {
	$f = WP_PLUGIN_DIR . '/forminator/library/class-api.php';
	if ( file_exists( $f ) ) { require_once $f; }
}
if ( ! class_exists( 'Forminator_API' ) ) { echo "Forminator not active\n"; return; }
$uk = function () { return function_exists( 'forminator_unique_key' ) ? forminator_unique_key() : substr( md5( uniqid( '', true ) ), 0, 10 ); };

$existing = get_option( 'ukv_callback_form_id' );
if ( $existing && get_post( $existing ) ) { echo "callback form exists (#$existing)\n"; return; }

$dests = [ 'turkey' => 'Turkey', 'egypt' => 'Egypt', 'india' => 'India', 'usa' => 'USA', 'australia' => 'Australia', 'thailand' => 'Thailand', 'other' => 'Other / not sure' ];
$opts = [];
foreach ( $dests as $v => $l ) { $opts[] = [ 'label' => $l, 'value' => $v, 'limit' => '', 'key' => $uk() ]; }

$wrappers = [
	[ 'wrapper_id' => 'w1', 'fields' => [ [ 'element_id' => 'name-1', 'id' => 'name-1', 'type' => 'name', 'cols' => '12', 'required' => true, 'field_label' => 'Your name' ] ] ],
	[ 'wrapper_id' => 'w2', 'fields' => [ [ 'element_id' => 'phone-1', 'id' => 'phone-1', 'type' => 'phone', 'cols' => '12', 'required' => true, 'field_label' => 'Phone number', 'phone_validation' => false ] ] ],
	[ 'wrapper_id' => 'w3', 'fields' => [ [ 'element_id' => 'select-1', 'id' => 'select-1', 'type' => 'select', 'cols' => '12', 'required' => false, 'field_label' => 'Destination', 'value_type' => 'single', 'options' => $opts ] ] ],
	[ 'wrapper_id' => 'w4', 'fields' => [ [ 'element_id' => 'textarea-1', 'id' => 'textarea-1', 'type' => 'textarea', 'cols' => '12', 'required' => false, 'field_label' => 'How can we help? (optional)' ] ] ],
];
$settings = [ 'formName' => 'Request a callback', 'form-type' => 'default', 'submission-behaviour' => 'behaviour-thankyou', 'thankyou-message' => 'Thanks — we\'ll call you back shortly.', 'enable-ajax' => 'true' ];

$id = Forminator_API::add_form( 'Request a callback', $wrappers, $settings, 'publish' );
if ( is_wp_error( $id ) ) { echo 'ERR: ' . $id->get_error_message() . "\n"; return; }
update_option( 'ukv_callback_form_id', $id );

// page
$e = get_page_by_path( 'request-a-callback', OBJECT, 'page' );
$content = '<h2>Request a free callback</h2><p>Leave your number and our UK visa team will call you back &mdash; no obligation. Independent service, not a government website.</p>[forminator_form id="' . $id . '"]';
$arr = [ 'post_type' => 'page', 'post_name' => 'request-a-callback', 'post_title' => 'Request a Callback', 'post_content' => $content, 'post_status' => 'publish' ];
if ( $e ) { $arr['ID'] = $e->ID; wp_update_post( $arr ); } else { wp_insert_post( $arr ); }
echo "callback form #$id + /request-a-callback/ page\n";
