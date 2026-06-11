<?php
// T12: add a Stripe payment field to the Apply form (299), bound to the total calculation. Run via wp eval-file.
// No secrets here — keys live in option 'forminator_stripe_configuration'.
$form_id = 299;
$meta = get_post_meta( $form_id, 'forminator_form_meta', true );
if ( ! is_array( $meta ) || empty( $meta['fields'] ) ) { echo "form meta not found\n"; return; }

// already has stripe?
foreach ( $meta['fields'] as $f ) {
	if ( isset( $f['type'] ) && 'stripe' === $f['type'] ) { echo "stripe field already present\n"; return; }
}

$meta['fields'][] = [
	'element_id'         => 'stripe-1',
	'type'               => 'stripe',
	'wrapper_id'         => 'w-stripe',
	'cols'               => '12',
	'field_label'        => 'Payment',
	'amount_type'        => 'variable',
	'variable'           => 'calculation-1',   // bind charge to the total
	'amount'             => '',
	'currency'           => 'GBP',
	'mode'               => 'test',
	'product_name'       => 'Visa service',
	'company_name'       => 'UKVisaCo',
	'stripe_default_label' => 'Pay securely',
	'payment_method'     => 'card',
	'receipt'            => 'true',
];

update_post_meta( $form_id, 'forminator_form_meta', $meta );
echo "stripe field added to form $form_id\n";
