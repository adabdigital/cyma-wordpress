<?php
get_header();
load_page_data('page-employee-login-inside');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        get_template_part('template-parts/content/page-employee-login-inside');
    endwhile;
    ?>
</main>

<?php
get_footer();
