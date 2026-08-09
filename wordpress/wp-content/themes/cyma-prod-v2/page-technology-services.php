<?php
get_header();
load_page_data('page-technology-services');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-technology-services' );
    endwhile;
    ?>
</main>

<?php
get_footer();
