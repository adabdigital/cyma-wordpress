<?php
get_header();
load_page_data('page-it-infrastructure');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-it-infrastructure' );
    endwhile;
    ?>
</main>

<?php
get_footer();
