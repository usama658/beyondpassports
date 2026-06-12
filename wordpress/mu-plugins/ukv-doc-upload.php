<?php
/**
 * Plugin Name: UKV Secure Document Upload (Gap #68)
 * Desc: Privacy + file-security critical customer document upload tied to ONE order.
 *       [ukv_doc_upload] shortcode authenticates with order reference + email (the same
 *       non-enumerating ref+email match the tracker uses) and, only on a valid match,
 *       accepts strictly-validated files (jpg/jpeg/png/pdf/heic, <=10MB), stores each as a
 *       PRIVATE attachment parented to that order, appends the attachment id to the order's
 *       ukv_documents array, and logs a journey note (channel "upload").
 *
 * SECURITY:
 *   - Failed auth returns a GENERIC message — wrong-email and nonexistent-ref are indistinguishable.
 *   - The email is NEVER echoed back; all output is escaped.
 *   - Only the matched order receives the file; no other order's data is revealed.
 *   - MIME + extension are both checked (wp_check_filetype_and_ext); only the allow-list passes.
 *
 * Depends: ukv-tracker.php (ukv_tracker_lookup — auth) and ukv-orders.php (ukv_order CPT, ukv_create_order).
 */

defined( 'ABSPATH' ) || exit;

/** Allowed uploads: extension => mime. The ONLY types accepted. */
const UKV_DOC_UPLOAD_ALLOWED = [
	'jpg'  => 'image/jpeg',
	'jpeg' => 'image/jpeg',
	'png'  => 'image/png',
	'pdf'  => 'application/pdf',
	'heic' => 'image/heic',
];

/** Max bytes per uploaded file (10 MB). */
const UKV_DOC_UPLOAD_MAX_BYTES = 10485760;

/**
 * Authenticate an order by reference + email.
 * Reuses the tracker's non-enumerating lookup when present; otherwise replicates it
 * (ukv_order_ref === ref AND strtolower(ukv_email) === strtolower(email)). No match -> null.
 *
 * @return int|null Order ID on match, null otherwise.
 */
function ukv_doc_upload_authenticate( $ref, $email ) {
	if ( function_exists( 'ukv_tracker_lookup' ) ) {
		return ukv_tracker_lookup( $ref, $email );
	}
	$ref   = trim( (string) $ref );
	$email = strtolower( trim( (string) $email ) );
	if ( '' === $ref || '' === $email ) {
		return null;
	}
	$ids = get_posts( [
		'post_type'   => 'ukv_order',
		'post_status' => 'publish',
		'fields'      => 'ids',
		'numberposts' => -1,
		'meta_query'  => [ [ 'key' => 'ukv_order_ref', 'value' => $ref ] ],
	] );
	foreach ( $ids as $oid ) {
		if ( strtolower( trim( (string) get_post_meta( $oid, 'ukv_email', true ) ) ) === $email ) {
			return (int) $oid;
		}
	}
	return null;
}

/**
 * Validate + store ONE uploaded file against an order. Testable core.
 *
 * @param int   $order_id A ukv_order post ID (caller must have already authenticated).
 * @param array $file     A $_FILES-style array: name, type, tmp_name, error, size.
 * @return int|WP_Error   New attachment ID (>0) on success, or WP_Error on any rejection.
 */
