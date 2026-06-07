<?php
get_header();
load_page_data('page-sign-up');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-sign-up');
    endwhile;
    ?>
</main>

<?php
get_footer();
