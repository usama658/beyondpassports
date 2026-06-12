<?php
/**
 * Plugin Name: UKV Supply Chain Registry (#92)
 * Desc: Single registry of external supply-chain nodes (visa centres, couriers, PayPoint, embassies)
 *       per destination. Nodes are stored ONCE in the option `ukv_supply_nodes`; a node with an empty
 *       `destinations` list is global (applies to every destination). Surfaced LIVE by query for an
 *       order's destination. Merge-safe writes, deterministic ids, escaped output.
 * Public surface:
 *   ukv_supply_add(), ukv_supply_all(), ukv_supply_by_type(),
 *   ukv_supply_for_destination(), ukv_supply_for_order(), ukv_supply_seed().
 */
defined( 'ABSPATH' ) || exit;

const UKV_SUPPLY_OPTION = 'ukv_supply_nodes';
const UKV_SUPPLY_SEED_FLAG = 'ukv_supply_seeded';
const UKV_SUPPLY_TYPES = [
	'centre'   => 'Visa centre',
	'courier'  => 'Courier',
	'paypoint' => 'PayPoint',
	'embassy'  => 'Embassy',
];

/** Normalise any destination value (slug or "Egypt") to a slug for matching. */
if ( ! function_exists( 'ukv_dest_slug' ) ) {
	function ukv_dest_slug( $v ) { return sanitize_title( (string) $v ); }
}

/* ----------------------------------------------------------------- internals */

/** A deterministic id from type + name (no rand / no Date). Stable across runs. */
function ukv_supply_make_id( string $type, string $name ): string {
	$type = sanitize_key( $type );
	$slug = sanitize_title( $name );
	return $type . '-' . ( '' !== $slug ? $slug : substr( md5( $name ), 0, 8 ) );
}

/** Coerce a stored value into a clean node array (defaults + sanitised), or null if unusable. */
function ukv_supply_normalise( $node ): ?array {
	if ( ! is_array( $node ) ) { return null; }
	$type = isset( $node['type'] ) && isset( UKV_SUPPLY_TYPES[ $node['type'] ] ) ? $node['type'] : '';
	$name = isset( $node['name'] ) ? trim( (string) $node['name'] ) : '';
	if ( '' === $type || '' === $name ) { return null; }

	$dests = [];
	if ( isset( $node['destinations'] ) && is_array( $node['destinations'] ) ) {
		foreach ( $node['destinations'] as $d ) {
			$slug = ukv_dest_slug( $d );
			if ( '' !== $slug ) { $dests[ $slug ] = $slug; }
		}
	}

	$id = isset( $node['id'] ) ? sanitize_key( (string) $node['id'] ) : '';
	if ( '' === $id ) { $id = ukv_supply_make_id( $type, $name ); }

	return [
		'id'           => $id,
		'type'         => $type,
		'name'         => $name,
		'destinations' => array_values( $dests ),
		'contact'      => isset( $node['contact'] ) ? trim( (string) $node['contact'] ) : '',
		'sla'          => isset( $node['sla'] ) ? trim( (string) $node['sla'] ) : '',
		'notes'        => isset( $node['notes'] ) ? trim( (string) $node['notes'] ) : '',
	];
}

/* ----------------------------------------------------------------- read API */

/** All registered nodes (normalised list). */
function ukv_supply_all(): array {
	$raw = get_option( UKV_SUPPLY_OPTION, [] );
	if ( ! is_array( $raw ) ) { return []; }
	$out = [];
	foreach ( $raw as $node ) {
		$n = ukv_supply_normalise( $node );
		if ( null !== $n ) { $out[] = $n; }
	}
	return $out;
}

/** Nodes of a single type. */
function ukv_supply_by_type( string $type ): array {
	$type = sanitize_key( $type );
	return array_values( array_filter( ukv_supply_all(), static function ( $n ) use ( $type ) {
		return $n['type'] === $type;
	} ) );
}

/**
 * LIVE surface for a destination slug: nodes whose `destinations` contains the slug,
 * OR whose `destinations` is empty (global / applies to all). No copies — computed at call time.
 */
function ukv_supply_for_destination( string $dest_slug ): array {
	$dest_slug = ukv_dest_slug( $dest_slug );
	$out = [];
	foreach ( ukv_supply_all() as $n ) {
		if ( empty( $n['destinations'] ) || in_array( $dest_slug, $n['destinations'], true ) ) {
			$out[] = $n;
		}
	}
	return $out;
}

/** Convenience: nodes for the order's destination slug. */
function ukv_supply_for_order( int $order_id ): array {
	$slug = ukv_dest_slug( get_post_meta( $order_id, 'ukv_destination', true ) );
	return ukv_supply_for_destination( $slug );
}

/* ----------------------------------------------------------------- write API */

/**
 * Add or update a node. Merge-safe: reads the current option, replaces only the matching id,
 * and writes the whole list back (never destroys other nodes). Returns the node id.
 */
