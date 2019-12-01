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
	protected function initialize() {

		$this->settings = array(

			// slugs
			'parent_slug'		=> 'mstaxsync-dashboard',
			'menu_slug'			=> 'mstaxsync-taxonomies',

			// titles
			'page_title'		=> __( 'Taxonomies Sync', 'mstaxsync' ),
			'menu_title'		=> __( 'Taxonomies', 'mstaxsync' ),

			// tabs
			'tabs'				=> array(),
			'active_tab'		=> '',

		);

		// append taxonomies
		$this->append_taxonomies();

	}

	/**
	 * append_taxonomies
	 *
	 * This function will append taxonomies to settings tabs according to taxonomies settings
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	private function append_taxonomies() {

		// vars
		$categories	= get_option( 'mstaxsync_sync_categories', array( 'category' ) );
		$taxonomies	= get_option( 'mstaxsync_synced_taxonomies' );
		$active_tab	= '';

		if ( $categories && in_array( 'category', $categories ) ) {

			$this->settings[ 'tabs' ][ 'category' ] = array(
				'title'	=> __( 'Categories', 'mstaxsync' ),
			);

			$active_tab = 'category';

		}

		if ( $taxonomies ) {
			foreach ( $taxonomies as $tax_name ) {

				$tax = get_taxonomy( $tax_name );

				if ( $tax ) {

					$this->settings[ 'tabs' ][ $tax_name ] = array(
						'title'	=> $tax->label,
					);

					if ( ! $active_tab ) {
						$active_tab = $tax_name;
					}

				}

			}
		}

		$this->settings[ 'active_tab' ] = $active_tab;

	}

}

// initialize
new MSTaxSync_Admin_Taxonomies();

endif; // class_exists check