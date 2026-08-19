<?php

function cyma_708003_setup() {
    add_theme_support(
        'html5',
        array(
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
            'navigation-widgets',
        )
    );
    
    add_theme_support('woocommerce');

    $logo_width  = 300;
    $logo_height = 100;

    add_theme_support(
        'custom-logo',
        array(
            'height'               => $logo_height,
            'width'                => $logo_width,
            'flex-width'           => true,
            'flex-height'          => true,
            'unlink-homepage-logo' => true,
        )
    );

    add_theme_support( 'title-tag' );
    add_theme_support( 'menus' );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'post-thumbnails' ); 
}

add_action( 'after_setup_theme', 'cyma_708003_setup' );

function cyma_enqueue_styles() {
    wp_enqueue_style('normalize', get_template_directory_uri() . '/assets/css/normalize.css', [], '1780144474');
    wp_enqueue_style('wordpress', get_template_directory_uri() . '/assets/css/wordpress.css', [], '1780144474');
    wp_enqueue_style('cyma-style', get_template_directory_uri() . '/assets/css/style.css', [], time());
    wp_enqueue_style('cyma-animations', get_template_directory_uri() . '/assets/css/animations.css', ['cyma-style'], '1787096501');

    wp_add_inline_style(
        'cyma-style',
        /* Keep Webflow fade-ins visible, but do not break intentional hover overlays
           (e.g. industry cards .div-block-1366) that use opacity:0 as their idle state. */
        '[data-w-id]:not(section.cyma-reveal):not(.mg-right-10px):not(.div-block-1366),'
        . '[style*="opacity:0"]:not(section.cyma-reveal):not(.div-block-1366):not(.mg-right-10px)'
        . '{ opacity: 1 !important; }'
    );

    // Hide header Contact Us on mobile/tablet (drawer already has CTA)
    wp_add_inline_style(
        'cyma-animations',
        '@media screen and (max-width:991px){'
        . 'body .navbar-logo-left-container.header-992.w-nav .navbar-wrapper>a.contact,'
        . 'body .navbar-logo-left-container.header-992.w-nav .navbar-wrapper>.contact,'
        . 'body .navbar-logo-left-container.header-992.w-nav .navbar-wrapper>.contact-btn-copy-nav-about,'
        . 'body .navbar-logo-left-container.header-992.w-nav .navbar-wrapper>.contact-btn-copy-nav-about.blue,'
        . 'body .navbar-logo-left-container.header-992.w-nav .menu-button>[class*="contact-btn"]'
        . '{display:none!important;visibility:hidden!important;pointer-events:none!important;}'
        . '}'
    );

    // Remove WordPress default styles that might conflict
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
}
add_action('wp_enqueue_scripts', 'cyma_enqueue_styles', 999);

function cyma_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'cyma-section-animations',
        get_template_directory_uri() . '/assets/js/section-animations.js',
        [],
        '1780144510',
        true
    );

    // Navbar scrolled state (home + white-header pages)
    wp_enqueue_script(
        'cyma-home-ui',
        get_template_directory_uri() . '/assets/js/home-ui.js',
        [],
        '1787095801',
        true
    );
}
add_action('wp_enqueue_scripts', 'cyma_enqueue_scripts');

function cyma_register_careers_post_type() {
    register_post_type(
        'explore-careers',
        array(
            'labels'       => array(
                'name'          => 'Careers',
                'singular_name' => 'Career',
            ),
            'public'       => true,
            'has_archive'  => false,
            'rewrite'      => array(
                'slug'       => 'explore-careers',
                'with_front' => false,
            ),
            'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
            'show_in_rest' => true,
        )
    );
}
add_action( 'init', 'cyma_register_careers_post_type' );

