<?php
// T1: rebrand active Elementor kit to direction A (navy/blue/gold + Inter). Run via wp eval-file.
$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
$id  = $kit->get_id();
$settings = get_post_meta( $id, '_elementor_page_settings', true );
if ( ! is_array( $settings ) ) { $settings = []; }

$colorMap = [
	'primary'   => '#0A2540', // navy
	'secondary' => '#1456B8', // blue (CTA/links)
	'text'      => '#1B1B1B',
	'accent'    => '#C8A24A', // gold (Premium only)
];
if ( ! empty( $settings['system_colors'] ) ) {
	foreach ( $settings['system_colors'] as &$c ) {
		if ( isset( $colorMap[ $c['_id'] ] ) ) { $c['color'] = $colorMap[ $c['_id'] ]; }
	}
	unset( $c );
}

// fonts -> Inter for all system typography slots
if ( ! empty( $settings['system_typography'] ) ) {
	foreach ( $settings['system_typography'] as &$t ) {
		$t['typography_typography']  = 'custom';
		$t['typography_font_family'] = 'Inter';
	}
	unset( $t );
}
// default body + heading font
$settings['body_typography_typography']  = 'custom';
$settings['body_typography_font_family'] = 'Inter';

update_post_meta( $id, '_elementor_page_settings', $settings );

// regenerate CSS
\Elementor\Plugin::$instance->files_manager->clear_cache();

// verify
$check = get_post_meta( $id, '_elementor_page_settings', true );
foreach ( (array) ( $check['system_colors'] ?? [] ) as $c ) { echo $c['_id'] . '=' . $c['color'] . '  '; }
echo "\nkit id: $id\n";
