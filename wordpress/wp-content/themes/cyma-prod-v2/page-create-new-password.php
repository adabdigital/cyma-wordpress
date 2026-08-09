<?php
get_header();
load_page_data('page-create-new-password');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-create-new-password' );
    endwhile;
    ?>
</main>

<?php
get_footer();
