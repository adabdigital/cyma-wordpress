<?php
get_header();
load_page_data('page-style-guide');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-style-guide');
    endwhile;
    ?>
</main>

<?php
get_footer();
