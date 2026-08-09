<?php
get_header();
load_page_data('page-software-development');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-software-development' );
    endwhile;
    ?>
</main>

<?php
get_footer();
