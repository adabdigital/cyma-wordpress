<?php
get_header();
load_page_data('page-blockchain');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-blockchain');
    endwhile;
    ?>
</main>

<?php
get_footer();
