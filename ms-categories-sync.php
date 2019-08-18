<?php
/**
* Plugin Name: Multisite Categories Sync
* Plugin URI: http://www.htmline.com/
* Description: Extends WordPress Multisite platforms with categories sync capabilities
* Version: 1.0.0
* Author: Nir Goldberg
* Author URI: http://www.htmline.com/
* License: GPLv3
* Text Domain: mscatsync
* Domain Path: /lang
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSCatSync' ) ) :

class MSCatSync {

	/**
	 * vars
	 *
	 * @var $version (string) plugin version number
	 * @var required_plugins (array) required plugins must be active for MSCatSync
	 * @var $settings (array) plugin settings array
	 */
	var $version = '1.0.0';

	var $required_plugins = array();

	var $settings = array();

	/**
	* __construct
	*
	* A dummy constructor to ensure MSCatSync is only initialized once
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function __construct() {

		add_action( 'admin_init', array( $this, 'check_required_plugins' ) );

		/* Do nothing here */

	}

	/**
	* initialize
	*
	* The real constructor to initialize MSCatSync
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function initialize() {

		// vars
		$version	= $this->version;
		$basename	= plugin_basename( __FILE__ );
		$path		= plugin_dir_path( __FILE__ );
		$url		= plugin_dir_url( __FILE__ );
		$slug		= dirname( $basename );

		// settings
		$this->settings = array(

			// basic
			'name'				=> __( 'Multisite Categories Sync', 'mscatsync' ),
			'version'			=> $version,

			// urls
			'basename'			=> $basename,
			'path'				=> $path,		// with trailing slash
			'url'				=> $url,		// with trailing slash
			'slug'				=> $slug,

			// options
			'show_admin'		=> true,
			'capability'		=> 'manage_options',
			'debug'				=> false,

		);

		// constants
		$this->define( 'MSCatSync',			true );
		$this->define( 'MSCatSync_VERSION',	$version );
		$this->define( 'MSCatSync_PATH',	$path );

		// api
		include_once( MSCatSync_PATH . 'includes/api/api-helpers.php' );

		// core
		mscatsync_include( 'includes/classes/class-mscatsync-core.php' );

		if ( is_admin() ) {

			// admin
			// functions
			mscatsync_include( 'includes/admin/settings-api.php' );

			// actions
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );

		} else {

			// frontend

		}

		// actions
		add_action( 'plugins_loaded',	array( $this, 'plugins_loaded' ), 5 );
		add_action( 'init',				array( $this, 'init' ), 5 );
		add_action( 'init',				array( $this, 'register_assets' ), 5 );

		// plugin activation / deactivation
		register_activation_hook	( __FILE__,	array( $this, 'mscatsync_activate' ) );
		register_deactivation_hook	( __FILE__,	array( $this, 'mscatsync_deactivate' ) );

	}

	/**
	* plugins_loaded
	*
	* This function will run after all plugins and theme functions have been included
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function plugins_loaded() {

		// exit if called too early
		if ( ! did_action( 'plugins_loaded' ) )
			return;

		if ( is_admin() ) {

			// admin

		} else {

			// frontend

		}

	}

	/**
	* init
	*
	* This function will run after all plugins and theme functions have been included
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function init() {

		// exit if called too early
		if ( ! did_action( 'plugins_loaded' ) )
			return;

		// exit if already init
		if ( mscatsync_get_setting( 'init' ) )
			return;

		// only run once
		mscatsync_update_setting( 'init', true );

		// update url - allow another plugin to modify dir
		mscatsync_update_setting( 'url', plugin_dir_url( __FILE__ ) );

		// set textdomain
		$this->load_plugin_textdomain();

		// admin
		if ( is_admin() ) {

			// admin
			mscatsync_include( 'includes/admin/class-admin-settings-page.php' );
			mscatsync_include( 'includes/admin/class-admin-settings.php' );

		}

		// action for 3rd party
		do_action( 'mscatsync/init' );

	}

	/**
	 * register_assets
	 *
	 * This function will register scripts and styles
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function register_assets() {

		// append styles
		$styles		= array(
			'mscatsync-admin'	=> array(
				'src'		=> mscatsync_get_url( 'assets/css/mscatsync-admin-style.css' ),
				'deps'		=> false
			)
		);

		// register styles
		foreach( $styles as $handle => $style ) {
			wp_register_style( $handle, $style[ 'src' ], $style[ 'deps' ], MSCatSync_VERSION );
		}

	}

	/**
	 * admin_enqueue_scripts
	 *
	 * This function will enque admin scripts and styles
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function admin_enqueue_scripts() {

		// enqueue styles
		wp_enqueue_style( 'mscatsync-admin' );

	}

	/**
	* define
	*
	* This function will safely define a constant
	*
	* @since		1.0.0
	* @param		$name (string)
	* @param		$value (string)
	* @return		N/A
	*/
	function define( $name, $value = true ) {

		if ( ! defined( $name ) ) {
			define( $name, $value );
		}

	}

	/**
	* load_plugin_textdomain
	*
	* This function will load the textdomain file
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function load_plugin_textdomain() {

		// vars
		$domain = 'mscatsync';
		$locale = apply_filters( 'plugin_locale', mscatsync_get_locale(), $domain );
		$mofile = $domain . '-' . $locale . '.mo';

		// load from the languages directory first
		load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );

		// load from plugin lang folder
		load_textdomain( $domain, mscatsync_get_path( 'lang/' . $mofile ) );

	}

	/**
	* has_setting
	*
	* This function will return true if has setting
	*
	* @since		1.0.0
	* @param		$name (string)
	* @return		(boolean)
	*/
	function has_setting( $name ) {

		// return
		return isset( $this->settings[ $name ] );

	}

	/**
	* get_setting
	*
	* This function will return a setting value
	*
	* @since		1.0.0
	* @param		$name (string)
	* @return		(mixed)
	*/
	function get_setting( $name ) {

		// return
		return isset( $this->settings[ $name ] ) ? $this->settings[ $name ] : null;

	}

	/**
	* update_setting
	*
	* This function will update a setting value
	*
	* @since		1.0.0
	* @param		$name (string)
	* @param		$value (mixed)
	* @return		N/A
	*/
	function update_setting( $name, $value ) {

		$this->settings[ $name ] = $value;

		// return
		return true;

	}

	/**
	* mscatsync_activate
	*
	* Actions perform on activation of plugin
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function mscatsync_activate() {

		if ( $this->check_required_plugins() ) {

			// perform activation process

		}

	}

	/**
	* mscatsync_deactivate
	*
	* Actions perform on deactivation of plugin
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function mscatsync_deactivate() {}

	/**
	* check_required_plugins
	*
	* This function will check if required plugins are activated.
	* A backup sanity check, in case the plugin is activated in a weird way,
	* or one of required plugins has been deactivated
	*
	* @since		1.0.0
	* @param		N/A
	* @return		(boolean)
	*/
	function check_required_plugins() {

		// vars
		$basename = $this->settings[ 'basename' ];

		if ( ! $this->has_required_plugins() ) {

			if ( is_plugin_active( $basename ) ) {

				deactivate_plugins( $basename );
				add_action( 'admin_notices', array( $this, 'admin_notices_error' ) );

				if ( isset( $_GET[ 'activate' ] ) ) {
					unset( $_GET[ 'activate' ] );
				}

			}

			// return
			return false;

		}

		// return
		return true;

	}

	/**
	* has_required_plugins
	*
	* This function will check if required plugins are activated
	*
	* @since		1.0.0
	* @param		N/A
	* @return		(boolean)
	*/
	function has_required_plugins() {

		// vars
		$required = $this->required_plugins;

		if ( empty( $required ) )
			// return
			return true;

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		foreach ( $required as $key => $plugin ) {

			$plugin = ( ! is_numeric( $key ) ) ? "{$key}/{$plugin}.php" : "{$plugin}/{$plugin}.php";

			if ( ! in_array( $plugin, $active_plugins ) && ! array_key_exists( $plugin, $active_plugins ) )
				// return
				return false;

		}

		// return
		return true;

	}

	/**
	* admin_notices_error
	*
	* This function will add admin error notice
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function admin_notices_error() {

		// vars
		$required	= $this->required_plugins;
		$class		= 'notice notice-error is-dismissible';
		$msg		= sprintf( __( "<strong>%s</strong> plugin can't be activated.<br />The following plugins should be installed and activated first:<br />", 'mscatsync' ), $this->settings[ 'name' ] );

		foreach ( $required as $key => $plugin ) {

			$path = ( ! is_numeric( $key ) ) ? "{$key}/{$plugin}.php" : "{$plugin}/{$plugin}.php";

			if ( file_exists( plugin_dir_path( __DIR__ ) . $path ) ) {
				$name = get_plugin_data( plugin_dir_path( __DIR__ ) . $path )[ 'Name' ];
			} else {
				$name = $plugin;
			}

			$msg .= "<br />&bull; {$name}";

		}

		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $msg );

	}

}

/**
* mscatsync
*
* The main function responsible for returning the one true mscatsync instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mscatsync() {

	// globals
	global $mscatsync;

	// initialize
	if( ! isset( $mscatsync ) ) {

		$mscatsync = new MSCatSync();
		$mscatsync->initialize();

	}

	// return
	return $mscatsync;

}

// initialize
mscatsync();

endif; // class_exists check