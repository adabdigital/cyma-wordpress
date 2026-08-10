<?php
/**
 * H-1B LCA public posting notices — CPT, admin UI, seeding, front-end helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CYMA_LCA_CPT', 'lca_posting' );
define( 'CYMA_LCA_SEED_OPTION', 'cyma_lca_postings_seeded_v1' );

/**
 * Default seed data (used for one-time import and as front-end fallback).
 *
 * @return array<int, array<string, string>>
 */
function cyma_lca_default_postings() {
	return array(
		array(
			'title'     => 'Senior Data Engineer — Yarabarla',
			'role'      => 'This worker is being sought as a Senior Data Engineer (15-1252 SOC O*NET Code).',
			'wage'      => 'Wages of $135,000 per year are being offered to this worker.',
			'period'    => 'The period of employment is 10/01/2026 to 09/30/2029.',
			'location'  => 'The location where the H-1B employee will work: 10512 Amarillo Lane, Aubrey, TX 76227.',
			'doc_file'  => 'Cyma LCA Posting Yarabarla April 2026.doc',
			'menu_order'=> 1,
		),
		array(
			'title'     => 'SharePoint Web Application Developer — Daggula Ramya',
			'role'      => 'This worker is being sought as a SharePoint Web Application Developer (15-1252 SOC O*NET Code).',
			'wage'      => 'Wages of $121,500 per year are being offered to this worker.',
			'period'    => 'The period of employment is 12/15/2026 to 12/14/2029.',
			'location'  => 'The location where the H-1B employee will work: 1232 Patterson Terrace, Lake Mary, FL 32746.',
			'doc_file'  => 'Cyma LCA Posting Daggula Ramya June 2026.doc',
			'menu_order'=> 2,
		),
		array(
			'title'     => 'Sr Data Engineer — Daggula Ranjith',
			'role'      => 'This worker is being sought as an Sr Data Engineer (15-1252 SOC O*NET Code).',
			'wage'      => 'Wages of $126,100 per year are being offered to this worker.',
			'period'    => 'The period of employment is 07/15/2026 to 07/14/2029.',
			'location'  => 'The location where the H-1B employee will work: Kroger Blue Ash Tech Center (BTC), 11450 Grooms Road, Blue Ash, OH 45242.',
			'doc_file'  => 'Cyma LCA Posting Daggula Ranjith July 2026.doc',
			'menu_order'=> 3,
		),
		array(
			'title'     => 'Sr Oracle EBS Developer — Marinaicker',
			'role'      => 'This worker is being sought as a Sr Oracle EBS Developer (15-1252 SOC O*NET Code).',
			'wage'      => 'Wages of $131,500 per year are being offered to this worker.',
			'period'    => 'The period of employment is 12/01/2026 to 11/30/2029.',
			'location'  => 'The locations where the H-1B employee will work: Virginia State Police, 7700 Midlothian Turnpike, North Chesterfield, VA 23235; 11456 Hayloft Lane, Glen Allen, VA 23060.',
			'doc_file'  => 'Cyma LCA Posting Marinaicker June 2026.doc',
			'menu_order'=> 4,
		),
		array(
			'title'     => 'Senior Big Data Developer — Vangavaragu',
			'role'      => 'This worker is being sought as an Senior Big Data Developer (15-1252 SOC O*NET Code).',
			'wage'      => 'Wages of $139,000 per year are being offered to this worker.',
			'period'    => 'The period of employment is 08/28/2026 to 08/27/2029.',
			'location'  => 'The location where the H-1B employee will work: 4310 Blackwood Street, Prosper, TX 75078.',
			'doc_file'  => 'Cyma LCA Posting Vangavaragu July 2026.doc',
			'menu_order'=> 5,
		),
	);
}

/**
 * Theme docs base URI for LCA files.
 *
 * @return string
 */
function cyma_lca_docs_base_uri() {
	return trailingslashit( get_template_directory_uri() ) . 'assets/docs/lca/';
}

