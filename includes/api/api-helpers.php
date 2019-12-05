<?php
/**
 * Helper functions
 *
 * @author		Nir Goldberg
 * @package		includes/api
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * mstaxsync_has_setting
 *
 * Alias of mstaxsync()->has_setting()
 *
 * @since		1.0.0
 * @param		$name (string)
 * @return		(boolean)
 */
function mstaxsync_has_setting( $name = '' ) {

	// return
	return mstaxsync()->has_setting( $name );

}

/**
 * mstaxsync_get_setting
 *
 * This function will return a value from the settings array found in the mstaxsync object
 *
 * @since		1.0.0
 * @param		$name (string)
 * @return		(mixed)
 */
function mstaxsync_get_setting( $name, $default = null ) {

	// vars
	$settings = mstaxsync()->settings;

	// find setting
	$setting = mstaxsync_maybe_get( $settings, $name, $default );

	// filter for 3rd party
	$setting = apply_filters( "mstaxsync/settings/{$name}", $setting );

	// return
	return $setting;

}

/**
 * mstaxsync_update_setting
 *
 * Alias of mstaxsync()->update_setting()
 *
 * @since		1.0.0
 * @param		$name (string)
 * @param		$value (mixed)
 * @return		N/A
 */
function mstaxsync_update_setting( $name, $value ) {

	// return
	return mstaxsync()->update_setting( $name, $value );

}

/**
 * mstaxsync_get_path
 *
 * This function will return the path to a file within the plugin folder
 *
 * @since		1.0.0
 * @param		$path (string) the relative path from the root of the plugin folder
 * @return		(string)
 */
function mstaxsync_get_path( $path = '' ) {

	// return
	return MSTaxSync_PATH . $path;

}

/**
 * mstaxsync_get_url
 *
 * This function will return the url to a file within the plugin folder
 *
 * @since		1.0.0
 * @param		$path (string) the relative path from the root of the plugin folder
 * @return		(string)
 */
function mstaxsync_get_url( $path = '' ) {

	// define MSTaxSync_URL to optimize performance
	mstaxsync()->define( 'MSTaxSync_URL', mstaxsync_get_setting( 'url' ) );

	// return
	return MSTaxSync_URL . $path;

}

/**
 * mstaxsync_include
 *
 * This function will include a file
 *
 * @since		1.0.0
 * @param		$file (string) the file name to be included
 * @return		N/A
 */
function mstaxsync_include( $file ) {

	$path = mstaxsync_get_path( $file );

	if ( file_exists( $path ) ) {
		include_once( $path );
	}

}

/**
 * mstaxsync_get_view
 *
 * This function will load in a file from the 'includes/admin/views' folder and allow variables to be passed through
 *
 * @since		1.0.0
 * @param		$view_name (string)
 * @param		$args (array)
 * @return		N/A
 */
function mstaxsync_get_view( $view_name = '', $args = array() ) {

	// vars
	$path = mstaxsync_get_path( "includes/admin/views/{$view_name}.php" );

	if( file_exists( $path ) ) {
		include( $path );
	}

}

/**
 * mstaxsync_maybe_get
 *
 * This function will return a variable if it exists in an array
 *
 * @since		1.0.0
 * @param		$array (array) the array to look within
 * @param		$key (key) the array key to look for
 * @param		$default (mixed) the value returned if not found
 * @return		(mixed)
 */
function mstaxsync_maybe_get( $array = array(), $key = 0, $default = null ) {

	// return
	return isset( $array[ $key ] ) ? $array[ $key ] : $default;

}

/**
 * mstaxsync_get_locale
 *
 * This function is a wrapper for the get_locale() function
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(string)
 */
function mstaxsync_get_locale() {

	// return
	return is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();

}

/**
 * mstaxsync_get_active_languages
 *
 * This function will return array of language code and language native name
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(array)
 */
function mstaxsync_get_active_languages() {

	if ( ! function_exists( 'icl_get_languages' ) )
		return;

	// vars
	$languages	= icl_get_languages( 'skip_missing=0&orderby=custom' );
	$lang_arr	= array();

	if ( $languages ) {
		foreach ( $languages as $code => $lang ) {
			$lang_arr[ $code ] = $lang[ 'native_name' ];
		}
	}

	// return
	return $lang_arr;

}

