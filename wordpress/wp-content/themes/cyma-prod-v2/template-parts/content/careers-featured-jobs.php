<?php
$jobs = get_posts(
	array(
		'post_type'      => 'explore-careers',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);

if ( empty( $jobs ) ) {
	return;
}

$base = get_template_directory_uri() . '/assets/images';
?>
<div data-delay="4000" data-animation="slide" class="slider-27 w-slider" data-autoplay="false" data-easing="ease" data-hide-arrows="false" data-disable-swipe="false" data-autoplay-limit="0" data-nav-spacing="3" data-duration="500" data-infinite="true">
  <div class="w-slider-mask">
    <?php foreach ( $jobs as $index => $job ) : ?>
      <?php
			$summary     = cyma_get_career_meta( 'sub-text', $job->ID );
			$slide_class = ( 0 === $index ) ? 'slide-14 w-slide' : 'slide-15 w-slide';
			?>
      <div class="<?php echo esc_attr( $slide_class ); ?>">
        <div class="div-block-1179">
          <div class="text-block-487">Featured Job</div>
          <h1 class="heading-59"><?php echo esc_html( get_the_title( $job ) ); ?></h1>
          <div class="div-block-1181">
            <div class="div-block-1180">
              <img src="<?php echo esc_url( $base . '/location.svg' ); ?>" loading="lazy" alt="">
              <div class="text-block-489">Manchester, CT</div>
            </div>
            <div class="text-block-492"></div>
            <div class="text-block-491">Open Role</div>
          </div>
          <?php if ( $summary ) : ?>
            <div class="text-block-493"><?php echo esc_html( $summary ); ?></div>
          <?php endif; ?>
          <div class="div-block-1182">
            <div class="text-block-494">Full-time</div>
            <div class="text-block-494">Travel</div>
            <div class="text-block-494">US</div>
          </div>
          <div class="div-block-1184">
            <div class="div-block-1183">
              <img src="<?php echo esc_url( $base . '/featuredjob1.webp' ); ?>" loading="lazy" alt="" class="image-49">
              <img src="<?php echo esc_url( $base . '/featuredjob2.webp' ); ?>" loading="lazy" alt="" class="image-50">
              <img src="<?php echo esc_url( $base . '/featuredjob3.webp' ); ?>" loading="lazy" alt="" class="image-51">
              <img src="<?php echo esc_url( $base . '/featuredjob4.webp' ); ?>" loading="lazy" alt="" class="image-52">
              <div class="text-block-495">1+</div>
            </div>
            <div class="text-block-496">Openings</div>
          </div>
          <a href="<?php echo esc_url( cyma_get_dice_jobs_url() ); ?>" target="_blank" rel="noopener noreferrer" class="featuredjobcontent-btn w-inline-block">
            <div class="text-block-497">View on Dice</div>
            <img loading="lazy" src="<?php echo esc_url( $base . '/group-1000007155-1.svg' ); ?>" alt="" class="image-150">
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="left-arrow-14 w-slider-arrow-left">
    <div class="w-icon-slider-left"></div>
  </div>
  <div class="right-arrow-14 w-slider-arrow-right">
    <div class="w-icon-slider-right"></div>
  </div>
  <div class="slide-nav-15 w-slider-nav w-round w-num"></div>
</div>
