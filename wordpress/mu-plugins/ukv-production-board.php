<?php
/**
 * Plugin Name: UKV Production Line (Kanban ops board)
 * Desc: Read-only horizontal kanban of orders grouped by stage (UKV_ORDER_STATUSES).
 *       Each card links to the order edit screen and shows ref/name/dest/tier plus
 *       SLA, risk, AI doc-review badge and a barriers count. Read-only — advancing a
 *       stage happens on the order edit screen. Reuses existing UKV helpers (guarded).
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * 1. Top-level admin menu.
 * ------------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_menu_page(
		'Production Line',
		'Production Line',
		'edit_posts',
		'ukv-production-board',
		'ukv_prodboard_render',
		'dashicons-screenoptions',
		4
	);
} );

/* ---------------------------------------------------------------------------
 * 2. Columns: ordered stages -> order IDs currently in each stage.
 *
 * Open stages first (in UKV_ORDER_STATUSES order), then the closed-success
 * stages (delivered / won). rejected / refunded are collapsed into a single
 * trailing 'closed' column so the board stays focused on live work.
 * ------------------------------------------------------------------------- */
function ukv_prodboard_columns(): array {
	$statuses = defined( 'UKV_ORDER_STATUSES' ) ? UKV_ORDER_STATUSES : [];
	$closed   = defined( 'UKV_ORDER_CLOSED' ) ? UKV_ORDER_CLOSED : [ 'delivered', 'won', 'rejected', 'refunded' ];

	// Stage columns we render individually (everything except the dead-end closed pair).
	$collapse = [ 'rejected', 'refunded' ];
	$cols     = [];
	foreach ( $statuses as $key => $label ) {
		if ( in_array( $key, $collapse, true ) ) { continue; }
		$cols[ $key ] = [ 'label' => $label, 'orders' => [] ];
	}
	// Trailing collapsed column for the dead-end closed states.
	$cols['_closed'] = [ 'label' => 'Closed (rejected / refunded)', 'orders' => [] ];

	$ids = get_posts( [
		'post_type'   => 'ukv_order',
		'post_status' => 'publish',
		'fields'      => 'ids',
		'numberposts' => -1,
	] );

	foreach ( $ids as $oid ) {
		$status = (string) get_post_meta( $oid, 'ukv_status', true );
		if ( '' === $status ) { $status = 'paid'; }

		if ( in_array( $status, $collapse, true ) ) {
			$cols['_closed']['orders'][] = (int) $oid;
		} elseif ( isset( $cols[ $status ] ) ) {
			$cols[ $status ]['orders'][] = (int) $oid;
		} else {
			// Unknown status — surface it in the closed column so nothing is lost.
			$cols['_closed']['orders'][] = (int) $oid;
		}
	}

	unset( $closed ); // computed defensively above; not otherwise needed.
	return $cols;
}

/* ---------------------------------------------------------------------------
 * 3. Card data for one order (every helper guarded; degrades gracefully).
 * ------------------------------------------------------------------------- */
function ukv_prodboard_card( int $order_id ): array {
	$g = static function ( $k ) use ( $order_id ) {
		return (string) get_post_meta( $order_id, $k, true );
	};

	// Owner display name (guarded).
	$owner = '';
	if ( function_exists( 'ukv_get_owner' ) ) {
		$uid = ukv_get_owner( $order_id );
		if ( $uid > 0 ) {
			$u     = get_userdata( $uid );
			$owner = $u ? (string) $u->display_name : ( '#' . $uid );
		}
	}

	// SLA (guarded).
	$sla = 'ok';
	if ( function_exists( 'ukv_order_sla_breached' ) && ukv_order_sla_breached( $order_id ) ) {
		$sla = 'breach';
	}

	// AI doc-review badge (guarded; already returns escaped markup).
	$docs_badge = function_exists( 'ukv_doc_review_badge' ) ? ukv_doc_review_badge( $order_id ) : '';

	// Barriers count (guarded).
	$barriers = 0;
	if ( function_exists( 'ukv_barriers_for_order' ) ) {
		$barriers = count( (array) ukv_barriers_for_order( $order_id ) );
	}

	return [
		'ref'         => $g( 'ukv_order_ref' ),
		'name'        => $g( 'ukv_name' ),
		'destination' => $g( 'ukv_destination' ),
		'tier'        => $g( 'ukv_tier' ),
		'owner'       => $owner,
		'sla'         => $sla,
		'risk'        => '1' === $g( 'ukv_risk_flag' ),
		'docs_badge'  => $docs_badge,
		'barriers'    => (int) $barriers,
		'next_action' => $g( 'ukv_next_action' ),
	];
}

/* ---------------------------------------------------------------------------
 * 4 + 5. Render the read-only kanban (escaped throughout, empty-safe).
 * ------------------------------------------------------------------------- */
