<?php
// Creates the "destination" Pods CPT + fields (single source of truth). Run: wp eval-file this.
if ( ! function_exists( 'pods_api' ) ) { echo "Pods not active\n"; exit( 1 ); }
$api = pods_api();

// 1) the CPT
$pod = $api->load_pod( [ 'name' => 'destination' ] );
if ( empty( $pod ) ) {
	$api->save_pod( [
		'name'    => 'destination',
		'type'    => 'post_type',
		'storage' => 'meta',
		'label'   => 'Destinations',
		'options' => [
			'public'             => 1,
			'has_archive'        => 1,
			'rewrite'            => 1,
			'rewrite_custom_slug'=> 'destination',
			'supports_title'     => 1,
			'supports_editor'    => 1,
			'supports_thumbnail' => 1,
			'menu_icon'          => 'dashicons-airplane',
		],
	] );
	echo "pod created\n";
} else {
	echo "pod exists\n";
}

// 2) fields  (name => [type, label, extra])
$fields = [
	'required_for_uk'          => [ 'boolean',   'Visa required for UK citizens' ],
	'visa_type'                => [ 'text',      'Visa type (eVisa/eTA/visa-on-arrival/visa-free/embassy)' ],
	'evisa_available'          => [ 'boolean',   'eVisa available' ],
	'max_stay_days'            => [ 'number',    'Max stay (days)' ],
	'validity_days'            => [ 'number',    'Validity (days)' ],
	'entry'                    => [ 'text',      'Entry (single/multiple)' ],
	'govt_fee_gbp'             => [ 'currency',  'Government fee (GBP, at cost)' ],
	'processing_standard_days' => [ 'number',    'Processing standard (days)' ],
	'processing_express_hours' => [ 'number',    'Processing express (hours)' ],
	'requirements'             => [ 'paragraph', 'Requirements (one per line)' ],
	'how_to_steps'             => [ 'paragraph', 'How-to steps (one per line)' ],
	'tier_standard_gbp'        => [ 'currency',  'Service fee — Standard (GBP)' ],
	'tier_express_gbp'         => [ 'currency',  'Service fee — Express (GBP)' ],
	'tier_premium_gbp'         => [ 'currency',  'Service fee — Premium (GBP)' ],
	'idp_recommended'          => [ 'boolean',   'IDP recommended' ],
	'idp_permit_type'          => [ 'text',      'IDP permit type (1926/1949/1968)' ],
	'idp_required_photocard'   => [ 'boolean',   'IDP required for UK photocard licence' ],
	'idp_required_paper'       => [ 'boolean',   'IDP required for paper licence' ],
	'notes'                    => [ 'paragraph', 'Notes' ],
];

$count = 0;
foreach ( $fields as $name => $def ) {
	$existing = $api->load_field( [ 'pod' => 'destination', 'name' => $name ] );
	if ( ! empty( $existing ) ) { continue; }
	$api->save_field( [
		'pod'   => 'destination',
		'name'  => $name,
		'type'  => $def[0],
		'label' => $def[1],
	] );
	$count++;
}
echo "fields added: $count\n";

// report
$pod = $api->load_pod( [ 'name' => 'destination' ] );
$fieldNames = is_array( $pod['fields'] ?? null ) ? array_keys( $pod['fields'] ) : [];
echo 'total fields: ' . count( $fieldNames ) . "\n";
echo implode( ',', $fieldNames ) . "\n";
