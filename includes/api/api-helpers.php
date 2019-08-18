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
 * mscatsync_has_setting
 *
 * Alias of mscatsync()->has_setting()
 *
 * @since		1.0.0
 * @param		$name (string)
 * @return		(boolean)
 */
function mscatsync_has_setting( $name = '' ) {

	// return
	return mscatsync()->has_setting( $name );

}

/**
 * mscatsync_get_setting
 *
 * This function will return a value from the settings array found in the mscatsync object
 *
 * @since		1.0.0
 * @param		$name (string)
 * @return		(mixed)
 */
function mscatsync_get_setting( $name, $default = null ) {

	// vars
	$settings = mscatsync()->settings;

	// find setting
	$setting = mscatsync_maybe_get( $settings, $name, $default );

	// filter for 3rd party
	$setting = apply_filters( "mscatsync/settings/{$name}", $setting );

	// return
	return $setting;

}

/**
 * mscatsync_update_setting
 *
 * Alias of mscatsync()->update_setting()
 *
 * @since		1.0.0
 * @param		$name (string)
 * @param		$value (mixed)
 * @return		N/A
 */
function mscatsync_update_setting( $name, $value ) {

	// return
	return mscatsync()->update_setting( $name, $value );

}

/**
 * mscatsync_get_path
 *
 * This function will return the path to a file within the plugin folder
 *
 * @since		1.0.0
 * @param		$path (string) the relative path from the root of the plugin folder
 * @return		(string)
 */
function mscatsync_get_path( $path = '' ) {

	// return
	return MSCatSync_PATH . $path;

}

/**
 * mscatsync_get_url
 *
 * This function will return the url to a file within the plugin folder
 *
 * @since		1.0.0
 * @param		$path (string) the relative path from the root of the plugin folder
 * @return		(string)
 */
function mscatsync_get_url( $path = '' ) {

	// define MSCatSync_URL to optimize performance
	mscatsync()->define( 'MSCatSync_URL', mscatsync_get_setting( 'url' ) );

	// return
	return MSCatSync_URL . $path;

}

/**
 * mscatsync_include
 *
 * This function will include a file
 *
 * @since		1.0.0
 * @param		$file (string) the file name to be included
 * @return		N/A
 */
function mscatsync_include( $file ) {

	$path = mscatsync_get_path( $file );

	if ( file_exists( $path ) ) {
		include_once( $path );
	}

}

/**
 * mscatsync_get_view
 *
 * This function will load in a file from the 'includes/admin/views' folder and allow variables to be passed through
 *
 * @since		1.0.0
 * @param		$view_name (string)
 * @param		$args (array)
 * @return		N/A
 */
function mscatsync_get_view( $view_name = '', $args = array() ) {

	// vars
	$path = mscatsync_get_path( "includes/admin/views/{$view_name}.php" );

	if( file_exists( $path ) ) {
		include( $path );
	}

}

/**
 * mscatsync_maybe_get
 *
 * This function will return a variable if it exists in an array
 *
 * @since		1.0.0
 * @param		$array (array) the array to look within
 * @param		$key (key) the array key to look for
 * @param		$default (mixed) the value returned if not found
 * @return		(mixed)
 */
function mscatsync_maybe_get( $array = array(), $key = 0, $default = null ) {

	// return
	return isset( $array[ $key ] ) ? $array[ $key ] : $default;

}

/**
 * mscatsync_get_locale
 *
 * This function is a wrapper for the get_locale() function
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(string)
 */
function mscatsync_get_locale() {

	// return
	return is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();

}

/**
 * mscatsync_get_active_languages
 *
 * This function will return array of language code and language native name
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(array)
 */
function mscatsync_get_active_languages() {

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
 * mscatsync_get_categories
 *
 * This function will return array of category ID and category name
 *
 * @since		1.0.0
 * @param		N/A
 * @return		(array)
 */
function mscatsync_get_categories() {

	// vars
	$categories	= get_terms( array(
		'taxonomy'		=> 'category',
		'hide_empty'	=> false
	) );

	$cat_arr = array();

	if ( $categories ) {
		foreach ( $categories as $cat ) {
			$cat_arr[ 'cat_' . $cat->term_id ] = $cat->name;
		}
	}

	// return
	return $cat_arr;

}