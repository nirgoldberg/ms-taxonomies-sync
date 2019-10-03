<?php
/**
 * Taxonomy terms functions
 *
 * @author		Nir Goldberg
 * @package		includes/api
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * mstaxsync_taxonomy_terms_sync
 *
 * @since		1.0.0
 * @param		N/A
 * @return		N/A
 */
function mstaxsync_taxonomy_terms_sync() {

	// check nonce
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'taxonomy_terms_sync' ) ) {
		exit();
	}

	/**
	 * Variables
	 */
	$result = array(
		'errors'	=> array(),
		'main'		=> array(),
		'local'		=> array(),
	);

	// update taxonomy terms data
	mstaxsync_update_taxonomy_terms_data( $result );

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
add_action( 'wp_ajax_taxonomy_terms_sync', 'mstaxsync_taxonomy_terms_sync' );

/**
 * mstaxsync_update_taxonomy_terms_data
 *
 * This functions updates taxonomy terms data
 *
 * @since		1.0.0
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_update_taxonomy_terms_data( &$result ) {

	/**
	 * Variables
	 */
	$taxonomy_terms = $_REQUEST[ 'taxonomy_terms' ];

	if ( ! $taxonomy_terms ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> '1',
			'description'	=> 'No taxonomy terms to update',
		), $result );

		// return
		return;

	}

	foreach ( $taxonomy_terms as $tt ) {

		$taxonomy	= $tt[ 'taxonomy' ];
		$terms		= $tt[ 'terms' ];
		$orders		= array();	// array of term parent IDs and order count - term_order support
		$parents	= array();	// array of main site term IDs and new generated local term IDs - used to update parent IDs for children terms

		if ( $terms ) {
			foreach ( $terms as $t ) {

				// update taxonomy term
				mstaxsync_update_taxonomy_term( $t, $taxonomy, $orders, $parents, $result );

			}
		}

	}

}

/**
 * mstaxsync_update_taxonomy_term
 *
 * This function updates a single taxonomy term data
 *
 * @since		1.0.0
 * @param		$term (array)
 * @param		$taxonomy (string)
 * @param		&$orders (array)
 * @param		&$parents (array)
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_update_taxonomy_term( $term, $taxonomy, &$orders, &$parents, &$result ) {

	// globals
	global $wpdb;

	/**
	 * Variables
	 */
	$blog_id = get_current_blog_id();

	// get terms table
	$terms_table = $wpdb->get_blog_prefix( $blog_id ) . 'terms';

	// get term data
	// check if term parent ID should be replaced by a new generated local parent term ID
	if ( array_key_exists( $term[ 'parent' ], $parents ) ) {
		$term[ 'parent' ] = $parents[ $term[ 'parent' ] ];
	}

	$id			= $term[ 'id' ];
	$name		= $term[ 'name' ];
	$source		= $term[ 'source' ];
	$parent		= ( $term[ 'parent' ] > 0 && ! term_exists( (int) $term[ 'parent' ], $taxonomy ) ) ? 0 : $term[ 'parent' ];

	// set parent order
	if ( ! array_key_exists( $parent, $orders ) ) {
		$orders[ $parent ] = 1;
	}

	if ( 'local' == $source ) {

		// local site term
		$term_arr = wp_update_term( $id, $taxonomy, array(
			'name'		=> $name,
			'parent'	=> $parent,
		) );

	}
	else {

		// main site term
		$term_arr = wp_insert_term( $name, $taxonomy, array(
			'parent'	=> $parent,
		) );

	}

	if ( ! is_wp_error( $term_arr ) && $term_arr[ 'term_id' ] ) {

		// log
		mstaxsync_result_log( $source, array(
			'term_id'	=> $term_arr[ 'term_id' ],
			'name'		=> $name,
			'parent'	=> $parent,
		), $result );

		if ( 'main' == $source ) {

			// update $parents to include main site term ID and new generated local term ID
			if ( ! array_key_exists( $id, $parents ) ) {
				$parents[ $id ] = $term_arr[ 'term_id' ];
			}

			// update term correlation with main site
			mstaxsync_update_taxonomy_term_correlation( $id, $term_arr[ 'term_id' ], $result );

		}

		// update term_order
		$wpdb->update( $terms_table, array( 'term_order' => $orders[ $parent ] ), array( 'term_id' => $term_arr[ 'term_id' ] ) );

		$orders[ $parent ]++;

	}
	else {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> 'local' == $source ? '2' : '3',
			'description'	=> $term_arr->get_error_message(),
		), $result );

	}

}

