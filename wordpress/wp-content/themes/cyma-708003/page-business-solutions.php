<?php
get_header();
load_page_data('page-business-solutions');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-business-solutions');
    endwhile;
    ?>
</main>

<?php
get_footer();
