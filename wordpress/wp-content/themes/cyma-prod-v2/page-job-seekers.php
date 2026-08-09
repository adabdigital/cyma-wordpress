<?php
get_header();
load_page_data('page-job-seekers');
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        cyma_the_page_content( 'page-job-seekers' );
    endwhile;
    ?>
</main>

<?php
get_footer();
