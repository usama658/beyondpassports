<?php
// Build kit-native Elementor SECTION templates (hybrid: kit global styles + core widgets + [ukv_*] shortcode for
// live data). Idempotent upsert by title. Run: wp eval-file automation/build-kit-sections.php
// Spec: docs/superpowers/specs (kit-native sections) + .claude/skills/ukv-parallel-build/references/kit-patterns.md

function ks_id() { return substr( md5( uniqid( '', true ) ), 0, 8 ); }

// section wrapper with responsive padding + optional bg colour
function ks_section( array $widgets, $bg = '', $pad_top = 60, $pad_bottom = 60 ) {
	$settings = [
		'layout'  => 'boxed',
		'padding' => [ 'unit' => 'px', 'top' => (string) $pad_top, 'right' => '0', 'bottom' => (string) $pad_bottom, 'left' => '0', 'isLinked' => false ],
		'padding_tablet' => [ 'unit' => 'px', 'top' => '40', 'right' => '0', 'bottom' => '40', 'left' => '0', 'isLinked' => false ],
		'padding_mobile' => [ 'unit' => 'px', 'top' => '28', 'right' => '0', 'bottom' => '28', 'left' => '0', 'isLinked' => false ],
	];
	if ( $bg ) { $settings['background_background'] = 'classic'; $settings['background_color'] = $bg; }
	return [ [
		'id' => ks_id(), 'elType' => 'section', 'settings' => $settings,
		'elements' => [ [
			'id' => ks_id(), 'elType' => 'column',
			'settings' => [ '_column_size' => 100, '_inline_size' => null ],
			'elements' => $widgets,
		] ],
	] ];
}

// kit heading referencing global colour + typography
function ks_heading( $text, $color = 'primary', $size = 'h2' ) {
	return [
		'id' => ks_id(), 'elType' => 'widget', 'widgetType' => 'heading',
		'settings' => [
			'title' => $text, 'header_size' => $size, 'align' => 'center',
			'title_color' => '', '__globals__' => [ 'title_color' => 'globals/colors?id=' . $color, 'typography_typography' => 'globals/typography?id=primary' ],
		],
	];
}

function ks_shortcode( $sc ) {
	return [ 'id' => ks_id(), 'elType' => 'widget', 'widgetType' => 'shortcode', 'settings' => [ 'shortcode' => $sc ] ];
}

// idempotent upsert by title
function ks_upsert( $title, array $data ) {
	$existing = get_posts( [ 'post_type' => 'elementor_library', 'post_status' => 'any', 'title' => $title, 'numberposts' => 1, 'fields' => 'ids' ] );
	$arr = [ 'post_type' => 'elementor_library', 'post_status' => 'publish', 'post_title' => $title ];
	if ( $existing ) { $arr['ID'] = $existing[0]; $id = $existing[0]; wp_update_post( $arr ); }
	else { $id = wp_insert_post( $arr ); }
	update_post_meta( $id, '_elementor_template_type', 'section' );
	update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	update_post_meta( $id, '_elementor_edit_mode', 'builder' );
	if ( function_exists( 'wp_set_object_terms' ) ) { wp_set_object_terms( $id, 'section', 'elementor_library_type' ); }
	return $id;
}

$sections = [
	'UKV — Trust bar'         => ks_section( [ ks_shortcode( '[ukv_trust_bar]' ) ], '#F7F9FC', 24, 24 ),
	'UKV — Testimonials'      => ks_section( [ ks_heading( 'What our clients say', 'primary' ), ks_shortcode( '[ukv_testimonials]' ) ], '#FFFFFF' ),
	'UKV — Track application' => ks_section( [ ks_heading( 'Track your application', 'primary' ), ks_shortcode( '[ukv_tracker]' ) ], '#F7F9FC' ),
	'UKV — Destinations'      => ks_section( [ ks_heading( 'Popular destinations', 'primary' ), ks_shortcode( '[ukv_dest_grid]' ) ], '#FFFFFF' ),
	'UKV — Contact CTA'       => ks_section( [ ks_heading( 'Speak to our UK visa team', 'accent' ), ks_shortcode( '[ukv_whatsapp]' ) ], '#0A2540' ),
];

foreach ( $sections as $title => $data ) {
	$id = ks_upsert( $title, $data );
	echo "$title -> #$id\n";
}
echo "done\n";
