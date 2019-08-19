<?php
/**
 * Admin settings filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSCatSync_Admin_Settings' ) ) :

class MSCatSync_Admin_Settings extends MSCatSync_Admin_Settings_Page {

	/**
	 * initialize
	 *
	 * This function will setup the settings page data
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function initialize() {

		// settings
		$this->settings = array(

			// slugs
			'parent'				=> 'mscatsync-dashboard',
			'slug'					=> 'mscatsync-settings',

			// titles
			'page_title'			=> __( 'Multisite Categories Sync Settings', 'mscatsync' ),
			'menu_title'			=> __( 'Settings', 'mscatsync' ),

			// tabs
			'tabs'					=> array(
				'general'			=> array(
					'title'				=> 'General',
					'sections'			=> array(
						'general'		=> array(
							'title'			=> 'General Settings',
							'description'	=> ''
						)
					)
				),
				'uninstall'			=> array(
					'title'				=> 'Uninstall',
					'sections'			=> array(
						'uninstall'		=> array(
							'title'			=> 'Uninstall Settings',
							'description'	=> ''
						)
					)
				)
			),
			'active_tab'			=> 'general',

			// fields
			'fields'				=> array(
				array(
					'uid'			=> 'mscatsync_general_default_lang',
					'label'			=> 'Default Language(s)',
					'label_for'		=> 'mscatsync_general_default_lang',
					'tab'			=> 'general',
					'section'		=> 'general',
					'type'			=> 'checkbox',
					'options'		=> mscatsync_get_active_languages(),
					'helper'		=> __( 'Used to assign default language(s) for already registered subscribers', 'mscatsync' )
				),
				array(
					'uid'			=> 'mscatsync_general_default_cat' . ( defined( 'ICL_LANGUAGE_CODE' ) ? '_' . ICL_LANGUAGE_CODE : '' ),
					'label'			=> 'Default Categories',
					'label_for'		=> 'mscatsync_general_default_cat' . ( defined( 'ICL_LANGUAGE_CODE' ) ? '_' . ICL_LANGUAGE_CODE : '' ),
					'tab'			=> 'general',
					'section'		=> 'general',
					'type'			=> 'checkbox',
					'options'		=> mscatsync_get_categories(),
					'helper'		=> __( 'Used to assign default post categories', 'mscatsync' )
				),
				array(
					'uid'			=> 'mscatsync_uninstall_remove_data',
					'label'			=> 'Remove Data on Uninstall',
					'label_for'		=> 'mscatsync_uninstall_remove_data',
					'tab'			=> 'uninstall',
					'section'		=> 'uninstall',
					'type'			=> 'checkbox',
					'options'		=> array(
						'remove'	=> ''
					),
					'helper'		=> __( 'Caution: all data will be removed without any option to restore', 'mscatsync' )
				)
			)

		);

	}

}

// initialize
new MSCatSync_Admin_Settings();

endif; // class_exists check