<?php
get_header();
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content();
    endwhile;
    ?>
</main>

<?php
get_footer();
