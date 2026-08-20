<?php
/**
 * One-shot repair: rewrite Insights nav links (data-link a207e37db) in CMS
 * post_content from /insights-2/ (article) to /insights/ (hub).
 *
 * Usage (WP-CLI / docker):
 *   wp eval-file wp-content/themes/cyma-prod-v2/inc/repair-insights-nav-links.php --allow-root
 *
 * Set CYMA_REPAIR_DRY_RUN=1 to preview without writing.
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param string $html Page HTML.
 * @return array{0:string,1:int} Updated HTML and replacement count.
 */
function cyma_repair_insights_nav_in_html( $html ) {
	$count = 0;
	$url   = home_url( '/insights/' );

	$updated = preg_replace_callback(
		'#<a\b([^>]*\bdata-link="a207e37db"[^>]*)>#i',
		static function ( $matches ) use ( $url, &$count ) {
			$attrs = $matches[1];
			$before = $attrs;
			if ( preg_match( '/\bhref="/i', $attrs ) ) {
				$attrs = preg_replace( '/\bhref="[^"]*"/i', 'href="' . esc_url( $url ) . '"', $attrs, 1 );
			} else {
				$attrs .= ' href="' . esc_url( $url ) . '"';
			}
			if ( $attrs !== $before ) {
				++$count;
			}
			return '<a' . $attrs . '>';
		},
		$html
	);

	if ( ! is_string( $updated ) ) {
		return array( $html, 0 );
	}

	return array( $updated, $count );
}

$dry_run = (string) getenv( 'CYMA_REPAIR_DRY_RUN' ) === '1';
$pages   = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	)
);

$changed = 0;
$total   = 0;

foreach ( $pages as $page ) {
	$html = (string) $page->post_content;
	if ( false === strpos( $html, 'a207e37db' ) ) {
		continue;
	}
	if ( false === strpos( $html, 'insights-2' ) && false === strpos( $html, 'page-insights-2' ) ) {
		continue;
	}

	list( $new_html, $n ) = cyma_repair_insights_nav_in_html( $html );
	if ( $n < 1 || $new_html === $html ) {
		continue;
	}

	$total += $n;
	++$changed;
	$label = $page->post_name ? $page->post_name : (string) $page->ID;
	echo "page {$label}: {$n} Insights nav link(s)\n";

	if ( $dry_run ) {
		continue;
	}

	kses_remove_filters();
	wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => $new_html,
		),
		true
	);
	kses_init_filters();
}

echo $dry_run
	? "Dry run: would update {$changed} page(s), {$total} link(s).\n"
	: "Updated {$changed} page(s), {$total} link(s).\n";
