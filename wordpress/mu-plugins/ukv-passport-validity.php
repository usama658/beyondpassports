<?php
/**
 * Plugin Name: UKV Passport Validity (Gap #74)
 * Desc: Supplies the data the (previously inert) Barriers Rule 2 needs:
 *         (a) order meta `ukv_passport_expiry` (date Y-m-d) — meta box + nonce/cap-gated save,
 *             plus optional capture from the Stripe-charge prepared_data if a passport-expiry
 *             field is present (the Apply-form date field is added at launch);
 *         (b) one-time seeding of Pods `passport_validity_months` = 6 on destinations with no value.
 *       Rule 2 in ukv-barriers.php now reads `ukv_passport_expiry` and fires on real data.
 */
defined( 'ABSPATH' ) || exit;

/** Read an order's passport expiry (Y-m-d) or '' when unset. */
function ukv_passport_expiry( int $order_id ): string {
	return (string) get_post_meta( $order_id, 'ukv_passport_expiry', true );
}

/**
 * Ensure `passport_validity_months` is a registered Pods NUMBER field on the destination pod.
 * Without registration Pods returns the raw meta wrapped in an array (so `(int) ukv_dest_value()`
 * collapses to 1); registering it makes `field()` return the scalar like its sibling number fields.
 * Idempotent — save_field upserts.
 */
function ukv_passport_validity_register_field(): void {
	if ( ! function_exists( 'pods_api' ) ) { return; }
	$api = pods_api();
	$existing = $api->load_field( [ 'pod' => 'destination', 'name' => 'passport_validity_months' ] );
	if ( ! empty( $existing ) ) { return; }
	$api->save_field( [
		'pod'     => 'destination',
		'name'    => 'passport_validity_months',
		'label'   => 'Passport validity months',
		'type'    => 'number',
		'options' => [ 'number_decimals' => 0 ],
	] );
}

/**
 * One-time seed: register the Pods field (if needed) then set `passport_validity_months` = 6 on
 * every `destination` that has no value. 6 months is the common requirement. Returns the number of
 * destinations updated. Idempotent.
 */
function ukv_seed_passport_validity(): int {
	if ( ! function_exists( 'pods' ) ) { return 0; }
	ukv_passport_validity_register_field();
	$ids = get_posts( [
		'post_type'   => 'destination',
		'post_status' => 'publish',
		'numberposts' => -1,
		'fields'      => 'ids',
	] );
	$updated = 0;
	foreach ( $ids as $id ) {
		$pod = pods( 'destination', $id );
		$cur = $pod->field( 'passport_validity_months' );
		if ( is_array( $cur ) ) { $cur = reset( $cur ); }
		if ( '' === (string) $cur || null === $cur || 0 === (int) $cur ) {
			$pod->save( 'passport_validity_months', 6 );
			$updated++;
		}
	}
	return $updated;
}

/** Guarded one-time seeding on admin init (runs once, then a flag short-circuits it). */
add_action( 'admin_init', function () {
	if ( get_option( 'ukv_passport_validity_seeded' ) ) { return; }
	if ( ukv_seed_passport_validity() >= 0 ) {
		update_option( 'ukv_passport_validity_seeded', 1, false );
	}
} );

/** Order meta box: passport expiry date. */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_order_passport_expiry', 'Passport expiry', 'ukv_passport_expiry_metabox', 'ukv_order', 'side', 'default' );
} );
function ukv_passport_expiry_metabox( $post ) {
	wp_nonce_field( 'ukv_passport_expiry', 'ukv_passport_expiry_nonce' );
	$val = ukv_passport_expiry( $post->ID );
	echo '<label style="font-weight:600;display:block;margin-bottom:4px">Passport expiry date</label>';
	echo '<input type="date" name="ukv_passport_expiry" value="' . esc_attr( $val ) . '" style="width:100%">';
	echo '<p style="margin:6px 0 0;color:#666">Used to flag passports short of the destination\'s validity requirement.</p>';
}
add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_passport_expiry_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_passport_expiry_nonce'], 'ukv_passport_expiry' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }
	if ( ! isset( $_POST['ukv_passport_expiry'] ) ) { return; }
	$raw = sanitize_text_field( wp_unslash( $_POST['ukv_passport_expiry'] ) );
	// Accept only a Y-m-d date (or clear when empty).
	if ( '' === $raw ) {
		delete_post_meta( $pid, 'ukv_passport_expiry' );
	} elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
		update_post_meta( $pid, 'ukv_passport_expiry', $raw );
	}
}, 11 );

/**
 * Optional: capture passport expiry from the Stripe-charge prepared_data when the Apply form
 * carries a passport-expiry date field (added at launch). Best-effort — guarded throughout.
 * Matches the order just created from this charge by the customer email (most recent open order).
 */
add_action( 'forminator_custom_form_after_stripe_charge', function ( $module, $field, $stripe_entry_data, $prepared_data, $field_data_array ) {
	$pd = is_array( $prepared_data ) ? $prepared_data : [];
	// Only our Apply form (destination select + tier radio).
	if ( empty( $pd['select-1'] ) || ! isset( $pd['radio-1'] ) ) { return; }

	// Look for a date field that names "passport" + "expiry"/"expire" in its key, else a known fallback key.
	$expiry = '';
	foreach ( $pd as $k => $v ) {
		if ( is_string( $k ) && preg_match( '/passport.*expir|expir.*passport/i', $k ) ) {
			$expiry = is_array( $v ) ? (string) reset( $v ) : (string) $v;
			break;
		}
	}
	if ( '' === $expiry && ! empty( $pd['date-1'] ) ) {
		$expiry = is_array( $pd['date-1'] ) ? (string) reset( $pd['date-1'] ) : (string) $pd['date-1'];
	}
	$expiry = trim( $expiry );
	if ( '' === $expiry ) { return; }

	// Normalise to Y-m-d.
	$ts = strtotime( $expiry );
	if ( ! $ts ) { return; }
	$ymd = gmdate( 'Y-m-d', $ts );

	$email = (string) ( $pd['email-1'] ?? '' );
	if ( '' === $email ) { return; }

	// Find the most recent order for this email (the one this charge just created).
	$oid = get_posts( [
		'post_type'   => 'ukv_order',
		'post_status' => 'publish',
		'numberposts' => 1,
		'fields'      => 'ids',
		'orderby'     => 'date',
		'order'       => 'DESC',
		'meta_query'  => [ [ 'key' => 'ukv_email', 'value' => $email ] ],
	] );
	if ( $oid ) {
		update_post_meta( (int) $oid[0], 'ukv_passport_expiry', $ymd );
	}
}, 20, 5 );
