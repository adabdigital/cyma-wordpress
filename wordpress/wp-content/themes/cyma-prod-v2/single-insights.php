<?php
get_header();
load_page_data( 'single-insights' );
?>

<main>
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content/single-insights' );
	endwhile;
	?>
</main>

<?php
get_footer();