/**
 * mstaxsync_update_taxonomy_term_correlation
 *
 * This function provide a taxonomy term correlation between main and local sites
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site term ID
 * @param		$local_id (int) Local site term ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_update_taxonomy_term_correlation( $main_id, $local_id, &$result ) {

	if ( ! $main_id || ! $local_id )
		return;

	// update local site term
	mstaxsync_update_local_taxonomy_term_correlation( $main_id, $local_id, $result );

	// update main site term
	mstaxsync_update_main_taxonomy_term_correlation( $main_id, $local_id, $result );

}

/**
 * mstaxsync_update_local_taxonomy_term_correlation
 *
 * This function provide a taxonomy term local site correlation
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site term ID
 * @param		$local_id (int) Local site term ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_update_local_taxonomy_term_correlation( $main_id, $local_id, &$result ) {

	// update local site term
	$meta_id = update_term_meta( $local_id, 'main_taxonomy_term', $main_id );

	if ( ! $meta_id || is_wp_error( $meta_id ) ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> ! $meta_id ? '4' : '5',
			'description'	=> ! $meta_id ? 'Failed to update a taxonomy term correlation (local site term)' : $meta_id->get_error_message(),
		), $result );

	}

}

/**
 * mstaxsync_update_main_taxonomy_term_correlation
 *
 * This function provide a taxonomy term main site correlation
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site term ID
 * @param		$local_id (int) Local site term ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_update_main_taxonomy_term_correlation( $main_id, $local_id, &$result ) {

	/**
	 * Variables
	 */
	$blog_id = get_current_blog_id();

	// get main site
	$main_site_id = get_main_site_id();

	switch_to_blog( $main_site_id );

	// get current term meta
	$synced_taxonomy_terms = get_term_meta( $main_id, 'synced_taxonomy_terms', true );

	if ( ! $synced_taxonomy_terms ) {
		$synced_taxonomy_terms = array();
	}

	$synced_taxonomy_terms[ $blog_id ] = $local_id;

	// update main site term
	$meta_id = update_term_meta( $main_id, 'synced_taxonomy_terms', $synced_taxonomy_terms );

	if ( ! $meta_id || is_wp_error( $meta_id ) ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> ! $meta_id ? '6' : '7',
			'description'	=> ! $meta_id ? 'Failed to update a taxonomy term correlation (main site term)' : $meta_id->get_error_message(),
		), $result );

	}

	restore_current_blog();

}

/**
 * mstaxsync_detach_taxonomy_term
 *
 * @since		1.0.0
 * @param		N/A
 * @return		N/A
 */
function mstaxsync_detach_taxonomy_term() {

	// check nonce
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'detach_taxonomy_term' ) ) {
		exit();
	}

	/**
	 * Variables
	 */
	$result = array(
		'errors'	=> array(),
		'main_id'	=> '',
		'local_id'	=> '',
	);

	// detach taxonomy term
	mstaxsync_detach_tt( $result );

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
add_action( 'wp_ajax_detach_taxonomy_term', 'mstaxsync_detach_taxonomy_term' );

/**
 * mstaxsync_detach_tt
 *
 * This functions detaches taxonomy term
 *
 * @since		1.0.0
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_detach_tt( &$result ) {

	/**
	 * Variables
	 */
	$main_id	= $_REQUEST[ 'main_id' ];
	$local_id	= $_REQUEST[ 'local_id' ];

	if ( ! $main_id || ! $local_id ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> '1',
			'description'	=> 'No taxonomy term to update',
		), $result );

		// return
		return;

	}

	mstaxsync_detach_taxonomy_term_correlation( $main_id, $local_id, $result );

}

/**
 * mstaxsync_detach_taxonomy_term_correlation
 *
 * This function detaches a taxonomy term correlation
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site term ID
 * @param		$local_id (int) Local site term ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_detach_taxonomy_term_correlation( $main_id, $local_id, &$result ) {

	if ( ! $main_id || ! $local_id )
		return;

	// detach local site term
	mstaxsync_detach_local_taxonomy_term_correlation( $main_id, $local_id, $result );

	// detach main site term
	mstaxsync_detach_main_taxonomy_term_correlation( $main_id, $local_id, $result );

}

/**
 * mstaxsync_detach_local_taxonomy_term_correlation
 *
 * This function detaches a taxonomy term local site correlation
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site term ID
 * @param		$local_id (int) Local site term ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_detach_local_taxonomy_term_correlation( $main_id, $local_id, &$result ) {

	// detach local site term
	$res = delete_term_meta( $local_id, 'main_taxonomy_term' );

	if ( ! $res ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> '2',
			'description'	=> 'Failed to delete a taxonomy term correlation (local site term)',
		), $result );

	}
	else {

		// log
		mstaxsync_result_log( 'local_id', $local_id, $result );

	}

}

/**
 * mstaxsync_detach_main_taxonomy_term_correlation
 *
 * This function detaches a taxonomy term main site correlation
 *
 * @since		1.0.0
 * @param		$main_id (int) Main site term ID
 * @param		$local_id (int) Local site term ID
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_detach_main_taxonomy_term_correlation( $main_id, $local_id, &$result ) {

	/**
	 * Variables
	 */
	$blog_id = get_current_blog_id();

	// get main site
	$main_site_id = get_main_site_id();

	switch_to_blog( $main_site_id );

	// get current term meta
	$synced_taxonomy_terms = get_term_meta( $main_id, 'synced_taxonomy_terms', true );

	if ( ! $synced_taxonomy_terms ) {
		$synced_taxonomy_terms = array();
	}

	if ( array_key_exists( $blog_id, $synced_taxonomy_terms ) ) {

		// detach main site term
		unset( $synced_taxonomy_terms[ $blog_id ] );

	}

	if ( ! count( $synced_taxonomy_terms ) ) {

		// detach main site term
		$res = delete_term_meta( $main_id, 'synced_taxonomy_terms' );

	}
	else {

		// update main site term
		$res = update_term_meta( $main_id, 'synced_taxonomy_terms', $synced_taxonomy_terms );

	}

	if ( ! $res || is_wp_error( $res ) ) {

		// log
		mstaxsync_result_log( 'errors', array(
			'code'			=> ! $res ? '3' : '4',
			'description'	=> ! $res ? 'Failed to update a taxonomy term correlation (main site term)' : $res->get_error_message(),
		), $result );

	}
	else {

		// log
		mstaxsync_result_log( 'main_id', $main_id, $result );

	}

	restore_current_blog();

}

/**
 * mstaxsync_result_log
 *
 * This function logs API activities
 *
 * @since		1.0.0
 * @param		$type (string) Log type
 * @param		$data (array)
 * @param		&$result (array)
 * @return		N/A
 */
function mstaxsync_result_log( $type, $data, &$result ) {

	if ( ! $result[ $type ] ) {
		$result[ $type ] = array();
	}

	$result[ $type ][] = $data;

}