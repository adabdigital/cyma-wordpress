<?php
get_header();
load_page_data('page-emerging-technologies');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-emerging-technologies' );
    endwhile;
    ?>
</main>

<?php
get_footer();