/**
 * Resolve download URL + label for a posting.
 *
 * @param int    $attachment_id Media library attachment ID.
 * @param string $theme_file    Filename under assets/docs/lca/.
 * @return array{url:string,label:string}
 */
function cyma_lca_resolve_doc( $attachment_id, $theme_file = '' ) {
	$attachment_id = absint( $attachment_id );
	if ( $attachment_id ) {
		$url = wp_get_attachment_url( $attachment_id );
		if ( $url ) {
			return array(
				'url'   => $url,
				'label' => basename( get_attached_file( $attachment_id ) ?: $url ),
			);
		}
	}

	// Keep spaces in original filenames; only strip path traversal.
	$theme_file = is_string( $theme_file ) ? basename( trim( $theme_file ) ) : '';
	$theme_file = str_replace( array( '..', '/', '\\' ), '', $theme_file );
	if ( $theme_file !== '' ) {
		$path = get_template_directory() . '/assets/docs/lca/' . $theme_file;
		if ( file_exists( $path ) ) {
			return array(
				'url'   => cyma_lca_docs_base_uri() . rawurlencode( $theme_file ),
				'label' => $theme_file,
			);
		}
	}

	return array( 'url' => '', 'label' => '' );
}

/**
 * Register LCA Postings CPT.
 */
