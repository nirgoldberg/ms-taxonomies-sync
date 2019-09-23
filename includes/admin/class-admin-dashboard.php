<?php
/**
 * Admin dashboard page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSCatSync_Admin_Dashboard' ) ) :

class MSCatSync_Admin_Dashboard extends MSCatSync_Admin_Page {

	/**
	 * initialize
	 *
	 * This function will initialize the admin submenu page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function initialize() {

		$this->settings = array(

			// slugs
			'parent_slug'	=> 'mscatsync-dashboard',
			'menu_slug'		=> 'mscatsync-dashboard',

			// titles
			'page_title'	=> __( 'Multisite Categories Sync Dashboard', 'mscatsync' ),
			'menu_title'	=> __( 'Dashboard', 'mscatsync' ),

			// tabs
			'tabs'			=> array(
				'new'		=> __( 'What\'s New',	'mscatsync' ),
				'changelog'	=> __( 'Changelog',		'mscatsync' ),
			),
			'active_tab'	=> 'new',

		);

	}

}

// initialize
new MSCatSync_Admin_Dashboard();

endif; // class_exists check