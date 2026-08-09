<?php
/**
 * Sync explore-careers posts from live Cyma job openings.
 * Source: https://cymasys.com/job-seekers/job-openings/ (WP REST jobpost CPT)
 *
 * Run:
 *   docker exec cyma-wordpress php /var/www/html/wp-content/themes/cyma-prod-v2/import-careers-from-cymasys.php
 */
require_once '/var/www/html/wp-load.php';

$api_url = 'https://cymasys.com/wp-json/wp/v2/jobpost?per_page=100&status=publish';

$response = wp_remote_get(
	$api_url,
	array(
		'timeout' => 30,
		'headers' => array(
			'User-Agent' => 'CYMA-WordPress-Sync/1.0',
			'Accept'     => 'application/json',
		),
	)
);

if ( is_wp_error( $response ) ) {
	fwrite( STDERR, 'Fetch failed: ' . $response->get_error_message() . "\n" );
	exit( 1 );
}

$code = (int) wp_remote_retrieve_response_code( $response );
$body = wp_remote_retrieve_body( $response );
$jobs = json_decode( $body, true );

if ( 200 !== $code || ! is_array( $jobs ) ) {
	fwrite( STDERR, "Unexpected API response (HTTP {$code}).\n" );
	exit( 1 );
}

/**
 * Split job HTML into sub-text, bullet list, and mailing footer.
 *
 * @param string $html Job content HTML.
 * @return array{sub_text:string,list:string[],card_under_text:string}
 */
function cyma_parse_jobpost_content( $html ) {
	$html  = (string) $html;
	$paras = array();
	if ( preg_match_all( '#<p\b[^>]*>(.*?)</p>#is', $html, $matches ) ) {
		foreach ( $matches[1] as $para ) {
			$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $para ) ) );
			if ( $text !== '' ) {
				$paras[] = $text;
			}
		}
	}

	if ( empty( $paras ) ) {
		$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $html ) ) );
		if ( $text !== '' ) {
			$paras[] = $text;
		}
	}

	$card_under = '';
	$body_paras = $paras;
	if ( ! empty( $paras ) ) {
		$last = $paras[ count( $paras ) - 1 ];
		if ( stripos( $last, 'Mail Resume' ) !== false || stripos( $last, 'HR Dept' ) !== false ) {
			$card_under = $last;
			$body_paras = array_slice( $paras, 0, -1 );
		}
	}

	$body = trim( implode( ' ', $body_paras ) );
	$list = array();
	if ( $body !== '' ) {
		$parts = preg_split( '/(?<=\.)\s+(?=[A-Z])/', $body );
		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( $part !== '' ) {
				$list[] = $part;
			}
		}
	}

	$sub_text = '';
	if ( count( $list ) >= 2 ) {
		$sub_text = $list[0] . ' ' . $list[1];
	} elseif ( ! empty( $list ) ) {
		$sub_text = $list[0];
	} else {
		$sub_text = $body;
	}

	if ( $card_under === '' ) {
		$card_under = 'Mail Resume to HR Dept., Cyma Systems, Inc., 360 Tolland Turnpike, Suite 2D, Manchester, CT 06042.';
	}

	return array(
		'sub_text'        => $sub_text,
		'list'            => array_slice( $list, 0, 13 ),
		'card_under_text' => $card_under,
	);
}

$synced_slugs = array();

foreach ( $jobs as $job ) {
	$title   = isset( $job['title']['rendered'] ) ? html_entity_decode( wp_strip_all_tags( $job['title']['rendered'] ), ENT_QUOTES, 'UTF-8' ) : '';
	$slug    = isset( $job['slug'] ) ? sanitize_title( $job['slug'] ) : sanitize_title( $title );
	$content = isset( $job['content']['rendered'] ) ? $job['content']['rendered'] : '';
	$excerpt = isset( $job['excerpt']['rendered'] ) ? trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $job['excerpt']['rendered'] ) ) ) : '';

	if ( $title === '' || $slug === '' ) {
		continue;
	}

	$parsed   = cyma_parse_jobpost_content( $content );
	$sub_text = $excerpt !== '' ? $excerpt : $parsed['sub_text'];

	$existing = get_posts(
		array(
			'post_type'      => 'explore-careers',
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		)
	);

	$postarr = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'explore-careers',
		'post_content' => $content,
	);

	if ( $existing ) {
		$post_id         = $existing[0]->ID;
		$postarr['ID']   = $post_id;
		$updated         = wp_update_post( $postarr, true );
		$action          = is_wp_error( $updated ) ? 'failed-update' : 'updated';
		if ( is_wp_error( $updated ) ) {
			echo "Failed update: {$title} - " . $updated->get_error_message() . "\n";
			continue;
		}
	} else {
		$post_id = wp_insert_post( $postarr, true );
		$action  = is_wp_error( $post_id ) ? 'failed-insert' : 'created';
		if ( is_wp_error( $post_id ) ) {
			echo "Failed create: {$title} - " . $post_id->get_error_message() . "\n";
			continue;
		}
	}

	update_post_meta( $post_id, 'main-heading', $title );
	update_post_meta( $post_id, 'sub-text', $sub_text );
	update_post_meta( $post_id, 'card-under-text', $parsed['card_under_text'] );
	update_post_meta( $post_id, 'apply', 'Apply Now' );
	update_post_meta( $post_id, 'readmore', 'View Role' );
	update_post_meta( $post_id, '_cyma_source', 'cymasys.com' );
	update_post_meta( $post_id, '_cyma_source_id', isset( $job['id'] ) ? (string) $job['id'] : '' );

	for ( $i = 1; $i <= 13; $i++ ) {
		$key = 'list-' . $i;
		if ( isset( $parsed['list'][ $i - 1 ] ) ) {
			update_post_meta( $post_id, $key, $parsed['list'][ $i - 1 ] );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}

	$synced_slugs[] = $slug;
	echo ucfirst( $action ) . ": {$title} (ID: {$post_id}, slug: {$slug})\n";
}

// Draft local careers that are no longer on the live openings page.
$local = get_posts(
	array(
		'post_type'      => 'explore-careers',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
	)
);

foreach ( $local as $post ) {
	if ( in_array( $post->post_name, $synced_slugs, true ) ) {
		continue;
	}
	if ( 'draft' === $post->post_status ) {
		echo "Already draft (not on cymasys): {$post->post_title}\n";
		continue;
	}
	wp_update_post(
		array(
			'ID'          => $post->ID,
			'post_status' => 'draft',
		)
	);
	echo "Drafted (not on cymasys): {$post->post_title} ({$post->post_name})\n";
}

flush_rewrite_rules( false );
echo 'Sync complete. Live openings imported: ' . count( $synced_slugs ) . "\n";
