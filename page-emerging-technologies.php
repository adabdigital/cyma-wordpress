<?php
get_header();
load_page_data('page-emerging-technologies');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-emerging-technologies');
    endwhile;
    ?>
</main>

<?php
get_footer();
