<?php
get_header();
load_page_data('page-verification');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-verification');
    endwhile;
    ?>
</main>

<?php
get_footer();
