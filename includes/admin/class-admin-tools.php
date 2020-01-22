<?php
/**
 * Admin tools page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Admin_Tools' ) ) :

class MSTaxSync_Admin_Tools extends MSTaxSync_Admin_Page {

	/**
	 * initialize
	 *
	 * This function will initialize the tools submenu page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	protected function initialize() {

		$this->settings = array(

			// slugs
			'parent_slug'		=> 'mstaxsync-dashboard',
			'menu_slug'			=> 'mstaxsync-tools',

			// titles
			'page_title'		=> __( 'Multisite Taxonomies Sync Tools', 'mstaxsync' ),
			'menu_title'		=> __( 'Tools', 'mstaxsync' ),

			// tabs
			'tabs'				=> array(
				'resync-posts'	=> array(
					'title'			=> __( 'Resync Posts', 'mstaxsync' ),
					'permission'	=> get_option( 'mstaxsync_resync_posts', array( 'can' ) ),
				),
			),
			'active_tab'		=> '',

		);

	}

}

// initialize
new MSTaxSync_Admin_Tools();

endif; // class_exists check