function ukv_prodboard_render(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'ukv' ) );
	}

	$cols  = ukv_prodboard_columns();
	$total = 0;
	foreach ( $cols as $c ) { $total += count( $c['orders'] ); }

	echo '<div class="wrap ukv-prodboard">';
	echo '<h1>' . esc_html__( 'Production Line', 'ukv' ) . '</h1>';
	echo '<p class="description">' . esc_html(
		sprintf(
			/* translators: %d: total number of orders on the board. */
			_n( '%d order across all stages. Read-only board — advance an order from its edit screen.', '%d orders across all stages. Read-only board — advance an order from its edit screen.', $total, 'ukv' ),
			$total
		)
	) . '</p>';

	// Inline CSS — flex row of scrollable columns. No JS.
	echo '<style>
		.ukv-prodboard .ukvpb-board{display:flex;gap:14px;overflow-x:auto;padding:8px 2px 18px;align-items:flex-start}
		.ukv-prodboard .ukvpb-col{flex:0 0 250px;background:#f0f0f1;border:1px solid #dcdcde;border-radius:8px;display:flex;flex-direction:column;max-height:78vh}
		.ukv-prodboard .ukvpb-colhead{padding:10px 12px;font-weight:600;border-bottom:1px solid #dcdcde;position:sticky;top:0;background:#f0f0f1;border-radius:8px 8px 0 0}
		.ukv-prodboard .ukvpb-count{display:inline-block;min-width:20px;text-align:center;margin-left:6px;padding:0 6px;border-radius:10px;background:#2271b1;color:#fff;font-size:11px;line-height:18px}
		.ukv-prodboard .ukvpb-cards{padding:10px;overflow-y:auto;display:flex;flex-direction:column;gap:10px}
		.ukv-prodboard .ukvpb-card{display:block;background:#fff;border:1px solid #dcdcde;border-left:4px solid #2271b1;border-radius:6px;padding:9px 10px;text-decoration:none;color:#1d2327;box-shadow:0 1px 2px rgba(0,0,0,.04)}
		.ukv-prodboard .ukvpb-card:hover{border-color:#2271b1;box-shadow:0 2px 6px rgba(0,0,0,.12)}
		.ukv-prodboard .ukvpb-ref{font-weight:600;font-size:12px}
		.ukv-prodboard .ukvpb-name{font-size:13px;margin:1px 0}
		.ukv-prodboard .ukvpb-meta{font-size:11px;color:#646970;margin-bottom:6px}
		.ukv-prodboard .ukvpb-pills{display:flex;flex-wrap:wrap;gap:4px;align-items:center}
		.ukv-prodboard .ukvpb-pill{display:inline-block;padding:1px 8px;border-radius:9px;font-size:11px;line-height:16px}
		.ukv-prodboard .ukvpb-sla-breach{background:#fbdcdc;color:#b32d2e}
		.ukv-prodboard .ukvpb-sla-ok{background:#d6f0df;color:#0f7b3f}
		.ukv-prodboard .ukvpb-risk{background:#fcf0d6;color:#996800}
		.ukv-prodboard .ukvpb-barriers{background:#e2e4e7;color:#50575e}
		.ukv-prodboard .ukvpb-next{font-size:11px;color:#50575e;margin-top:6px}
		.ukv-prodboard .ukvpb-empty{color:#787c82;font-style:italic;font-size:12px;padding:6px 2px}
	</style>';

	echo '<div class="ukvpb-board">';
	foreach ( $cols as $stage => $col ) {
		echo '<div class="ukvpb-col" data-stage="' . esc_attr( $stage ) . '">';
		echo '<div class="ukvpb-colhead">' . esc_html( $col['label'] )
			. '<span class="ukvpb-count">' . esc_html( (string) count( $col['orders'] ) ) . '</span></div>';
		echo '<div class="ukvpb-cards">';

		if ( empty( $col['orders'] ) ) {
			echo '<div class="ukvpb-empty">' . esc_html__( 'No orders', 'ukv' ) . '</div>';
		} else {
			foreach ( $col['orders'] as $oid ) {
				$c    = ukv_prodboard_card( $oid );
				$link = get_edit_post_link( $oid );
				$href = $link ? $link : '#';

				echo '<a class="ukvpb-card" href="' . esc_url( $href ) . '">';
				echo '<span class="ukvpb-ref">' . esc_html( $c['ref'] ?: ( '#' . $oid ) ) . '</span>';
				echo '<div class="ukvpb-name">' . esc_html( $c['name'] ) . '</div>';

				$meta = array_filter( [ $c['destination'], $c['tier'], $c['owner'] ] );
				echo '<div class="ukvpb-meta">' . esc_html( implode( ' · ', $meta ) ) . '</div>';

				echo '<div class="ukvpb-pills">';
				if ( 'breach' === $c['sla'] ) {
					echo '<span class="ukvpb-pill ukvpb-sla-breach">' . esc_html__( 'SLA breached', 'ukv' ) . '</span>';
				} else {
					echo '<span class="ukvpb-pill ukvpb-sla-ok">' . esc_html__( 'SLA OK', 'ukv' ) . '</span>';
				}
				if ( $c['risk'] ) {
					echo '<span class="ukvpb-pill ukvpb-risk">' . esc_html__( 'Risk', 'ukv' ) . '</span>';
				}
				// docs_badge is helper-produced escaped markup; safe to emit.
				if ( '' !== $c['docs_badge'] ) {
					echo $c['docs_badge']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				if ( $c['barriers'] > 0 ) {
					echo '<span class="ukvpb-pill ukvpb-barriers">' . esc_html(
						sprintf(
							/* translators: %d: number of open barriers affecting this order. */
							_n( '%d barrier', '%d barriers', $c['barriers'], 'ukv' ),
							$c['barriers']
						)
					) . '</span>';
				}
				echo '</div>';

				if ( '' !== $c['next_action'] ) {
					echo '<div class="ukvpb-next">▸ ' . esc_html( $c['next_action'] ) . '</div>';
				}
				echo '</a>';
			}
		}

		echo '</div>'; // .ukvpb-cards
		echo '</div>'; // .ukvpb-col
	}
	echo '</div>'; // .ukvpb-board
	echo '</div>'; // .wrap
}
