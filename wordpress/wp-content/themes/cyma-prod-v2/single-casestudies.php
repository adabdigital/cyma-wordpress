<?php
get_header();
load_page_data( 'single-casestudies' );
?>

<main>
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content/single-casestudies' );
	endwhile;
	?>
</main>

<?php
get_footer();
