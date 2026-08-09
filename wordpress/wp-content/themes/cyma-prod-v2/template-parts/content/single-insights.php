<?php
$heading  = cyma_get_cms_meta( 'heading' );
$subtext  = cyma_get_cms_meta( 'sub-text' );
$overlay  = cyma_get_cms_meta( 'text-overlay' );
$title    = get_the_title();
$thumb    = get_the_post_thumbnail_url( get_the_ID(), 'large' );
?>
<section class="section-78" style="background-image:url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/bg-insights1.webp' ); ?>');background-size:contain;background-position:bottom right;background-repeat:no-repeat">
	<div class="w-layout-blockcontainer container-68 w-container">
		<div class="div-block-1423">
			<div class="text-block-688">Home • Resources • Insights • <strong><?php echo esc_html( $title ); ?></strong></div>
			<?php if ( $overlay ) : ?>
				<div class="text-block-687"><?php echo esc_html( $overlay ); ?></div>
			<?php endif; ?>
			<h1 class="heading-158"><?php echo esc_html( $heading ? $heading : $title ); ?></h1>
			<?php if ( $thumb ) : ?>
				<div class="div-block-1424"><img src="<?php echo esc_url( $thumb ); ?>" loading="lazy" alt="" class="image-223"></div>
			<?php endif; ?>
		</div>
	</div>
</section>
<section class="section-33">
	<div class="w-layout-blockcontainer container-59 w-container">
		<?php if ( $subtext ) : ?>
			<p class="paragraph-38"><?php echo esc_html( $subtext ); ?></p>
		<?php endif; ?>
		<div class="paragraph-39">
			<?php the_content(); ?>
		</div>
		<p style="margin-top:24px"><a href="<?php echo esc_url( home_url( '/insights/' ) ); ?>">← Back to Latest News</a></p>
	</div>
</section>
