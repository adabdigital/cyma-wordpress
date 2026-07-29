<?php
/**
 * CYMA whole-page CMS.
 *
 * Each page body is stored in WordPress post_content and edited as one document
 * in the classic editor. Design PHP/JSON templates are used only as a fallback
 * and as the source when seeding content into the CMS.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CYMA_CONTENT_META_KEY', '_cyma_content_overrides' ); // legacy field overrides
define( 'CYMA_PAGE_SEEDED_META', '_cyma_page_content_seeded' );

/**
 * Map a WordPress page to its frontend-editor / template-part slug.
 */
function cyma_get_page_data_slug( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_queried_object_id();
	}
	if ( ! $post_id ) {
		return '';
	}

	$post = get_post( $post_id );
	if ( ! $post || 'page' !== $post->post_type ) {
		return '';
	}

	if ( (int) get_option( 'page_on_front' ) === (int) $post_id ) {
		return 'front-page';
	}

	$slug = $post->post_name;

	if ( 'explore-careers' === $slug ) {
		return 'page-job-seekers';
	}

	$candidates = array(
		'page-' . $slug,
		$slug,
	);

	foreach ( $candidates as $candidate ) {
		$file = get_template_directory() . '/_data/frontend-editor/' . $candidate . '.json';
		if ( file_exists( $file ) ) {
			return $candidate;
		}
		if ( locate_template( 'template-parts/content/' . $candidate . '.php' ) ) {
			return $candidate;
		}
	}

	return '';
}

function cyma_page_has_design_template( $post_id ) {
	return (bool) cyma_get_page_data_slug( $post_id );
}

/**
 * Content template part slug for a page (may differ from data slug).
 */
function cyma_get_content_template_slug( $post_id ) {
	$slug = cyma_get_page_data_slug( $post_id );
	return $slug ? $slug : '';
}

/**
 * Whether this page should use CMS post_content on the front-end.
 * Only after an explicit CYMA seed/save — never for leftover default WP text.
 */
function cyma_page_uses_cms_content( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$post = get_post( $post_id );
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}

	$content = trim( (string) $post->post_content );
	if ( $content === '' ) {
		return false;
	}

	// Ignore classic WordPress starter copy if it somehow landed in a page.
	if ( stripos( $content, 'Welcome to WordPress. This is your first post.' ) !== false ) {
		return false;
	}

	// Prefer design templates until content was intentionally seeded/managed.
	if ( ! get_post_meta( $post_id, CYMA_PAGE_SEEDED_META, true ) ) {
		// Still allow CMS HTML that clearly came from the CYMA design.
		if ( strpos( $content, 'w-nav' ) === false && strpos( $content, 'section-' ) === false ) {
			return false;
		}
	}

	return true;
}

/**
 * Render the page body from WordPress CMS, or fall back to the design template.
 *
 * @param string $fallback_slug Template part slug, e.g. front-page or page-about-us.
 */
function cyma_the_page_content( $fallback_slug = '' ) {
	$post_id = get_the_ID();

	if ( cyma_page_uses_cms_content( $post_id ) ) {
		echo '<div class="cyma-cms-page">';
		the_content();
		echo '</div>';
		return;
	}

	if ( ! $fallback_slug ) {
		$fallback_slug = cyma_get_content_template_slug( $post_id );
	}

	if ( $fallback_slug && locate_template( 'template-parts/content/' . $fallback_slug . '.php' ) ) {
		get_template_part( 'template-parts/content/' . $fallback_slug );
		return;
	}

	the_content();
}

/**
 * Render design template HTML for seeding (no legacy field overrides).
 */
function cyma_render_design_html( $post_id ) {
	$slug = cyma_get_content_template_slug( $post_id );
	if ( ! $slug ) {
		return '';
	}

	global $current_page_data, $post;
	$previous_data = $current_page_data;
	$previous_post = $post;

	$post = get_post( $post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );

	$current_page_data = get_page_data( $slug );

	ob_start();
	$template = locate_template( 'template-parts/content/' . $slug . '.php' );
	if ( $template ) {
		include $template;
	}

	$post_obj = get_post( $post_id );
	if ( $post_obj && 'explore-careers' === $post_obj->post_name ) {
		echo "\n\n<!-- wp:shortcode -->\n[cyma_open_roles]\n<!-- /wp:shortcode -->\n";
	}

	$html = ob_get_clean();

	wp_reset_postdata();
	$current_page_data = $previous_data;
	$post              = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	return is_string( $html ) ? $html : '';
}

/**
 * Seed a page's post_content from the design template.
 *
 * @param bool $force Overwrite existing CMS content.
 */
