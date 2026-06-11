<?php
// P8 quick-note + HubSpot sync. Run: wp eval-file automation/test-quicknote.php
function q( $c, $m ) { echo ( $c ? 'PASS' : 'FAIL' ) . " — $m\n"; if ( ! $c ) { $GLOBALS['qf'] = true; } }
$GLOBALS['qf'] = false;

$oid = ukv_create_order( [ 'order_ref' => 'QN-1', 'name' => 'Test', 'email' => 't@e.com', 'destination' => 'Egypt', 'tier' => 'Standard', 'total' => 49 ] );
$before = count( (array) ( get_post_meta( $oid, 'ukv_journey', true ) ?: [] ) );
$n = ukv_quicknote_add( $oid, 'Called client, confirmed travel date.', 'call' );
$after = (array) get_post_meta( $oid, 'ukv_journey', true );
q( $n === $before + 1, 'quick note appended (count incremented)' );
$last = end( $after );
q( false !== strpos( $last['text'], 'confirmed travel date' ), 'note text stored' );
q( 'call' === $last['channel'], 'channel stored' );
q( false === ukv_quicknote_add( $oid, '   ', 'call' ) || count( (array) get_post_meta( $oid, 'ukv_journey', true ) ) === $before + 1, 'blank note is a no-op' );
// HubSpot push must be non-fatal without a token. Temporarily remove the (live) token so we
// test the no-op path WITHOUT creating a real note in the live CRM, then restore it.
$saved_token = get_option( 'ukv_hubspot_token', '' );
update_option( 'ukv_hubspot_token', '' );
$pushed = ukv_hs_push_note( $oid, 'sync test' );
update_option( 'ukv_hubspot_token', $saved_token );
q( false === $pushed, 'ukv_hs_push_note returns false (no-op) when no token — non-fatal' );

wp_delete_post( $oid, true );
echo $GLOBALS['qf'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
