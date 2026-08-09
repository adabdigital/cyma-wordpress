<?php
$articles = get_pages(
	array(
		'post_status' => 'publish',
		'sort_column' => 'post_date',
		'sort_order'  => 'DESC',
	)
);

$articles = array_values(
	array_filter(
		$articles,
		static function ( $page ) {
			return preg_match( '/^insights-\d+$/', $page->post_name );
		}
	)
);

if ( empty( $articles ) ) {
	$articles = get_posts(
		array(
			'post_type'      => 'insights',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

if ( empty( $articles ) ) {
	echo '<div class="w-dyn-empty"><div>No insights found.</div></div>';
	return;
}
?>
<div class="w-layout-grid grid-37" style="grid-row-gap:24px">
	<?php foreach ( $articles as $article ) : ?>
		<?php
		$title = get_the_title( $article );
		$url   = get_permalink( $article );
		$excerpt = '';
		if ( 'insights' === $article->post_type ) {
			$excerpt = cyma_get_cms_meta( 'sub-text', $article->ID );
			if ( $excerpt === '' ) {
				$excerpt = cyma_get_cms_meta( 'heading', $article->ID );
			}
		}
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
