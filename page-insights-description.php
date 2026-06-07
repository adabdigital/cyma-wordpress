<?php
get_header();
load_page_data('page-insights-description');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-insights-description');
    endwhile;
    ?>
</main>

<?php
get_footer();
