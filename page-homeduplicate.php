<?php
get_header();
load_page_data('page-homeduplicate');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-homeduplicate');
    endwhile;
    ?>
</main>

<?php
get_footer();
