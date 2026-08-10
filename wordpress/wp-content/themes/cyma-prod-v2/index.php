<?php
/**
 * Main template fallback.
 *
 * Used when no more-specific template matches (e.g. Reading is still
 * "Your latest posts"). Always render the homepage so the site is never blank.
 */
get_header();
load_page_data( 'front-page' );
?>

<main>
	<?php get_template_part( 'template-parts/content/front-page' ); ?>
</main>

<?php
get_footer();
