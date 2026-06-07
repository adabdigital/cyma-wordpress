<?php
get_header();
load_page_data('front-page');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/front-page');
    endwhile;
    ?>
</main>

<?php
get_footer();
