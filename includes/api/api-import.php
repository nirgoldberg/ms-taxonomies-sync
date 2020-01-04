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
	mstaxsync_import_posts( $main_term_id );

	// die
	die();

}
add_action( 'wp_ajax_import_taxonomy_term_posts', 'mstaxsync_import_taxonomy_term_posts' );

/**
 * mstaxsync_import_posts
 *
 * This function will import all posts associated with main site term ID
 *
 * @since		1.0.0
 * @param		$main_term_id (int) Main site term ID
 * @return		N/A
 */
function mstaxsync_import_posts( $main_term_id ) {

	/**
	 * Variables
	 */
	$main_site_id	= mstaxsync_get_main_site_id();
	$site_id		= get_current_blog_id();
	$posts			= array();
	$post_ids		= array();

	switch_to_blog( $main_site_id );

	// get term
	$term = get_term( $main_term_id );

	if ( ! $term || is_wp_error( $term ) )
		return;

	// query posts
	$args = array(
		'post_type'				=> 'post',
		'post_status'			=> 'publish',
		'posts_per_page'		=> -1,
		'tax_query'				=> array(
			array(
				'taxonomy'			=> $term->taxonomy,
				'terms'				=> $main_term_id,
				'include_children'	=> false,
			),
		),
	);

	$posts_query = new WP_Query( $args );

	if ( $posts_query->have_posts() ) : while ( $posts_query->have_posts() ) : $posts_query->the_post();

		// broadcast single post
		$post_id = mstaxsync_broadcast()->broadcast_single_post( $posts_query->post, array( $site_id ) );

		if ( $post_id ) {
			$post_ids[] = $post_id;
		}

	endwhile; endif;

	wp_reset_postdata();

	restore_current_blog();

	echo count( $post_ids );

}