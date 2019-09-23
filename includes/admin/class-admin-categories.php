<?php
/**
 * Admin categories page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSCatSync_Admin_Categories' ) ) :

class MSCatSync_Admin_Categories extends MSCatSync_Admin_Page {

	/**
	 * initialize
	 *
	 * This function will initialize the categories submenu page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function initialize() {

		$this->settings = array(

			// slugs
			'parent_slug'		=> 'mscatsync-dashboard',
			'menu_slug'			=> 'mscatsync-categories',

			// titles
			'page_title'		=> __( 'Categories & Taxonomies Sync', 'mscatsync' ),
			'menu_title'		=> __( 'Categories', 'mscatsync' ),

			// tabs
			'tabs'				=> array(
				'category'		=> array(
					'title'		=> __( 'Categories', 'mscatsync' ),
				),
			),
			'active_tab'		=> 'category',

		);

		// append custom taxonomies
		$this->append_custom_taxonomies();

	}

	/**
	 * append_custom_taxonomies
	 *
	 * This function will append custom taxonomies to settings tabs
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function append_custom_taxonomies() {

		// vars
		$taxonomies = mscatsync_get_main_site_custom_taxonomies();

		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {

				$this->settings[ 'tabs' ][ $taxonomy->name ] = array(
					'title'		=> $taxonomy->label,
				);

			}
		}

	}

}

// initialize
new MSCatSync_Admin_Categories();

endif; // class_exists check