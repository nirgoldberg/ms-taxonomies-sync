<?php
/**
 * Admin settings filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Admin_Settings' ) ) :

class MSTaxSync_Admin_Settings extends MSTaxSync_Admin_Settings_Page {

	/**
	 * initialize
	 *
	 * This function will initialize the admin settings page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function initialize() {

		// settings
		$this->settings = array(

			// slugs
			'parent_slug'			=> 'mstaxsync-dashboard',
			'menu_slug'				=> 'mstaxsync-settings',

			// titles
			'page_title'			=> __( 'Multisite Taxonomies Sync Settings', 'mstaxsync' ),
			'menu_title'			=> __( 'Settings', 'mstaxsync' ),

			// tabs
			'tabs'					=> array(
				'taxonomies'		=> array(
					'title'				=> __( 'Taxonomies', 'mstaxsync' ),
					'sections'			=> array(
						'categories'	=> array(
							'title'			=> __( 'Categories Settings', 'mstaxsync' ),
							'description'	=> '',
						),
						'taxonomies'	=> array(
							'title'			=> __( 'Custom Taxonomies Settings', 'mstaxsync' ),
							'description'	=> '',
						),
					),
				),
				'capabilities'		=> array(
					'title'				=> __( 'Capabilities', 'mstaxsync' ),
					'sections'			=> array(
						'edit'			=> array(
							'title'			=> __( 'Edit Capabilities Settings', 'mstaxsync' ),
							'description'	=> '',
						),
					),
				),
				'uninstall'			=> array(
					'title'				=> __( 'Uninstall', 'mstaxsync' ),
					'sections'			=> array(
						'uninstall'		=> array(
							'title'			=> __( 'Uninstall Settings', 'mstaxsync' ),
							'description'	=> '',
						),
					),
				),
			),
			'active_tab'				=> 'taxonomies',

			// fields
			'fields'					=> array(
				array(
					'uid'				=> 'mstaxsync_sync_categories',
					'label'				=> __( 'Sync categories?', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_sync_categories',
					'tab'				=> 'taxonomies',
					'section'			=> 'categories',
					'type'				=> 'checkbox',
					'options'			=> array(
						'category'		=> '',
					),
				),
				array(
					'uid'				=> 'mstaxsync_synced_taxonomies',
					'label'				=> __( 'Taxonomies to sync', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_synced_taxonomies',
					'tab'				=> 'taxonomies',
					'section'			=> 'taxonomies',
					'type'				=> 'checkbox',
					'options'			=> mstaxsync_get_main_site_custom_taxonomies_names(),
				),
				array(
					'uid'				=> 'mstaxsync_edit_taxonomy_terms',
					'label'				=> __( 'Edit taxonomy term names', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_edit_taxonomy_terms',
					'tab'				=> 'capabilities',
					'section'			=> 'edit',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'helper'			=> __( 'Check this option to allow editing taxonomy term names directly from this plugin', 'mstaxsync' ),
				),
				array(
					'uid'				=> 'mstaxsync_detach_taxonomy_terms',
					'label'				=> __( 'Detach taxonomy terms', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_detach_taxonomy_terms',
					'tab'				=> 'capabilities',
					'section'			=> 'edit',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'helper'			=> __( 'Check this option to allow detaching of already synced taxonomy terms. Detaching a taxonomy term will not remove or modify posts associated with this term', 'mstaxsync' ),
				),
				array(
					'uid'				=> 'mstaxsync_uninstall_remove_data',
					'label'				=> __( 'Remove Data on Uninstall', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_uninstall_remove_data',
					'tab'				=> 'uninstall',
					'section'			=> 'uninstall',
					'type'				=> 'checkbox',
					'options'			=> array(
						'remove'		=> '',
					),
					'helper'			=> __( 'Caution: all data will be removed without any option to restore', 'mstaxsync' ),
				),
			),

		);

	}

}

// initialize
new MSTaxSync_Admin_Settings();

endif; // class_exists check