<?php
get_header();
load_page_data('page-industries');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-industries' );
    endwhile;
    ?>
</main>

<?php
get_footer();
