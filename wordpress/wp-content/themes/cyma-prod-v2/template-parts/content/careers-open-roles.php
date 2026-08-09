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
    <div class="w-layout-grid grid-44 open-roles-grid">
      <?php foreach ( $jobs as $job ) : ?>
        <?php
        setup_postdata( $job );
        $summary = cyma_get_career_meta( 'sub-text', $job->ID );
        ?>
        <div class="div-block-1389" style="padding: 24px; border: 1px solid #e8eef5; border-radius: 16px;">
          <h3 class="heading-61" style="margin-bottom: 8px;"><?php echo esc_html( get_the_title( $job ) ); ?></h3>
          <?php if ( $summary ) : ?>
            <p style="margin-bottom: 16px;"><?php echo esc_html( $summary ); ?></p>
          <?php endif; ?>
          <div style="display: flex; gap: 12px; flex-wrap: wrap;">
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
    <div class="open-roles-controls">
      <button class="open-roles-prev" aria-label="Previous">‹</button>
      <div class="open-roles-dots" aria-hidden="false"></div>
      <button class="open-roles-next" aria-label="Next">›</button>
    </div>
    <script>(function(){
      if (!window.matchMedia('(max-width: 767px)').matches) return;
      var grid = document.querySelector('.section-71 .open-roles-grid');
      var prev = document.querySelector('.section-71 .open-roles-prev');
      var next = document.querySelector('.section-71 .open-roles-next');
      var dots = document.querySelector('.section-71 .open-roles-dots');
      if (!grid || !prev || !next || !dots) return;
      var slides = Array.prototype.slice.call(grid.children);
      var count = slides.length;
      function createDots(){
        for (var i=0;i<count;i++){
          var btn=document.createElement('button');
          btn.className='open-roles-dot';
          btn.setAttribute('data-index',i);
          btn.addEventListener('click',function(e){
            var idx= +e.currentTarget.getAttribute('data-index');
            grid.scrollTo({left: idx*grid.clientWidth, behavior:'smooth'});
          });
          dots.appendChild(btn);
        }
      }
      createDots();
      var dotButtons = dots.querySelectorAll('.open-roles-dot');
      function updateActive(){
        var idx = Math.round(grid.scrollLeft / grid.clientWidth) || 0;
        dotButtons.forEach(function(b,i){ b.classList.toggle('active', i===idx); });
      }
      prev.addEventListener('click', function(){ grid.scrollBy({left: -grid.clientWidth, behavior:'smooth'}); });
      next.addEventListener('click', function(){ grid.scrollBy({left: grid.clientWidth, behavior:'smooth'}); });
      grid.addEventListener('scroll', function(){ window.requestAnimationFrame(updateActive); });
      updateActive();
    })();</script>
  </div>
</section>
