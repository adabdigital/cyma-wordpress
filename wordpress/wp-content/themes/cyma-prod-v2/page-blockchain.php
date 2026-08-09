<?php
get_header();
load_page_data('page-blockchain');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-blockchain' );
    endwhile;
    ?>
</main>

<?php
get_footer();
