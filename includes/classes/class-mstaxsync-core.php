<?php
/**
 * MSTaxSync_Core
 *
 * @author		Nir Goldberg
 * @package		includes
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Core' ) ) :

class MSTaxSync_Core {

	/**
	* __construct
	*
	* A dummy constructor to ensure is only initialized once
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function __construct() {

		/* Do nothing here */

	}

	/**
	* initialize
	*
	* The real constructor to initialize MSTaxSync_Core
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function initialize() {

		// settings
		$this->settings = array(

			// taxonomies
			'taxonomies'	=> array(),

		);

		// actions
		add_action( 'init',	array( $this, 'init' ), 99 );

	}

	/**
	* init
	*
	* This function will run after all plugins and theme functions have been included
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function init() {

		// exit if called too early
		if ( ! did_action( 'plugins_loaded' ) )
			return;

		// admin
		if ( is_admin() ) {

			// get main site
			$main_site_id = get_main_site_id();

			switch_to_blog( $main_site_id );

			$this->set_main_site_custom_taxonomies();

			restore_current_blog();

		}

		// action for 3rd party
		do_action( 'mstaxsync_core/init' );

	}

	/**
	* set_main_site_custom_taxonomies
	*
	* This function will get main site custom taxonomies and store them in settings array
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function set_main_site_custom_taxonomies() {

		// vars
		$args = array(
			'public'	=> true,
			'_builtin'	=> false,
		);

		$taxonomies = get_taxonomies( $args, 'objects' );

		if ( $taxonomies ) {
			foreach ( $taxonomies as $tax ) {

				$this->settings[ 'taxonomies' ][] = $tax;

			}
		}

	}

	/**
	* get_main_site_custom_taxonomies
	*
	* This function will get main site custom taxonomies
	*
	* @since		1.0.0
	* @param		N/A
	* @return		(array)
	*/
	function get_main_site_custom_taxonomies() {

		// return
		return $this->settings[ 'taxonomies' ];

	}

}

/**
* mstaxsync_core
*
* The main function responsible for returning the one true instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mstaxsync_core() {

	// globals
	global $mstaxsync_core;

	// initialize
	if( ! isset( $mstaxsync_core ) ) {

		$mstaxsync_core = new MSTaxSync_Core();
		$mstaxsync_core->initialize();

	}

	// return
	return $mstaxsync_core;

}

// initialize
mstaxsync_core();

endif; // class_exists check