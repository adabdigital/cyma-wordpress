<?php
get_header();
load_page_data('page-password-updated');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-password-updated' );
    endwhile;
    ?>
</main>

<?php
get_footer();
