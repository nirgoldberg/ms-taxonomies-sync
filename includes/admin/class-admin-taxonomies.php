<?php
/**
 * Admin taxonomies page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Admin_Taxonomies' ) ) :

class MSTaxSync_Admin_Taxonomies extends MSTaxSync_Admin_Page {

	/**
	 * initialize
	 *
	 * This function will initialize the taxonomies submenu page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function initialize() {

		$this->settings = array(

			// slugs
			'parent_slug'		=> 'mstaxsync-dashboard',
			'menu_slug'			=> 'mstaxsync-taxonomies',

			// titles
			'page_title'		=> __( 'Taxonomies Sync', 'mstaxsync' ),
			'menu_title'		=> __( 'Taxonomies', 'mstaxsync' ),

			// tabs
			'tabs'				=> array(
				'category'		=> array(
					'title'		=> __( 'Categories', 'mstaxsync' ),
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
		$taxonomies = mstaxsync_get_main_site_custom_taxonomies_objects();

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
new MSTaxSync_Admin_Taxonomies();

endif; // class_exists check