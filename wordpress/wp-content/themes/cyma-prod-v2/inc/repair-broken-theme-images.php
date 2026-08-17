<?php
/**
 * One-shot repair: replace baked CMS URLs pointing at
 * /wp-content/themes/1.webp with the correct theme asset from
 * _data/frontend-editor JSON (matched via data-img keys).
 *
 * Usage (WP-CLI / docker):
 *   wp eval-file wp-content/themes/cyma-prod-v2/inc/repair-broken-theme-images.php
 *   # or: php -r 'require "wp-load.php"; require ".../repair-broken-theme-images.php";'
 *
 * Set CYMA_REPAIR_DRY_RUN=1 to preview without writing.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build data-img key => relative src map from frontend-editor JSON.
 *
 * @param string $slug Template / page slug.
 * @return array<string,string>
 */
function cyma_repair_img_map_for_slug( $slug ) {
	$editor_dir = get_template_directory() . '/_data/frontend-editor';
	$candidates = array( $slug . '.json' );
	if ( strpos( $slug, 'page-' ) !== 0 && $slug !== 'front-page' ) {
		$candidates[] = 'page-' . $slug . '.json';
	}
	if ( $slug === 'front-page' || $slug === 'home' ) {
		$candidates[] = 'front-page.json';
	}

	$map = array();
	foreach ( array_unique( $candidates ) as $file ) {
		$path = $editor_dir . '/' . $file;
		if ( ! file_exists( $path ) ) {
			continue;
		}
		$data = json_decode( (string) file_get_contents( $path ), true );
		if ( empty( $data['img'] ) || ! is_array( $data['img'] ) ) {
			continue;
		}
		foreach ( $data['img'] as $key => $img ) {
			if ( is_array( $img ) && ! empty( $img['src'] ) ) {
				$map[ $key ] = $img['src'];
			}
		}
	}

	return $map;
}

/**
 * Absolute theme asset URL from a relative /assets/... path.
 *
 * @param string $src Relative or absolute.
 * @return string
 */
function cyma_repair_abs_theme_src( $src ) {
	if ( preg_match( '#^https?://#i', $src ) ) {
		return $src;
	}
	return get_template_directory_uri() . $src;
}

/**
 * Replace broken themes/1.webp img src/srcset using data-img keys.
 *
 * @param string               $html HTML.
 * @param array<string,string> $map  data-img => relative src.
 * @return array{0:string,1:int} [html, fix_count]
 */
function cyma_repair_1webp_in_html( $html, $map ) {
	$fixed = 0;
	$html  = preg_replace_callback(
		'#<img\b[^>]*>#i',
		function ( $m ) use ( $map, &$fixed ) {
			$tag = $m[0];
			if ( stripos( $tag, 'themes/1.webp' ) === false ) {
				return $tag;
			}
			if ( ! preg_match( '#\bdata-img=(["\'])([^"\']+)\1#i', $tag, $dm ) ) {
				return $tag;
			}
			$key = $dm[2];
			if ( empty( $map[ $key ] ) ) {
				return $tag;
			}
			$src = cyma_repair_abs_theme_src( $map[ $key ] );
			$tag = preg_replace( '#\bsrc=(["\'])[^"\']*\1#i', 'src="' . esc_url( $src ) . '"', $tag, 1 );
			if ( preg_match( '#\bsrcset=#i', $tag ) ) {
				$tag = preg_replace( '#\bsrcset=(["\'])[^"\']*\1#i', 'srcset="' . esc_url( $src ) . '"', $tag, 1 );
			}
			$fixed++;
			return $tag;
		},
		$html
	);

	return array( $html, $fixed );
}

/**
 * Fix known background-image placeholders that lost their asset path.
 * Matches section class → canonical theme WebP used in design templates.
 *
 * @param string               $html HTML.
 * @param array<string,string> $map  Image map (unused; kept for call-site compat).
 * @param string               $slug Page slug.
 * @return array{0:string,1:int}
 */
