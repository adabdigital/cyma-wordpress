<?php
get_header();
load_page_data('page-forgot-password');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-forgot-password');
    endwhile;
    ?>
</main>

<?php
get_footer();