function cyma_register_lca_posting_cpt() {
	register_post_type(
		CYMA_LCA_CPT,
		array(
			'labels'              => array(
				'name'               => 'H-1B LCA',
				'singular_name'      => 'LCA Posting',
				'menu_name'          => 'H-1B LCA',
				'name_admin_bar'     => 'LCA Posting',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add LCA Posting',
				'edit_item'          => 'Edit LCA Posting',
				'new_item'           => 'New LCA Posting',
				'view_item'          => 'View LCA Posting',
				'search_items'       => 'Search LCA Postings',
				'not_found'          => 'No LCA postings found',
				'not_found_in_trash' => 'No LCA postings found in Trash',
				'all_items'          => 'All LCA Postings',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 25,
			'menu_icon'           => 'dashicons-media-text',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'page-attributes' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
		)
	);
}
add_action( 'init', 'cyma_register_lca_posting_cpt' );

/**
 * Allow .doc / .docx / .pdf uploads for LCA documents.
 *
 * @param array<string,string> $mimes Mime types.
 * @return array<string,string>
 */
function cyma_lca_allow_doc_uploads( $mimes ) {
	$mimes['doc']  = 'application/msword';
	$mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
	$mimes['pdf']  = 'application/pdf';
	return $mimes;
}
add_filter( 'upload_mimes', 'cyma_lca_allow_doc_uploads' );

/**
 * Meta box registration.
 */
function cyma_lca_add_meta_boxes() {
	add_meta_box(
		'cyma_lca_details',
		'LCA Posting Details',
		'cyma_lca_render_meta_box',
		CYMA_LCA_CPT,
		'normal',
		'high'
	);
	add_meta_box(
		'cyma_lca_help',
		'How to edit H-1B LCA postings',
		'cyma_lca_render_help_meta_box',
		CYMA_LCA_CPT,
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'cyma_lca_add_meta_boxes' );

/**
 * Side help text for editors.
 *
 * @param WP_Post $post Post.
 */
function cyma_lca_render_help_meta_box( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	echo '<p style="margin-top:0"><strong>Title</strong> is the heading shown on the public H-1B LCA page (e.g. job title — employee name).</p>';
	echo '<p>Fill in the role, wage, employment period, and worksite text exactly as they should appear publicly.</p>';
	echo '<p>Attach a .doc/.pdf from the Media Library, or leave the theme filename if the file lives in <code>assets/docs/lca/</code>.</p>';
	echo '<p>Use <strong>Order</strong> (Attributes) to control display order — lower numbers appear first.</p>';
	echo '<p>Publish the posting to show it on <a href="' . esc_url( home_url( '/h1b-lca/' ) ) . '" target="_blank" rel="noopener noreferrer">/h1b-lca/</a>. Trash or unpublish to remove it.</p>';
}

/**
 * Main details meta box.
 *
 * @param WP_Post $post Post.
 */
function cyma_lca_render_meta_box( $post ) {
	wp_nonce_field( 'cyma_lca_save_meta', 'cyma_lca_meta_nonce' );

	$role       = (string) get_post_meta( $post->ID, '_lca_role', true );
	$wage       = (string) get_post_meta( $post->ID, '_lca_wage', true );
	$period     = (string) get_post_meta( $post->ID, '_lca_period', true );
	$location   = (string) get_post_meta( $post->ID, '_lca_location', true );
	$theme_file = (string) get_post_meta( $post->ID, '_lca_doc_theme_file', true );
	$attach_id  = absint( get_post_meta( $post->ID, '_lca_doc_attachment_id', true ) );
	$attach_url = $attach_id ? wp_get_attachment_url( $attach_id ) : '';
	$attach_name = $attach_id ? basename( get_attached_file( $attach_id ) ?: '' ) : '';

	$fields = array(
		'_lca_role'     => array(
			'label'       => 'Occupation / role text',
			'value'       => $role,
			'placeholder' => 'This worker is being sought as a … (SOC O*NET Code).',
			'rows'        => 2,
		),
		'_lca_wage'     => array(
			'label'       => 'Wage / salary text',
			'value'       => $wage,
			'placeholder' => 'Wages of $X per year are being offered to this worker.',
			'rows'        => 2,
		),
		'_lca_period'   => array(
			'label'       => 'Employment period text',
			'value'       => $period,
			'placeholder' => 'The period of employment is MM/DD/YYYY to MM/DD/YYYY.',
			'rows'        => 2,
		),
		'_lca_location' => array(
			'label'       => 'Worksite location(s) text',
			'value'       => $location,
			'placeholder' => 'The location where the H-1B employee will work: …',
			'rows'        => 3,
		),
	);

	echo '<style>
		.cyma-lca-field{margin:0 0 16px}
		.cyma-lca-field label{display:block;font-weight:600;margin-bottom:4px}
		.cyma-lca-field textarea,.cyma-lca-field input[type=text]{width:100%;max-width:720px}
		.cyma-lca-doc-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:6px}
		.cyma-lca-doc-name{font-style:italic;color:#50575e}
	</style>';

	foreach ( $fields as $key => $field ) {
		echo '<div class="cyma-lca-field">';
		echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label>';
		printf(
			'<textarea id="%1$s" name="%1$s" rows="%2$d" placeholder="%3$s">%4$s</textarea>',
			esc_attr( $key ),
			(int) $field['rows'],
			esc_attr( $field['placeholder'] ),
			esc_textarea( $field['value'] )
		);
		echo '</div>';
	}

	echo '<div class="cyma-lca-field">';
	echo '<label>Download document</label>';
	echo '<p class="description" style="margin:0 0 8px">Prefer Media Library upload for new files. Theme filename is a fallback for files already in the theme folder.</p>';

	echo '<input type="hidden" id="_lca_doc_attachment_id" name="_lca_doc_attachment_id" value="' . esc_attr( (string) $attach_id ) . '">';
	echo '<div class="cyma-lca-doc-row">';
	echo '<button type="button" class="button" id="cyma-lca-select-doc">Select / upload file</button>';
	echo '<button type="button" class="button" id="cyma-lca-clear-doc"' . ( $attach_id ? '' : ' style="display:none"' ) . '>Clear attachment</button>';
	echo '<span class="cyma-lca-doc-name" id="cyma-lca-doc-name">';
	if ( $attach_id && $attach_name ) {
		echo esc_html( $attach_name );
		if ( $attach_url ) {
			echo ' — <a href="' . esc_url( $attach_url ) . '" target="_blank" rel="noopener noreferrer">view</a>';
		}
	} else {
		echo 'No media attachment selected';
	}
	echo '</span></div>';

	echo '<p style="margin:12px 0 4px"><label for="_lca_doc_theme_file"><strong>Theme docs filename</strong> (optional fallback)</label></p>';
	echo '<input type="text" id="_lca_doc_theme_file" name="_lca_doc_theme_file" value="' . esc_attr( $theme_file ) . '" placeholder="e.g. Cyma LCA Posting Name.doc" style="max-width:480px">';
	echo '<p class="description">File must exist under <code>wp-content/themes/cyma-prod-v2/assets/docs/lca/</code>.</p>';
	echo '</div>';
}

/**
 * Enqueue media picker on LCA edit screens.
 *
 * @param string $hook_suffix Admin page hook.
 */
function cyma_lca_admin_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || CYMA_LCA_CPT !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_add_inline_script(
		'jquery',
		"(function($){
			$(function(){
				var frame;
				$('#cyma-lca-select-doc').on('click', function(e){
					e.preventDefault();
					if (frame) { frame.open(); return; }
					frame = wp.media({
						title: 'Select LCA document',
						button: { text: 'Use this file' },
						multiple: false
					});
					frame.on('select', function(){
						var att = frame.state().get('selection').first().toJSON();
						$('#_lca_doc_attachment_id').val(att.id);
						var label = att.filename || att.title || ('Attachment #' + att.id);
						var html = $('<span/>').text(label).html();
						if (att.url) {
							html += ' — <a href=\"' + att.url + '\" target=\"_blank\" rel=\"noopener noreferrer\">view</a>';
						}
						$('#cyma-lca-doc-name').html(html);
						$('#cyma-lca-clear-doc').show();
					});
					frame.open();
				});
				$('#cyma-lca-clear-doc').on('click', function(e){
					e.preventDefault();
					$('#_lca_doc_attachment_id').val('0');
					$('#cyma-lca-doc-name').text('No media attachment selected');
					$(this).hide();
				});
			});
		})(jQuery);"
	);
}
add_action( 'admin_enqueue_scripts', 'cyma_lca_admin_assets' );

