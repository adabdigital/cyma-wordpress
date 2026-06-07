<?php
get_header();
load_page_data('page-modernizing-loan-processing-systems');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-modernizing-loan-processing-systems');
    endwhile;
    ?>
</main>

<?php
get_footer();
