<?php
get_header();
load_page_data('page-cloud-devops');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-cloud-devops');
    endwhile;
    ?>
</main>

<?php
get_footer();
