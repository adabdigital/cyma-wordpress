<?php
get_header();
load_page_data('page-technology-services');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-technology-services');
    endwhile;
    ?>
</main>

<?php
get_footer();
