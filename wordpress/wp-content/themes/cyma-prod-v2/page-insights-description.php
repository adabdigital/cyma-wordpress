<?php
get_header();
load_page_data('page-insights-description');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-insights-description' );
    endwhile;
    ?>
</main>

<?php
get_footer();
