<?php
/**
 * Admin menu page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSCatSync_Admin' ) ) :

class MSCatSync_Admin {

	/**
	 * vars
	 *
	 * @var $settings (array) settings array
	 */
	public $settings = array();

	/**
	 * __construct
	 *
	 * This function will initialize the admin menu page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function __construct() {

		// initialize
		$this->initialize();

		// actions
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}

	/**
	 * initialize
	 *
	 * This function will initialize the admin menu page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function initialize() {

		$this->settings = array(

			// slug
			'menu_slug'		=> 'mscatsync-dashboard',

			// titles
			'page_title'	=> __( 'Multisite Categories Sync', 'mscatsync' ),
			'menu_title'	=> __( 'Categories Sync', 'mscatsync' ),

			// icon
			'icon_url'		=> 'dashicons-category',

			// position
			'position'		=> 6,

		);

	}

	/**
	 * admin_menu
	 *
	 * This function will add Multisite Categories Sync submenu item to the WP admin
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function admin_menu() {
		
		// exit if no show_admin
		if ( ! mscatsync_get_setting( 'show_admin' ) )
			return;

		// vars
		$capability = mscatsync_get_setting( 'capability' );

		// add menu page
		add_menu_page(
			$this->settings[ 'page_title' ],
			$this->settings[ 'menu_title' ],
			$capability,
			$this->settings[ 'menu_slug' ],
			'',
			$this->settings[ 'icon_url' ],
			$this->settings[ 'position' ]
		);

	}

}

// initialize
new MSCatSync_Admin();

endif; // class_exists check