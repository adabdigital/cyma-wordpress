<?php
get_header();
load_page_data('page-login');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-login' );
    endwhile;
    ?>
</main>

<?php
get_footer();
