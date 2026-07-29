<?php
get_header();
load_page_data('page-verification');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-verification' );
    endwhile;
    ?>
</main>

<?php
get_footer();
