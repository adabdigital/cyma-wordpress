<?php
/**
 * Insights hub — latest news / article listing.
 */
get_header();
load_page_data( 'page-insights-2' );
?>

<main>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<section class="section-78" style="background-image:url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/bg-insights1.webp' ); ?>');background-size:contain;background-position:bottom right;background-repeat:no-repeat">
			<div class="w-layout-blockcontainer container-68 w-container">
				<div class="div-block-1423">
					<div class="text-block-688">Home • Resources • <strong>Latest News</strong></div>
					<h1 class="heading-158">Insights &amp; Latest News</h1>
					<p class="paragraph-38">Explore Cyma’s latest perspectives on technology, talent, and transformation.</p>
				</div>
			</div>
		</section>
		<section class="section-65">
			<div class="w-layout-blockcontainer container-55 w-container">
				<?php get_template_part( 'template-parts/content/insights-articles-list' ); ?>
			</div>
		</section>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();
