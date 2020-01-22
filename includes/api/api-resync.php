<?php
/**
 * Resync functions
 *
 * @author		Nir Goldberg
 * @package		includes/api
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * mstaxsync_prepare_posts_resync
 *
 * @since		1.0.0
 * @param		N/A
 * @return		N/A
 */
function mstaxsync_prepare_posts_resync() {

	// verify nonce
	if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'resync_posts' ) ) {
		exit();
	}

	/**
	 * Variables
	 */
	$result = array(
		'errors'	=> array(),
		'posts'		=> array(),
	);

	// query all synced posts
	mstaxsync_prepare_posts_before_resync( $result );

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
add_action( 'wp_ajax_prepare_posts_resync', 'mstaxsync_prepare_posts_resync' );

/**
 * mstaxsync_prepare_posts_before_resync
 *
 * This function will query all synced posts
 *
 * @since		1.0.0
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_prepare_posts_before_resync( &$result ) {

	// query posts
	$args = array(
		'post_type'			=> 'post',
		'post_status'		=> 'publish',
		'posts_per_page'	=> -1,
		'order'				=> 'ASC',
		'meta_query'		=> array(
			array(
				'key'		=> 'main_post',
				'compare'	=> 'EXISTS',
			),
		),
	);

	$posts_query = new WP_Query( $args );

	if ( $posts_query->have_posts() ) :

		while ( $posts_query->have_posts() ) : $posts_query->the_post();

			// log
			mstaxsync_result_log( 'posts', $posts_query->post, $result );

		endwhile;

	else :

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> '1',
			'description'	=> __( 'No synced posts', 'mstaxsync' ),
		), $result );

	endif;

	wp_reset_postdata();

}

/**
 * mstaxsync_resync_post
 *
 * @since		1.0.0
 * @param		N/A
 * @return		N/A
 */
function mstaxsync_resync_post() {

	// verify nonce
	if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'resync_posts' ) ) {
		exit();
	}

	/**
	 * Variables
	 */
	$result = array(
		'errors'		=> array(),
		'post_title'	=> array(),
		'taxonomies'	=> array(),
	);

	$post_to_resync	= $_REQUEST[ 'post' ];

	if ( ! $post_to_resync ) {
		exit();
	}

	// resync single post
	mstaxsync_resync_single_post( $post_to_resync, $result );

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
add_action( 'wp_ajax_resync_post', 'mstaxsync_resync_post' );

/**
 * mstaxsync_resync_single_post
 *
 * This function will resync a single post
 *
 * @since		1.0.0
 * @param		$post_to_resync (array)
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_resync_single_post( $post_to_resync, &$result ) {

	// resync single post
	$reassigned_tt = mstaxsync_broadcast()->resync_single_post( $post_to_resync[ 'ID' ] );

	if ( $reassigned_tt && ! empty( $reassigned_tt ) ) {

		// log
		mstaxsync_result_log( 'post_title', $post_to_resync[ 'post_title' ], $result );
		mstaxsync_result_log( 'taxonomies', $reassigned_tt, $result );

	}

}