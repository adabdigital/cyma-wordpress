<?php
/**
 * Careers listing — CMS page content, with dynamic open roles via [cyma_open_roles].
 */
get_header();
load_page_data( 'page-job-seekers' );
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-job-seekers' );
        // Fallback when page is not yet seeded into CMS:
        if ( ! cyma_page_uses_cms_content( get_the_ID() ) ) {
            get_template_part( 'template-parts/content/careers-open-roles' );
        }
    endwhile;
    ?>
</main>

<?php
get_footer();
