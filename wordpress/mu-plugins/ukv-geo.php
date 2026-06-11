<?php
/**
 * Plugin Name: UKV GEO (llms.txt + AI crawlers)
 * Desc: Serves /llms.txt for AI search engines + explicitly allows AI crawlers in robots.txt.
 */
defined( 'ABSPATH' ) || exit;

// Allow AI crawlers in the virtual robots.txt
add_filter( 'robots_txt', function ( $output, $public ) {
	if ( ! $public ) { return $output; }
	$output .= "\n# AI crawlers (GEO)\n";
	foreach ( [ 'GPTBot', 'ChatGPT-User', 'ClaudeBot', 'anthropic-ai', 'PerplexityBot', 'Google-Extended', 'CCBot' ] as $bot ) {
		$output .= "User-agent: {$bot}\nAllow: /\n";
	}
	$output .= "\nLLM: " . home_url( '/llms.txt' ) . "\n";
	return $output;
}, 10, 2 );

// Serve /llms.txt
add_action( 'template_redirect', function () {
	$path = strtok( $_SERVER['REQUEST_URI'] ?? '', '?' );
	if ( ! preg_match( '#/llms\.txt/?$#', (string) $path ) ) { return; }
	header( 'Content-Type: text/plain; charset=utf-8' );
	$base = home_url( '/' );
	echo "# UKVisaCo\n";
	echo "> Independent UK visa, eVisa and ETA facilitation plus International Driving Permit guidance for British travellers. Not a government website.\n\n";
	echo "## Visa destinations (entry requirements, fees, how to apply)\n";
	$ids = get_posts( [ 'post_type' => 'destination', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'fields' => 'ids' ] );
	foreach ( $ids as $id ) {
		echo '- [' . get_the_title( $id ) . '](' . get_permalink( $id ) . ")\n";
	}
	echo "\n## Tools & guides\n";
	echo '- [Do I need a visa? free checker](' . $base . "do-i-need-a-visa/)\n";
	echo '- [International Driving Permit (IDP) guide](' . $base . "international-driving-permit/)\n";
	echo '- [How it works](' . $base . "how-it-works/)\n";
	echo '- [Pricing](' . $base . "pricing/)\n";
	exit;
} );
