<?php
get_header();
load_page_data('page-legal');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-legal' );
    endwhile;
    ?>
</main>

<?php
get_footer();
