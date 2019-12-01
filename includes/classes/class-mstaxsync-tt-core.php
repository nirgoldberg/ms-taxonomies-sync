<?php
/**
 * MSTaxSync_TT_Core
 *
 * @author		Nir Goldberg
 * @package		includes/classes
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_TT_Core' ) ) :

class MSTaxSync_TT_Core {

	/**
	* __construct
	*
	* A dummy constructor to ensure is only initialized once
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public function __construct() {

		/* Do nothing here */

	}

	/**
	* initialize
	*
	* The real constructor to initialize MSTaxSync_TT_Core
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public function initialize() {

		$this->prepare_tt_core();

	}

	/**
	* prepare_tt_core
	*
	* This function will prepare core data model for local site taxonomy terms
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	private function prepare_tt_core() {

		// globals
		global $wpdb;

		// vars
		$site_id = get_current_blog_id();

		// get terms table
		$terms_table = $wpdb->get_blog_prefix( $site_id ) . 'terms';

		// check if term_order column exists
		$term_order_column = $wpdb->query(
			"SHOW COLUMNS FROM $terms_table
			 LIKE 'term_order'"
		);

		if ( $term_order_column == 0 ) {

			// add term_order_column
			$term_order_column = $wpdb->query(
				"ALTER TABLE $terms_table
				 ADD term_order INT( 4 ) NULL DEFAULT '0'"
			);

		}

	}

}

/**
* mstaxsync_tt_core
*
* The main function responsible for returning the one true instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mstaxsync_tt_core() {

	// globals
	global $mstaxsync_tt_core;

	// initialize
	if( ! isset( $mstaxsync_tt_core ) ) {

		$mstaxsync_tt_core = new MSTaxSync_TT_Core();
		$mstaxsync_tt_core->initialize();

	}

	// return
	return $mstaxsync_tt_core;

}

// initialize
mstaxsync_tt_core();

endif; // class_exists check