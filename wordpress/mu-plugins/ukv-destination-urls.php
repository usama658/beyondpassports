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

// Decode the per-destination FAQ field
function ukv_dest_faq( $id ) {
	$raw = get_post_meta( $id, 'faq', true );
	$f = $raw ? json_decode( $raw, true ) : null;
	return is_array( $f ) ? $f : [];
}

// Render money-page Pods template (+ FAQ) on destination singles
add_filter( 'the_content', function ( $c ) {
	if ( is_singular( 'destination' ) && is_main_query() && in_the_loop() && function_exists( 'pods' ) ) {
		$out = pods( 'destination', get_the_ID() )->template( 'destination-single' );
		$faq = ukv_dest_faq( get_the_ID() );
		if ( $faq ) {
			$out .= '<section class="ukv-faq" style="max-width:820px;margin:30px auto;padding:0 20px;font-family:Inter,sans-serif"><h2 style="color:#0A2540">Frequently asked questions</h2>';
			foreach ( $faq as $f ) {
				$out .= '<details style="margin:8px 0;border:1px solid #e3e8f0;border-radius:8px;padding:12px 16px"><summary style="font-weight:600;cursor:pointer;color:#0A2540">' . esc_html( $f['q'] ) . '</summary><p style="margin:10px 0 0;color:#1B1B1B">' . esc_html( $f['a'] ) . '</p></details>';
			}
			$out .= '</section>';
		}
		if ( $out ) { return $out; }
	}
	return $c;
} );

// FAQPage schema on destination singles
add_action( 'wp_head', function () {
	if ( ! is_singular( 'destination' ) ) { return; }
	$faq = ukv_dest_faq( get_the_ID() );
	if ( ! $faq ) { return; }
	$ld = [ '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map( function ( $f ) {
		return [ '@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $f['a'] ] ];
	}, $faq ) ];
	echo '<script type="application/ld+json">' . wp_json_encode( $ld ) . '</script>';
} );

// Site-wide top bar: prominent CALL CTA (main lead channel) + "not a government website" disclaimer
add_action( 'wp_body_open', function () {
	$phone = trim( (string) get_option( 'ukv_phone_number', '' ) );
	$tel = preg_replace( '/[^0-9+]/', '', $phone );
	if ( $phone ) {
		echo '<div class="ukv-call-bar" style="background:#1456B8;color:#fff;font:600 15px/1.4 Inter,Arial,sans-serif;text-align:center;padding:9px 12px">'
			. 'Speak to a UK visa expert: <a href="tel:' . esc_attr( $tel ) . '" style="color:#fff;text-decoration:underline">' . esc_html( $phone ) . '</a> &middot; Mon&ndash;Fri 9am&ndash;6pm</div>';
	}
	echo '<div class="ukv-gov-bar" style="background:#0A2540;color:#cdd8e8;font:12px/1.5 Inter,Arial,sans-serif;text-align:center;padding:7px 12px">Independent visa &amp; permit service &mdash; we are <strong>not a government website</strong> and charge a service fee in addition to any official fees.</div>';
} );

// Homepage Organization + WebSite schema (reliable; independent of RankMath wizard)
add_action( 'wp_head', function () {
	if ( ! is_front_page() ) { return; }
	$org = [ '@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'UKVisaCo', 'url' => home_url( '/' ), 'description' => 'Independent UK visa, eVisa and ETA facilitation service for British travellers.' ];
	$web = [ '@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'UKVisaCo', 'url' => home_url( '/' ) ];
	echo '<script type="application/ld+json">' . wp_json_encode( [ $org, $web ] ) . '</script>';
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
