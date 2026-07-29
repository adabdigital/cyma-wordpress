<?php
get_header();
load_page_data('page-about-us');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-about-us' );
    endwhile;
    ?>
</main>

<?php
get_footer();
