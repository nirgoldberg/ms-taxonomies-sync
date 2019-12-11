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
	protected function initialize() {

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
					'target'			=> 'local',
					'title'				=> __( 'Taxonomies', 'mstaxsync' ),
					'sections'			=> array(
						'categories'	=> array(
							'target'		=> 'local',
							'title'			=> __( 'Categories Settings', 'mstaxsync' ),
							'description'	=> '',
						),
						'taxonomies'	=> array(
							'target'		=> 'local',
							'title'			=> __( 'Custom Taxonomies Settings', 'mstaxsync' ),
							'description'	=> '',
						),
					),
				),
				'display'			=> array(
					'title'				=> __( 'Display', 'mstaxsync' ),
					'sections'			=> array(
						'ui'			=> array(
							'title'			=> __( 'User Interface Settings', 'mstaxsync' ),
							'description'	=> '',
						),
					),
				),
				'capabilities'		=> array(
					'target'			=> 'local',
					'title'				=> __( 'Capabilities', 'mstaxsync' ),
					'sections'			=> array(
						'edit'			=> array(
							'target'		=> 'local',
							'title'			=> __( 'Edit Settings', 'mstaxsync' ),
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
			'active_tab'			=> $this->is_main_site ? 'display' : 'taxonomies',

			// sections
			'sections'				=> array(),

			// fields
			'fields'				=> array(
				array(
					'target'			=> 'local',
					'uid'				=> 'mstaxsync_sync_categories',
					'label'				=> __( 'Sync categories?', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_sync_categories',
					'tab'				=> 'taxonomies',
					'section'			=> 'categories',
					'type'				=> 'checkbox',
					'options'			=> array(
						'category'		=> '',
					),
					'default'			=> array( 'category' ),
					'helper'			=> __( '(Default: sync categories)', 'mstaxsync' ),
				),
				array(
					'target'			=> 'local',
					'uid'				=> 'mstaxsync_synced_taxonomies',
					'label'				=> __( 'Taxonomies to sync', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_synced_taxonomies',
					'tab'				=> 'taxonomies',
					'section'			=> 'taxonomies',
					'type'				=> 'checkbox',
					'options'			=> mstaxsync_get_main_site_custom_taxonomies_names(),
				),
				array(
					'target'			=> 'local',
					'uid'				=> 'mstaxsync_advanced_treeview',
					'label'				=> __( 'Advanced tree view', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_advanced_treeview',
					'tab'				=> 'display',
					'section'			=> 'ui',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'default'			=> array( 'can' ),
					'supplimental'		=> __( 'Check this option to allow advanced tree view enabling multiselect taxonomy terms to sync', 'mstaxsync' ),
					'helper'			=> __( '(Default: true)', 'mstaxsync' ),
				),
				array(
					'target'			=> 'main',
					'uid'				=> 'mstaxsync_display_synced_taxonomy_terms_column',
					'label'				=> __( 'Display taxonomy terms \'Synced Sites\' column', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_display_synced_taxonomy_terms_column',
					'tab'				=> 'display',
					'section'			=> 'ui',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'default'			=> array( 'can' ),
					'supplimental'		=> __( 'Check this option to display taxonomy terms \'Synced Sites\' column (taxonomy screen)', 'mstaxsync' ),
					'helper'			=> __( '(Default: true)', 'mstaxsync' ),
				),
				array(
					'target'			=> 'local',
					'uid'				=> 'mstaxsync_display_main_taxonomy_term_column',
					'label'				=> __( 'Display taxonomy terms \'Main Site Term\' column', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_display_main_taxonomy_term_column',
					'tab'				=> 'display',
					'section'			=> 'ui',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'default'			=> array( 'can' ),
					'supplimental'		=> __( 'Check this option to display taxonomy terms \'Main Site Term\' column (taxonomy screen)', 'mstaxsync' ),
					'helper'			=> __( '(Default: true)', 'mstaxsync' ),
				),
				array(
					'target'			=> 'main',
					'uid'				=> 'mstaxsync_display_synced_posts_column',
					'label'				=> __( 'Display posts \'Synced Sites\' column', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_display_synced_posts_column',
					'tab'				=> 'display',
					'section'			=> 'ui',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'default'			=> array( 'can' ),
					'supplimental'		=> __( 'Check this option to display posts \'Synced Sites\' column (posts screen)', 'mstaxsync' ),
					'helper'			=> __( '(Default: true)', 'mstaxsync' ),
				),
				array(
					'target'			=> 'local',
					'uid'				=> 'mstaxsync_display_main_post_column',
					'label'				=> __( 'Display posts \'Main Site Post\' column', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_display_main_post_column',
					'tab'				=> 'display',
					'section'			=> 'ui',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'default'			=> array( 'can' ),
					'supplimental'		=> __( 'Check this option to display posts \'Main Site Post\' column (posts screen)', 'mstaxsync' ),
					'helper'			=> __( '(Default: true)', 'mstaxsync' ),
				),
				array(
					'target'			=> 'local',
					'uid'				=> 'mstaxsync_edit_taxonomy_terms',
					'label'				=> __( 'Edit taxonomy term names', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_edit_taxonomy_terms',
					'tab'				=> 'capabilities',
					'section'			=> 'edit',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'default'			=> array( 'can' ),
					'supplimental'		=> __( 'Check this option to allow editing taxonomy term names directly from this plugin', 'mstaxsync' ),
					'helper'			=> __( '(Default: true)', 'mstaxsync' ),
				),
				array(
					'target'			=> 'local',
					'uid'				=> 'mstaxsync_detach_taxonomy_terms',
					'label'				=> __( 'Detach taxonomy terms', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_detach_taxonomy_terms',
					'tab'				=> 'capabilities',
					'section'			=> 'edit',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'supplimental'		=> __( 'Check this option to allow detaching of already synced taxonomy terms.<br />Detaching a taxonomy term will not remove or modify posts associated with this term', 'mstaxsync' ),
					'helper'			=> __( '(Default: false)', 'mstaxsync' ),
				),
				array(
					'target'			=> 'local',
					'uid'				=> 'mstaxsync_delete_taxonomy_terms',
					'label'				=> __( 'Delete taxonomy terms', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_delete_taxonomy_terms',
					'tab'				=> 'capabilities',
					'section'			=> 'edit',
					'type'				=> 'checkbox',
					'options'			=> array(
						'can'			=> '',
					),
					'supplimental'		=> __( 'Check this option to allow deleting of taxonomy terms.<br />Deleting a synced taxonomy term will first detach it.<br />Deleting a taxonomy term will not remove or modify posts associated with this term', 'mstaxsync' ),
					'helper'			=> __( '(Default: false)', 'mstaxsync' ),
				),
				array(
					'uid'				=> 'mstaxsync_uninstall_remove_data',
					'label'				=> __( 'Remove data on uninstall', 'mstaxsync' ),
					'label_for'			=> 'mstaxsync_uninstall_remove_data',
					'tab'				=> 'uninstall',
					'section'			=> 'uninstall',
					'type'				=> 'checkbox',
					'options'			=> array(
						'remove'		=> '',
					),
					'supplimental'		=> __( 'Caution: all data will be removed without any option to restore', 'mstaxsync' ),
					'helper'			=> __( '(Default: false)', 'mstaxsync' ),
				),
			),

		);

	}

}

// initialize
new MSTaxSync_Admin_Settings();

endif; // class_exists check