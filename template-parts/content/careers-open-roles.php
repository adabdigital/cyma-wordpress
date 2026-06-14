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
?>

<section class="section-71">
  <div class="w-layout-blockcontainer container-62 w-container">
    <h2 class="heading-149" style="margin-bottom: 32px;">Open Roles</h2>
    <div class="open-roles-grid" style="display: grid; width: 100%;">
      <?php foreach ( $jobs as $job ) : ?>
        <?php setup_postdata( $job ); ?>
        <div class="div-block-1389" style="padding: 24px; border: 1px solid #e8eef5; border-radius: 16px; width: 100%; box-sizing: border-box; display: flex; flex-direction: column;">
          <h3 class="heading-61" style="margin-bottom: 8px;"><?php echo esc_html( get_the_title( $job ) ); ?></h3>
          <p style="margin-bottom: 16px; display: <?php echo cyma_get_career_meta( 'sub-text', $job->ID ) ? 'block' : 'none'; ?>;"><?php echo esc_html( cyma_get_career_meta( 'sub-text', $job->ID ) ); ?></p>
          <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: auto;">
            <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="contact-btn-exploreinjobseeksers w-inline-block">
              <div class="text-block-483">View Role</div>
            </a>
            <a href="<?php echo esc_url( cyma_get_apply_url( get_the_title( $job ) ) ); ?>" class="featuredjobcontent-btn w-inline-block">
              <div class="text-block-497">Apply Now</div>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
      <?php wp_reset_postdata(); ?>
    </div>
  </div>
</section>