function cyma_seed_page_content( $post_id, $force = false ) {
	$post = get_post( $post_id );
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}

	if ( ! cyma_page_has_design_template( $post_id ) ) {
		return false;
	}

	$existing = trim( (string) $post->post_content );
	if ( $existing !== '' && ! $force ) {
		return false;
	}

	$html = cyma_render_design_html( $post_id );
	if ( $html === '' ) {
		return false;
	}

	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $html,
		)
	);

	// Drop legacy per-field overrides; whole page is CMS now.
	delete_post_meta( $post_id, CYMA_CONTENT_META_KEY );
	update_post_meta( $post_id, CYMA_PAGE_SEEDED_META, time() );

	return true;
}

/**
 * Open roles shortcode — keeps careers listing dynamic inside CMS pages.
 */
function cyma_open_roles_shortcode() {
	ob_start();
	get_template_part( 'template-parts/content/careers-open-roles' );
	return ob_get_clean();
}
add_shortcode( 'cyma_open_roles', 'cyma_open_roles_shortcode' );

/* -------------------------------------------------------------------------- */
/* Editor experience: classic editor, one document per page                   */
/* -------------------------------------------------------------------------- */

function cyma_use_classic_editor_for_pages( $use_block_editor, $post_type ) {
	if ( 'page' === $post_type ) {
		return false;
	}
	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'cyma_use_classic_editor_for_pages', 10, 2 );

function cyma_disable_wpautop_on_cms_pages( $content ) {
	if ( is_page() && cyma_page_uses_cms_content() ) {
		return $content;
	}
	return $content;
}

function cyma_setup_cms_content_filters() {
	if ( ! is_page() ) {
		return;
	}
	if ( cyma_page_uses_cms_content( get_queried_object_id() ) ) {
		remove_filter( 'the_content', 'wpautop' );
	}
}
add_action( 'wp', 'cyma_setup_cms_content_filters' );

function cyma_cms_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->id ) {
		return;
	}

	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! cyma_page_has_design_template( $post_id ) ) {
		return;
	}

	echo '<div class="notice notice-info"><p><strong>CYMA page:</strong> Edit the <em>entire page</em> in the content editor below (HTML). Header/footer chrome and styles still come from the theme. Use <strong>Tools → CYMA Content Seed</strong> to re-import design HTML if needed.</p></div>';
}
add_action( 'admin_notices', 'cyma_cms_admin_notice' );

function cyma_register_seed_tools_page() {
	add_management_page(
		'CYMA Content Seed',
		'CYMA Content Seed',
		'manage_options',
		'cyma-content-seed',
		'cyma_render_seed_tools_page'
	);
}
add_action( 'admin_menu', 'cyma_register_seed_tools_page' );

function cyma_render_seed_tools_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['cyma_seed_all'] ) && check_admin_referer( 'cyma_seed_all_pages' ) ) {
		$force = ! empty( $_POST['cyma_seed_force'] );
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$count = 0;
		foreach ( $pages as $page_id ) {
			if ( cyma_seed_page_content( $page_id, $force ) ) {
				$count++;
			}
		}
		$message = sprintf( 'Imported design HTML into WordPress for %d page(s).', $count );
	}
	?>
	<div class="wrap">
		<h1>CYMA Content Seed</h1>
		<?php if ( $message ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>
		<p>Copy each page’s full design HTML into the WordPress page editor so the <strong>entire page</strong> is CMS-editable (not individual fields).</p>
		<form method="post">
			<?php wp_nonce_field( 'cyma_seed_all_pages' ); ?>
			<label>
				<input type="checkbox" name="cyma_seed_force" value="1" />
				Overwrite existing page content in WordPress
			</label>
			<p class="submit">
				<button type="submit" name="cyma_seed_all" class="button button-primary" value="1">Seed all CYMA pages</button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Legacy no-ops kept so older calls do not fatally error during transition.
 */
function cyma_get_content_overrides( $post_id ) {
	return array();
}

function cyma_apply_content_overrides_to_page_data( $page_slug = '' ) {
	// Field-level overrides removed — whole page is CMS-backed via post_content.
}

function cyma_normalize_image_data( $img_data ) {
	if ( ! is_array( $img_data ) ) {
		return (object) array( 'src' => '', 'alt' => '', 'srcset' => '' );
	}

	$alt = isset( $img_data['alt'] ) ? $img_data['alt'] : '';
	$src = '';

	if ( ! empty( $img_data['attachment_id'] ) ) {
		$attachment_id = (int) $img_data['attachment_id'];
		$src           = wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( ! $alt ) {
			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		}
		if ( ! $src ) {
			$src = '';
		}
	} elseif ( ! empty( $img_data['src'] ) ) {
		$path = $img_data['src'];
		if ( preg_match( '#^https?://#i', $path ) ) {
			$src = $path;
		} else {
			$src = get_template_directory_uri() . $path;
		}
	}

	return (object) array(
		'src'    => $src,
		'alt'    => $alt,
		'srcset' => $src,
	);
}
