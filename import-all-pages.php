<?php
/**
 * Wrapper — run the theme import script inside Docker:
 *   docker exec cyma-wordpress php /var/www/html/wp-content/themes/cyma-708003/import-all-pages.php
 */
require __DIR__ . '/wordpress/wp-content/themes/cyma-708003/import-all-pages.php';
