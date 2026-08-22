<?php
$studies = get_posts(
	array(
		'post_type'      => 'casestudies',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if ( empty( $studies ) ) {
	echo '<div class="w-dyn-empty cyma-live-empty"><div>No case studies found.</div></div>';
	return;
}

$arrow = get_template_directory_uri() . '/assets/images/group-1000007155-2.svg?v=1780144474';
?>
<div role="list" class="collection-list w-dyn-items">
	<?php foreach ( $studies as $study ) : ?>
		<?php
		$industry = cyma_get_cms_meta( 'industry', $study->ID );
		$heading  = cyma_get_cms_meta( 'heading-text', $study->ID );
		if ( $heading === '' ) {
			$heading = get_the_title( $study );
		}
		$thumb = get_the_post_thumbnail_url( $study->ID, 'large' );
		if ( ! $thumb ) {
			$thumb = get_template_directory_uri() . '/assets/images/casestudies2.webp';
		}
		?>
		<div role="listitem" class="w-dyn-item">
			<div class="div-block-1230">
				<img src="<?php echo esc_url( $thumb ); ?>" loading="lazy" alt="" class="image-68">
				<div class="div-block-1233">
					<div class="div-block-1382">
						<?php if ( $industry ) : ?>
							<div class="text-block-554"><?php echo esc_html( $industry ); ?></div>
						<?php endif; ?>
						<h2 class="heading-96"><?php echo esc_html( $heading ); ?></h2>
					</div>
					<div class="div-block-1383">
						<a href="<?php echo esc_url( get_permalink( $study ) ); ?>" class="transformingbusiness-ai-btn w-inline-block">
							<div class="text-block-529-copy">Read More</div>
							<img loading="lazy" src="<?php echo esc_url( $arrow ); ?>" alt="" class="image-145">
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
