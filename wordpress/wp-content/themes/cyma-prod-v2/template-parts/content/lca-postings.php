<?php
$postings = function_exists( 'cyma_get_lca_postings' ) ? cyma_get_lca_postings() : array();
if ( empty( $postings ) ) {
	return;
}

$backgrounds = array( 'bg-lca1.webp', 'bg-lca2.webp', 'bg-lca3.webp' );
$resolve_bg = static function ( $index ) use ( $backgrounds ) {
	$file = $backgrounds[ $index % 3 ];
	if ( file_exists( get_template_directory() . '/assets/images/' . $file ) ) {
		return get_template_directory_uri() . '/assets/images/' . $file;
	}
	return get_template_directory_uri() . '/assets/images/bg-lca1.webp';
};

$chunks = array_chunk( $postings, 3 );
foreach ( $chunks as $chunk_index => $chunk ) :
	$bg = $resolve_bg( $chunk_index );
	?>
<div style="background-image:url('<?php echo esc_url( $bg ); ?>');background-size:cover;background-position:center;background-repeat:no-repeat;padding:20px 0">
	<?php foreach ( $chunk as $posting ) : ?>
	<div class="div-block-1295">
		<h2 class="heading-125"><?php echo esc_html( $posting['title'] ); ?></h2>
		<ul role="list" class="list-4">
			<li class="list-item-19"><?php echo esc_html( cyma_lca_intro_text() ); ?></li>
			<li class="list-item-20">One such worker is sought.</li>
			<li class="list-item-21"><?php echo esc_html( $posting['role'] ); ?></li>
			<li class="list-item-22"><?php echo esc_html( $posting['wage'] ); ?></li>
			<li class="list-item-23"><?php echo esc_html( $posting['period'] ); ?></li>
			<li class="list-item-24"><?php echo esc_html( $posting['location'] ); ?></li>
			<li class="list-item-25"><?php echo esc_html( cyma_lca_inspection_text() ); ?></li>
		</ul>
		<p class="text-block-627" style="margin-top:12px">
			<a href="<?php echo esc_url( $posting['doc'] ); ?>" target="_blank" rel="noopener noreferrer">Download LCA posting (<?php echo esc_html( $posting['doc_label'] ); ?>)</a>
		</p>
		<div class="text-block-627"><?php echo esc_html( cyma_lca_complaint_text() ); ?></div>
	</div>
	<?php endforeach; ?>
</div>
<?php endforeach; ?>
