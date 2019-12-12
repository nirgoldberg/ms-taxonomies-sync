<?php
/**
 * Broadcast functions
 *
 * @author		Nir Goldberg
 * @package		includes/api
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * mstaxsync_bulk_broadcast
 *
 * @since		1.0.0
 * @param		N/A
 * @return		N/A
 */
function mstaxsync_bulk_broadcast() {

	// verify nonce
	if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'mstaxsync_quick_edit_post_broadcast' ) ) {
		exit();
	}

	/**
	 * Variables
	 */
	$post_ids	= $_REQUEST[ 'post_ids' ];
	$dest_sites	= $_REQUEST[ 'dest_sites' ];

	if ( ! is_array( $post_ids ) || empty( $post_ids ) || ! is_array( $dest_sites ) || empty( $dest_sites ) ) {
		exit();
	}

	// broadcast posts
	mstaxsync_broadcast_posts( $post_ids, $dest_sites );

	// die
	die();

}
add_action( 'wp_ajax_bulk_broadcast', 'mstaxsync_bulk_broadcast' );

/**
 * mstaxsync_broadcast_posts
 *
 * @since		1.0.0
 * @param		$post_ids (array)
 * @param		$sites (array)
 * @return		N/A
 */
function mstaxsync_broadcast_posts( $post_ids, $sites ) {

	if ( ! is_array( $post_ids ) || empty( $post_ids ) || ! is_array( $sites ) || empty( $sites ) )
		return;

	foreach ( $post_ids as $post_id ) {

		// get post
		$post = get_post( $post_id );

		if ( $post ) {

			// broadcast single post
			mstaxsync_broadcast()->broadcast_single_post( $post, $sites );

		}

	}

}