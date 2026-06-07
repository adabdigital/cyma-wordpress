<?php
get_header();
load_page_data('page-data-analytics');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-data-analytics');
    endwhile;
    ?>
</main>

<?php
get_footer();
