<?php
/**
 * Admin page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSCatSync_Admin_Page' ) ) :

class MSCatSync_Admin_Page {

	/**
	 * vars
	 *
	 * @var $_instances (array) instances array
	 * @var $settings (array) settings array
	 */
	protected static $_instances = array();
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
		$this->add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// store instance
		self::$_instances[] = $this;

	}

	/**
	 * __destruct
	 *
	 * This function will unset the stored instance
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function __destruct() {

		// unset stored instance
		unset( self::$_instances[ array_search( $this, self::$_instances, true ) ] );

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

			// slugs
			'parent_slug'	=> '',
			'menu_slug'		=> '',

			// titles
			'page_title'	=> '',
			'menu_title'	=> '',

			// tabs
			/**
			 * tabs structure:
			 *
			 * '[tab slug]'	=> [tab title],
			 * ...
			 */
			'tabs'			=> array(),
			'active_tab'	=> '',

		);

	}

	/**
	 * add_action
	 *
	 * This function will check page settings validity before adding the action
	 *
	 * @since		1.0.0
	 * @param		$tag (string)
	 * @param		$function_to_add (string)
	 * @param		$priority (int)
	 * @param		$accepted_args (int)
	 * @return		N/A
	 */
	function add_action( $tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1 ) {

		if ( empty( $this->settings[ 'parent_slug' ] ) || empty( $this->settings[ 'menu_slug' ] ) || empty( $this->settings[ 'page_title' ] ) || empty( $this->settings[ 'menu_title' ] ) )
			return;

		// add action
		add_action( $tag, $function_to_add, $priority, $accepted_args );

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

		// add submenu page
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
	 * This function will display the page content
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function html() {

		// vars
		$view = array(

			'version'		=> mscatsync_get_setting( 'version' ),
			'tabs'			=> $this->settings[ 'tabs' ],
			'active'		=> $this->settings[ 'active_tab' ]

		);

		// set active tab
		if ( ! empty( $_GET[ 'tab' ] ) && array_key_exists( $_GET[ 'tab' ], $view[ 'tabs' ] ) ) {
			$view[ 'active' ] = $_GET[ 'tab' ];
		}

		// load view
		mscatsync_get_view( $this->settings[ 'menu_slug' ], $view );

	}

	/**
	 * get_instances
	 *
	 * This function will return all class instances
	 *
	 * @since		1.0.0
	 * @param		$include_subclasses (boolean) Optionally include subclasses in returned set
	 * @return		(array)
	 */
	static function get_instances( $include_subclasses = false ) {

		// vars
		$instances = array();

		foreach ( self::$_instances as $instance ) {

			// vars
			$class = get_class( $this );

			if ( $instance instanceof $class ) {
				if ( $include_subclasses || ( get_class( $instance ) === $class ) ) {
					$instances[] = $instance;
				}
			}

		}

		// return
		return $instances;

	}

}

endif; // class_exists check