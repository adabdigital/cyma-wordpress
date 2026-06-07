<?php
get_header();
load_page_data('page-contact-us');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-contact-us');
    endwhile;
    ?>
</main>

<?php
get_footer();