function ukv_supply_add( array $node ): string {
	$n = ukv_supply_normalise( $node );
	if ( null === $n ) { return ''; }

	$raw = get_option( UKV_SUPPLY_OPTION, [] );
	if ( ! is_array( $raw ) ) { $raw = []; }

	$replaced = false;
	foreach ( $raw as $i => $existing ) {
		$ex = ukv_supply_normalise( $existing );
		if ( null !== $ex && $ex['id'] === $n['id'] ) {
			$raw[ $i ]  = $n;
			$replaced = true;
			break;
		}
	}
	if ( ! $replaced ) { $raw[] = $n; }

	update_option( UKV_SUPPLY_OPTION, array_values( $raw ), false );
	return $n['id'];
}

/**
 * Seed sensible defaults ONCE (guarded by the seeded flag). Returns the number of nodes seeded
 * (0 if already seeded). Uses ukv_supply_add so re-running by hand stays merge-safe / idempotent.
 */
function ukv_supply_seed(): int {
	if ( get_option( UKV_SUPPLY_SEED_FLAG ) ) { return 0; }

	$defaults = [
		[
			'type'         => 'courier',
			'name'         => 'Royal Mail Special Delivery',
			'destinations' => [], // global
			'contact'      => 'https://www.royalmail.com/',
			'sla'          => 'Next-day, tracked & signed',
			'notes'        => 'Default secure passport return.',
		],
		[
			'type'         => 'paypoint',
			'name'         => 'PayPoint',
			'destinations' => [], // global
			'contact'      => 'https://www.paypoint.com/',
			'sla'          => 'In-person, same day',
			'notes'        => 'UK IDP issuer — guided self-service, in person only.',
		],
		[
			'type'         => 'centre',
			'name'         => 'VFS Global',
			'destinations' => [], // global until configured per destination
			'contact'      => 'https://www.vfsglobal.com/',
			'sla'          => 'Per destination appointment',
			'notes'        => 'Visa application centre operator.',
		],
		[
			'type'         => 'centre',
			'name'         => 'TLScontact',
			'destinations' => [], // global until configured per destination
			'contact'      => 'https://www.tlscontact.com/',
			'sla'          => 'Per destination appointment',
			'notes'        => 'Visa application centre operator.',
		],
	];

	$count = 0;
	foreach ( $defaults as $node ) {
		if ( '' !== ukv_supply_add( $node ) ) { $count++; }
	}

	update_option( UKV_SUPPLY_SEED_FLAG, 1, false );
	return $count;
}

/** Seed on load (one-time, flag-guarded — cheap when already seeded). */
add_action( 'init', 'ukv_supply_seed', 20 );

/* ----------------------------------------------------------------- admin page */

add_action( 'admin_menu', static function () {
	add_submenu_page(
		'tools.php',
		'UKV Supply Chain',
		'UKV Supply Chain',
		'manage_options',
		'ukv-supply-chain',
		'ukv_supply_admin_page'
	);
} );

