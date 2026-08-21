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

$live_news = array();
if ( empty( $news_posts ) ) {
	$feed_response = wp_remote_get(
		'https://api.rss2json.com/v1/api.json?rss_url=' . rawurlencode( 'https://feeds.bbci.co.uk/news/technology/rss.xml' ),
		array( 'timeout' => 8 )
	);
	if ( ! is_wp_error( $feed_response ) ) {
		$feed_data = json_decode( wp_remote_retrieve_body( $feed_response ), true );
		if ( ! empty( $feed_data['items'] ) && is_array( $feed_data['items'] ) ) {
			foreach ( $feed_data['items'] as $item ) {
				$text = strtolower( (string) ( $item['title'] ?? '' ) . ' ' . (string) ( $item['description'] ?? '' ) );
				foreach ( $technology_terms as $term ) {
					if ( false !== strpos( $text, $term ) ) {
						$live_news[] = $item;
						break;
					}
				}
				if ( count( $live_news ) >= 3 ) {
					break;
				}
			}
		}
	}
}

if ( empty( $news_posts ) && empty( $live_news ) ) {
	echo '<section class="section-18"><div class="w-layout-blockcontainer container-8 w-container"><p class="job-seeker-news-status">Loading technology news...</p><div class="div-block-1106-copy-js job-seeker-news-live"></div></div></section>';
	?>
	<script>
	(function () {
		var container = document.querySelector('.job-seeker-news-live');
		var status = document.querySelector('.job-seeker-news-status');
		if (!container || !status) return;
		var feed = 'https://api.rss2json.com/v1/api.json?rss_url=' + encodeURIComponent('https://feeds.bbci.co.uk/news/technology/rss.xml');
		var fallback = [{ title: 'Latest BBC Technology News', description: 'Read the latest technology news from BBC.', link: 'https://www.bbc.com/news/technology' }];
		var timeout = new Promise(function (_, reject) { setTimeout(function () { reject(new Error('Feed timeout')); }, 5000); });
		Promise.race([fetch(feed).then(function (response) { return response.json(); }), timeout]).then(function (data) {
			var items = (data.items || []).slice(0, 3);
			if (!items.length) items = fallback;
			container.innerHTML = items.map(function (item) {
				return '<article class="div-block-1103"><h3 class="heading-17">' + item.title + '</h3><div class="text-block-453">' + (item.description || '') + '</div><a href="' + item.link + '" class="div-block-1105" target="_blank" rel="noopener noreferrer"><span class="text-block-454">Read More</span><span aria-hidden="true">&#8599;</span></a></article>';
			}).join('');
			status.remove();
		}).catch(function () { status.textContent = ''; container.innerHTML = '<article class="div-block-1103"><h3 class="heading-17">Latest BBC Technology News</h3><a href="https://www.bbc.com/news/technology" class="div-block-1105" target="_blank" rel="noopener noreferrer"><span class="text-block-454">Read More</span></a></article>'; });
	}());
	</script>
	<?php
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
			<?php if ( ! empty( $live_news ) ) : ?>
				<?php foreach ( $live_news as $item ) : ?>
					<article class="div-block-1103">
						<div class="div-block-1104"><div class="text-block-449">News</div><div class="text-block-450"><?php echo esc_html( $item['pubDate'] ?? '' ); ?></div></div>
						<h3 class="heading-17"><?php echo esc_html( wp_strip_all_tags( $item['title'] ?? '' ) ); ?></h3>
						<div class="text-block-453"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item['description'] ?? '' ), 24 ) ); ?></div>
						<a href="<?php echo esc_url( $item['link'] ?? '#' ); ?>" class="div-block-1105" target="_blank" rel="noopener noreferrer"><span class="text-block-454">Read More</span><span aria-hidden="true">&#8599;</span></a>
					</article>
				<?php endforeach; ?>
			<?php else : ?>
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
			<?php endif; ?>
    </div>
  </div>
</section>
