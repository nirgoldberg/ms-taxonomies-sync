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
 * mstaxsync_prepare_taxonomy_term_posts_import
 *
 * @since		1.0.0
 * @param		N/A
 * @return		N/A
 */
function mstaxsync_prepare_taxonomy_term_posts_import() {

	// verify nonce
	if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'import_taxonomy_term' ) ) {
		exit();
	}

	/**
	 * Variables
	 */
	$main_term_id = $_REQUEST[ 'main_term_id' ];

	$result = array(
		'errors'		=> array(),
		'posts'			=> array(),
		'locked_posts'	=> array(),
	);

	if ( ! $main_term_id ) {
		exit();
	}

	// query all posts associated with main site term ID
	mstaxsync_prepare_posts_before_import( $main_term_id, $result );

	// Check if action was fired via Ajax call. If yes, JS code will be triggered, else the user will be redirected to the post page
	if ( ! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && 'xmlhttprequest' == strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) ) {

		$result = json_encode( $result );
		echo $result;

	}
	else {

		header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );

	}

	// die
	die();

}
add_action( 'wp_ajax_prepare_taxonomy_term_posts_import', 'mstaxsync_prepare_taxonomy_term_posts_import' );

/**
 * mstaxsync_prepare_posts_before_import
 *
 * This function will query all posts associated with main site term ID
 *
 * @since		1.0.0
 * @param		$main_term_id (int) Main site term ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_prepare_posts_before_import( $main_term_id, &$result ) {

	/**
	 * Variables
	 */
	$main_site_id	= mstaxsync_get_main_site_id();
	$local_site_id	= get_current_blog_id();

	switch_to_blog( $main_site_id );

	// get term
	$term = get_term( $main_term_id );

	if ( ! $term || is_wp_error( $term ) ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> '1',
			'description'	=> is_wp_error( $term ) ? $term->get_error_message() : __( 'Internal server error', 'mstaxsync' ),
		), $result );

		restore_current_blog();

		// return
		return;

	}

	// query posts
	$args = array(
		'post_type'			=> 'post',
		'post_status'		=> 'publish',
		'posts_per_page'	=> -1,
		'order'				=> 'ASC',
		'tax_query'			=> array(
			array(
				'taxonomy'			=> $term->taxonomy,
				'terms'				=> $main_term_id,
				'include_children'	=> false,
			),
		),
	);

	$posts_query = new WP_Query( $args );

	if ( $posts_query->have_posts() ) :

		while ( $posts_query->have_posts() ) : $posts_query->the_post();

			// check if locked
			if ( !mstaxsync_broadcast()->is_post_locked( $posts_query->post->ID, $local_site_id ) ) {

				// log
				mstaxsync_result_log( 'posts', $posts_query->post, $result );

			}
			else {

				// log
				mstaxsync_result_log( 'locked_posts', $posts_query->post, $result );

			}

		endwhile;

	else :

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> '2',
			'description'	=> __( 'No posts to import', 'mstaxsync' ),
		), $result );

	endif;

	wp_reset_postdata();

	restore_current_blog();

}

/**
 * mstaxsync_import_post
 *
 * @since		1.0.0
 * @param		N/A
 * @return		N/A
 */
function mstaxsync_import_post() {

	// verify nonce
	if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'import_taxonomy_term' ) ) {
		exit();
	}

	/**
	 * Variables
	 */
	$post_to_import	= $_REQUEST[ 'post' ];

	if ( ! $post_to_import ) {
		exit();
	}

	// import single post
	mstaxsync_import_single_post( $post_to_import );

	// die
	die();

}
add_action( 'wp_ajax_import_post', 'mstaxsync_import_post' );

/**
 * mstaxsync_import_single_post
 *
 * This function will import a single post
 *
 * @since		1.0.0
 * @param		$post_to_import (array)
 * @return		N/A
 */
function mstaxsync_import_single_post( $post_to_import ) {

	/**
	 * Variables
	 */
	$main_site_id	= mstaxsync_get_main_site_id();
	$local_site_id	= get_current_blog_id();

	switch_to_blog( $main_site_id );

	// broadcast single post
	$post_id = mstaxsync_broadcast()->broadcast_single_post( (object) $post_to_import, array( $local_site_id ) );

	restore_current_blog();

	echo json_encode( $post_id );

}