/** Render + handle the registry admin page (nonce + cap gated, escaped output). */
function ukv_supply_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'ukv' ) );
	}

	$saved = false;
	if ( isset( $_POST['ukv_supply_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_supply_nonce'] ) ), 'ukv_supply_save' ) ) {

		$dests = isset( $_POST['ukv_supply_destinations'] )
			? (string) wp_unslash( $_POST['ukv_supply_destinations'] )
			: '';
		$dest_list = array_filter( array_map( 'trim', preg_split( '/[\n,]+/', $dests ) ?: [] ) );

		ukv_supply_add( [
			'id'           => sanitize_key( (string) ( $_POST['ukv_supply_id'] ?? '' ) ),
			'type'         => sanitize_key( (string) ( $_POST['ukv_supply_type'] ?? '' ) ),
			'name'         => sanitize_text_field( wp_unslash( $_POST['ukv_supply_name'] ?? '' ) ),
			'destinations' => $dest_list,
			'contact'      => sanitize_text_field( wp_unslash( $_POST['ukv_supply_contact'] ?? '' ) ),
			'sla'          => sanitize_text_field( wp_unslash( $_POST['ukv_supply_sla'] ?? '' ) ),
			'notes'        => sanitize_textarea_field( wp_unslash( $_POST['ukv_supply_notes'] ?? '' ) ),
		] );
		$saved = true;
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'UKV Supply Chain Registry', 'ukv' ) . '</h1>';
	if ( $saved ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Node saved.', 'ukv' ) . '</p></div>';
	}
	echo '<p>' . esc_html__( 'External nodes per destination. A node with no destinations is global (applies to every destination).', 'ukv' ) . '</p>';

	// Listing grouped by type.
	foreach ( UKV_SUPPLY_TYPES as $type => $label ) {
		$nodes = ukv_supply_by_type( $type );
		echo '<h2>' . esc_html( $label ) . '</h2>';
		if ( empty( $nodes ) ) {
			echo '<p><em>' . esc_html__( 'None registered.', 'ukv' ) . '</em></p>';
			continue;
		}
		echo '<table class="widefat striped"><thead><tr>'
			. '<th>' . esc_html__( 'Name', 'ukv' ) . '</th>'
			. '<th>' . esc_html__( 'Destinations', 'ukv' ) . '</th>'
			. '<th>' . esc_html__( 'Contact', 'ukv' ) . '</th>'
			. '<th>' . esc_html__( 'SLA', 'ukv' ) . '</th>'
			. '<th>' . esc_html__( 'Notes', 'ukv' ) . '</th>'
			. '<th>' . esc_html__( 'Edit', 'ukv' ) . '</th>'
			. '</tr></thead><tbody>';
		foreach ( $nodes as $n ) {
			$dest_text = empty( $n['destinations'] )
				? __( 'Global (all)', 'ukv' )
				: implode( ', ', $n['destinations'] );
			$edit = '#ukv-supply-form?id=' . rawurlencode( $n['id'] );
			echo '<tr>'
				. '<td><strong>' . esc_html( $n['name'] ) . '</strong></td>'
				. '<td>' . esc_html( $dest_text ) . '</td>'
				. '<td>' . esc_html( $n['contact'] ) . '</td>'
				. '<td>' . esc_html( $n['sla'] ) . '</td>'
				. '<td>' . esc_html( $n['notes'] ) . '</td>'
				. '<td><code>' . esc_html( $n['id'] ) . '</code></td>'
				. '</tr>';
		}
		echo '</tbody></table>';
	}

	// Add / edit form.
	echo '<h2 id="ukv-supply-form">' . esc_html__( 'Add / update a node', 'ukv' ) . '</h2>';
	echo '<form method="post" action="">';
	wp_nonce_field( 'ukv_supply_save', 'ukv_supply_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';

	echo '<tr><th scope="row"><label for="ukv_supply_type">' . esc_html__( 'Type', 'ukv' ) . '</label></th><td>'
		. '<select name="ukv_supply_type" id="ukv_supply_type">';
	foreach ( UKV_SUPPLY_TYPES as $type => $label ) {
		echo '<option value="' . esc_attr( $type ) . '">' . esc_html( $label ) . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr><th scope="row"><label for="ukv_supply_name">' . esc_html__( 'Name', 'ukv' ) . '</label></th>'
		. '<td><input name="ukv_supply_name" id="ukv_supply_name" type="text" class="regular-text" required></td></tr>';

	echo '<tr><th scope="row"><label for="ukv_supply_destinations">' . esc_html__( 'Destinations', 'ukv' ) . '</label></th>'
		. '<td><textarea name="ukv_supply_destinations" id="ukv_supply_destinations" rows="2" class="large-text" placeholder="' . esc_attr__( 'egypt, turkey — one per line or comma-separated. Leave blank for global.', 'ukv' ) . '"></textarea>'
		. '<p class="description">' . esc_html__( 'Slugs or display names. Blank = global (all destinations).', 'ukv' ) . '</p></td></tr>';

	echo '<tr><th scope="row"><label for="ukv_supply_contact">' . esc_html__( 'Contact', 'ukv' ) . '</label></th>'
		. '<td><input name="ukv_supply_contact" id="ukv_supply_contact" type="text" class="regular-text"></td></tr>';

	echo '<tr><th scope="row"><label for="ukv_supply_sla">' . esc_html__( 'SLA', 'ukv' ) . '</label></th>'
		. '<td><input name="ukv_supply_sla" id="ukv_supply_sla" type="text" class="regular-text"></td></tr>';

	echo '<tr><th scope="row"><label for="ukv_supply_notes">' . esc_html__( 'Notes', 'ukv' ) . '</label></th>'
		. '<td><textarea name="ukv_supply_notes" id="ukv_supply_notes" rows="2" class="large-text"></textarea></td></tr>';

	echo '</tbody></table>';
	echo '<input type="hidden" name="ukv_supply_id" value="">';
	submit_button( __( 'Save node', 'ukv' ) );
	echo '</form></div>';
}

/* ----------------------------------------------------------------- order meta box */

add_action( 'add_meta_boxes', static function () {
	add_meta_box(
		'ukv_order_supply_chain',
		'Supply chain for this destination',
		'ukv_order_supply_metabox',
		'ukv_order',
		'side',
		'default'
	);
} );

/** Read-only meta box: live supply nodes for this order's destination. Escaped. */
function ukv_order_supply_metabox( $post ): void {
	$nodes = ukv_supply_for_order( (int) $post->ID );
	if ( empty( $nodes ) ) {
		echo '<p><em>' . esc_html__( 'No supply-chain nodes for this destination.', 'ukv' ) . '</em></p>';
		return;
	}
	echo '<ul style="margin:0">';
	foreach ( $nodes as $n ) {
		$type_label = UKV_SUPPLY_TYPES[ $n['type'] ] ?? $n['type'];
		echo '<li style="border-bottom:1px solid #eee;padding:4px 0">'
			. '<strong>' . esc_html( $n['name'] ) . '</strong> '
			. '<span style="color:#666">(' . esc_html( $type_label ) . ')</span>';
		if ( '' !== $n['contact'] ) {
			echo '<br><span style="color:#555">' . esc_html( $n['contact'] ) . '</span>';
		}
		if ( '' !== $n['sla'] ) {
			echo '<br><em style="color:#555">' . esc_html( $n['sla'] ) . '</em>';
		}
		echo '</li>';
	}
	echo '</ul>';
}
