<?php
defined( 'ABSPATH' ) || exit;

wp_footer();
?>
<?php
$footer_slug = cyma_get_footer_slug();
$footer_template = 'template-parts/footer/' . $footer_slug;
if (locate_template($footer_template . '.php')) {
    get_template_part('template-parts/footer/' . $footer_slug);
} else {
    get_template_part('template-parts/footer/default');
}
?>
</body>
</html>
