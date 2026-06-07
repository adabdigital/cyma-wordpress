<?php
get_header();
load_page_data('page-quality-assurance-and-testing');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-quality-assurance-and-testing');
    endwhile;
    ?>
</main>

<?php
get_footer();
