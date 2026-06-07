<?php
get_header();
load_page_data('page-login');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-login');
    endwhile;
    ?>
</main>

<?php
get_footer();
