<?php
/**
 * Plugin Name: UKV Checker Eligibility Inputs — Phase 1 Unit 5
 * Desc: Front-door to the visa checker. Asks for nationality + residence and only gives the UK answer
 *       (the existing [ukv_visa_table]) when BOTH are UK; for any other combo it shows a compliance-safe
 *       "we'll confirm your specific requirements" message + callback CTA. Additive — does not touch the table.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Local UK check. Reuses ukv_is_uk() from ukv-eligibility.php when present; otherwise a safe fallback.
 */
function ukv_checker_is_uk( $v ) {
	if ( function_exists( 'ukv_is_uk' ) ) {
		return ukv_is_uk( $v );
	}
	$s = strtolower( trim( (string) $v ) );
	return in_array( $s, [ 'uk', 'gb', 'united kingdom', 'britain' ], true );
}

/**
 * Decide the checker lane from nationality + residence.
 * UK answer ONLY when both are UK; otherwise a non-standard "we'll confirm" lane (no auto-answer — compliance).
 *
 * @return array{lane:'uk'|'non_standard', message:string}
 */
function ukv_checker_eligibility_result( string $nationality, string $residence ): array {
	if ( ukv_checker_is_uk( $nationality ) && ukv_checker_is_uk( $residence ) ) {
		return [
			'lane'    => 'uk',
			'message' => 'As a UK passport holder living in the UK, here is whether you need a visa for your destination.',
		];
	}
	return [
		'lane'    => 'non_standard',
		'message' => "Your requirements depend on your nationality and where you live — we'll confirm your specific requirements and price. Request a callback.",
	];
}

/**
 * [ukv_eligibility_checker] — small self-posting form (nationality + residence + nonce).
 * On submit: UK+UK shows the standard checker table; anything else shows the compliance message + callback CTA.
 */
add_shortcode( 'ukv_eligibility_checker', function () {
	$nat = '';
	$res = '';
	$submitted = false;

	if ( isset( $_POST['ukv_ec_submit'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		&& isset( $_POST['ukv_ec_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_ec_nonce'] ) ), 'ukv_ec' ) ) {
		$nat = isset( $_POST['ukv_ec_nationality'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_ec_nationality'] ) ) : '';
		$res = isset( $_POST['ukv_ec_residence'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_ec_residence'] ) ) : '';
		$submitted = ( '' !== $nat && '' !== $res );
	}

	$action = esc_url( remove_query_arg( array_keys( $_GET ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	ob_start();
	?>
	<div class="ukv-eligibility-checker" style="max-width:520px;font-family:Inter,sans-serif">
		<form method="post" action="<?php echo $action; // already escaped ?>">
			<?php wp_nonce_field( 'ukv_ec', 'ukv_ec_nonce' ); ?>
			<p style="margin:0 0 12px">
				<label style="display:block;font-weight:600;margin-bottom:4px" for="ukv_ec_nationality">Your nationality</label>
				<input type="text" id="ukv_ec_nationality" name="ukv_ec_nationality"
					value="<?php echo esc_attr( $nat ); ?>" placeholder="e.g. United Kingdom"
					style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px" required>
			</p>
			<p style="margin:0 0 12px">
				<label style="display:block;font-weight:600;margin-bottom:4px" for="ukv_ec_residence">Where you live</label>
				<input type="text" id="ukv_ec_residence" name="ukv_ec_residence"
					value="<?php echo esc_attr( $res ); ?>" placeholder="e.g. United Kingdom"
					style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px" required>
			</p>
			<button type="submit" name="ukv_ec_submit" value="1"
				style="background:#1456B8;color:#fff;border:0;padding:12px 22px;border-radius:6px;font-weight:600;cursor:pointer">
				Check my requirements
			</button>
		</form>
		<?php if ( $submitted ) :
			$r = ukv_checker_eligibility_result( $nat, $res );
			if ( 'uk' === $r['lane'] ) : ?>
				<div class="ukv-ec-result ukv-ec-uk" style="margin-top:20px;padding:16px;border:1px solid #cfe3cf;background:#f1faf1;border-radius:8px">
					<p style="margin:0 0 12px;color:#0f7b3f;font-weight:600"><?php echo esc_html( $r['message'] ); ?></p>
					<?php echo do_shortcode( '[ukv_visa_table]' ); ?>
				</div>
			<?php else : ?>
				<div class="ukv-ec-result ukv-ec-nonstandard" style="margin-top:20px;padding:16px;border:1px solid #d6e0ef;background:#f3f7fc;border-radius:8px">
					<p style="margin:0 0 12px;color:#0A2540"><?php echo esc_html( $r['message'] ); ?></p>
					<a href="<?php echo esc_url( '/ukvisa/request-a-callback/' ); ?>"
						style="display:inline-block;background:#1456B8;color:#fff;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:600">
						Request a callback
					</a>
				</div>
			<?php endif;
		endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
} );
