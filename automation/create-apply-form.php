<?php
// T12: create the multi-step "Apply" Forminator form (our 8 destinations). Stripe field added later in UI.
// Run via wp eval-file.
if ( ! class_exists( 'Forminator_API' ) ) {
	$f = WP_PLUGIN_DIR . '/forminator/library/class-api.php';
	if ( file_exists( $f ) ) { require_once $f; }
}
if ( ! class_exists( 'Forminator_API' ) ) { echo "Forminator not active\n"; return; }

// skip if exists
foreach ( get_posts( [ 'post_type' => 'forminator_forms', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ] ) as $mid ) {
	if ( 'Apply' === get_post( $mid )->post_title ) { echo "Apply form exists (ID $mid). [forminator_form id=\"$mid\"]\n"; return; }
}
$uk = function () { return function_exists( 'forminator_unique_key' ) ? forminator_unique_key() : substr( md5( uniqid( '', true ) ), 0, 10 ); };

$dests = [ 'turkey' => 'Turkey', 'egypt' => 'Egypt', 'india' => 'India', 'morocco' => 'Morocco', 'uae' => 'United Arab Emirates', 'australia' => 'Australia', 'usa' => 'United States', 'schengen' => 'Schengen Area' ];
$dest_options = [];
foreach ( $dests as $slug => $label ) { $dest_options[] = [ 'label' => $label, 'value' => $slug, 'limit' => '', 'key' => $uk() ]; }
$tier_options = [
	[ 'label' => 'Standard (£29)', 'value' => '29', 'calculation' => '29', 'key' => $uk() ],
	[ 'label' => 'Express (£49)', 'value' => '49', 'calculation' => '49', 'key' => $uk() ],
	[ 'label' => 'Premium (£79)', 'value' => '79', 'calculation' => '79', 'key' => $uk() ],
];

$wrappers = [
	[ 'wrapper_id' => 'w1', 'fields' => [ [ 'element_id' => 'select-1', 'type' => 'select', 'cols' => '12', 'required' => true, 'field_label' => 'Destination', 'placeholder' => 'Select a country', 'value_type' => 'single', 'options' => $dest_options ] ] ],
	[ 'wrapper_id' => 'w2', 'fields' => [ [ 'element_id' => 'radio-1', 'type' => 'radio', 'cols' => '12', 'required' => true, 'field_label' => 'Service tier', 'value_type' => 'radio', 'options' => $tier_options, 'calculations' => true ] ] ],
	[ 'wrapper_id' => 'w3', 'fields' => [ [ 'element_id' => 'page-break-1', 'type' => 'page-break', 'cols' => '12', 'btn_left' => 'Back', 'btn_right' => 'Next' ] ] ],
	[ 'wrapper_id' => 'w4', 'fields' => [ [ 'element_id' => 'name-1', 'type' => 'name', 'cols' => '12', 'required' => true, 'field_label' => 'Full name', 'fname' => 'true', 'lname' => 'true', 'multiple_name' => 'true' ] ] ],
	[ 'wrapper_id' => 'w5', 'fields' => [ [ 'element_id' => 'email-1', 'type' => 'email', 'cols' => '12', 'required' => true, 'field_label' => 'Email' ] ] ],
	[ 'wrapper_id' => 'w6', 'fields' => [ [ 'element_id' => 'text-1', 'type' => 'text', 'cols' => '12', 'required' => true, 'field_label' => 'Passport number', 'input_type' => 'line' ] ] ],
	[ 'wrapper_id' => 'w7', 'fields' => [ [ 'element_id' => 'page-break-2', 'type' => 'page-break', 'cols' => '12', 'btn_left' => 'Back', 'btn_right' => 'Next' ] ] ],
	[ 'wrapper_id' => 'w8', 'fields' => [ [ 'element_id' => 'upload-1', 'type' => 'upload', 'cols' => '12', 'required' => true, 'field_label' => 'Supporting documents', 'file-type' => 'single', 'upload-limit' => 8, 'filesize' => 'MB' ] ] ],
	[ 'wrapper_id' => 'w9', 'fields' => [ [ 'element_id' => 'page-break-3', 'type' => 'page-break', 'cols' => '12', 'btn_left' => 'Back', 'btn_right' => 'Next' ] ] ],
	[ 'wrapper_id' => 'w10', 'fields' => [ [ 'element_id' => 'hidden-1', 'type' => 'hidden', 'cols' => '12', 'field_label' => 'Government fee', 'default_value' => 'custom_value', 'custom_value' => '[ukv_dest_fee]', 'calculations' => true ] ] ],
	[ 'wrapper_id' => 'w11', 'fields' => [ [ 'element_id' => 'calculation-1', 'type' => 'calculation', 'cols' => '12', 'field_label' => 'Total payable', 'formula' => '{radio-1} + {hidden-1}', 'precision' => 2, 'prefix' => '£' ] ] ],
	[ 'wrapper_id' => 'w12', 'fields' => [ [ 'element_id' => 'html-1', 'type' => 'html', 'cols' => '12', 'field_label' => 'Review', 'variations' => '<h3>Review</h3><ul><li>Destination: {select-1}</li><li>Tier: {radio-1}</li><li>Name: {name-1}</li><li>Email: {email-1}</li><li>Total: {calculation-1}</li></ul><p><em>Independent service — not a government website. The government fee is shown at cost.</em></p>' ] ] ],
	// NOTE: Stripe payment field added later in UI (Step 4), amount = {calculation-1}, test keys.
];

$settings = [
	'formName' => 'Apply', 'form-type' => 'default', 'submission-behaviour' => 'behaviour-thankyou',
	'thankyou-message' => 'Thank you — your application has been received. We will review your documents and be in touch.',
	'enable-ajax' => 'true', 'pagination-header' => 'nav',
];

$id = Forminator_API::add_form( 'Apply', $wrappers, $settings, 'publish' );
if ( is_wp_error( $id ) ) { echo 'ERROR: ' . $id->get_error_message() . "\n"; return; }
echo "Created Apply form ID $id\nshortcode: [forminator_form id=\"$id\"]\n";
