<?php
get_header();
load_page_data('page-h1b-lca');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-h1b-lca' );
    endwhile;
    ?>
</main>

<?php
get_footer();
