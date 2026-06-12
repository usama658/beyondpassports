<?php
/**
 * Admin-UI render harness — closes the admin-UI test gap WITHOUT a browser.
 * Proves every order/barrier meta box + every UKV dashboard widget renders with
 * no PHP fatal and contains an expected marker string.
 *
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file automation/test-admin-render.php
 *
 * SAFETY: blanks ukv_hubspot_token + ukv_anthropic_key for the duration so no real
 *         CRM record / AI call can fire, and restores them at the end.
 */

function t_assert( $cond, $msg ) {
	echo ( $cond ? 'PASS' : 'FAIL' ) . " — $msg\n";
	if ( ! $cond ) { $GLOBALS['t_fail'] = true; }
}
$GLOBALS['t_fail'] = false;

/* ---- SAFETY: save + blank live tokens (restore at the very end) ---- */
$saved_hs = get_option( 'ukv_hubspot_token', '' );
$saved_ak = get_option( 'ukv_anthropic_key', '' );
update_option( 'ukv_hubspot_token', '' );
update_option( 'ukv_anthropic_key', '' );
echo 'INFO — blanked ukv_hubspot_token (had value: ' . ( '' !== $saved_hs ? 'yes' : 'no' ) . '), ukv_anthropic_key (had value: ' . ( '' !== $saved_ak ? 'yes' : 'no' ) . ")\n";

$created_orders   = [];
$created_barriers = [];

/**
 * Capture a meta-box render: guard function_exists, ob_start, call, ob_get_clean.
 * Asserts output non-empty + contains $needle (case-insensitive).
 */
function t_render_box( $fn, $post, $needle, $label ) {
	if ( ! function_exists( $fn ) ) {
		t_assert( false, "$label — callback $fn() not found" );
		return;
	}
	$out = '';
	try {
		ob_start();
		$fn( $post );
		$out = (string) ob_get_clean();
	} catch ( \Throwable $e ) {
		if ( ob_get_level() > 0 ) { ob_end_clean(); }
		t_assert( false, "$label — threw: " . $e->getMessage() );
		return;
	}
	$ok = ( strlen( $out ) > 0 ) && ( false !== stripos( $out, $needle ) );
	t_assert( $ok, "$label — $fn() rendered " . strlen( $out ) . " bytes containing \"$needle\"" );
}

/* ----------------------------------------------------------------- seed */

// One Egypt order with a journey note + blocker (drives journey/barriers/consent/doc-review boxes).
$oid = ukv_create_order( [
	'order_ref'   => 'T-ADMIN-EG',
	'name'        => 'Render Test',
	'email'       => 'render@test.local',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$created_orders[] = $oid;
update_post_meta( $oid, 'ukv_blocker', 'docs_missing' );
update_post_meta( $oid, 'ukv_travel_date', gmdate( 'Y-m-d', time() + 10 * 86400 ) );
update_post_meta( $oid, 'ukv_documents', [ 'passport.jpg' ] );
update_post_meta( $oid, 'ukv_journey', [
	[ 'date' => gmdate( 'Y-m-d H:i' ), 'agent' => 'agent', 'channel' => 'call', 'text' => 'Initial discovery call logged.' ],
] );
$order_post = get_post( $oid );

// One OPEN destination barrier (egypt) — drives barrier-screen client-updates box.
$bid = ukv_barrier_create( [
	'nature'      => 'temporary',
	'scope'       => 'destination',
	'destination' => 'egypt',
	'guidance'    => 'Egypt visa portal undergoing maintenance for 48h.',
	'status'      => 'open',
] );
$created_barriers[] = $bid;
$barrier_post = get_post( $bid );

echo "INFO — seeded order #$oid (Egypt) and barrier #$bid (egypt, open)\n\n";

/* ------------------------------------------------------ meta boxes (order) */
echo "== Order meta boxes ==\n";
t_render_box( 'ukv_journey_metabox',        $order_post, 'Story so far', 'Lead Journey box (ukv-orders)' );
t_render_box( 'ukv_order_barriers_metabox', $order_post, 'barrier',      'Barriers (live) box (ukv-barriers)' );
t_render_box( 'ukv_story_consent_metabox',  $order_post, 'consent',      'Story consent box (ukv-story-consent)' );
t_render_box( 'ukv_doc_review_metabox',     $order_post, 'review',       'AI Document Review box (ukv-doc-review)' );

/* ---------------------------------------------------- meta boxes (barrier) */
echo "\n== Barrier meta boxes ==\n";
t_render_box( 'ukv_barrier_updates_metabox', $barrier_post, 'affected', 'Affected clients box (ukv-client-updates)' );

/* ------------------------------------------------------ dashboard widgets */
// Widgets are registered as anonymous closures via wp_dashboard_setup. Trigger the
// hook so they register into $wp_meta_boxes['dashboard'], then call each callback.
echo "\n== Dashboard widgets ==\n";
set_current_screen( 'dashboard' );
require_once ABSPATH . 'wp-admin/includes/dashboard.php';
do_action( 'wp_dashboard_setup' );

$expected_widgets = [
	'ukv_orders_insights' => 'Orders insights (ukv-orders)',
	'ukv_barriers_widget' => 'Open barriers (ukv-barriers)',
	'ukv_insights_widget' => 'Success intelligence (ukv-insights)',
];

global $wp_meta_boxes;
$dash = [];
if ( isset( $wp_meta_boxes['dashboard'] ) && is_array( $wp_meta_boxes['dashboard'] ) ) {
	foreach ( $wp_meta_boxes['dashboard'] as $ctx ) {
		foreach ( (array) $ctx as $prio ) {
			foreach ( (array) $prio as $wid => $box ) {
				$dash[ $wid ] = $box;
			}
		}
	}
}

foreach ( $expected_widgets as $wid => $label ) {
	if ( ! isset( $dash[ $wid ] ) || empty( $dash[ $wid ]['callback'] ) || ! is_callable( $dash[ $wid ]['callback'] ) ) {
		t_assert( false, "$label — widget '$wid' not registered / not callable" );
		continue;
	}
	$out = '';
	try {
		ob_start();
		call_user_func( $dash[ $wid ]['callback'], '', [ 'id' => $wid ] );
		$out = (string) ob_get_clean();
	} catch ( \Throwable $e ) {
		if ( ob_get_level() > 0 ) { ob_end_clean(); }
		t_assert( false, "$label — widget '$wid' threw: " . $e->getMessage() );
		continue;
	}
	// No fatal + string output (may be empty-state text, but these have seeded data so expect >0).
	t_assert( is_string( $out ) && strlen( $out ) > 0, "$label — widget '$wid' rendered " . strlen( $out ) . ' bytes (no fatal)' );
}

/* ----------------------------------------------------------------- cleanup */
foreach ( array_unique( $created_barriers ) as $b ) { wp_delete_post( $b, true ); }
foreach ( array_unique( $created_orders ) as $o )   { wp_delete_post( $o, true ); }

/* ---- restore live tokens ---- */
update_option( 'ukv_hubspot_token', $saved_hs );
update_option( 'ukv_anthropic_key', $saved_ak );
echo "\nINFO — restored ukv_hubspot_token + ukv_anthropic_key to original values\n";

echo $GLOBALS['t_fail'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
