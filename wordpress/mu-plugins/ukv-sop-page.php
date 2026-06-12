<?php
/**
 * Plugin Name: UKV Runbook / SOP reference page
 * Desc: In-app staff SOP / runbook reference (#93). A single read-only admin page that
 *       surfaces the locked policies, the per-stage SOP (do / watch / next) from
 *       UKV_STAGE_SOP, and the troubleshooting playbook from UKV_TROUBLESHOOTING.
 *       Reuses the consts defined in ukv-sop.php — does not duplicate the content.
 *       Read-only: no inputs, no saves. All output escaped.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * 1. Admin menu — submenu under "Production Line" if that menu exists,
 *    otherwise a top-level page. Capability: edit_posts.
 * ------------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	global $admin_page_hooks;

	$has_prodline = is_array( $admin_page_hooks ) && isset( $admin_page_hooks['ukv-production-board'] );

	if ( $has_prodline ) {
		add_submenu_page(
			'ukv-production-board',
			'Runbook / SOP',
			'Runbook / SOP',
			'edit_posts',
			'ukv-sop-runbook',
			'ukv_sop_page_render'
		);
	} else {
		add_menu_page(
			'Runbook / SOP',
			'Runbook / SOP',
			'edit_posts',
			'ukv-sop-runbook',
			'ukv_sop_page_render',
			'dashicons-book-alt',
			5
		);
	}
} );

/* ---------------------------------------------------------------------------
 * 2. The locked policies — hard-coded reference bullets (the standing rules
 *    every operator must follow regardless of stage).
 * ------------------------------------------------------------------------- */
function ukv_sop_locked_policies(): array {
	return [
		'Refunds: we refund our service fee; the government fee is non-refundable once paid to the authority.',
		'Appointments: we book the appointments on the customer\'s behalf.',
		'Passport return: tracked + insured courier — we pay for the return.',
		'Data retention: stored documents are purged 90 days after delivery (GDPR).',
		'First contact: call the customer within ~1 working hour of payment.',
		'Ownership: every order has an assigned owner (queue fallback if none).',
		'One order per traveller — never combine travellers into a single order.',
		'Conditional submit: only near-travel where the portal allows later upload, and only with the customer\'s consent.',
		'Premium / fast-track slots are a paid add-on, never bundled as standard.',
		'Expedite: contact the authority and offer paid official expedite where available — plus honest contingency advice, never false promises.',
		'Express = faster handling on our side, NOT a faster government decision (and an ETA carries no document).',
		'Reviews: offer a next-order discount in exchange for a (consented) review.',
		'Loyalty: returning customers get a lighter intake and a discount.',
	];
}

/* ---------------------------------------------------------------------------
 * 3. Page render — read-only reference. All output escaped.
 * ------------------------------------------------------------------------- */
function ukv_sop_page_render(): void {
	if ( function_exists( 'current_user_can' ) && ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'default' ) );
	}

	echo '<div class="wrap ukv-sop-runbook">';
	echo '<h1>' . esc_html__( 'Runbook / SOP', 'default' ) . '</h1>';
	echo '<p style="max-width:760px">' . esc_html__( 'Read-only operator reference: the standing policies, the per-stage standard operating procedure (what to do, what to watch for, what moves it forward), and the troubleshooting playbook.', 'default' ) . '</p>';

	/* --- Locked policies ------------------------------------------------- */
	echo '<h2>' . esc_html__( 'Locked policies', 'default' ) . '</h2>';
	echo '<ul style="list-style:disc;padding-left:22px;max-width:820px">';
	foreach ( ukv_sop_locked_policies() as $bullet ) {
		echo '<li style="margin:4px 0">' . esc_html( $bullet ) . '</li>';
	}
	echo '</ul>';

	/* --- Per-stage SOP --------------------------------------------------- */
	echo '<h2>' . esc_html__( 'Stage-by-stage SOP', 'default' ) . '</h2>';
	if ( ! defined( 'UKV_STAGE_SOP' ) || ! is_array( UKV_STAGE_SOP ) || empty( UKV_STAGE_SOP ) ) {
		echo '<p><em>' . esc_html__( 'Stage SOP is not loaded (UKV_STAGE_SOP unavailable).', 'default' ) . '</em></p>';
	} else {
		foreach ( UKV_STAGE_SOP as $stage ) {
			$title = isset( $stage['title'] ) ? (string) $stage['title'] : '';
			echo '<div style="border:1px solid #dcdcde;border-radius:6px;padding:12px 16px;margin:0 0 12px;max-width:820px;background:#fff">';
			if ( '' !== $title ) {
				echo '<h3 style="margin:0 0 8px">' . esc_html( $title ) . '</h3>';
			}

			if ( ! empty( $stage['do'] ) && is_array( $stage['do'] ) ) {
				echo '<p style="margin:8px 0 2px"><strong>' . esc_html__( 'Do', 'default' ) . '</strong></p>';
				echo '<ul style="list-style:disc;padding-left:20px;margin:0 0 8px">';
				foreach ( $stage['do'] as $line ) {
					echo '<li>' . esc_html( (string) $line ) . '</li>';
				}
				echo '</ul>';
			}

			if ( ! empty( $stage['watch'] ) && is_array( $stage['watch'] ) ) {
				echo '<p style="margin:8px 0 2px"><strong>' . esc_html__( 'Watch for', 'default' ) . '</strong></p>';
				echo '<ul style="list-style:disc;padding-left:20px;margin:0 0 8px">';
				foreach ( $stage['watch'] as $line ) {
					echo '<li>' . esc_html( (string) $line ) . '</li>';
				}
				echo '</ul>';
			}

			if ( ! empty( $stage['next'] ) ) {
				echo '<p style="margin:8px 0 0"><strong>' . esc_html__( 'Moves forward when', 'default' ) . ':</strong> ' . esc_html( (string) $stage['next'] ) . '</p>';
			}

			echo '</div>';
		}
	}

	/* --- Troubleshooting ------------------------------------------------- */
	echo '<h2>' . esc_html__( 'Troubleshooting', 'default' ) . '</h2>';
	if ( ! defined( 'UKV_TROUBLESHOOTING' ) || ! is_array( UKV_TROUBLESHOOTING ) || empty( UKV_TROUBLESHOOTING ) ) {
		echo '<p><em>' . esc_html__( 'Troubleshooting playbook is not loaded (UKV_TROUBLESHOOTING unavailable).', 'default' ) . '</em></p>';
	} else {
		$humanise = function ( $key ) {
			if ( function_exists( 'ukv_sop_humanise' ) ) {
				return ukv_sop_humanise( $key );
			}
			return ucfirst( str_replace( '_', ' ', (string) $key ) );
		};
		echo '<table class="widefat striped" style="max-width:900px"><thead><tr>';
		echo '<th style="width:220px">' . esc_html__( 'Problem', 'default' ) . '</th>';
		echo '<th>' . esc_html__( 'Solution', 'default' ) . '</th>';
		echo '</tr></thead><tbody>';
		foreach ( UKV_TROUBLESHOOTING as $key => $solution ) {
			echo '<tr>';
			echo '<td><strong>' . esc_html( $humanise( $key ) ) . '</strong></td>';
			echo '<td>' . esc_html( (string) $solution ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	echo '</div>'; // .wrap
}
