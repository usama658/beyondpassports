<?php
// Export full site structure -> CSV (opens in Excel). Pages + guides/posts + destination money pages,
// with RankMath keywords + SEO meta + URL + status. Run: wp eval-file automation/export-site-structure.php
$dir = 'C:/Users/mumya/OneDrive/Desktop/Claude Projects/UK VIsa/exports';
if ( ! is_dir( $dir ) ) { wp_mkdir_p( $dir ); }
$file = $dir . '/site-structure.csv';
$fh = fopen( $file, 'w' );
fputcsv( $fh, [ 'Type', 'Title', 'URL', 'Slug', 'Status', 'Focus Keyword', 'SEO Title', 'Meta Description', 'Silo/Category', 'Words' ] );

function ss_rows( $fh, $post_type, $label ) {
	$ids = get_posts( [ 'post_type' => $post_type, 'post_status' => [ 'publish', 'draft' ], 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'title', 'order' => 'ASC' ] );
	$n = 0;
	foreach ( $ids as $id ) {
		$cats = [];
		if ( 'post' === $post_type ) { foreach ( get_the_category( $id ) as $c ) { $cats[] = $c->name; } }
		$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $id ) ) );
		fputcsv( $fh, [
			$label,
			get_the_title( $id ),
			get_permalink( $id ),
			get_post_field( 'post_name', $id ),
			get_post_status( $id ),
			(string) get_post_meta( $id, 'rank_math_focus_keyword', true ),
			(string) get_post_meta( $id, 'rank_math_title', true ),
			(string) get_post_meta( $id, 'rank_math_description', true ),
			implode( '; ', $cats ),
			$words,
		] );
		$n++;
	}
	return $n;
}

$p = ss_rows( $fh, 'destination', 'Money page' );
$g = ss_rows( $fh, 'post', 'Guide/Post' );
$pg = ss_rows( $fh, 'page', 'Page' );
fclose( $fh );
echo "Wrote $file\n";
echo "Money pages: $p | Guides/Posts: $g | Pages: $pg | Total: " . ( $p + $g + $pg ) . "\n";
