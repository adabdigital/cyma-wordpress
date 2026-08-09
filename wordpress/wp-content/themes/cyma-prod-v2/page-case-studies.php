<?php
get_header();
load_page_data('page-case-studies');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-case-studies' );
    endwhile;
    ?>
</main>

<?php
get_footer();
