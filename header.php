<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <?php
    // Get current page slug
    $page_slug = cyma_get_template_slug();
    
    // Load appropriate head template
    if ($page_slug) {
        $head_template = 'template-parts/head/' . $page_slug;
        if (locate_template($head_template . '.php')) {
            get_template_part('template-parts/head/' . $page_slug);
        } else {
            get_template_part('template-parts/head/front-page');
        }
    } else {
        get_template_part('template-parts/head/front-page');
    }
    ?>
    <?php wp_head(); ?>
    <?php get_template_part('template-parts/head/global-assets'); ?>
</head>
<body class="w-mod-js">
<?php wp_body_open(); ?>
 