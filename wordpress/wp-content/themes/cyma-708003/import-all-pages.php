<?php
/**
 * Create or update all CYMA theme pages from _data/frontend-editor/page-*.json.
 *
 * Run inside Docker:
 *   docker exec cyma-wordpress php /var/www/html/wp-content/themes/cyma-708003/import-all-pages.php
 */
require_once '/var/www/html/wp-load.php';

$title_map = array(
    'front-page'                        => 'Home',
    'about-us'                          => 'About Us',
    'business-solutions'                => 'Business Solutions',
    'technology-services'               => 'Technology Services',
    'software-development'              => 'Software Development',
    'cloud-devops'                      => 'Cloud & DevOps',
    'cyber-security'                    => 'Cyber Security',
    'data-analytics'                    => 'Data Analytics',
    'ai'                                => 'AI & Machine Learning',
    'blockchain'                        => 'Blockchain',
    'emerging-technologies'             => 'Emerging Technologies',
    'it-infrastructure'                 => 'IT Infrastructure',
    'quality-assurance-and-testing'     => 'Quality Assurance & Testing',
    'industries'                        => 'Industries',
    'job-seekers'                       => 'Job Seekers',
    'explore-careers'                   => 'Explore Careers',
    'case-studies'                      => 'Case Studies',
    'insights-2'                        => 'Insights',
    'insights-3'                        => 'Insights',
    'insights-4'                        => 'Insights',
    'insights-5'                        => 'Insights',
    'insights-6'                        => 'Insights',
    'insights-7'                        => 'Insights',
    'insights-8'                        => 'Insights',
    'insights-9'                        => 'Insights',
    'insights-description'              => 'Insights',
    'resources'                         => 'Resources',
    'legal'                             => 'Legal',
    'contact-us'                        => 'Contact Us',
    'login'                             => 'Login',
    'sign-up'                           => 'Sign Up',
    'employee-login'                    => 'Employee Login',
    'employee-login-inside'             => 'Employee Login',
    'forgot-password'                   => 'Forgot Password',
    'create-new-password'               => 'Create New Password',
    'password-updated'                  => 'Password Updated',
    'verification'                      => 'Verification',
    'email-verification'                => 'Email Verification',
    'h1b-lca'                           => 'H-1B LCA',
    'notice-of-filing'                  => 'Notice of Filing',
    'modernizing-loan-processing-systems' => 'Modernizing Loan Processing Systems',
    'homeduplicate'                     => 'Home (Duplicate)',
    'style-guide'                       => 'Style Guide',
);

function cyma_import_page_title( $slug, $title_map ) {
    if ( isset( $title_map[ $slug ] ) ) {
        return $title_map[ $slug ];
    }

    return ucwords( str_replace( '-', ' ', $slug ) );
}

function cyma_import_upsert_page( $slug, $title ) {
    $existing = get_page_by_path( $slug );

    if ( $existing ) {
        wp_update_post(
            array(
                'ID'         => $existing->ID,
                'post_title' => $title,
                'post_name'  => $slug,
                'post_status'=> 'publish',
            )
        );
        return array( 'action' => 'updated', 'id' => $existing->ID );
    }

    $id = wp_insert_post(
        array(
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        )
    );

    if ( is_wp_error( $id ) ) {
        return array( 'action' => 'error', 'message' => $id->get_error_message() );
    }

    return array( 'action' => 'created', 'id' => $id );
}

$created = 0;
$updated = 0;
$errors  = 0;

function cyma_import_log_result( &$created, &$updated, &$errors, $slug, $title, $result ) {
    if ( $result['action'] === 'error' ) {
        echo "ERROR: $slug — {$result['message']}\n";
        $errors++;
        return;
    }

    echo strtoupper( $result['action'] ) . ": $title ($slug) ID {$result['id']}\n";
    if ( $result['action'] === 'created' ) {
        $created++;
    } else {
        $updated++;
    }
}

// Homepage (uses front-page.json, not page-front-page.json).
$result = cyma_import_upsert_page( 'front-page', $title_map['front-page'] );
cyma_import_log_result( $created, $updated, $errors, 'front-page', $title_map['front-page'], $result );

// All template pages from JSON files.
$data_dir = get_template_directory() . '/_data/frontend-editor';
foreach ( glob( $data_dir . '/page-*.json' ) as $file ) {
    $slug   = preg_replace( '/^page-/', '', basename( $file, '.json' ) );
    $title  = cyma_import_page_title( $slug, $title_map );
    $result = cyma_import_upsert_page( $slug, $title );
    cyma_import_log_result( $created, $updated, $errors, $slug, $title, $result );
}

// Careers landing page (no page-*.json file).
$result = cyma_import_upsert_page( 'explore-careers', $title_map['explore-careers'] );
cyma_import_log_result( $created, $updated, $errors, 'explore-careers', $title_map['explore-careers'], $result );

// Set static homepage to front-page.
$home = get_page_by_path( 'front-page' );
if ( $home ) {
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $home->ID );
    echo "SET HOMEPAGE: front-page (ID {$home->ID})\n";
}

flush_rewrite_rules();

echo "\nSummary: $created created, $updated updated, $errors errors\n";
echo "Done.\n";
