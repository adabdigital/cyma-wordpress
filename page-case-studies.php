<?php
get_header();
load_page_data('page-case-studies');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-case-studies');
    endwhile;
    ?>
</main>

<?php
get_footer();
