<?php
// Import pages from _data directory
require_once('/var/www/html/wp-load.php');

$pages = [
    'front-page' => 'Home',
    'page-about-us' => 'About Us',
    'page-business-solutions' => 'Business Solutions',
    'page-case-studies' => 'Case Studies',
    'page-contact-us' => 'Contact Us',
    'page-industries' => 'Industries',
    'page-resources' => 'Resources',
    'page-legal' => 'Legal',
    'page-style-guide' => 'Style Guide',
];

foreach ($pages as $slug => $title) {
    $existing = get_page_by_path($slug);
    if (!$existing) {
        $page_id = wp_insert_post([
            'post_title' => $title,
            'post_name' => $slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '<p>Page content will be added from JSON data.</p>'
        ]);
        echo "Created page: $title (ID: $page_id)\n";
    } else {
        echo "Page already exists: $title\n";
    }
}

echo "Page import complete.\n";
