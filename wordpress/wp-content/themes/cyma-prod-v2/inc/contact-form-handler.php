<?php
/**
 * Contact Us / job-application form: CPT storage + admin-post handler (no email).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Contact Submissions CPT (admin-only).
 */
function cyma_register_contact_submission_cpt() {
	register_post_type(
		'cyma_contact',
		array(
			'labels'              => array(
				'name'               => 'Contact Submissions',
				'singular_name'      => 'Contact Submission',
				'menu_name'          => 'Contact Submissions',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add Contact Submission',
				'edit_item'          => 'View Contact Submission',
				'new_item'           => 'New Contact Submission',
				'view_item'          => 'View Contact Submission',
				'search_items'       => 'Search Submissions',
				'not_found'          => 'No submissions found',
				'not_found_in_trash' => 'No submissions found in Trash',
				'all_items'          => 'All Submissions',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 26,
			'menu_icon'           => 'dashicons-email-alt',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
		)
	);
}
add_action( 'init', 'cyma_register_contact_submission_cpt' );

/**
 * Redirect URL after form submit.
 *
 * @param string $status success|error
 * @return string
 */
function cyma_contact_redirect_url( $status = 'success' ) {
	$contact = get_page_by_path( 'contact-us' );
	$url     = $contact ? get_permalink( $contact ) : home_url( '/contact-us/' );

	$role = isset( $_POST['contact']['role'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		? sanitize_text_field( wp_unslash( $_POST['contact']['role'] ) )
		: '';

	$args = array( 'contact' => $status );
	if ( $role !== '' ) {
		$args['role'] = $role;
	}

	return add_query_arg( $args, $url );
}

/**
 * Light rate limit by IP (1 submission / 60s).
 *
 * @return bool True if allowed.
 */
function cyma_contact_rate_limit_ok() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( $ip === '' ) {
		return true;
	}
	$key = 'cyma_contact_rl_' . md5( $ip );
	if ( get_transient( $key ) ) {
		return false;
	}
	set_transient( $key, 1, 60 );
	return true;
}

/**
 * Sanitize posted contact fields.
 *
 * @return array{full_name:string,email:string,phone:string,interest:string,message:string,role:string}|WP_Error
 */
function cyma_contact_sanitize_request() {
	$raw = isset( $_POST['contact'] ) && is_array( $_POST['contact'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		? wp_unslash( $_POST['contact'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();

	$data = array(
		'full_name' => isset( $raw['full-name'] ) ? sanitize_text_field( $raw['full-name'] ) : '',
		'email'     => isset( $raw['email'] ) ? sanitize_email( $raw['email'] ) : '',
		'phone'     => isset( $raw['mobile-number'] ) ? sanitize_text_field( $raw['mobile-number'] ) : '',
		'interest'  => isset( $raw['interested-in'] ) ? sanitize_text_field( $raw['interested-in'] ) : '',
		'message'   => isset( $raw['message'] ) ? sanitize_textarea_field( $raw['message'] ) : '',
		'role'      => isset( $raw['role'] ) ? sanitize_text_field( $raw['role'] ) : '',
	);

	if ( $data['full_name'] === '' || $data['email'] === '' || ! is_email( $data['email'] ) ) {
		return new WP_Error( 'cyma_contact_invalid', 'Name and a valid email are required.' );
	}

	return $data;
}

/**
 * Store submission as CPT + meta.
 *
 * @param array $data Sanitized fields.
 * @return int|WP_Error Post ID.
 */
function cyma_contact_store_submission( $data ) {
	$title = sprintf(
		'%s — %s',
		$data['full_name'],
		wp_date( 'Y-m-d H:i' )
	);

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'cyma_contact',
			'post_status' => 'publish',
			'post_title'  => $title,
			'post_content'=> '',
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	update_post_meta( $post_id, '_cyma_contact_full_name', $data['full_name'] );
	update_post_meta( $post_id, '_cyma_contact_email', $data['email'] );
	update_post_meta( $post_id, '_cyma_contact_phone', $data['phone'] );
	update_post_meta( $post_id, '_cyma_contact_interest', $data['interest'] );
	update_post_meta( $post_id, '_cyma_contact_message', $data['message'] );
	update_post_meta( $post_id, '_cyma_contact_role', $data['role'] );

	return (int) $post_id;
}

/**
 * Handle admin-post submission (logged-in + guests).
 * Submissions are stored in CPT only; no email is sent.
 */
function cyma_handle_contact_submit() {
	$nonce = isset( $_POST['cyma_contact_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['cyma_contact_nonce'] ) )
		: '';

	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'cyma_contact_submit' ) ) {
		wp_safe_redirect( cyma_contact_redirect_url( 'error' ) );
		exit;
	}

	if ( ! cyma_contact_rate_limit_ok() ) {
		wp_safe_redirect( cyma_contact_redirect_url( 'error' ) );
		exit;
	}

	$data = cyma_contact_sanitize_request();
	if ( is_wp_error( $data ) ) {
		wp_safe_redirect( cyma_contact_redirect_url( 'error' ) );
		exit;
	}

	$post_id = cyma_contact_store_submission( $data );
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_safe_redirect( cyma_contact_redirect_url( 'error' ) );
		exit;
	}

	wp_safe_redirect( cyma_contact_redirect_url( 'success' ) );
	exit;
}
add_action( 'admin_post_nopriv_cyma_contact_submit', 'cyma_handle_contact_submit' );
add_action( 'admin_post_cyma_contact_submit', 'cyma_handle_contact_submit' );

/* -------------------------------------------------------------------------- */
/* Admin list columns + meta box                                              */
/* -------------------------------------------------------------------------- */

/**
 * @param array $columns Default columns.
 * @return array
 */
function cyma_contact_admin_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['cyma_email']    = 'Email';
			$new['cyma_interest'] = 'Interest / Role';
			$new['cyma_phone']    = 'Phone';
		}
	}
	return $new;
}
add_filter( 'manage_cyma_contact_posts_columns', 'cyma_contact_admin_columns' );

/**
 * @param string $column Column key.
 * @param int    $post_id Post ID.
 */
function cyma_contact_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'cyma_email':
			echo esc_html( (string) get_post_meta( $post_id, '_cyma_contact_email', true ) );
			break;
		case 'cyma_phone':
			echo esc_html( (string) get_post_meta( $post_id, '_cyma_contact_phone', true ) );
			break;
		case 'cyma_interest':
			$interest = (string) get_post_meta( $post_id, '_cyma_contact_interest', true );
			$role     = (string) get_post_meta( $post_id, '_cyma_contact_role', true );
			$label    = $role !== '' ? $role : $interest;
			echo esc_html( $label );
			break;
	}
}
add_action( 'manage_cyma_contact_posts_custom_column', 'cyma_contact_admin_column_content', 10, 2 );

