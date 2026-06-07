<?php
get_header();
?>

<main>
    <h1><?php the_title(); ?></h1>
    <div class="page-content">
        <?php the_content(); ?>
    </div>
</main>

<?php
get_footer();
