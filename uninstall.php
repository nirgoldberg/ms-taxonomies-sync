<?php
/**
 * Multisite Taxonomies Sync uninstall
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
		'mstaxsync_sync_categories',
		'mstaxsync_synced_taxonomies',
		'mstaxsync_advanced_treeview',
		'mstaxsync_display_synced_taxonomy_terms_column',
		'mstaxsync_display_main_taxonomy_term_column',
		'mstaxsync_display_synced_posts_column',
		'mstaxsync_display_main_post_column',
		'mstaxsync_edit_taxonomy_terms',
		'mstaxsync_detach_taxonomy_terms',
		'mstaxsync_delete_taxonomy_terms',
		'mstaxsync_import_taxonomy_terms_posts',
		'mstaxsync_uninstall_remove_data',
	);

	foreach ( $sites as $site_id ) {

		$remove_data = get_blog_option( $site_id, 'mstaxsync_uninstall_remove_data' );

		if ( $remove_data && in_array( 'remove', $remove_data ) ) {

			// remove plugin data
			mstaxsync_remove_data( $site_id, $options );

		}

	}

}

/**
 * mstaxsync_remove_data
 *
 * This function will remove options and database plugin data
 *
 * @since		1.0.0
 * @param		$site_id (int) site ID
 * @param		$options (array) plugin options
 * @return		N/A
 */
function mstaxsync_remove_data( $site_id, $options = array() ) {

	// remove plugin options
	mstaxsync_remove_options_data( $site_id, $options );

	// remove database plugin data
	mstaxsync_remove_db_data( $site_id );

}

/**
 * mstaxsync_remove_options_data
 *
 * This function will remove plugin options
 *
 * @since		1.0.0
 * @param		$site_id (int) site ID
 * @param		&$options (array) plugin options
 * @return		N/A
 */
function mstaxsync_remove_options_data( $site_id, &$options = array() ) {

	foreach ( $options as $option ) {
		delete_blog_option( $site_id, $option );
	}

}

/**
 * mstaxsync_remove_db_data
 *
 * This function will remove database plugin data
 *
 * @since		1.0.0
 * @param		$site_id (int) site ID
 * @return		N/A
 */
function mstaxsync_remove_db_data( $site_id ) {}