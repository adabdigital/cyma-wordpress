<?php
get_header();
load_page_data('page-industries');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-industries');
    endwhile;
    ?>
</main>

<?php
get_footer();
