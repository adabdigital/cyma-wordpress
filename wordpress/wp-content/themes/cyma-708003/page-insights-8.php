<?php
get_header();
load_page_data('page-insights-8');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-insights-8' );
    endwhile;
    ?>
</main>

<?php
get_footer();
