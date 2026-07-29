<?php
get_header();
load_page_data('page-style-guide');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-style-guide' );
    endwhile;
    ?>
</main>

<?php
get_footer();
