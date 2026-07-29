<?php
get_header();
load_page_data('page-ai');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-ai' );
    endwhile;
    ?>
</main>

<?php
get_footer();