function cyma_repair_1webp_backgrounds( $html, $map, $slug ) {
	unset( $map );
	$fixed = 0;
	$theme = get_template_directory_uri();

	// section-* class => relative asset (from design PHP templates).
	$section_bgs = array(
		'section-11' => '/assets/images/bg-industries.webp',
		'section-62' => '/assets/images/bg-notice.webp',
		'section-78' => '/assets/images/bg-insights1.webp',
		'section-33' => '/assets/images/bg-insights2.webp',
	);

	foreach ( $section_bgs as $section_class => $rel ) {
		$src  = $theme . $rel;
		$html = preg_replace_callback(
			'#(<section\b[^>]*\bclass=(["\'])(?:[^"\']*\s)?' . preg_quote( $section_class, '#' ) . '(?:\s[^"\']*)?\2[^>]*\bstyle=(["\'])[^"\']*?background-image\s*:\s*url\()(?:\\\\?&apos;|\'|")https?://[^)"\']*?/wp-content/themes/1\.webp(?:\?[^)"\']*)?(?:\\\\?&apos;|\'|")(\)[^"\']*\3[^>]*>)#i',
			function ( $m ) use ( $src, &$fixed ) {
				$fixed++;
				return $m[1] . '&apos;' . esc_url( $src ) . '&apos;' . $m[4];
			},
			$html
		);
	}

	// Any remaining bare themes/1.webp in url(...) — last-resort by page slug.
	$slug_fallback = array(
		'industries'         => '/assets/images/bg-industries.webp',
		'notice-of-filing'   => '/assets/images/bg-notice.webp',
		'insights-2'         => '/assets/images/bg-insights1.webp',
		'h1b-lca'            => '/assets/images/bg-notice.webp',
	);
	$rel = isset( $slug_fallback[ $slug ] ) ? $slug_fallback[ $slug ] : '';
	if ( $rel && strpos( $html, 'themes/1.webp' ) !== false ) {
		$src  = $theme . $rel;
		$html = preg_replace_callback(
			'#url\((\\\\?&apos;|&apos;|\'|")https?://[^)]*?/wp-content/themes/1\.webp(?:\?[^)]*)?\1\)#i',
			function ( $m ) use ( $src, &$fixed ) {
				$fixed++;
				$q = $m[1];
				if ( $q === '\\&apos;' || $q === '&apos;' ) {
					return "url(&apos;" . esc_url( $src ) . "&apos;)";
				}
				return 'url(' . $q . esc_url( $src ) . $q . ')';
			},
			$html
		);
	}

	return array( $html, $fixed );
}

/**
 * Run repair across published pages that contain themes/1.webp.
 *
 * @param bool $dry_run Preview only.
 * @return array{pages:int,img_fixes:int,bg_fixes:int,leftover_pages:int,details:array<int,array>}
 */
function cyma_run_broken_theme_image_repair( $dry_run = true ) {
	global $wpdb;

	$ids = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts}
		WHERE post_type = 'page' AND post_status = 'publish'
		AND post_content LIKE '%themes/1.webp%'"
	);

	$report = array(
		'pages'          => count( $ids ),
		'img_fixes'      => 0,
		'bg_fixes'       => 0,
		'leftover_pages' => 0,
		'details'        => array(),
	);

	foreach ( $ids as $id ) {
		$id   = (int) $id;
		$post = get_post( $id );
		if ( ! $post ) {
			continue;
		}

		$slug = $post->post_name;
		$tpl  = function_exists( 'cyma_get_content_template_slug' ) ? cyma_get_content_template_slug( $id ) : '';
		$map  = cyma_repair_img_map_for_slug( $tpl ? $tpl : $slug );
		if ( ! $map ) {
			$map = cyma_repair_img_map_for_slug( 'page-' . $slug );
		}

		$html = $post->post_content;
		list( $html, $img_n ) = cyma_repair_1webp_in_html( $html, $map );
		list( $html, $bg_n )  = cyma_repair_1webp_backgrounds( $html, $map, $slug );

		$left = substr_count( $html, 'themes/1.webp' );
		$report['img_fixes'] += $img_n;
		$report['bg_fixes']  += $bg_n;
		if ( $left > 0 ) {
			$report['leftover_pages']++;
		}

		$report['details'][ $id ] = array(
			'slug'     => $slug,
			'map'      => count( $map ),
			'img'      => $img_n,
			'bg'       => $bg_n,
			'leftover' => $left,
		);

		if ( ! $dry_run && ( $img_n > 0 || $bg_n > 0 ) ) {
			kses_remove_filters();
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => $html,
				),
				true
			);
			kses_init_filters();
		}
	}

	return $report;
}

// CLI only when explicitly requested (avoids mutating content on accidental include).
//   CYMA_REPAIR_RUN=1 CYMA_REPAIR_APPLY=1 php -r 'require "wp-load.php"; require ".../repair-broken-theme-images.php";'
if ( getenv( 'CYMA_REPAIR_RUN' ) === '1' || ( isset( $_SERVER['CYMA_REPAIR_RUN'] ) && (string) $_SERVER['CYMA_REPAIR_RUN'] === '1' ) ) {
	$dry = ! ( getenv( 'CYMA_REPAIR_APPLY' ) === '1' || ( isset( $_SERVER['CYMA_REPAIR_APPLY'] ) && (string) $_SERVER['CYMA_REPAIR_APPLY'] === '1' ) );
	$report = cyma_run_broken_theme_image_repair( $dry );
	echo $dry ? "DRY RUN\n" : "APPLIED\n";
	echo 'pages=' . $report['pages'] . ' img_fixes=' . $report['img_fixes'] . ' bg_fixes=' . $report['bg_fixes'] . ' leftover_pages=' . $report['leftover_pages'] . "\n";
	foreach ( $report['details'] as $id => $d ) {
		echo $id . ' ' . $d['slug'] . ' map=' . $d['map'] . ' img=' . $d['img'] . ' bg=' . $d['bg'] . ' leftover=' . $d['leftover'] . "\n";
	}
}
