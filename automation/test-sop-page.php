<?php
// #93 SOP/runbook reference page. Run: wp eval-file automation/test-sop-page.php
// Relies on WP having loaded the mu-plugins (do NOT require plugin files — that would redeclare).
function s( $c, $m ) { echo ( $c ? 'PASS' : 'FAIL' ) . " — $m\n"; if ( ! $c ) { $GLOBALS['sf'] = true; } }
$GLOBALS['sf'] = false;
wp_set_current_user( 1 ); // admin, so the edit_posts cap guard passes

s( function_exists( 'ukv_sop_page_render' ), 'ukv_sop_page_render() defined' );
s( function_exists( 'ukv_sop_locked_policies' ), 'ukv_sop_locked_policies() defined' );
$pol = function_exists( 'ukv_sop_locked_policies' ) ? ukv_sop_locked_policies() : [];
s( is_array( $pol ) && count( $pol ) >= 5, 'locked policies returns a list' );

ob_start(); ukv_sop_page_render(); $h = ob_get_clean();
s( strlen( $h ) > 200, 'page renders non-empty HTML' );
s( false !== stripos( $h, 'polic' ), 'page contains the policies block' );
s( false !== stripos( $h, 'troubleshoot' ), 'page contains a troubleshooting section' );
s( false !== stripos( $h, 'refund' ) || false !== stripos( $h, 'appointment' ), 'page surfaces a locked policy' );

echo $GLOBALS['sf'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
