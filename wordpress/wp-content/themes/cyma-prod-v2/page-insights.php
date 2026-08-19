<?php
/**
 * Insights hub — CMS page with live article listing via [cyma_insights_list].
 */
get_header();
load_page_data( 'page-insights-2' );
?>

<main>
	<?php
	while ( have_posts() ) :
		the_post();
		cyma_the_page_content( 'page-insights' );
	endwhile;
	?>
</main>

<?php
get_footer();
