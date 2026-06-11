<?php
/**
 * Plugin Name: UKV — Destination URLs + money-page render + disclaimer + schema
 */

// Pretty permalink -> /<slug>/
add_filter( 'post_type_link', function ( $link, $post ) {
	if ( $post->post_type === 'destination' ) {
		return home_url( '/' . $post->post_name . '/' );
	}
	return $link;
}, 10, 2 );

// Rewrite rule per real destination slug (pages unaffected)
add_action( 'init', function () {
	$ids = get_posts( [ 'post_type' => 'destination', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ] );
	foreach ( $ids as $id ) {
		$name = get_post_field( 'post_name', $id );
		if ( $name ) {
			add_rewrite_rule( '^' . preg_quote( $name ) . '/?$', 'index.php?destination=' . $name, 'top' );
		}
	}
} );

// Render money-page Pods template on destination singles
add_filter( 'the_content', function ( $c ) {
	if ( is_singular( 'destination' ) && is_main_query() && in_the_loop() && function_exists( 'pods' ) ) {
		$out = pods( 'destination', get_the_ID() )->template( 'destination-single' );
		if ( $out ) { return $out; }
	}
	return $c;
} );

// Site-wide "not a government website" disclaimer strip (T2)
add_action( 'wp_body_open', function () {
	echo '<div class="ukv-gov-bar" style="background:#0A2540;color:#cdd8e8;font:12px/1.5 Inter,Arial,sans-serif;text-align:center;padding:7px 12px">Independent visa &amp; permit service &mdash; we are <strong>not a government website</strong> and charge a service fee in addition to any official fees.</div>';
} );

// Money-page schema (T9): Service + Organization JSON-LD on destination singles
add_action( 'wp_head', function () {
	if ( ! is_singular( 'destination' ) ) { return; }
	$ld = [
		'@context' => 'https://schema.org',
		'@type'    => 'Service',
		'name'     => get_the_title() . ' Visa Service',
		'serviceType' => 'Visa facilitation',
		'provider' => [ '@type' => 'Organization', 'name' => 'UKVisaCo' ],
		'areaServed' => [ '@type' => 'Country', 'name' => 'United Kingdom' ],
	];
	echo '<script type="application/ld+json">' . wp_json_encode( $ld ) . '</script>';
} );

// Brand CSS for money pages
add_action( 'wp_head', function () {
	if ( ! is_singular( 'destination' ) ) { return; }
	echo '<style>'
		. '.ukv-money{max-width:820px;margin:40px auto;padding:0 20px;font-family:Inter,system-ui,sans-serif;color:#1B1B1B}'
		. '.ukv-money h1{color:#0A2540}.ukv-money h2{color:#0A2540;margin-top:28px}'
		. '.ukv-status{padding:12px 16px;border-radius:8px;background:#EEF3FA;font-size:18px}'
		. '.ukv-list{white-space:pre-line;line-height:1.7}'
		. '.ukv-fees{width:100%;border-collapse:collapse;margin:12px 0}'
		. '.ukv-fees th,.ukv-fees td{border:1px solid #d7e0ee;padding:10px;text-align:left}'
		. '.ukv-fees th{background:#0A2540;color:#fff}'
		. '.ukv-cta{display:inline-block;background:#1456B8;color:#fff;padding:12px 24px;border-radius:6px;font-weight:600;text-decoration:none}'
		. '.ukv-idp{background:#fff7e6;border-left:4px solid #C8A24A;padding:12px 16px;margin:20px 0}'
		. '.ukv-disclaim{font-size:13px;color:#5a6577;border-top:1px solid #e3e8f0;padding-top:14px;margin-top:28px}'
		. '</style>';
} );
