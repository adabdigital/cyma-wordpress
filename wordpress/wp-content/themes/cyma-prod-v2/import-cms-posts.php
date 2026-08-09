<?php
/**
 * Import casestudies and insights posts from _data/data.json.
 *
 * Run: docker exec cyma-wordpress php /var/www/html/wp-content/themes/cyma-prod-v2/import-cms-posts.php
 */
require_once '/var/www/html/wp-load.php';

$data_file = get_template_directory() . '/_data/data.json';
if ( ! file_exists( $data_file ) ) {
	echo "No data.json found.\n";
	exit( 1 );
}

$data  = json_decode( file_get_contents( $data_file ), true );
$posts = isset( $data['posts'] ) ? $data['posts'] : array();
$types = array( 'casestudies', 'insights' );

foreach ( $posts as $item ) {
	if ( empty( $item['post_type'] ) || ! in_array( $item['post_type'], $types, true ) ) {
		continue;
	}

	$slug     = $item['post_name'];
	$existing = get_posts(
		array(
			'post_type'      => $item['post_type'],
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		)
	);

	if ( $existing ) {
		echo ucfirst( $item['post_type'] ) . " exists: {$item['post_title']} ($slug)\n";
		continue;
	}

	$post_id = wp_insert_post(
		array(
			'post_title'   => $item['post_title'],
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => $item['post_type'],
			'post_content' => isset( $item['post_content'] ) ? $item['post_content'] : '',
		)
	);

	if ( is_wp_error( $post_id ) ) {
		echo "Failed: {$item['post_title']} - " . $post_id->get_error_message() . "\n";
		continue;
	}

	if ( ! empty( $item['custom_meta_input'] ) && is_array( $item['custom_meta_input'] ) ) {
		foreach ( $item['custom_meta_input'] as $key => $field ) {
			if ( isset( $field['value'] ) && $field['value'] !== null && $field['value'] !== '' ) {
				update_post_meta( $post_id, $key, $field['value'] );
			}
		}
	}

	echo 'Imported ' . $item['post_type'] . ": {$item['post_title']} (ID: $post_id)\n";
}

$page = get_page_by_path( 'insights' );
if ( ! $page ) {
	$page_id = wp_insert_post(
		array(
			'post_title'  => 'Latest News',
			'post_name'   => 'insights',
			'post_status' => 'publish',
			'post_type'   => 'page',
		)
	);
	echo "Created insights hub page (ID: $page_id)\n";
}

flush_rewrite_rules( false );
echo "CMS posts import complete.\n";
