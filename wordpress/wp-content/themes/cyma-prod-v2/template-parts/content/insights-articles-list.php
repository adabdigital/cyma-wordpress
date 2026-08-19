<?php
$cpt_articles = get_posts(
	array(
		'post_type'      => 'insights',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$cpt_articles = array_values(
	array_filter(
		$cpt_articles,
		static function ( $post ) {
			return trim( (string) $post->post_content ) !== '';
		}
	)
);

$articles = $cpt_articles;

if ( empty( $articles ) ) {
	$pages = get_pages(
		array(
			'post_status' => 'publish',
			'sort_column' => 'post_date',
			'sort_order'  => 'DESC',
		)
	);
	$articles = array_values(
		array_filter(
			$pages,
			static function ( $page ) {
				return preg_match( '/^insights-\d+$/', $page->post_name );
			}
		)
	);
}

if ( empty( $articles ) ) {
	echo '<div class="w-dyn-empty"><div>No insights found. Add posts under Insights in WordPress.</div></div>';
	return;
}
?>
<div class="w-layout-grid grid-37" style="grid-row-gap:24px">
	<?php foreach ( $articles as $article ) : ?>
		<?php
		$title   = get_the_title( $article );
		$url     = get_permalink( $article );
		$excerpt = '';
		$html    = (string) $article->post_content;
		if ( preg_match( '/<h1[^>]*heading-158[^>]*>(.*?)<\/h1>/is', $html, $hm ) ) {
			$from_h1 = trim( wp_strip_all_tags( $hm[1] ) );
			if ( $from_h1 !== '' && ( $title === '' || strcasecmp( $title, 'Insights' ) === 0 ) ) {
				$title = $from_h1;
			}
		}
		if ( 'insights' === $article->post_type ) {
			$excerpt = cyma_get_cms_meta( 'sub-text', $article->ID );
		}
		if ( $excerpt === '' && preg_match( '/<p[^>]*paragraph-38[^>]*>(.*?)<\/p>/is', $html, $pm ) ) {
			$excerpt = trim( wp_strip_all_tags( $pm[1] ) );
		}
		if ( $excerpt === '' ) {
			$excerpt = wp_trim_words( wp_strip_all_tags( $html ), 24 );
		}
		$excerpt = wp_trim_words( $excerpt, 28 );
		?>
		<div class="div-block-1300">
			<div class="text-block-632">News</div>
			<h2 class="heading-129"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h2>
			<?php if ( $excerpt ) : ?>
				<div class="text-block-633"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></div>
			<?php endif; ?>
			<a href="<?php echo esc_url( $url ); ?>" class="ct-cs w-inline-block">
				<div class="text-block-529-copy-copy-cs">Read More</div>
			</a>
		</div>
	<?php endforeach; ?>
</div>
