<?php
/**
 * MSCatSync_Core
 *
 * @author		Nir Goldberg
 * @package		includes
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSCatSync_Core' ) ) :

class MSCatSync_Core {

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

}

/**
* mscatsync_core
*
* The main function responsible for returning the one true instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mscatsync_core() {

	// globals
	global $mscatsync_core;

	// initialize
	if( ! isset( $mscatsync_core ) ) {
		$mscatsync_core = new MSCatSync_Core();
	}

	// return
	return $mscatsync_core;

}

// initialize
mscatsync_core();

endif; // class_exists check