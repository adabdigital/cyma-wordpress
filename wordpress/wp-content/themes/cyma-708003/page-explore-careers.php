<?php
/**
 * Careers listing — uses Job Seekers page content (Webflow collection page was not exported separately).
 */
get_header();
load_page_data( 'page-job-seekers' );
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part( 'template-parts/content/page-job-seekers' );
    endwhile;
    ?>
</main>

<?php
get_footer();
