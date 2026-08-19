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

	if ( ! is_string( $html ) ) {
		return '';
	}

	// Keep scripts out of post_content — they break when `<=` is parsed as HTML
	// and WordPress/browsers can dump the JS as visible text.
	$html = preg_replace( '#<script\b[^>]*>[\s\S]*?</script>#i', '', $html );

	// Store LCA postings as a shortcode so CPT edits stay live after seeding.
	if ( $post_obj && 'h1b-lca' === $post_obj->post_name && function_exists( 'cyma_lca_replace_static_blocks' ) ) {
		$html = cyma_lca_replace_static_blocks(
			$html,
			"\n<!-- wp:shortcode -->\n[cyma_lca_postings]\n<!-- /wp:shortcode -->\n"
		);
	}

	if ( $post_obj && 'case-studies' === $post_obj->post_name ) {
		$replaced = preg_replace(
			'#<section class="section-65">[\s\S]*?</section>#i',
			'<section class="section-65"><div class="w-layout-blockcontainer container-55 w-container"><div class="w-dyn-list">[cyma_case_studies]</div></div></section>',
			$html,
			1
		);
		if ( is_string( $replaced ) && $replaced !== '' ) {
			$html = $replaced;
		}
	}

	if ( $post_obj && 'insights' === $post_obj->post_name ) {
		$replaced = preg_replace(
			'#<section class="section-65">[\s\S]*?</section>#i',
			'<section class="section-65"><div class="w-layout-blockcontainer container-55 w-container">[cyma_insights_list]</div></section>',
			$html,
			1
		);
		if ( is_string( $replaced ) && $replaced !== '' ) {
			$html = $replaced;
		}
		if ( false === strpos( $html, '[cyma_insights_list]' ) ) {
			$html .= "\n[cyma_insights_list]\n";
		}
	}

	return $html;
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

	// Preserve <video>/<source> markup that WordPress kses would strip.
	kses_remove_filters();
	$result = wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $html,
		),
		true
	);
	kses_init_filters();

	if ( is_wp_error( $result ) ) {
		return false;
	}

	// Drop legacy per-field overrides; whole page is CMS now.
	delete_post_meta( $post_id, CYMA_CONTENT_META_KEY );
	update_post_meta( $post_id, CYMA_PAGE_SEEDED_META, time() );

	return true;
}

/**
 * Allow Webflow banner video markup in page content.
 */
function cyma_allow_video_html( $tags, $context ) {
	if ( 'post' !== $context ) {
		return $tags;
	}

	$tags['video'] = array(
		'id'               => true,
		'class'            => true,
		'style'            => true,
		'autoplay'         => true,
		'loop'             => true,
		'muted'            => true,
		'playsinline'      => true,
		'controls'         => true,
		'poster'           => true,
		'preload'          => true,
		'width'            => true,
		'height'           => true,
		'data-wf-ignore'   => true,
		'data-object-fit'  => true,
	);

	$tags['source'] = array(
		'src'            => true,
		'type'           => true,
		'media'          => true,
		'data-wf-ignore' => true,
		'data-iframe'    => true,
	);

	$form_attrs = array(
		'id'       => true,
		'class'    => true,
		'name'     => true,
		'type'     => true,
		'value'    => true,
		'placeholder' => true,
		'required' => true,
		'maxlength'=> true,
		'for'      => true,
		'action'   => true,
		'method'   => true,
		'enctype'  => true,
		'checked'  => true,
		'selected' => true,
		'disabled' => true,
		'readonly' => true,
		'autocomplete' => true,
		'data-name' => true,
		'data-wait' => true,
		'data-ajax-action' => true,
	);

	foreach ( array( 'form', 'input', 'textarea', 'select', 'option', 'label' ) as $tag ) {
		$tags[ $tag ] = isset( $tags[ $tag ] ) && is_array( $tags[ $tag ] )
			? array_merge( $tags[ $tag ], $form_attrs )
			: $form_attrs;
	}

	return $tags;
}
add_filter( 'wp_kses_allowed_html', 'cyma_allow_video_html', 10, 2 );

/**
 * Open roles shortcode — keeps careers listing dynamic inside CMS pages.
 */
function cyma_open_roles_shortcode() {
	ob_start();
	get_template_part( 'template-parts/content/careers-open-roles' );
	return ob_get_clean();
}
add_shortcode( 'cyma_open_roles', 'cyma_open_roles_shortcode' );

