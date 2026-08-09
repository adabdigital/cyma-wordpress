<?php
/**
 * Import / update all CYMA pages.
 *
 * Local (Windows / Mac / Linux):
 *   php import-all-pages.php
 *
 * Docker:
 *   docker exec cyma-wordpress php /var/www/html/wp-content/themes/cyma-prod-v2/import-all-pages.php
 */
require __DIR__ . '/wordpress/wp-content/themes/cyma-prod-v2/import-all-pages.php';
