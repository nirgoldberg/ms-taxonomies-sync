<?php
/**
 * Posts functions
 *
 * @author		Nir Goldberg
 * @package		includes/api
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * mstaxsync_update_post_correlation
 *
 * This function will provide a post correlation between main and local sites
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site post ID
 * @param		$local_id (int) Local site post ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_update_post_correlation( $main_id, $local_id, &$result ) {

	if ( ! $main_id || ! $local_id )
		return;

	// update local site post
	mstaxsync_update_local_post_correlation( $main_id, $local_id, $result );

	// update main site post
	mstaxsync_update_main_post_correlation( $main_id, $local_id, $result );

}

/**
 * mstaxsync_update_local_post_correlation
 *
 * This function will provide a post local site correlation
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site post ID
 * @param		$local_id (int) Local site post ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_update_local_post_correlation( $main_id, $local_id, &$result ) {

	// update local site post
	$meta_id = update_post_meta( $local_id, 'main_post', $main_id );

	if ( ! $meta_id ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> '31',
			'description'	=> __( 'Failed to update a post correlation (local site post)', 'mstaxsync' ),
		), $result );

	}

}

/**
 * mstaxsync_update_main_post_correlation
 *
 * This function will provide a post main site correlation
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site post ID
 * @param		$local_id (int) Local site post ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_update_main_post_correlation( $main_id, $local_id, &$result ) {

	/**
	 * Variables
	 */
	$site_id = get_current_blog_id();

	// get main site ID
	$main_site_id = mstaxsync_get_main_site_id();

	switch_to_blog( $main_site_id );

	// get current post meta
	$synced_posts = get_post_meta( $main_id, 'synced_posts', true );

	if ( ! $synced_posts ) {
		$synced_posts = array();
	}

	$synced_posts[ $site_id ] = $local_id;

	// update main site post
	$meta_id = update_post_meta( $main_id, 'synced_posts', $synced_posts );

	if ( ! $meta_id ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> '32',
			'description'	=> __( 'Failed to update a post correlation (main site post)', 'mstaxsync' ),
		), $result );

	}

	restore_current_blog();

}

/**
 * mstaxsync_before_delete_post
 *
 * @since		1.0.0
 * @param		$post_id (int) Post ID
 * @return		N/A
 */
function mstaxsync_before_delete_post( $post_id ) {

	if ( 'post' != get_post_type( $post_id ) )
		return;

	/**
	 * Variables
	 */
	$main_site = is_main_site();

	if ( $main_site ) {

		// main site
		// get current post meta
		$synced_posts = get_post_meta( $post_id, 'synced_posts', true );

		if ( $synced_posts ) {
			foreach ( $synced_posts as $site_id => $p_id ) {

				switch_to_blog( $site_id );

				// detach local site post
				delete_post_meta( $p_id, 'main_post' );

				restore_current_blog();

			}
		}

	}
	else {

		// local site
		// get current site ID
		$site_id = get_current_blog_id();

		// get current post meta
		$main_post = get_post_meta( $post_id, 'main_post', true );

		if ( $main_post ) {

			// get main site ID
			$main_site_id = mstaxsync_get_main_site_id();

			switch_to_blog( $main_site_id );

			// get current post meta
			$synced_posts = get_post_meta( $main_post, 'synced_posts', true );

			if ( ! $synced_posts ) {
				$synced_posts = array();
			}

			if ( array_key_exists( $site_id, $synced_posts ) ) {

				// detach main site post
				unset( $synced_posts[ $site_id ] );

			}

			if ( ! count( $synced_posts ) ) {

				// detach main site post
				delete_post_meta( $main_post, 'synced_posts' );

			}
			else {

				// update main site post
				update_post_meta( $main_post, 'synced_posts', $synced_posts );

			}

			restore_current_blog();

		}

	}

}
add_action( 'before_delete_post', 'mstaxsync_before_delete_post', 10, 1 );