/**
 * mstaxsync_get_main_site_custom_taxonomies_objects
 *
 * This function will return array of main site custom taxonomies objects
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(array)
 */
function mstaxsync_get_main_site_custom_taxonomies_objects() {

	// return
	return mstaxsync_core()->get_main_site_custom_taxonomies();

}

/**
 * mstaxsync_get_main_site_custom_taxonomies_names
 *
 * This function will return array of taxonomy name and taxonomy label
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(array)
 */
function mstaxsync_get_main_site_custom_taxonomies_names() {

	// vars
	$taxonomies	= mstaxsync_get_main_site_custom_taxonomies_objects();

	$tax_arr = array();

	if ( $taxonomies ) {
		foreach ( $taxonomies as $tax ) {
			$tax_arr[ $tax->name ] = $tax->label;
		}
	}

	// return
	return $tax_arr;

}

/**
 * mstaxsync_get_custom_taxonomy_terms
 *
 * This function will return array of custom taxonomy terms
 *
 * @since		1.0.0
 * @param		$tax (object)
 * @param		$main (boolean)
 * @return		(mixed)
 */
function mstaxsync_get_custom_taxonomy_terms( $tax, $main = false ) {

	// return
	return mstaxsync_core()->get_custom_taxonomy_terms( $tax, $main );

}

/**
 * mstaxsync_get_main_site_id
 *
 * This function will return main site id
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(int)
 */
function mstaxsync_get_main_site_id() {

	// return
	return mstaxsync_core()->get_main_site_id();

}

/**
 * mstaxsync_is_main_site_wpml_active
 *
 * This function will return true if WPML is active for main site
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(bool)
 */
function mstaxsync_is_main_site_wpml_active() {

	// return
	return mstaxsync_core()->is_main_site_wpml_active();

}

/**
 * mstaxsync_is_local_site_wpml_active
 *
 * This function will return true if WPML is active for current site
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(bool)
 */
function mstaxsync_is_local_site_wpml_active() {

	// return
	return mstaxsync_core()->is_local_site_wpml_active();

}

/**
 * mstaxsync_is_term_synced
 *
 * This function will check whether a term is synced in either direction.
 * If term is synced, its ID will be returned
 *
 * @since		1.0.0
 * @param		$term_id (int) Term ID
 * @param		$direction (boolean) true - synced in local site (default) / false - synced in main site
 * @return		(mixed)
 */
function mstaxsync_is_term_synced( $term_id, $direction = true ) {

	if ( $direction ) {

		// is synced in local site
		$main_id = get_term_meta( $term_id, 'main_taxonomy_term', true );

		// return
		return $main_id ?: false;

	}
	else {

		// is synced in main site
		$site_id = get_current_blog_id();

		// get main site ID
		$main_site_id = mstaxsync_get_main_site_id();

		switch_to_blog( $main_site_id );

		$synced_taxonomy_terms = get_term_meta( $term_id, 'synced_taxonomy_terms', true );

		if ( ! $synced_taxonomy_terms ) {
			$synced_taxonomy_terms = array();
		}

		restore_current_blog();

		// return
		return array_key_exists( $site_id, $synced_taxonomy_terms ) ? $synced_taxonomy_terms[ $site_id ] : false;

	}

}

/**
 * mstaxsync_sort_terms_hierarchically
 *
 * This function will recursively sort an array of taxonomy terms hierarchically.
 * Child categories will be placed under a 'children' member of their parent term
 *
 * @since		1.0.0
 * @param		$terms (array) taxonomy term objects to sort
 * @param		$output (array) result array to put sorted terms in
 * @param		$parent_id (int) the current parent ID to put sorted terms in
 * @return		N/A
 */
function mstaxsync_sort_terms_hierarchically( &$terms = array(), &$output = array(), $parent_id = 0 ) {

	foreach ( $terms as $key => $t ) {
		if ( $t->parent == $parent_id ) {

			$output[] = $t;
			unset( $terms[ $key ] );

		}
	}

	foreach ( $output as $top_term ) {

		$top_term->children = array();
		mstaxsync_sort_terms_hierarchically( $terms, $top_term->children, $top_term->term_id );

	}

}

/**
 * mstaxsync_display_terms_hierarchically
 *
 * This function will recursively display an array of taxonomy terms hierarchically.
 * Child categories are placed under a 'children' member of their parent term.
 * It will also check for synced terms and will mark them accordingly
 *
 * @since		1.0.0
 * @param		$terms (array) taxonomy term objects
 * @param		$li_class (string) taxonomy term li class
 * @param		$echo (boolean) whether to echo or return terms
 * @return		(string)
 */
