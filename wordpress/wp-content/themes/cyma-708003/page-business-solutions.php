<?php
get_header();
load_page_data('page-business-solutions');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-business-solutions' );
    endwhile;
    ?>
</main>

<?php
get_footer();