/**
 * Featured jobs slider shortcode — pulls published explore-careers posts.
 */
function cyma_featured_jobs_shortcode() {
	ob_start();
	get_template_part( 'template-parts/content/careers-featured-jobs' );
	return ob_get_clean();
}
add_shortcode( 'cyma_featured_jobs', 'cyma_featured_jobs_shortcode' );

function cyma_case_studies_shortcode() {
	ob_start();
	get_template_part( 'template-parts/content/case-studies-list' );
	return ob_get_clean();
}
add_shortcode( 'cyma_case_studies', 'cyma_case_studies_shortcode' );

function cyma_insights_list_shortcode() {
	ob_start();
	get_template_part( 'template-parts/content/insights-articles-list' );
	return ob_get_clean();
}
add_shortcode( 'cyma_insights_list', 'cyma_insights_list_shortcode' );

/**
 * Keep Case Studies listing live from the Case Studies CPT.
 * Runs before do_shortcode (priority 11) so the shortcode can expand.
 */
function cyma_replace_case_studies_list_in_cms( $content ) {
	if ( ! is_page( 'case-studies' ) || ! is_string( $content ) || $content === '' ) {
		return $content;
	}
	if ( false !== strpos( $content, '[cyma_case_studies]' ) ) {
		return $content;
	}

	$replaced = preg_replace(
		'#<section class="section-65">[\s\S]*?</section>#i',
		'<section class="section-65"><div class="w-layout-blockcontainer container-55 w-container"><div class="w-dyn-list">[cyma_case_studies]</div></div></section>',
		$content,
		1
	);

	return is_string( $replaced ) && $replaced !== '' ? $replaced : $content;
}

/**
 * Keep Insights hub listing live from Pages + Insights CPT.
 * Runs before do_shortcode (priority 11) so the shortcode can expand.
 */
function cyma_replace_insights_list_in_cms( $content ) {
	if ( ! is_page( 'insights' ) || ! is_string( $content ) || $content === '' ) {
		return $content;
	}
	if ( false !== strpos( $content, '[cyma_insights_list]' ) ) {
		return $content;
	}

	$replaced = preg_replace(
		'#<section class="section-65">[\s\S]*?</section>#i',
		'<section class="section-65"><div class="w-layout-blockcontainer container-55 w-container">[cyma_insights_list]</div></section>',
		$content,
		1
	);

	if ( is_string( $replaced ) && $replaced !== '' && $replaced !== $content ) {
		return $replaced;
	}

	return $content . "\n[cyma_insights_list]\n";
}

/**
 * On Job Seekers CMS HTML, replace the featured slider with live openings
 * plus the Dice CTA card (also injects the CTA when no slider is present).
 */
function cyma_replace_job_seekers_featured_slider( $content ) {
	if ( ! is_page( array( 'job-seekers', 'explore-careers' ) ) || ! is_string( $content ) || $content === '' ) {
		return $content;
	}

	$dynamic = do_shortcode( '[cyma_featured_jobs]' );
	if ( $dynamic === '' ) {
		return $content;
	}

	if ( false !== strpos( $content, 'cyma-featured-jobs-row' ) ) {
		$replaced = preg_replace(
			'#<div[^>]*class="[^"]*\bcyma-featured-jobs-row\b[^"]*"[^>]*>[\s\S]*?</div>(?=\s*</section>)#i',
			$dynamic,
			$content,
			1
		);
		return is_string( $replaced ) && $replaced !== '' ? $replaced : $content;
	}

	if ( false !== strpos( $content, 'slider-27' ) ) {
		$replaced = preg_replace(
			'#<div[^>]*class="[^"]*\bslider-27\b[^"]*"[^>]*>[\s\S]*?</div>\s*</div>(?=\s*</section>)#i',
			$dynamic,
			$content,
			1
		);
		return is_string( $replaced ) && $replaced !== '' ? $replaced : $content;
	}

	if ( false !== strpos( $content, 'section-28' ) || false !== strpos( $content, 'trusted-by-talent' ) ) {
		$replaced = preg_replace(
			'#(<section[^>]*(?:id="trusted-by-talent"|class="[^"]*\bsection-28\b)[^>]*>[\s\S]*?)(</section>)#i',
			'$1' . $dynamic . '$2',
			$content,
			1
		);
		return is_string( $replaced ) && $replaced !== '' ? $replaced : $content;
	}

	return $content;
}

