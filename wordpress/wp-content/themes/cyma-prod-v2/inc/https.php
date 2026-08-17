<?php
/**
 * HTTPS helpers for production (Sucuri / reverse proxy) without breaking local Docker HTTP.
 *
 * Localhost and 127.0.0.1 stay on http:// — browsers will show "Not secure"; that is expected.
 */

defined( 'ABSPATH' ) || exit;

/**
 * True when the request host is a local / preview environment that must stay on HTTP.
 *
 * @return bool
 */
function cyma_is_local_https_exempt_host() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';
	$host = preg_replace( '/:\d+$/', '', $host );

	if ( $host === '' ) {
		return true;
	}

	if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
		return true;
	}

	if ( substr( $host, -6 ) === '.local' || substr( $host, -5 ) === '.test' ) {
		return true;
	}

	// Cloudflare quick tunnels used for local previews.
	if ( strpos( $host, 'trycloudflare.com' ) !== false ) {
		return true;
	}

	return false;
}

/**
 * Known public hostnames for this project (apex + www).
 *
 * @return bool
 */
function cyma_is_production_host() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';
	$host = preg_replace( '/:\d+$/', '', $host );

	return in_array( $host, array( 'cymasys.com', 'www.cymasys.com' ), true );
}

/**
 * Whether the current request should be treated as HTTPS (incl. reverse proxies).
 *
 * @return bool
 */
function cyma_request_is_https() {
	if ( is_ssl() ) {
		return true;
	}

	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strpos( (string) $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https' ) !== false ) {
		return true;
	}

	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && strtolower( (string) $_SERVER['HTTP_X_FORWARDED_SSL'] ) === 'on' ) {
		return true;
	}

	return false;
}

/**
 * Upgrade http:// URLs to https:// for same-site / known asset hosts.
 * Leaves localhost and non-upgradeable third-party http links alone.
 *
 * @param string $url Absolute or relative URL.
 * @return string
 */
function cyma_upgrade_http_url( $url ) {
	if ( ! is_string( $url ) || $url === '' || stripos( $url, 'http://' ) !== 0 ) {
		return $url;
	}

	if ( preg_match( '#^http://(localhost|127\.0\.0\.1|\[::1\])(:\d+)?(/|$)#i', $url ) ) {
		return $url;
	}

	$upgrade_hosts = array(
		'cymasys.com',
		'www.cymasys.com',
		'd3e54v103j8qbb.cloudfront.net',
		'cdn.prod.website-files.com',
		'fonts.googleapis.com',
		'fonts.gstatic.com',
		'ajax.googleapis.com',
		'cdn.jsdelivr.net',
	);

	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	if ( $host === '' ) {
		return $url;
	}

	$home_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	if ( $home_host && $host === $home_host ) {
		return set_url_scheme( $url, 'https' );
	}

	if ( in_array( $host, $upgrade_hosts, true ) ) {
		return set_url_scheme( $url, 'https' );
	}

	return $url;
}

/**
 * When the request is HTTPS (or production behind a proxy), keep option URLs on https.
 *
 * @param string $url Option URL.
 * @return string
 */
function cyma_force_https_option_url( $url ) {
	if ( cyma_is_local_https_exempt_host() ) {
		return $url;
	}

	if ( cyma_request_is_https() || cyma_is_production_host() ) {
		return set_url_scheme( $url, 'https' );
	}

	return $url;
}
add_filter( 'option_home', 'cyma_force_https_option_url', 1 );
add_filter( 'option_siteurl', 'cyma_force_https_option_url', 1 );

/**
 * Prefer https scheme for generated WordPress URLs on secure / production requests.
 *
 * @param string $url    URL.
 * @param string $path   Path.
 * @param string|null $scheme Requested scheme.
 * @return string
 */
function cyma_force_https_url_scheme( $url, $path = null, $scheme = null ) {
	unset( $path, $scheme );

	if ( cyma_is_local_https_exempt_host() ) {
		return $url;
	}

	if ( cyma_request_is_https() || cyma_is_production_host() ) {
		return set_url_scheme( $url, 'https' );
	}

	return $url;
}
add_filter( 'home_url', 'cyma_force_https_url_scheme', 1, 3 );
add_filter( 'site_url', 'cyma_force_https_url_scheme', 1, 3 );
add_filter( 'content_url', 'cyma_force_https_url_scheme', 1, 2 );
add_filter( 'plugins_url', 'cyma_force_https_url_scheme', 1, 3 );
add_filter( 'script_loader_src', 'cyma_upgrade_http_url', 20 );
add_filter( 'style_loader_src', 'cyma_upgrade_http_url', 20 );
add_filter( 'wp_get_attachment_url', 'cyma_upgrade_http_url', 20 );

/**
 * Upgrade http:// URLs inside calculated image srcset sources.
 *
 * @param array $sources Srcset sources from wp_calculate_image_srcset.
 * @return array
 */
function cyma_upgrade_http_srcset_sources( $sources ) {
	if ( ! is_array( $sources ) || cyma_is_local_https_exempt_host() ) {
		return $sources;
	}

	if ( ! cyma_request_is_https() && ! cyma_is_production_host() ) {
		return $sources;
	}

	foreach ( $sources as $width => $source ) {
		if ( ! empty( $source['url'] ) ) {
			$sources[ $width ]['url'] = cyma_upgrade_http_url( $source['url'] );
		}
	}

	return $sources;
}
add_filter( 'wp_calculate_image_srcset', 'cyma_upgrade_http_srcset_sources', 20 );

/**
 * Soft-upgrade common http:// leftovers in post content on HTTPS pages.
 *
 * @param string $content HTML.
 * @return string
 */
function cyma_upgrade_insecure_content_urls( $content ) {
	if ( cyma_is_local_https_exempt_host() || ! is_string( $content ) || $content === '' ) {
		return $content;
	}

	if ( ! cyma_request_is_https() && ! cyma_is_production_host() ) {
		return $content;
	}

	$hosts = array(
		'cymasys.com',
		'www.cymasys.com',
		'd3e54v103j8qbb.cloudfront.net',
		'cdn.prod.website-files.com',
	);

	foreach ( $hosts as $host ) {
		$content = str_ireplace( 'http://' . $host, 'https://' . $host, $content );
	}

	return $content;
}
add_filter( 'the_content', 'cyma_upgrade_insecure_content_urls', 9 );
add_filter( 'widget_text', 'cyma_upgrade_insecure_content_urls', 9 );

/**
 * Redirect cleartext requests to HTTPS on production hosts only (never localhost).
 */
function cyma_force_https_redirect() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	if ( cyma_is_local_https_exempt_host() || cyma_request_is_https() ) {
		return;
	}

	if ( ! cyma_is_production_host() ) {
		return;
	}

	$host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : 'cymasys.com';
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
	wp_redirect( 'https://' . $host . $uri, 301 );
	exit;
}
add_action( 'template_redirect', 'cyma_force_https_redirect', 1 );

/**
 * Send upgrade-insecure-requests on HTTPS responses (safe; skipped for local HTTP).
 * Do not send HSTS from the theme — enable that at Sucuri/hosting once cert monitoring is in place.
 */
function cyma_send_https_headers() {
	if ( headers_sent() || cyma_is_local_https_exempt_host() ) {
		return;
	}

	if ( ! cyma_request_is_https() && ! cyma_is_production_host() ) {
		return;
	}

	header( 'Content-Security-Policy: upgrade-insecure-requests', false );
}
add_action( 'send_headers', 'cyma_send_https_headers', 1 );