function ukv_doc_upload_attach( int $order_id, array $file ) {
	$post = get_post( $order_id );
	if ( ! $post || 'ukv_order' !== $post->post_type ) {
		return new WP_Error( 'ukv_bad_order', 'Order not found.' );
	}

	// Basic upload-error / shape checks.
	if ( ! empty( $file['error'] ) ) {
		return new WP_Error( 'ukv_upload_error', 'The file failed to upload. Please try again.' );
	}
	$tmp = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
	if ( '' === $tmp || ! is_file( $tmp ) ) {
		return new WP_Error( 'ukv_no_file', 'No file was received.' );
	}

	$size = isset( $file['size'] ) ? (int) $file['size'] : (int) @filesize( $tmp );
	if ( $size <= 0 ) {
		return new WP_Error( 'ukv_empty_file', 'The file is empty.' );
	}
	if ( $size > UKV_DOC_UPLOAD_MAX_BYTES ) {
		return new WP_Error( 'ukv_too_large', 'That file is too large. The maximum is 10 MB.' );
	}

	$orig_name = isset( $file['name'] ) ? (string) $file['name'] : '';
	$safe_name = sanitize_file_name( $orig_name );
	if ( '' === $safe_name ) {
		return new WP_Error( 'ukv_bad_name', 'Invalid file name.' );
	}

	// STRICT type check: extension must be allow-listed AND the real bytes must agree.
	$ext = strtolower( (string) pathinfo( $safe_name, PATHINFO_EXTENSION ) );
	if ( ! isset( UKV_DOC_UPLOAD_ALLOWED[ $ext ] ) ) {
		return new WP_Error( 'ukv_bad_type', 'That file type is not allowed. Please upload a JPG, PNG, PDF or HEIC.' );
	}

	// wp_check_filetype_and_ext sniffs the real file and confirms ext<->mime agreement.
	$allowed_mimes = array_unique( UKV_DOC_UPLOAD_ALLOWED );
	$ftcheck       = wp_check_filetype_and_ext( $tmp, $safe_name, UKV_DOC_UPLOAD_ALLOWED );
	$checked_ext   = $ftcheck['ext'] ?? false;
	$checked_type  = $ftcheck['type'] ?? false;

	// HEIC and some PDFs are not always fingerprinted by the PHP/finfo build; fall back to the
	// declared mapping ONLY when the extension is on our allow-list AND finfo gave no contradicting
	// type. If finfo returns a type, it MUST match our allow-list value for that extension.
	if ( false === $checked_type ) {
		if ( in_array( $ext, [ 'heic', 'pdf' ], true ) ) {
			$checked_ext  = $ext;
			$checked_type = UKV_DOC_UPLOAD_ALLOWED[ $ext ];
		} else {
			return new WP_Error( 'ukv_type_mismatch', 'The file content does not match its type. Please upload a valid document.' );
		}
	}

	if ( ! $checked_ext || ! $checked_type
		|| ! isset( UKV_DOC_UPLOAD_ALLOWED[ strtolower( $checked_ext ) ] )
		|| UKV_DOC_UPLOAD_ALLOWED[ strtolower( $checked_ext ) ] !== $checked_type
	) {
		return new WP_Error( 'ukv_type_mismatch', 'The file content does not match its type. Please upload a valid document.' );
	}

	// Move the validated file into the uploads dir.
	if ( defined( 'UKV_DOC_UPLOAD_TEST' ) && UKV_DOC_UPLOAD_TEST ) {
		// Unit-test path: the tmp file is not a real HTTP upload, so move_uploaded_file()
		// (inside wp_handle_upload) would fail. Place it manually with the same result shape.
		$bits = wp_upload_bits( $safe_name, null, (string) file_get_contents( $tmp ) );
		if ( ! empty( $bits['error'] ) ) {
			return new WP_Error( 'ukv_store_failed', 'Could not store the file.' );
		}
		$dest_path = $bits['file'];
		$dest_url  = $bits['url'];
		$dest_type = $checked_type;
	} else {
		// Production path: wp_handle_upload performs the secure move + its own type check.
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$overrides = [
			'test_form' => false,
			'mimes'     => UKV_DOC_UPLOAD_ALLOWED,
		];
		$moved = wp_handle_upload( $file, $overrides );
		if ( isset( $moved['error'] ) ) {
			return new WP_Error( 'ukv_store_failed', (string) $moved['error'] );
		}
		$dest_path = $moved['file'];
		$dest_url  = $moved['url'];
		$dest_type = $moved['type'];
	}

	// Create a PRIVATE attachment parented to THIS order only (post_status inherit; excluded from nav).
	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}
	$attach_id = wp_insert_attachment( [
		'post_mime_type' => $dest_type,
		'post_title'     => sanitize_text_field( pathinfo( $safe_name, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
		'guid'           => $dest_url,
	], $dest_path, $order_id, true );

	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		@unlink( $dest_path );
		return new WP_Error( 'ukv_attach_failed', 'Could not save the document.' );
	}

	$meta = wp_generate_attachment_metadata( $attach_id, $dest_path );
	wp_update_attachment_metadata( $attach_id, $meta );

	// Mark as a customer-uploaded order document (lets the team distinguish it).
	update_post_meta( $attach_id, '_ukv_order_doc', $order_id );

	// Append to the order's documents array (idempotent on the id).
	$docs   = array_filter( array_map( 'intval', (array) get_post_meta( $order_id, 'ukv_documents', true ) ) );
	$docs[] = (int) $attach_id;
	update_post_meta( $order_id, 'ukv_documents', array_values( array_unique( $docs ) ) );

	// Append a journey note (channel 'upload', agent 'customer').
	$journey   = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$journey[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'customer',
		'channel' => 'upload',
		'text'    => 'Document uploaded: ' . $safe_name,
	];
	update_post_meta( $order_id, 'ukv_journey', $journey );

	return (int) $attach_id;
}

