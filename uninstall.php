<?php
/**
 * Multisite Categories Sync uninstall
 *
 * @author		Nir Goldberg
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit; // Exit if accessed directly

// exit if is large network
if ( wp_is_large_network() ) exit;

// vars
$sites		= array();
$options	= array();

// get sites
$sites		= get_sites( array( 'fields' => 'ids' ) );

if ( $sites ) {

	// set options
	$options	= array(
		'mscatsync_general_default_lang',
		'mscatsync_uninstall_remove_data'
	);

	foreach ( $sites as $site_id ) {

		$remove_data = get_blog_option( $site_id, 'mscatsync_uninstall_remove_data' );

		if ( $remove_data && in_array( 'remove', $remove_data ) ) {

			// remove plugin data
			mscatsync_remove_data( $site_id, $options );

		}

	}

}

/**
 * mscatsync_remove_data
 *
 * This function will delete options and database plugin data
 *
 * @since		1.0.0
 * @param		$site_id (int) site ID
 * @param		$options (array) plugin options
 * @return		N/A
 */
function mscatsync_remove_data( $site_id, $options = array() ) {

	// delete plugin options
	mscatsync_remove_options_data( $site_id, $options );

	// delete database plugin data
	mscatsync_remove_db_data( $site_id );

}

/**
 * mscatsync_remove_options_data
 *
 * This function will delete plugin options
 *
 * @since		1.0.0
 * @param		$site_id (int) site ID
 * @param		&$options (array) plugin options
 * @return		N/A
 */
function mscatsync_remove_options_data( $site_id, &$options = array() ) {

	// globals
	global $wpdb;

	// get options table
	$options_table = $wpdb->get_blog_prefix( $site_id ) . 'options';

	// append language based options
	$general_default_cat_options = $wpdb->get_results(
		"SELECT option_name FROM $options_table
		 WHERE option_name like 'mscatsync_general_default_cat%'", ARRAY_N
	);

	foreach ( $general_default_cat_options as $option ) {
		$options[] = $option[0];
	}

	foreach ( $options as $option ) {
		delete_blog_option( $site_id, $option );
	}

}

/**
 * mscatsync_remove_db_data
 *
 * This function will delete database plugin data
 *
 * @since		1.0.0
 * @param		$site_id (int) site ID
 * @return		N/A
 */
function mscatsync_remove_db_data( $site_id ) {}