function mstaxsync_display_terms_hierarchically( $terms = array(), $li_class = null, $echo = true ) {

	/**
	 * Variables
	 */
	$advanced_treeview	= get_option( 'mstaxsync_advanced_treeview', array( 'can' ) );
	$edit_terms			= get_option( 'mstaxsync_edit_taxonomy_terms', array( 'can' ) );
	$detach_terms		= get_option( 'mstaxsync_detach_taxonomy_terms' );
	$delete_terms		= get_option( 'mstaxsync_delete_taxonomy_terms' );

	$detach_nonce		= wp_create_nonce( 'detach_taxonomy_term' );
	$delete_nonce		= wp_create_nonce( 'delete_taxonomy_term' );

	$classes			= $li_class ?: '';
	$cb_input			= $advanced_treeview && 'choice' == $li_class ? '<input type="checkbox">' : '';
	$multiselect		= '<span class="multiselect"><span class="check-all">' . __( 'Select All', 'mstaxsync' ) . '</span> / <span class="uncheck-all">' . __( 'Remove All', 'mstaxsync' ) . '</span></span>';
	$synced_span		= '<span class="synced dashicons dashicons-update' . ( $detach_terms && in_array( 'can', $detach_terms ) ? ' can-detach' : '' ) . '" data-nonce="' . $detach_nonce . '"></span>';
	$edit_span			= '<span class="edit dashicons dashicons-edit"></span><span class="ok dashicons dashicons-yes"></span><span class="cancel dashicons dashicons-no"></span>';
	$delete_span		= 'choice' != $li_class && $delete_terms && in_array( 'can', $delete_terms ) ? '<span class="trash dashicons dashicons-trash" data-nonce="' . $delete_nonce . '"></span>' : '';

	$output				= '';

	foreach ( $terms as $t ) {

		// is term synced
		$synced_term = mstaxsync_is_term_synced( $t->term_id, 'choice' != $li_class );

		$output .=
			'<li class="' . $classes . '">' .
				'<div>' .
					'<span class="mstaxsync-rel-item' . ( 'choice' == $li_class && $synced_term ? ' disabled' : '' ) . '" data-id="' . $t->term_id . '" data-synced="' . $synced_term . '">' .
						( 'choice' != $li_class && $synced_term ? $synced_span : '' ) .
						$cb_input .
						'<span class="val">' . $t->name . '</span>' .
						( $t->children && $advanced_treeview && 'choice' == $li_class ? $multiselect : '' ) .
						( 'choice' != $li_class && $edit_terms && in_array( 'can', $edit_terms ) ?
							'<input type="text" placeholder="' . $t->name . '" />' .
							$edit_span
						: '' ) .
						$delete_span .
					'</span>' .
				'</div>';

		if ( $t->children ) {

			$output .= '<ul>';

				$output .= mstaxsync_display_terms_hierarchically( $t->children, $li_class, false );

			$output .= '</ul>';

		}

		$output .= '</li>';

	}

	if ( $echo ) {
		echo $output;
	}
	else {
		// return
		return $output;
	}

}

/**
 * mstaxsync_is_post_synced
 *
 * This function will check whether a post is synced in either direction.
 * If term is synced, its ID will be returned
 *
 * @since		1.0.0
 * @param		$post_id (int) post ID
 * @param		$direction (boolean) true - synced in local site (default) / false - synced in main site
 * @return		(mixed)
 */
function mstaxsync_is_post_synced( $post_id, $direction = true ) {

	if ( $direction ) {

		// is synced in local site
		$main_id = get_post_meta( $post_id, 'main_post', true );

		// return
		return $main_id ?: false;

	}
	else {

		// is synced in main site
		$site_id = get_current_blog_id();

		// get main site ID
		$main_site_id = mstaxsync_get_main_site_id();

		switch_to_blog( $main_site_id );

		$synced_posts = get_post_meta( $post_id, 'synced_posts', true );

		if ( ! $synced_posts ) {
			$synced_posts = array();
		}

		restore_current_blog();

		// return
		return array_key_exists( $site_id, $synced_posts ) ? $synced_posts[ $site_id ] : false;

	}

}