function cyma_register_cms_post_types() {
	register_post_type(
		'casestudies',
		array(
			'labels'       => array(
				'name'          => 'Case Studies',
				'singular_name' => 'Case Study',
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array(
				'slug'       => 'case-studies',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		'insights',
		array(
			'labels'       => array(
				'name'          => 'Insights',
				'singular_name' => 'Insight',
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array(
				'slug'       => 'insight',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'cyma_register_cms_post_types' );

function cyma_get_dice_jobs_url() {
	$stored = get_option( 'cyma_dice_jobs_url', '' );
	if ( is_string( $stored ) && $stored !== '' ) {
		return $stored;
	}
	return 'https://www.dice.com/jobs?filters.clientBrandNameFilter=Cyma+Systems+Inc';
}

function cyma_get_cms_meta( $key, $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$value = get_post_meta( $post_id, $key, true );
	if ( is_array( $value ) && isset( $value['value'] ) ) {
		$value = $value['value'];
	}
	return is_string( $value ) ? $value : ( is_scalar( $value ) ? (string) $value : '' );
}

function cyma_get_career_meta( $key, $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    $value = get_post_meta( $post_id, $key, true );
    return is_string( $value ) ? $value : '';
}

function cyma_get_apply_url( $job_title = '' ) {
    $contact = get_page_by_path( 'contact-us' );
    $url     = $contact ? get_permalink( $contact ) : home_url( '/contact-us/' );

    $job_title = preg_replace(
        '/\s+/',
        ' ',
        trim(
            wp_strip_all_tags(
                str_ireplace( array( '<br>', '<br/>', '<br />', "\n", "\r" ), ' ', (string) $job_title )
            )
        )
    );
    if ( $job_title !== '' ) {
        $url = add_query_arg( 'role', $job_title, $url );
    }

    return $url;
}

function cyma_get_template_slug() {
    if (is_front_page() || is_home()) {
        return 'front-page';
    }

    if (is_single()) {
        return 'single-' . get_post_type();
    }

    if (is_search()) {
        return 'search';
    }

    if (is_page()) {
        $slug = get_post_field('post_name', get_queried_object_id());
        if (locate_template('template-parts/head/page-' . $slug . '.php')) {
            return 'page-' . $slug;
        }
        return $slug;
    }

    return 'front-page';
}

function cyma_get_footer_slug() {
    if (is_front_page() || is_home()) {
        return 'front-page';
    }

    if (is_single()) {
        return 'single-' . get_post_type();
    }

    if (is_page()) {
        $slug = get_post_field('post_name', get_queried_object_id());
        if (locate_template('template-parts/footer/page-' . $slug . '.php')) {
            return 'page-' . $slug;
        }
    }

    return 'default';
}

// Load data from JSON files
function get_page_data($page_slug) {
    $data_file = get_template_directory() . '/_data/frontend-editor/' . $page_slug . '.json';
    if (file_exists($data_file)) {
        $json_content = file_get_contents($data_file);
        return json_decode($json_content, true);
    }
    return [];
}

// Global variable to store current page data
global $current_page_data;
$current_page_data = [];

function load_page_data($page_slug) {
    global $current_page_data;
    $current_page_data = get_page_data($page_slug);
    if ( function_exists( 'cyma_apply_content_overrides_to_page_data' ) ) {
        cyma_apply_content_overrides_to_page_data( $page_slug );
    }
}

function cyma_resolve_link($url) {
    if (empty($url) || $url === '#') {
        return '#';
    }

    if ($url === 'explore-careers' || $url === 'page-explore-careers') {
        return cyma_get_dice_jobs_url();
    }

    if (preg_match('#^https?://#i', $url)) {
        if ( function_exists( 'cyma_upgrade_http_url' ) ) {
            return cyma_upgrade_http_url( $url );
        }
        return $url;
    }

    if ($url === '/') {
        return home_url('/');
    }

    if ($url[0] === '/') {
        return home_url($url);
    }

    $slug = $url;
    if (strpos($slug, 'page-') === 0) {
        $slug = substr($slug, 5);
    }

    $page = get_page_by_path($slug);
    if ($page) {
        return get_permalink($page);
    }

    $page = get_page_by_path($url);
    if ($page) {
        return get_permalink($page);
    }

    return home_url('/' . trailingslashit($slug));
}

function cyma_resolve_asset($path) {
    if (empty($path)) {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        if ( function_exists( 'cyma_upgrade_http_url' ) ) {
            return cyma_upgrade_http_url( $path );
        }
        return $path;
    }

    if ($path[0] === '/') {
        return get_template_directory_uri() . $path;
    }

    return get_template_directory_uri() . '/assets/' . ltrim($path, '/');
}

// Theme helpers to load page content from JSON data files
function _u($key, $type = '') {
    global $current_page_data;
    if (!empty($type) && isset($current_page_data[$type][$key])) {
        $value = $current_page_data[$type][$key];
        if ($type === 'link') {
            return cyma_resolve_link($value);
        }
        if ($type === 'iframe') {
            return cyma_resolve_asset($value);
        }
        return $value;
    }
    // If type is empty, check if key exists in any top-level array
    if (empty($type) && isset($current_page_data[$key])) {
        return $current_page_data[$key];
    }
    return $key; // Return the key itself if not found
}

function cyma_get_image($key) {
    global $current_page_data;
    // Handle if key is already an array (from nested _u call)
    if (is_array($key)) {
        // If it's already an image data array, convert to object
        if (isset($key['src']) || isset($key['attachment_id'])) {
            if ( function_exists( 'cyma_normalize_image_data' ) ) {
                return cyma_normalize_image_data( $key );
            }
            $src = get_template_directory_uri() . $key['src'];
            return (object)[
                'src' => $src,
                'alt' => isset($key['alt']) ? $key['alt'] : '',
                'srcset' => $src
            ];
        }
        $key = isset($key[0]) ? $key[0] : '';
    }
    if (isset($current_page_data['img'][$key])) {
        $img_data = $current_page_data['img'][$key];
        if ( function_exists( 'cyma_normalize_image_data' ) ) {
            return cyma_normalize_image_data( $img_data );
        }
        $src = get_template_directory_uri() . $img_data['src'];
        return (object)[
            'src' => $src,
            'alt' => isset($img_data['alt']) ? $img_data['alt'] : '',
            'srcset' => $src
        ];
    }
    return (object)['src' => '', 'alt' => '', 'srcset' => ''];
}

$cyma_https = get_template_directory() . '/inc/https.php';
if ( file_exists( $cyma_https ) ) {
	require_once $cyma_https;
}

$cyma_lca_postings = get_template_directory() . '/inc/lca-postings.php';
if ( file_exists( $cyma_lca_postings ) ) {
	require_once $cyma_lca_postings;
}

$cyma_cms_content = get_template_directory() . '/inc/cms-content.php';
if ( file_exists( $cyma_cms_content ) ) {
	require_once $cyma_cms_content;
}

$cyma_cpt_editors = get_template_directory() . '/inc/cms-cpt-editors.php';
if ( file_exists( $cyma_cpt_editors ) ) {
	require_once $cyma_cpt_editors;
}

$cyma_contact_handler = get_template_directory() . '/inc/contact-form-handler.php';
if ( file_exists( $cyma_contact_handler ) ) {
	require_once $cyma_contact_handler;
}

function cyma_get_breadcrumb_html() {
    if (!is_page()) {
        return '';
    }

    $slug = get_post_field('post_name', get_queried_object_id());
    $home = home_url('/');

    $trails = array(
        'legal'                => array(
            array('Home', $home),
            array('Legal', '', true),
        ),
        'resources'            => array(
            array('Home', $home),
            array('Resources', home_url('/resources/'), true),
        ),
        'style-guide'          => array(
            array('Home', $home),
            array('Style Guide', home_url('/style-guide/'), true),
        ),
        'forgot-password'      => array(
            array('Home', $home),
            array('Forgot Password', '', true),
        ),
        'verification'         => array(
            array('Home', $home),
            array('Verification', '', true),
        ),
        'create-new-password'  => array(
            array('Home', $home),
            array('Create New Password', '', true),
        ),
        'password-updated'     => array(
            array('Home', $home),
            array('Password Updated', '', true),
        ),
        'email-verification'   => array(
            array('Home', $home),
            array('Email Verification', '', true),
        ),
        'homeduplicate'        => array(
            array('Home', $home),
            array('Home (Duplicate)', '', true),
        ),
        'explore-careers'      => array(
            array('Home', $home),
            array('Explore Careers', home_url('/explore-careers/'), true),
        ),
    );

    if (!isset($trails[$slug])) {
        return '';
    }

    $parts = array();
    foreach ($trails[$slug] as $item) {
        $label = $item[0];
        $url   = $item[1];
        $current = !empty($item[2]);

        if ($url && !$current) {
            $parts[] = '<a href="' . esc_url($url) . '" class="link-39">' . esc_html($label) . '</a>';
        } elseif ($url && $current) {
            $parts[] = '<a href="' . esc_url($url) . '" aria-current="page" class="link-39">' . esc_html($label) . '</a>';
        } else {
            $parts[] = '<strong>' . esc_html($label) . '</strong>';
        }
    }

    return implode(' • ', $parts);
}

function cyma_honeypot_field() {
    // Do nothing
}
        