/**
 * Save LCA meta.
 *
 * @param int $post_id Post ID.
 */
function cyma_lca_save_meta( $post_id ) {
	if ( ! isset( $_POST['cyma_lca_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cyma_lca_meta_nonce'] ) ), 'cyma_lca_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( get_post_type( $post_id ) !== CYMA_LCA_CPT ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_keys = array( '_lca_role', '_lca_wage', '_lca_period', '_lca_location' );
	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	if ( isset( $_POST['_lca_doc_theme_file'] ) ) {
		// Keep basename only; allow spaces in original filenames.
		$raw = wp_unslash( $_POST['_lca_doc_theme_file'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$raw = is_string( $raw ) ? basename( trim( $raw ) ) : '';
		$raw = str_replace( array( '..', '/', '\\' ), '', $raw );
		update_post_meta( $post_id, '_lca_doc_theme_file', $raw );
	}

	if ( isset( $_POST['_lca_doc_attachment_id'] ) ) {
		update_post_meta( $post_id, '_lca_doc_attachment_id', absint( $_POST['_lca_doc_attachment_id'] ) );
	}
}
add_action( 'save_post_' . CYMA_LCA_CPT, 'cyma_lca_save_meta' );

/**
 * Admin list columns.
 *
 * @param array<string,string> $columns Columns.
 * @return array<string,string>
 */
function cyma_lca_admin_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['lca_order'] = 'Order';
			$new['lca_doc']   = 'Document';
		}
	}
	return $new;
}
add_filter( 'manage_' . CYMA_LCA_CPT . '_posts_columns', 'cyma_lca_admin_columns' );