/**
 * [ukv_doc_upload] — ref + email + file input(s), nonce-protected, posts to itself.
 * On a valid auth match, processes each uploaded file through ukv_doc_upload_attach()
 * and shows a success confirmation. On no match, shows a GENERIC (non-enumerating) error.
 */
add_shortcode( 'ukv_doc_upload', function () {
	$ref_val = '';
	$result  = '';

	if ( isset( $_POST['ukv_doc_upload_submit'] ) ) {
		if ( ! isset( $_POST['ukv_doc_upload_nonce'] )
			|| ! wp_verify_nonce( wp_unslash( $_POST['ukv_doc_upload_nonce'] ), 'ukv_doc_upload' )
		) {
			$result = ukv_doc_upload_notice( 'error', 'Your session expired. Please try again.' );
		} else {
			$ref_val   = isset( $_POST['ukv_doc_ref'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_doc_ref'] ) ) : '';
			$email_val = isset( $_POST['ukv_doc_email'] ) ? sanitize_email( wp_unslash( $_POST['ukv_doc_email'] ) ) : '';
			$oid       = ukv_doc_upload_authenticate( $ref_val, $email_val );

			if ( ! $oid ) {
				// Non-enumerating: identical generic message for wrong email vs nonexistent ref.
				$result = ukv_doc_upload_notice(
					'error',
					"We couldn't find an application matching those details. Please check your reference and email, or contact us."
				);
			} else {
				$received = [];
				$errors   = [];
				$files    = $_FILES['ukv_doc_files'] ?? null;

				if ( is_array( $files ) && isset( $files['name'] ) ) {
					$names = (array) $files['name'];
					foreach ( array_keys( $names ) as $i ) {
						// Skip empty slots.
						if ( empty( $files['name'][ $i ] ) && (int) ( $files['error'][ $i ] ?? 4 ) === 4 ) {
							continue;
						}
						$one = [
							'name'     => $files['name'][ $i ] ?? '',
							'type'     => $files['type'][ $i ] ?? '',
							'tmp_name' => $files['tmp_name'][ $i ] ?? '',
							'error'    => $files['error'][ $i ] ?? 4,
							'size'     => $files['size'][ $i ] ?? 0,
						];
						$res = ukv_doc_upload_attach( (int) $oid, $one );
						if ( is_wp_error( $res ) ) {
							$errors[] = sanitize_file_name( (string) $one['name'] ) . ': ' . $res->get_error_message();
						} else {
							$received[] = sanitize_file_name( (string) $one['name'] );
						}
					}
				}

				if ( ! $received && ! $errors ) {
					$result = ukv_doc_upload_notice( 'error', 'Please choose at least one file to upload.' );
				} else {
					$body = '';
					if ( $received ) {
						$body .= '<p>Thank you — we received the following file(s):</p><ul style="margin-left:1.25em">';
						foreach ( $received as $r ) {
							$body .= '<li>' . esc_html( $r ) . '</li>';
						}
						$body .= '</ul>';
					}
					if ( $errors ) {
						$body .= '<p>We could not accept:</p><ul style="margin-left:1.25em;color:#c00">';
						foreach ( $errors as $e ) {
							$body .= '<li>' . esc_html( $e ) . '</li>';
						}
						$body .= '</ul>';
					}
					$result = ukv_doc_upload_notice( $received ? 'success' : 'error', $body, true );
					$ref_val = ''; // do not echo the ref back after a successful submit.
				}
			}
		}
	}

	$action = esc_url( remove_query_arg( 'x' ) );
	$allowed_hint = 'JPG, PNG, PDF or HEIC — up to 10 MB each.';

	$o  = '<form class="ukv-doc-upload-form" method="post" enctype="multipart/form-data" action="' . $action . '" style="max-width:640px">';
	$o .= wp_nonce_field( 'ukv_doc_upload', 'ukv_doc_upload_nonce', true, false );
	$o .= '<p style="margin:.5em 0"><label for="ukv_doc_ref" style="display:block;font-weight:600">Order reference</label>';
	$o .= '<input type="text" id="ukv_doc_ref" name="ukv_doc_ref" value="' . esc_attr( $ref_val ) . '" required style="width:100%;padding:.5em"></p>';
	$o .= '<p style="margin:.5em 0"><label for="ukv_doc_email" style="display:block;font-weight:600">Email</label>';
	$o .= '<input type="email" id="ukv_doc_email" name="ukv_doc_email" required style="width:100%;padding:.5em"></p>';
	$o .= '<p style="margin:.5em 0"><label for="ukv_doc_files" style="display:block;font-weight:600">Your documents</label>';
	$o .= '<input type="file" id="ukv_doc_files" name="ukv_doc_files[]" multiple accept=".jpg,.jpeg,.png,.pdf,.heic,image/jpeg,image/png,application/pdf,image/heic" required style="width:100%;padding:.5em">';
	$o .= '<span style="display:block;color:#666;font-size:.85em;margin-top:.25em">' . esc_html( $allowed_hint ) . '</span></p>';
	$o .= '<p style="margin:.75em 0"><button type="submit" name="ukv_doc_upload_submit" value="1" style="padding:.6em 1.4em;background:#0f7b3f;color:#fff;border:0;border-radius:6px;cursor:pointer">Upload documents</button></p>';
	$o .= '<p style="font-size:.85em;color:#777">Your files are sent securely and attached only to your application. Independent service — not a government website.</p>';
	$o .= '</form>';

	return $o . $result;
} );

/**
 * Render a small notice box. $body is treated as trusted pre-built HTML only when
 * $is_html is true (the shortcode builds those strings itself with esc_html on each item);
 * otherwise the plain message is escaped here.
 */
function ukv_doc_upload_notice( $type, $message, $is_html = false ) {
	$ok    = ( 'success' === $type );
	$bg    = $ok ? '#f3fbf5' : '#fdf3f3';
	$bd    = $ok ? '#bfe6c9' : '#e2c2c2';
	$inner = $is_html ? wp_kses_post( $message ) : esc_html( $message );
	return '<div class="ukv-doc-upload-result" style="max-width:640px;margin:1em 0;padding:1em;border:1px solid ' . esc_attr( $bd ) . ';border-radius:8px;background:' . esc_attr( $bg ) . '">'
		. $inner . '</div>';
}

// Upsert the published /upload-documents page containing the shortcode.
add_action( 'init', function () {
	$existing = get_page_by_path( 'upload-documents', OBJECT, 'page' );
	if ( $existing ) {
		if ( false === strpos( (string) $existing->post_content, '[ukv_doc_upload]' ) ) {
			wp_update_post( [ 'ID' => $existing->ID, 'post_content' => '[ukv_doc_upload]' ] );
		}
		return;
	}
	wp_insert_post( [
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Upload your documents',
		'post_name'    => 'upload-documents',
		'post_content' => '[ukv_doc_upload]',
	] );
} );
