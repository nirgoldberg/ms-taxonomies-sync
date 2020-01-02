<?php
/**
 * Import functions
 *
 * @author		Nir Goldberg
 * @package		includes/api
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * mstaxsync_import_taxonomy_term_posts
 *
 * @since		1.0.0
 * @param		N/A
 * @return		N/A
 */
function mstaxsync_import_taxonomy_term_posts() {

	// verify nonce
	if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'import_taxonomy_term' ) ) {
		exit();
	}

	/**
	 * Variables
	 */
	$main_term_id = $_REQUEST[ 'main_term_id' ];

	if ( ! $main_term_id ) {
		exit();
	}

	// import posts
//	mstaxsync_import_posts( $main_term_id );

	// die
	die();

}
add_action( 'wp_ajax_import_taxonomy_term_posts', 'mstaxsync_import_taxonomy_term_posts' );