/**
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function cyma_lca_admin_column_content( $column, $post_id ) {
	if ( 'lca_order' === $column ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
		return;
	}
	if ( 'lca_doc' === $column ) {
		$doc = cyma_lca_resolve_doc(
			(int) get_post_meta( $post_id, '_lca_doc_attachment_id', true ),
			(string) get_post_meta( $post_id, '_lca_doc_theme_file', true )
		);
		echo $doc['label'] !== '' ? esc_html( $doc['label'] ) : '—';
	}
}
add_action( 'manage_' . CYMA_LCA_CPT . '_posts_custom_column', 'cyma_lca_admin_column_content', 10, 2 );

/**
 * Make Order column sortable.
 *
 * @param array<string,string> $columns Columns.
 * @return array<string,string>
 */
function cyma_lca_sortable_columns( $columns ) {
	$columns['lca_order'] = 'menu_order';
	return $columns;
}
add_filter( 'manage_edit-' . CYMA_LCA_CPT . '_sortable_columns', 'cyma_lca_sortable_columns' );

/**
 * Seed default postings once (theme activation or first admin load).
 */
function cyma_lca_seed_postings() {
	if ( get_option( CYMA_LCA_SEED_OPTION ) ) {
		return;
	}

	$existing = get_posts(
		array(
			'post_type'      => CYMA_LCA_CPT,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $existing ) ) {
		update_option( CYMA_LCA_SEED_OPTION, 1, false );
		return;
	}

	foreach ( cyma_lca_default_postings() as $row ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => CYMA_LCA_CPT,
				'post_status' => 'publish',
				'post_title'  => $row['title'],
				'menu_order'  => (int) $row['menu_order'],
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}
		update_post_meta( $post_id, '_lca_role', $row['role'] );
		update_post_meta( $post_id, '_lca_wage', $row['wage'] );
		update_post_meta( $post_id, '_lca_period', $row['period'] );
		update_post_meta( $post_id, '_lca_location', $row['location'] );
		update_post_meta( $post_id, '_lca_doc_theme_file', $row['doc_file'] );
		update_post_meta( $post_id, '_lca_doc_attachment_id', 0 );
	}

	update_option( CYMA_LCA_SEED_OPTION, 1, false );
}
add_action( 'after_switch_theme', 'cyma_lca_seed_postings' );
add_action( 'admin_init', 'cyma_lca_seed_postings' );

/**
 * Admin notice pointing editors to the CPT.
 */
function cyma_lca_admin_notice() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || CYMA_LCA_CPT !== $screen->post_type ) {
		return;
	}
	if ( 'edit' !== $screen->base ) {
		return;
	}
	echo '<div class="notice notice-info"><p>';
	echo esc_html( 'These postings appear on the public H-1B LCA page (/h1b-lca/). Add, edit, reorder (Order), or trash items here — no PHP edits required.' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'cyma_lca_admin_notice' );

/**
 * Shortcode for dynamic LCA postings inside CMS page content.
 *
 * @return string
 */
function cyma_lca_postings_shortcode() {
	ob_start();
	get_template_part( 'template-parts/content/lca-postings' );
	return (string) ob_get_clean();
}
add_shortcode( 'cyma_lca_postings', 'cyma_lca_postings_shortcode' );

/**
 * Replace baked-in LCA posting HTML with live shortcode output.
 *
 * @param string $content Page HTML.
 * @param string $replacement Replacement HTML or shortcode tag.
 * @return string
 */