/**
 * On explore-careers / job-seekers CMS HTML, point Explore Career CTAs at Dice.
 */
function cyma_explore_careers_scroll_anchor( $content ) {
	if ( ! is_page( array( 'job-seekers', 'explore-careers' ) ) || ! is_string( $content ) || $content === '' ) {
		return $content;
	}

	$dice_url = function_exists( 'cyma_get_dice_jobs_url' ) ? cyma_get_dice_jobs_url() : '';

	$content = preg_replace_callback(
		'#<a\b([^>]*\bclass="[^"]*"[^>]*)>#i',
		static function ( $matches ) use ( $dice_url ) {
			$attrs = $matches[1];
			if ( $dice_url && preg_match( '/\bhref="[^"]*dice\.com/i', $attrs ) ) {
				return $matches[0];
			}
			$class_attr = '';
			if ( preg_match( '/\bclass="([^"]*)"/i', $attrs, $cm ) ) {
				$class_attr = $cm[1];
			}
			$class_tokens = preg_split( '/\s+/', trim( $class_attr ) );
			$is_hero      = in_array( 'contact-btn-copy-js-btn', $class_tokens, true );
			$is_explore   = in_array( 'contact-btn-exploreinjobseeksers', $class_tokens, true )
				&& false !== strpos( $attrs, 'data-link="a2643837b"' );
			$is_find_more = in_array( 'contact-btn-copy-findmoreopp', $class_tokens, true );
			$is_high      = in_array( 'contact-btn-copy-js-btn-copy-highdemand', $class_tokens, true );
			$is_footer    = false !== strpos( $attrs, 'data-link="a2643837b"' );

			if ( ! $is_hero && ! $is_explore && ! $is_find_more && ! $is_high && ! $is_footer ) {
				return $matches[0];
			}

			if ( preg_match( '/\bhref="/i', $attrs ) ) {
				$attrs = preg_replace( '/\bhref="[^"]*"/i', 'href="' . esc_url( $dice_url ) . '"', $attrs, 1 );
			} else {
				$attrs .= ' href="' . esc_url( $dice_url ) . '"';
			}
			if ( false === stripos( $attrs, 'target=' ) ) {
				$attrs .= ' target="_blank" rel="noopener noreferrer"';
			}

			return '<a' . $attrs . '>';
		},
		$content
	);

	return is_string( $content ) ? $content : '';
}

/**
 * Home header Contact Us is a non-linked .contact pill — make it a CTA to /contact-us/.
 */
function cyma_link_home_contact_cta( $content ) {
	if ( ! is_front_page() || ! is_string( $content ) || $content === '' ) {
		return $content;
	}
	if ( false === strpos( $content, 'class="contact"' ) ) {
		return $content;
	}
	// Already a link (exact class token "contact", not contact-btn*).
	if ( preg_match( '/<a\b[^>]*\bclass="(?:[^"]*\s)?contact(?:\s[^"]*)?"/i', $content ) ) {
		return $content;
	}

	$url = home_url( '/contact-us/' );
	if ( function_exists( '_u' ) ) {
		$resolved = _u( 'a8559f6b', 'link' );
		if ( is_string( $resolved ) && $resolved !== '' && $resolved !== '#' ) {
			$url = $resolved;
		}
	}

	$replaced = preg_replace(
		'#<div(\s[^>]*\bclass="contact"[^>]*)>(\s*<div[^>]*\btext-block-440\b[^>]*>[\s\S]*?</div>\s*<img[^>]*\bcall-icon-blue\b[^>]*>\s*<img[^>]*\bcall-icon-dark\b[^>]*>\s*)</div>#i',
		'<a href="' . esc_url( $url ) . '"$1 data-link="a8559f6b">$2</a>',
		$content,
		1
	);

	return is_string( $replaced ) && $replaced !== '' ? $replaced : $content;
}

/**
 * Contact Us CMS HTML lost <form>/<input> to kses — inject a working form.
 */
