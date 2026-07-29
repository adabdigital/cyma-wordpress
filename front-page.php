<?php
get_header();
load_page_data('front-page');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'front-page' );
    endwhile;
    ?>
</main>

<?php
get_footer();
