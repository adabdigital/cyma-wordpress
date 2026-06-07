<?php
defined( 'ABSPATH' ) || exit;

wp_footer();
?>
<script src="<?php echo esc_url(get_template_directory_uri() . '/assets/js/section-animations.js'); ?>?v=1780144480" defer></script>
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
