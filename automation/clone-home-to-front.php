<?php
// Clone the kit Home (#202) design onto the front page (#238) with regenerated element IDs, so the homepage
// opens in Elementor pre-built from the kit, ready to modify. Safe: #238 had no Elementor data.
// Run: wp eval-file automation/clone-home-to-front.php
$src = 202; $dst = 238;
$data = get_post_meta( $src, '_elementor_data', true );
if ( ! $data ) { echo "no source data on #$src\n"; return; }
$arr = json_decode( $data, true );
if ( ! is_array( $arr ) ) { echo "source data not valid JSON\n"; return; }

function regen_ids( &$els ) {
	foreach ( $els as &$e ) {
		$e['id'] = substr( md5( uniqid( '', true ) ), 0, 8 );
		if ( ! empty( $e['elements'] ) ) { regen_ids( $e['elements'] ); }
	}
}
regen_ids( $arr );

update_post_meta( $dst, '_elementor_data', wp_slash( wp_json_encode( $arr ) ) );
update_post_meta( $dst, '_elementor_edit_mode', 'builder' );
update_post_meta( $dst, '_elementor_template_type', 'wp-page' );
// copy the page-settings (layout) from source if present
$ps = get_post_meta( $src, '_elementor_page_settings', true );
if ( $ps ) { update_post_meta( $dst, '_elementor_page_settings', $ps ); }
// ensure Elementor (re)builds CSS for this page
delete_post_meta( $dst, '_elementor_css' );

echo "cloned #$src -> #$dst (" . count( $arr ) . " sections, ids regenerated)\n";
echo "Open: http://localhost/ukvisa/wp-admin/post.php?post=$dst&action=elementor\n";
