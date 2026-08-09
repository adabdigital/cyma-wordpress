<?php
get_header();
load_page_data('page-notice-of-filing');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-notice-of-filing' );
    endwhile;
    ?>
</main>

<?php
get_footer();
