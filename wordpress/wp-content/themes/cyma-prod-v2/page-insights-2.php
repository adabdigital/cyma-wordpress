<?php
get_header();
load_page_data('page-insights-2');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-insights-2' );
    endwhile;
    ?>
</main>

<?php
get_footer();