function cyma_replace_contact_form( $content ) {
	if ( ! is_page( 'contact-us' ) || ! is_string( $content ) || $content === '' ) {
		return $content;
	}
	if ( false === strpos( $content, 'form-block-3-copy' ) && false === strpos( $content, 'Get in Touch' ) ) {
		return $content;
	}

	ob_start();
	get_template_part( 'template-parts/content/contact-form' );
	$form = ob_get_clean();
	if ( $form === '' ) {
		return $content;
	}

	// Replace from the first broken form wrapper through the last leftover .w-form block.
	$replaced = preg_replace(
		'#<div[^>]*id="Business-Form---Contact-Us"[^>]*>[\s\S]*?</div>\s*<div class="w-form">[\s\S]*?</div>(?=\s*</div>\s*</div>\s*</div>\s*</section>)#i',
		$form,
		$content,
		1
	);

	if ( ! is_string( $replaced ) || $replaced === $content ) {
		// Fallback: swap only the first form-block-3-copy section.
		$replaced = preg_replace(
			'#<div[^>]*class="[^"]*form-block-3-copy[^"]*"[^>]*>[\s\S]*?</div>\s*<div class="w-form">[\s\S]*?</div>#i',
			$form,
			$content,
			1
		);
	}

	return is_string( $replaced ) && $replaced !== '' ? $replaced : $content;
}

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
		add_filter( 'the_content', 'cyma_strip_inline_scripts_from_content', 8 );
		add_filter( 'the_content', 'cyma_fix_broken_sf_symbol_icons', 9 );
		add_filter( 'the_content', 'cyma_fix_broken_theme_1webp_urls', 10 );
		add_filter( 'the_content', 'cyma_replace_case_studies_list_in_cms', 10 );
		add_filter( 'the_content', 'cyma_replace_insights_list_in_cms', 10 );
		add_filter( 'the_content', 'cyma_replace_job_seekers_featured_slider', 12 );
		add_filter( 'the_content', 'cyma_replace_contact_form', 13 );
		add_filter( 'the_content', 'cyma_link_home_contact_cta', 14 );
		add_filter( 'the_content', 'cyma_explore_careers_scroll_anchor', 15 );
	}
}
add_action( 'wp', 'cyma_setup_cms_content_filters' );

/**
 * Never print raw page scripts via the_content (avoids visible JS text).
 */
function cyma_strip_inline_scripts_from_content( $content ) {
	return preg_replace( '#<script\b[^>]*>[\s\S]*?</script>#i', '', $content );
}

/**
 * Stale CMS HTML sometimes bakes image URLs as /wp-content/themes/1.webp
 * (theme folder + /assets/images/ path stripped during an earlier seed/replace).
 * Rewrite live using data-img keys from frontend-editor JSON.
 *
 * @param string $content HTML.
 * @return string
 */
function cyma_fix_broken_theme_1webp_urls( $content ) {
	if ( ! is_string( $content ) || false === strpos( $content, 'themes/1.webp' ) ) {
		return $content;
	}

	$post_id = get_queried_object_id();
	$slug    = function_exists( 'cyma_get_content_template_slug' ) ? cyma_get_content_template_slug( $post_id ) : '';
	if ( ! $slug ) {
		$post = get_post( $post_id );
		$slug = $post ? 'page-' . $post->post_name : '';
	}

	$map = array();
	$file = get_template_directory() . '/_data/frontend-editor/' . $slug . '.json';
	if ( file_exists( $file ) ) {
		$data = json_decode( (string) file_get_contents( $file ), true );
		if ( ! empty( $data['img'] ) && is_array( $data['img'] ) ) {
			foreach ( $data['img'] as $key => $img ) {
				if ( is_array( $img ) && ! empty( $img['src'] ) ) {
					$map[ $key ] = $img['src'];
				}
			}
		}
	}

	$theme = get_template_directory_uri();

	$content = preg_replace_callback(
		'#<img\b[^>]*>#i',
		function ( $matches ) use ( $map, $theme ) {
			$tag = $matches[0];
			if ( false === stripos( $tag, 'themes/1.webp' ) ) {
				return $tag;
			}
			if ( ! preg_match( '#\bdata-img=(["\'])([^"\']+)\1#i', $tag, $dm ) ) {
				return $tag;
			}
			$key = $dm[2];
			if ( empty( $map[ $key ] ) ) {
				return $tag;
			}
			$src = $map[ $key ];
			if ( ! preg_match( '#^https?://#i', $src ) ) {
				$src = $theme . $src;
			}
			$tag = preg_replace( '#\bsrc=(["\'])[^"\']*\1#i', 'src="' . esc_url( $src ) . '"', $tag, 1 );
			if ( preg_match( '#\bsrcset=#i', $tag ) ) {
				$tag = preg_replace( '#\bsrcset=(["\'])[^"\']*\1#i', 'srcset="' . esc_url( $src ) . '"', $tag, 1 );
			}
			return $tag;
		},
		$content
	);

	$section_bgs = array(
		'section-11' => '/assets/images/bg-industries.webp',
		'section-62' => '/assets/images/bg-notice.webp',
		'section-78' => '/assets/images/bg-insights1.webp',
		'section-33' => '/assets/images/bg-insights2.webp',
	);
	foreach ( $section_bgs as $section_class => $rel ) {
		$src     = $theme . $rel;
		$content = preg_replace_callback(
			'#(<section\b[^>]*\bclass=(["\'])(?:[^"\']*\s)?' . preg_quote( $section_class, '#' ) . '(?:\s[^"\']*)?\2[^>]*\bstyle=(["\'])[^"\']*?background-image\s*:\s*url\()(?:\\\\?&apos;|&apos;|\'|")https?://[^)"\']*?/wp-content/themes/1\.webp(?:\?[^)"\']*)?(?:\\\\?&apos;|&apos;|\'|")(\)[^"\']*\3[^>]*>)#i',
			function ( $m ) use ( $src ) {
				return $m[1] . '&apos;' . esc_url( $src ) . '&apos;' . $m[4];
			},
			$content
		);
	}

	return $content;
}

