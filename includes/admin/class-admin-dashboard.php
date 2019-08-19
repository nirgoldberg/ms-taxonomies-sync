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

class MSCatSync_Admin_Dashboard {

	/**
	 * vars
	 *
	 * @var $settings (array) settings array
	 */
	public $settings = array();

	/**
	 * __construct
	 *
	 * This function will initialize the admin submenu page
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
	 * This function will initialize the admin submenu page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function initialize() {

		$this->settings = array(

			// slug
			'parent_slug'	=> 'mscatsync-dashboard',
			'menu_slug'		=> 'mscatsync-dashboard',

			// titles
			'page_title'	=> __( 'Multisite Categories Sync Dashboard', 'mscatsync' ),
			'menu_title'	=> __( 'Dashboard', 'mscatsync' ),

		);

	}

	/**
	 * admin_menu
	 *
	 * This function will add the MSCatSync menu item to the wordpress admin
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
		add_submenu_page(
			$this->settings[ 'parent_slug' ],
			$this->settings[ 'page_title' ],
			$this->settings[ 'menu_title' ],
			$capability,
			$this->settings[ 'menu_slug' ],
			array( $this, 'html' )
		);

	}

	/**
	 * html
	 *
	 * Output html content
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function html() {

		// vars
		$view = array(
			'version'		=> mscatsync_get_setting( 'version' ),
			'tabs'			=> array(
				'new'		=> __( "What's New", 'mscatsync' ),
				'changelog'	=> __( "Changelog", 'mscatsync' )
			),
			'active'		=> 'new'
		);

		// set active tab
		if ( ! empty( $_GET[ 'tab' ] ) && array_key_exists( $_GET[ 'tab' ], $view[ 'tabs' ] ) ) {
			$view[ 'active' ] = $_GET[ 'tab' ];
		}

		// load view
		mscatsync_get_view( 'dashboard', $view );

	}

}

// initialize
new MSCatSync_Admin_Dashboard();

endif; // class_exists check