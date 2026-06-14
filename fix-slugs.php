<?php
require '/var/www/html/wp-load.php';

// Fix slugs created by import-pages.php (page-about-us -> about-us, etc.)
$map = [
    'page-about-us' => 'about-us',
    'page-business-solutions' => 'business-solutions',
    'page-case-studies' => 'case-studies',
    'page-contact-us' => 'contact-us',
    'page-industries' => 'industries',
    'page-resources' => 'resources',
    'page-legal' => 'legal',
    'page-style-guide' => 'style-guide',
];

foreach ($map as $old => $new) {
    $page = get_page_by_path($old);
    if ($page) {
        wp_update_post(['ID' => $page->ID, 'post_name' => $new]);
        echo "Updated $old -> $new (ID {$page->ID})\n";
    } else {
        echo "Not found: $old\n";
    }
}

// Create remaining pages from JSON that don't exist yet
$dir = '/var/www/html/wp-content/themes/cyma-708003/_data/frontend-editor';
foreach (glob($dir . '/page-*.json') as $file) {
    $key = basename($file, '.json');
    $slug = preg_replace('/^page-/', '', $key);
    $title = ucwords(str_replace('-', ' ', $slug));
    if (!get_page_by_path($slug)) {
        $id = wp_insert_post([
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ]);
        echo "Created: $title ($slug) ID $id\n";
    } else {
        echo "Exists: $slug\n";
    }
}

// Flush rewrite rules (replaces "wp rewrite flush")
flush_rewrite_rules();
echo "Rewrite rules flushed.\n";
