<?php
/**
 * Create the explore-careers WordPress page and import job posts from _data/data.json.
 * Run: docker exec cyma-wordpress php /var/www/html/wp-content/themes/cyma-708003/import-careers.php
 */
require_once '/var/www/html/wp-load.php';

$page = get_page_by_path( 'explore-careers' );
if ( ! $page ) {
	$page_id = wp_insert_post(
		array(
			'post_title'   => 'Explore Careers',
			'post_name'    => 'explore-careers',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		)
	);
	echo "Created page: Explore Careers (ID: $page_id)\n";
} else {
	echo "Page already exists: explore-careers (ID: {$page->ID})\n";
}

$data_file = get_template_directory() . '/_data/data.json';
if ( ! file_exists( $data_file ) ) {
	echo "No data.json found.\n";
	exit( 1 );
}

$data  = json_decode( file_get_contents( $data_file ), true );
$posts = isset( $data['posts'] ) ? $data['posts'] : array();

foreach ( $posts as $item ) {
	if ( empty( $item['post_type'] ) || $item['post_type'] !== 'explore-careers' ) {
		continue;
	}

	$slug = $item['post_name'];
	$existing = get_posts(
		array(
			'post_type'      => 'explore-careers',
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		)
	);

	if ( $existing ) {
		echo "Job exists: {$item['post_title']} ($slug)\n";
		continue;
	}

	$post_id = wp_insert_post(
		array(
			'post_title'   => $item['post_title'],
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'explore-careers',
			'post_content' => '',
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

	echo "Imported job: {$item['post_title']} (ID: $post_id)\n";
}

flush_rewrite_rules( false );
echo "Careers import complete.\n";
