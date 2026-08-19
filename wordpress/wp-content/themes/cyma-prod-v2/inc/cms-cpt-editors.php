<?php
/**
 * CMS editors for Case Studies, Insights, Careers, and Dice URL setting.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cyma_use_classic_editor_for_cpts( $use_block_editor, $post_type ) {
	if ( in_array( $post_type, array( 'casestudies', 'insights', 'explore-careers', 'lca_posting' ), true ) ) {
		return false;
	}
	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'cyma_use_classic_editor_for_cpts', 10, 2 );

function cyma_cpt_field_map( $post_type ) {
	if ( 'casestudies' === $post_type ) {
		return array(
			'industry'            => array( 'Industry', 'text' ),
			'heading-text'        => array( 'Heading', 'text' ),
			'about-the-client'    => array( 'About the client', 'textarea' ),
			'requirements'        => array( 'Requirements', 'textarea' ),
			'the-challenge'       => array( 'The challenge', 'textarea' ),
			'cyma-s-approach-1'   => array( 'CYMA’s approach', 'textarea' ),
			'execution-1'         => array( 'Execution', 'textarea' ),
			'execution-2'         => array( 'Execution (continued)', 'textarea' ),
			'the-impact'          => array( 'The impact', 'textarea' ),
		);
	}
	if ( 'insights' === $post_type ) {
		return array(
			'heading'     => array( 'Hero heading (optional)', 'text' ),
			'sub-text'    => array( 'Excerpt / subtext', 'textarea' ),
			'text-overlay'=> array( 'Hero overlay text', 'text' ),
		);
	}
	if ( 'explore-careers' === $post_type ) {
		return array(
			'location'   => array( 'Location', 'text' ),
			'job-type'   => array( 'Job type', 'text' ),
			'skills'     => array( 'Skills (comma-separated)', 'text' ),
			'posted'     => array( 'Posted label', 'text' ),
		);
	}
	return array();
}

function cyma_cpt_add_meta_boxes() {
	add_meta_box( 'cyma_casestudies_fields', 'Case study details', 'cyma_cpt_render_meta_box', 'casestudies', 'normal', 'high' );
	add_meta_box( 'cyma_insights_fields', 'Insight details', 'cyma_cpt_render_meta_box', 'insights', 'normal', 'high' );
	add_meta_box( 'cyma_careers_fields', 'Job details', 'cyma_cpt_render_meta_box', 'explore-careers', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'cyma_cpt_add_meta_boxes' );

function cyma_cpt_render_meta_box( $post ) {
	$fields = cyma_cpt_field_map( $post->post_type );
	if ( empty( $fields ) ) {
		return;
	}
	wp_nonce_field( 'cyma_cpt_fields_' . $post->post_type, 'cyma_cpt_fields_nonce' );
	echo '<p>These fields appear on the live site. Title, featured image, and the editor above are also used.</p>';
	foreach ( $fields as $key => $config ) {
		$label = $config[0];
		$type  = $config[1];
		$value = function_exists( 'cyma_get_cms_meta' ) ? cyma_get_cms_meta( $key, $post->ID ) : (string) get_post_meta( $post->ID, $key, true );
		echo '<p><label for="cyma_field_' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( 'textarea' === $type ) {
			echo '<textarea id="cyma_field_' . esc_attr( $key ) . '" name="cyma_field[' . esc_attr( $key ) . ']" rows="5" style="width:100%">' . esc_textarea( $value ) . '</textarea>';
		} else {
			echo '<input type="text" id="cyma_field_' . esc_attr( $key ) . '" name="cyma_field[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" style="width:100%">';
		}
		echo '</p>';
	}
}

function cyma_cpt_save_meta_boxes( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$post = get_post( $post_id );
	if ( ! $post || ! isset( $_POST['cyma_cpt_fields_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cyma_cpt_fields_nonce'] ) ), 'cyma_cpt_fields_' . $post->post_type ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = cyma_cpt_field_map( $post->post_type );
	if ( empty( $fields ) || empty( $_POST['cyma_field'] ) || ! is_array( $_POST['cyma_field'] ) ) {
		return;
	}
	foreach ( $fields as $key => $config ) {
		if ( ! isset( $_POST['cyma_field'][ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST['cyma_field'][ $key ] );
		update_post_meta( $post_id, $key, 'textarea' === $config[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw ) );
	}
}
add_action( 'save_post', 'cyma_cpt_save_meta_boxes' );

function cyma_register_site_settings() {
	register_setting(
		'cyma_site_settings',
		'cyma_dice_jobs_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);
}
add_action( 'admin_init', 'cyma_register_site_settings' );

function cyma_add_site_settings_page() {
	add_options_page(
		'CYMA Site Settings',
		'CYMA Site',
		'manage_options',
		'cyma-site-settings',
		'cyma_render_site_settings_page'
	);
}
add_action( 'admin_menu', 'cyma_add_site_settings_page' );

function cyma_render_site_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$dice = get_option( 'cyma_dice_jobs_url', '' );
	?>
	<div class="wrap">
		<h1>CYMA Site Settings</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'cyma_site_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cyma_dice_jobs_url">Dice open-roles URL</label></th>
					<td>
						<input name="cyma_dice_jobs_url" id="cyma_dice_jobs_url" type="url" class="regular-text" value="<?php echo esc_attr( $dice ); ?>">
						<p class="description">Used on Job Seekers / Explore Careers CTAs. Leave blank to use the default Cyma Systems Dice search.</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<hr>
		<h2>Where to edit content</h2>
		<ul>
			<li><strong>Pages</strong> — homepage, About, services, Industries, Contact, login, Legal, H-1B LCA page chrome, Insights hub copy, Case Studies hub copy.</li>
			<li><strong>H-1B LCA</strong> — individual LCA postings and downloads.</li>
			<li><strong>Case Studies</strong> — each case study’s title, image, and details.</li>
			<li><strong>Insights</strong> — Latest News articles (title, excerpt, body).</li>
			<li><strong>Careers</strong> — featured / open roles on Job Seekers.</li>
			<li><strong>Tools → CYMA Content Seed</strong> — re-import design HTML into Pages if needed.</li>
		</ul>
	</div>
	<?php
}

function cyma_maybe_seed_insights_page() {
	if ( get_option( 'cyma_insights_hub_seeded' ) ) {
		return;
	}
	$page = get_page_by_path( 'insights' );
	if ( ! $page || ! function_exists( 'cyma_seed_page_content' ) ) {
		return;
	}
	if ( cyma_seed_page_content( $page->ID, false ) || cyma_page_uses_cms_content( $page->ID ) ) {
		update_option( 'cyma_insights_hub_seeded', 1, false );
	}
}
add_action( 'admin_init', 'cyma_maybe_seed_insights_page' );

function cyma_maybe_rename_insight_pages() {
	if ( get_option( 'cyma_insight_page_titles_synced' ) ) {
		return;
	}
	$pages = get_pages( array( 'post_status' => 'publish' ) );
	foreach ( $pages as $page ) {
		if ( ! preg_match( '/^insights-\d+$/', $page->post_name ) ) {
			continue;
		}
		if ( strcasecmp( $page->post_title, 'Insights' ) !== 0 ) {
			continue;
		}
		if ( ! preg_match( '/<h1[^>]*heading-158[^>]*>(.*?)<\/h1>/is', $page->post_content, $match ) ) {
			continue;
		}
		$title = trim( wp_strip_all_tags( $match[1] ) );
		if ( $title === '' ) {
			continue;
		}
		wp_update_post(
			array(
				'ID'         => $page->ID,
				'post_title' => $title,
			)
		);
	}
	update_option( 'cyma_insight_page_titles_synced', 1, false );
}
add_action( 'admin_init', 'cyma_maybe_rename_insight_pages' );
