<?php
$technology_terms = array(
	'ai',
	'artificial intelligence',
	'cloud',
	'cybersecurity',
	'data',
	'digital',
	'devops',
	'machine learning',
	'software',
	'technology',
	'tech',
);

$news_posts = get_posts(
	array(
		'post_type'      => 'insights',
		'posts_per_page' => 12,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$news_posts = array_values(
	array_filter(
		$news_posts,
		static function ( $post ) use ( $technology_terms ) {
			$content = strtolower( wp_strip_all_tags( $post->post_title . ' ' . $post->post_content ) );
			foreach ( $technology_terms as $term ) {
				if ( false !== strpos( $content, $term ) ) {
					return true;
				}
			}
			return false;
		}
	)
);

if ( empty( $news_posts ) ) {
	$news_posts = get_pages(
		array(
			'post_status' => 'publish',
			'sort_column' => 'post_date',
			'sort_order'  => 'DESC',
		)
	);
	$news_posts = array_values(
		array_filter(
			$news_posts,
			static function ( $post ) use ( $technology_terms ) {
				if ( ! preg_match( '/^insights-\d+$/', $post->post_name ) ) {
					return false;
				}
				$content = strtolower( wp_strip_all_tags( $post->post_title . ' ' . $post->post_content ) );
				foreach ( $technology_terms as $term ) {
					if ( false !== strpos( $content, $term ) ) {
						return true;
					}
				}
				return false;
			}
		)
	);
}

if ( empty( $news_posts ) ) {
	echo '<section class="section-18"><div class="w-layout-blockcontainer container-8 w-container"><p>No technology news is available yet.</p></div></section>';
	return;
}

$news_posts = array_slice( $news_posts, 0, 3 );
?>
<section class="section-18">
  <div class="w-layout-blockcontainer container-8 w-container">
    <div class="div-block-1102">
      <h1 class="heading-16">The Latest on <span class="text-span-24"><strong class="bold-text-24">Tech</strong></span>, <span class="text-span-25"><strong class="bold-text-23">Trends</strong></span>, and <span class="text-span-26"><strong class="bold-text-25">Talent</strong></span>.</h1>
      <div class="text-block-448">Latest technology news, trends, and insights.</div>
    </div>
    <div class="div-block-1106-copy-js">
      <?php foreach ( $news_posts as $news_post ) : ?>
        <?php
		$title   = get_the_title( $news_post );
        $url     = get_permalink( $news_post );
        $excerpt = wp_trim_words( wp_strip_all_tags( $news_post->post_content ), 24 );
		if ( preg_match( '/<h1[^>]*>(.*?)<\/h1>/is', $news_post->post_content, $heading_match ) ) {
			$heading_title = trim( wp_strip_all_tags( $heading_match[1] ) );
			if ( $heading_title !== '' && ( $title === '' || 'Insights' === $title ) ) {
				$title = $heading_title;
			}
		}
        ?>
        <article class="div-block-1103">
          <div class="div-block-1104">
            <div class="text-block-449">News</div>
            <div class="text-block-450"><?php echo esc_html( get_the_date( 'M j, Y', $news_post ) ); ?></div>
          </div>
          <h3 class="heading-17"><?php echo esc_html( $title ); ?></h3>
          <?php if ( $excerpt ) : ?><div class="text-block-453"><?php echo esc_html( $excerpt ); ?></div><?php endif; ?>
          <a href="<?php echo esc_url( $url ); ?>" class="div-block-1105">
            <span class="text-block-454">Read More</span><span aria-hidden="true">&#8599;</span>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