/**
 * Meta box to view full submission.
 */
function cyma_contact_add_meta_boxes() {
	add_meta_box(
		'cyma_contact_details',
		'Submission Details',
		'cyma_contact_render_meta_box',
		'cyma_contact',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'cyma_contact_add_meta_boxes' );

/**
 * @param WP_Post $post Post.
 */
function cyma_contact_render_meta_box( $post ) {
	$fields = array(
		'Full name' => '_cyma_contact_full_name',
		'Email'     => '_cyma_contact_email',
		'Phone'     => '_cyma_contact_phone',
		'Interest'  => '_cyma_contact_interest',
		'Role'      => '_cyma_contact_role',
		'Message'   => '_cyma_contact_message',
	);

	echo '<table class="widefat striped" style="max-width:720px"><tbody>';
	foreach ( $fields as $label => $key ) {
		$value = (string) get_post_meta( $post->ID, $key, true );
		echo '<tr><th style="width:140px;text-align:left">' . esc_html( $label ) . '</th><td>';
		if ( 'Message' === $label ) {
			echo nl2br( esc_html( $value ) );
		} elseif ( 'Email' === $label && $value !== '' && is_email( $value ) ) {
			echo '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
		} else {
			echo esc_html( $value !== '' ? $value : '—' );
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Hide "Add New" for submissions (read-oriented).
 */
function cyma_contact_admin_menu_tweaks() {
	remove_submenu_page( 'edit.php?post_type=cyma_contact', 'post-new.php?post_type=cyma_contact' );
}
add_action( 'admin_menu', 'cyma_contact_admin_menu_tweaks', 999 );
