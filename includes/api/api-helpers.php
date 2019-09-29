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
 * Child categories are placed under a 'children' member of their parent term
 *
 * @since		1.0.0
 * @param		$terms (array) taxonomy term objects
 * @param		$li_class (string) taxonomy term li class
 * @return		(string)
 */
function mstaxsync_display_terms_hierarchically( $terms = array(), $li_class = null ) {

	foreach ( $terms as $t ) {

		echo
			'<li' . ( $li_class ? ' class="' . $li_class . '"' : '' ) . '>' .
				'<div>' .
					'<span class="mstaxsync-rel-item" data-id="' . $t->term_id . '">' .
						'<span class="val">' . $t->name . '</span>' .
						( 'choice' != $li_class ?
							'<input type="text" placeholder="' . $t->name . '" />' .
							'<span class="edit dashicons dashicons-edit"></span>' .
							'<span class="ok dashicons dashicons-yes"></span>' .
							'<span class="cancel dashicons dashicons-no"></span>'
						: '' ) .
					'</span>' .
				'</div>';

		if ( $t->children ) {

			echo '<ul>';

				mstaxsync_display_terms_hierarchically( $t->children, $li_class );

			echo '</ul>';

		}

		echo '</li>';

	}

}