/**
 * Webflow exported SF Symbol icons with Unicode filenames (e.g. 􀯐.svg).
 * Those became "/assets/images/.svg" in the WP theme map — rewrite by class.
 */
function cyma_fix_broken_sf_symbol_icons( $content ) {
	if ( ! is_string( $content ) || false === strpos( $content, 'images/.svg' ) ) {
		return $content;
	}

	$map = array(
		'image-9-copy-1-copy-tailor-copy-sys1' => 'sf-symbol-sys-1.svg',
		'image-9-copy-2-copy-tail2-copysys2'   => 'sf-symbol-sys-2.svg',
		'image-9-copy-2-copy-tail2'            => 'sf-symbol-tailored-2.svg',
		'image-9-copy-3-copy-tail4'            => 'sf-symbol-product-3.svg',
		'image-9-copy-1'                      => 'sf-symbol-product-1.svg',
		'image-9-copy-2'                      => 'sf-symbol-product-2.svg',
		'image-9-copy-3'                      => 'sf-symbol-product-3.svg',
		'sys3'                                => 'sf-symbol-sys-3.svg',
	);

	$base = trailingslashit( get_template_directory_uri() ) . 'assets/images/';

	return preg_replace_callback(
		'/<img\b[^>]*>/i',
		function ( $matches ) use ( $map, $base ) {
			$tag = $matches[0];
			if ( false === strpos( $tag, 'images/.svg' ) ) {
				return $tag;
			}
			if ( ! preg_match( '/\bclass="([^"]*)"/i', $tag, $class_match ) ) {
				return $tag;
			}
			$classes = preg_split( '/\s+/', trim( $class_match[1] ) );
			foreach ( $map as $class => $file ) {
				if ( in_array( $class, $classes, true ) ) {
					return preg_replace(
						'#src="[^"]*images/\.svg"#i',
						'src="' . esc_url( $base . $file ) . '"',
						$tag,
						1
					);
				}
			}
			return $tag;
		},
		$content
	);
}

function cyma_cms_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	if ( 'page' === $screen->id ) {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $post_id || ! cyma_page_has_design_template( $post_id ) ) {
			return;
		}
		echo '<div class="notice notice-info"><p><strong>CYMA page:</strong> Edit the <em>entire page</em> in the content editor below (HTML). Header/footer chrome and styles still come from the theme. Use <strong>Tools → CYMA Content Seed</strong> to re-import design HTML if needed.</p></div>';
		return;
	}

	$cpt_hints = array(
		'casestudies'      => 'Edit this case study’s title, featured image, details, and body. It appears on the Case Studies page automatically.',
		'insights'         => 'Edit this article’s title, excerpt, featured image, and body. It appears on Insights automatically.',
		'explore-careers'  => 'Edit this role’s title, location, type, skills, and description. Featured openings on Job Seekers pull from published Careers posts.',
		'lca_posting'      => 'Edit this LCA posting’s details and document. It appears on the H-1B LCA page automatically.',
	);
	if ( isset( $cpt_hints[ $screen->id ] ) ) {
		echo '<div class="notice notice-info"><p><strong>CYMA CMS:</strong> ' . esc_html( $cpt_hints[ $screen->id ] ) . '</p></div>';
	}
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
