<?php
/**
 * Admin dashboard page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Admin_Dashboard' ) ) :

class MSTaxSync_Admin_Dashboard extends MSTaxSync_Admin_Page {

	/**
	 * initialize
	 *
	 * This function will initialize the admin submenu page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	protected function initialize() {

		$this->settings = array(

			// slugs
			'parent_slug'	=> 'mstaxsync-dashboard',
			'menu_slug'		=> 'mstaxsync-dashboard',

			// titles
			'page_title'	=> __( 'Multisite Taxonomies Sync Dashboard', 'mstaxsync' ),
			'menu_title'	=> __( 'Dashboard', 'mstaxsync' ),

			// tabs
			'tabs'			=> array(
				'new'		=> __( 'What\'s New',	'mstaxsync' ),
				'changelog'	=> __( 'Changelog',		'mstaxsync' ),
			),
			'active_tab'	=> 'new',

		);

	}

}

// initialize
new MSTaxSync_Admin_Dashboard();

endif; // class_exists check