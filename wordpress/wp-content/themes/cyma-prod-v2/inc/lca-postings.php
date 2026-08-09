<?php
/**
 * H-1B LCA public posting notices and document links.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array<string, string>>
 */
function cyma_get_lca_postings() {
	$base = trailingslashit( get_template_directory_uri() ) . 'assets/docs/lca/';

	return array(
		array(
			'title'    => 'Senior Data Engineer — Yarabarla',
			'role'     => 'This worker is being sought as a Senior Data Engineer (15-1252 SOC O*NET Code).',
			'wage'     => 'Wages of $135,000 per year are being offered to this worker.',
			'period'   => 'The period of employment is 10/01/2026 to 09/30/2029.',
			'location' => 'The location where the H-1B employee will work: 10512 Amarillo Lane, Aubrey, TX 76227.',
			'doc'      => $base . rawurlencode( 'Cyma LCA Posting Yarabarla April 2026.doc' ),
			'doc_label'=> 'Cyma LCA Posting Yarabarla April 2026.doc',
		),
		array(
			'title'    => 'SharePoint Web Application Developer — Daggula Ramya',
			'role'     => 'This worker is being sought as a SharePoint Web Application Developer (15-1252 SOC O*NET Code).',
			'wage'     => 'Wages of $121,500 per year are being offered to this worker.',
			'period'   => 'The period of employment is 12/15/2026 to 12/14/2029.',
			'location' => 'The location where the H-1B employee will work: 1232 Patterson Terrace, Lake Mary, FL 32746.',
			'doc'      => $base . rawurlencode( 'Cyma LCA Posting Daggula Ramya June 2026.doc' ),
			'doc_label'=> 'Cyma LCA Posting Daggula Ramya June 2026.doc',
		),
		array(
			'title'    => 'Sr Data Engineer — Daggula Ranjith',
			'role'     => 'This worker is being sought as an Sr Data Engineer (15-1252 SOC O*NET Code).',
			'wage'     => 'Wages of $126,100 per year are being offered to this worker.',
			'period'   => 'The period of employment is 07/15/2026 to 07/14/2029.',
			'location' => 'The location where the H-1B employee will work: Kroger Blue Ash Tech Center (BTC), 11450 Grooms Road, Blue Ash, OH 45242.',
			'doc'      => $base . rawurlencode( 'Cyma LCA Posting Daggula Ranjith July 2026.doc' ),
			'doc_label'=> 'Cyma LCA Posting Daggula Ranjith July 2026.doc',
		),
		array(
			'title'    => 'Sr Oracle EBS Developer — Marinaicker',
			'role'     => 'This worker is being sought as a Sr Oracle EBS Developer (15-1252 SOC O*NET Code).',
			'wage'     => 'Wages of $131,500 per year are being offered to this worker.',
			'period'   => 'The period of employment is 12/01/2026 to 11/30/2029.',
			'location' => 'The locations where the H-1B employee will work: Virginia State Police, 7700 Midlothian Turnpike, North Chesterfield, VA 23235; 11456 Hayloft Lane, Glen Allen, VA 23060.',
			'doc'      => $base . rawurlencode( 'Cyma LCA Posting Marinaicker June 2026.doc' ),
			'doc_label'=> 'Cyma LCA Posting Marinaicker June 2026.doc',
		),
		array(
			'title'    => 'Senior Big Data Developer — Vangavaragu',
			'role'     => 'This worker is being sought as an Senior Big Data Developer (15-1252 SOC O*NET Code).',
			'wage'     => 'Wages of $139,000 per year are being offered to this worker.',
			'period'   => 'The period of employment is 08/28/2026 to 08/27/2029.',
			'location' => 'The location where the H-1B employee will work: 4310 Blackwood Street, Prosper, TX 75078.',
			'doc'      => $base . rawurlencode( 'Cyma LCA Posting Vangavaragu July 2026.doc' ),
			'doc_label'=> 'Cyma LCA Posting Vangavaragu July 2026.doc',
		),
	);
}

function cyma_lca_intro_text() {
	return 'H-1B nonimmigrant worker is being sought by Cyma Systems Inc through the filing of a labor condition application with the Employment and Training Administration of the U.S. Department of Labor.';
}

function cyma_lca_preamble_text() {
	return 'Pursuant to 20 CFR 655.734, you are hereby notified that H-1B nonimmigrants are being sought and that a Labor Condition Application (“LCA”) will be (or has been) filed for the following occupation:';
}

function cyma_lca_inspection_text() {
	return 'The Labor Condition Application is available for public inspection at the offices of Cyma Systems Inc, 360 Tolland Turnpike, Suite 2D, Manchester, CT 06042.';
}

function cyma_lca_complaint_text() {
	return 'Complaints alleging misrepresentation of material facts in the labor condition application and/or failure to comply with the terms of the labor condition application may be filed with any office of the wage and hour division of the United States Department of Labor.';
}
