<?php
// P13 Smart Stories content engine self-test: PII redaction, competitor redaction, leak gate, draft generation.
// Run: wp eval-file automation/test-stories-content.php
function t_assert( $cond, $msg ) { echo ( $cond ? 'PASS' : 'FAIL' ) . " — $msg\n"; if ( ! $cond ) { $GLOBALS['t_fail'] = true; } }
$GLOBALS['t_fail'] = false;

// 1) PII redaction strips every personal token + known name; leak gate then clean.
$pii_in  = "Contact John Smith on john@x.com or 07700 900123, passport 123456789, travelling 2026-06-20.";
$pii_out = ukv_redact_pii( $pii_in, [ 'name' => 'John Smith' ] );
t_assert( false === stripos( $pii_out, 'john@x.com' ),   '1a email removed' );
t_assert( false === strpos( $pii_out, '900123' ),         '1b phone removed' );
t_assert( false === strpos( $pii_out, '123456789' ),      '1c passport removed' );
t_assert( false === strpos( $pii_out, '2026-06-20' ),     '1d date removed' );
t_assert( false === stripos( $pii_out, 'John Smith' ),    '1e known name removed' );
$leak1 = ukv_story_has_leak( $pii_out, [ 'name' => 'John Smith' ] );
t_assert( [] === $leak1, '1f leak gate finds nothing in redacted PII text (got: ' . implode( '; ', $leak1 ) . ')' );

// 2) Competitor redaction neutralises business-confidential terms.
$comp_out = ukv_redact_competitor( "Our supplier margin is 40%. We process via the Istanbul route in volume." );
t_assert( false === stripos( $comp_out, 'margin' ),   '2a margin removed' );
t_assert( false === stripos( $comp_out, 'supplier' ), '2b supplier removed' );
t_assert( false === stripos( $comp_out, 'route' ),    '2c route removed' );
t_assert( false === stripos( $comp_out, 'volume' ),   '2d volume removed' );

// 3) Generate a draft story from a RESOLVED Egypt barrier.
$bid = ukv_barrier_create( [
	'nature'      => 'temporary',
	'scope'       => 'destination',
	'destination' => 'egypt',
	'guidance'    => 'The e-visa portal was briefly down; applications were resubmitted automatically once it returned.',
	'status'      => 'resolved',
] );
$pid = ukv_generate_story_draft( $bid );
t_assert( $pid > 0, '3a draft post created (id=' . $pid . ')' );
t_assert( $pid > 0 && get_post_status( $pid ) === 'draft', '3b post status is draft (never published)' );
if ( $pid > 0 ) {
	$leak3 = ukv_story_has_leak( get_post( $pid )->post_content );
	t_assert( [] === $leak3, '3c generated content passes leak gate (got: ' . implode( '; ', $leak3 ) . ')' );
}

// 4) Cleanup (force delete).
if ( $pid > 0 ) { wp_delete_post( $pid, true ); }
wp_delete_post( $bid, true );

echo $GLOBALS['t_fail'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