function cyma_lca_replace_static_blocks( $content, $replacement ) {
	if ( ! is_string( $content ) || $content === '' ) {
		return $content;
	}

	// Prefer replacing an existing shortcode token if present without baked blocks.
	if ( false !== strpos( $content, '[cyma_lca_postings]' ) && false === stripos( $content, 'bg-lca' ) ) {
		return $content;
	}

	if ( ! preg_match_all( '/<div\b[^>]*style="[^"]*bg-lca\d+\.webp[^"]*"[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
		if ( false !== strpos( $content, '[cyma_lca_postings]' ) ) {
			return $content;
		}
		return $content;
	}

	$first_start = (int) $matches[0][0][1];
	$last_open   = $matches[0][ count( $matches[0] ) - 1 ];
	$pos         = (int) $last_open[1] + strlen( $last_open[0] );
	$depth       = 1;
	$len         = strlen( $content );

	while ( $pos < $len && $depth > 0 ) {
		$next_open  = stripos( $content, '<div', $pos );
		$next_close = stripos( $content, '</div>', $pos );
		if ( false === $next_close ) {
			return $content;
		}
		if ( false !== $next_open && $next_open < $next_close ) {
			++$depth;
			$pos = $next_open + 4;
			continue;
		}
		--$depth;
		$pos = $next_close + 6;
	}

	if ( 0 !== $depth ) {
		return $content;
	}

	return substr( $content, 0, $first_start ) . $replacement . substr( $content, $pos );
}

/**
 * On the H-1B LCA CMS page, swap static posting markup for live CPT output.
 *
 * @param string $content Content.
 * @return string
 */
function cyma_replace_lca_postings_in_cms( $content ) {
	if ( ! is_page( 'h1b-lca' ) || ! is_string( $content ) || $content === '' ) {
		return $content;
	}
	if ( false === stripos( $content, 'bg-lca' ) && false === strpos( $content, '[cyma_lca_postings]' ) ) {
		return $content;
	}

	$dynamic = do_shortcode( '[cyma_lca_postings]' );
	if ( $dynamic === '' ) {
		return $content;
	}

	return cyma_lca_replace_static_blocks( $content, $dynamic );
}
add_filter( 'the_content', 'cyma_replace_lca_postings_in_cms', 12 );

/**
 * Front-end posting list: CPT posts when available, else hardcoded defaults.
 *
 * @return array<int, array<string, string>>
 */
function cyma_get_lca_postings() {
	$posts = get_posts(
		array(
			'post_type'      => CYMA_LCA_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		)
	);

	if ( ! empty( $posts ) ) {
		$out = array();
		foreach ( $posts as $post ) {
			$doc = cyma_lca_resolve_doc(
				(int) get_post_meta( $post->ID, '_lca_doc_attachment_id', true ),
				(string) get_post_meta( $post->ID, '_lca_doc_theme_file', true )
			);
			$out[] = array(
				'title'     => get_the_title( $post ),
				'role'      => (string) get_post_meta( $post->ID, '_lca_role', true ),
				'wage'      => (string) get_post_meta( $post->ID, '_lca_wage', true ),
				'period'    => (string) get_post_meta( $post->ID, '_lca_period', true ),
				'location'  => (string) get_post_meta( $post->ID, '_lca_location', true ),
				'doc'       => $doc['url'],
				'doc_label' => $doc['label'] !== '' ? $doc['label'] : 'LCA posting document',
			);
		}
		return $out;
	}

	// Fallback if CPT not seeded yet.
	$base = cyma_lca_docs_base_uri();
	$out  = array();
	foreach ( cyma_lca_default_postings() as $row ) {
		$out[] = array(
			'title'     => $row['title'],
			'role'      => $row['role'],
			'wage'      => $row['wage'],
			'period'    => $row['period'],
			'location'  => $row['location'],
			'doc'       => $base . rawurlencode( $row['doc_file'] ),
			'doc_label' => $row['doc_file'],
		);
	}
	return $out;
}

function cyma_lca_intro_text() {
	return 'H-1B nonimmigrant worker is being sought by Cyma Systems Inc through the filing of a labor condition application with the Employment and Training Administration of the U.S. Department of Labor.';
}

function cyma_lca_preamble_text() {
	return 'Pursuant to 20 CFR 655.734, you are hereby notified that H-1B nonimmigrants are being sought and that a Labor Condition Application (“LCA”) will be (or has been) filed for the following occupation:';
}

function cyma_lca_inspection_text() {
	return 'The Labor Condition Application is available for public inspection at the offices of Cyma Systems Inc, 360 Tolland Turnpike, Suite 2D, Manchester, CT 06042.';
}

function cyma_lca_complaint_text() {
	return 'Complaints alleging misrepresentation of material facts in the labor condition application and/or failure to comply with the terms of the labor condition application may be filed with any office of the wage and hour division of the United States Department of Labor.';
}
