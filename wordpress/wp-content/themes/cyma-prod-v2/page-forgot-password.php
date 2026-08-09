<?php
get_header();
load_page_data('page-forgot-password');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-forgot-password' );
    endwhile;
    ?>
</main>

<?php
get_footer();
