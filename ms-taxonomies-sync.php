<?php
/**
* Plugin Name: Multisite Taxonomies Sync
* Plugin URI: http://www.htmline.com/
* Description: Extends WordPress Multisite platforms with taxonomies sync capabilities
* Version: 1.0.0
* Author: Nir Goldberg
* Author URI: http://www.htmline.com/
* License: GPLv3
* Text Domain: mstaxsync
* Domain Path: /lang
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync' ) ) :

class MSTaxSync {

	/**
	 * vars
	 *
	 * @var $version (string) plugin version number
	 * @var required_plugins (array) required plugins must be active for MSTaxSync
	 */
	public $version;
	public $required_plugins;

	/**
	* __construct
	*
	* A dummy constructor to ensure MSTaxSync is only initialized once
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function __construct() {

		$this->version				= '1.0.0';
		$this->required_plugins		= array();

		/* Do nothing here */

	}

	/**
	* initialize
	*
	* The real constructor to initialize MSTaxSync
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function initialize() {

		// vars
		$basename	= plugin_basename( __FILE__ );
		$path		= plugin_dir_path( __FILE__ );
		$url		= plugin_dir_url( __FILE__ );
		$slug		= dirname( $basename );

		// settings
		$this->settings = array(

			// basic
			'name'				=> __( 'Multisite Taxonomies Sync', 'mstaxsync' ),
			'version'			=> $this->version,

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

		if ( ! $this->check_multisite() )
			return;

		if ( ! $this->check_required_plugins() )
			return;

		// constants
		$this->define( 'MSTaxSync',			true );
		$this->define( 'MSTaxSync_VERSION',	$this->version );
		$this->define( 'MSTaxSync_PATH',	$path );

		// api
		include_once( MSTaxSync_PATH . 'includes/api/api-helpers.php' );
		include_once( MSTaxSync_PATH . 'includes/api/api-taxonomy-terms.php' );

		// core
		mstaxsync_include( 'includes/classes/class-mstaxsync-core.php' );

		// actions
		add_action( 'init',	array( $this, 'init' ), 99 );
		add_action( 'init',	array( $this, 'register_assets' ), 99 );

		// admin
		if ( is_admin() ) {

			// actions
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );

		}

		// plugin activation / deactivation
		register_activation_hook	( __FILE__,	array( $this, 'mstaxsync_activate' ) );
		register_deactivation_hook	( __FILE__,	array( $this, 'mstaxsync_deactivate' ) );

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
		if ( mstaxsync_get_setting( 'init' ) )
			return;

		// only run once
		mstaxsync_update_setting( 'init', true );

		// update url - allow another plugin to modify dir
		mstaxsync_update_setting( 'url', plugin_dir_url( __FILE__ ) );

		// set textdomain
		$this->load_plugin_textdomain();

		// admin
		if ( is_admin() ) {

			mstaxsync_include( 'includes/admin/settings-api.php' );
			mstaxsync_include( 'includes/admin/class-admin.php' );
			mstaxsync_include( 'includes/admin/class-admin-page.php' );
			mstaxsync_include( 'includes/admin/class-admin-dashboard.php' );
			mstaxsync_include( 'includes/admin/class-admin-taxonomies.php' );
			mstaxsync_include( 'includes/admin/class-admin-settings-page.php' );
			mstaxsync_include( 'includes/admin/class-admin-settings.php' );

		}

		// action for 3rd party
		do_action( 'mstaxsync/init' );

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
		$styles = array(
			'mstaxsync-admin'		=> array(
				'src'	=> mstaxsync_get_url( 'assets/css/mstaxsync-admin-style.css' ),
				'deps'	=> false,
			),
			'mstaxsync-admin-rtl'	=> array(
				'src'	=> mstaxsync_get_url( 'assets/css/mstaxsync-admin-style-rtl.css' ),
				'deps'	=> array( 'mstaxsync-admin' ),
			),
			'jquery-ui'				=> array(
				'src'	=> '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
				'deps'	=> false,
			),
		);

		// append scripts
		$scripts = array(
			'jquery-ui'			=> array(
				'src'	=> 'https://code.jquery.com/ui/1.12.1/jquery-ui.js',
				'deps'	=> array( 'jquery' ),
			),
			'nestedSortable'	=> array(
				'src'	=> mstaxsync_get_url( 'assets/js/lib/jquery.mjs.nestedSortable.js' ),
				'deps'	=> array( 'jquery-ui' ),
			),
			'mstaxsync'			=> array(
				'src'	=> mstaxsync_get_url( 'assets/js/min/mstaxsync.min.js' ),
				'deps'	=> array( 'jquery-ui' ),
			),
		);

		// register styles
		foreach( $styles as $handle => $style ) {
			wp_register_style( $handle, $style[ 'src' ], $style[ 'deps' ], MSTaxSync_VERSION );
		}

		// register scripts
		foreach( $scripts as $handle => $script ) {
			wp_register_script( $handle, $script[ 'src' ], $script[ 'deps' ], MSTaxSync_VERSION, true );
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
		wp_enqueue_style( 'mstaxsync-admin' );
		wp_enqueue_style( 'jquery-ui' );

		// rtl
		if ( is_rtl() ) {

			wp_enqueue_style( 'mstaxsync-admin-rtl' );

		}

		// enqueue scripts
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'nestedSortable' );

		// localize mstaxsync
		$translation_arr	= array(
			'settings'		=> array(
				'advanced_treeview'							=> get_option( 'mstaxsync_advanced_treeview', array( 'can' ) ),
				'edit_terms'								=> get_option( 'mstaxsync_edit_taxonomy_terms', array( 'can' ) ),
				'detach_terms'								=> get_option( 'mstaxsync_detach_taxonomy_terms' ),
				'delete_terms'								=> get_option( 'mstaxsync_delete_taxonomy_terms' ),
			),
			'strings'		=> array(
				'relationship_new_item_str'					=> __( 'New item',	'mstaxsync' ),
				'relationship_changed_item_str'				=> __( 'Changed',	'mstaxsync' ),
				'relationship_success_str'					=> __( 'All terms were updated successfully.', 'mstaxsync' ),
				'relationship_main_terms_str'				=> __( 'new terms were synced.', 'mstaxsync' ),
				'relationship_local_terms_str'				=> __( 'terms were updated.', 'mstaxsync' ),
				'relationship_errors_str'					=> __( 'The following errors were found:', 'mstaxsync' ),
				'relationship_error_str'					=> __( 'Error', 'mstaxsync' ),
				'relationship_internal_server_error_str'	=> __( 'Internal server error', 'mstaxsync' ),
				'confirm_detach'							=> __( 'You are about to permanently detach this taxonomy term.', 'mstaxsync' ) . "\n" . __( 'This action cannot be undone.', 'mstaxsync' ) . "\n" . __( "'Cancel' to stop, 'OK' to detach.", 'mstaxsync' ),
				'confirm_delete'							=> __( 'You are about to permanently delete this taxonomy term.', 'mstaxsync' ) . "\n" . __( 'This action cannot be undone.', 'mstaxsync' ) . "\n" . __( "'Cancel' to stop, 'OK' to delete.", 'mstaxsync' ),
			),
			'ajaxurl'										=> admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'mstaxsync', '_mstaxsync', $translation_arr );

		// Enqueued script with localized data.
		wp_enqueue_script( 'mstaxsync' );

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
		$domain = 'mstaxsync';
		$locale = apply_filters( 'plugin_locale', mstaxsync_get_locale(), $domain );
		$mofile = $domain . '-' . $locale . '.mo';

		// load from the languages directory first
		load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );

		// load from plugin lang folder
		load_textdomain( $domain, mstaxsync_get_path( 'lang/' . $mofile ) );

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
	* mstaxsync_activate
	*
	* Actions perform on activation of plugin
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function mstaxsync_activate() {

		// taxonomy terms core
		mstaxsync_include( 'includes/classes/class-mstaxsync-tt-core.php' );

	}

	/**
	* mstaxsync_deactivate
	*
	* Actions perform on deactivation of plugin
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function mstaxsync_deactivate() {}

	/**
	* check_multisite
	*
	* This function will check if multisite support is enabled
	*
	* @since		1.0.0
	* @param		N/A
	* @return		(boolean)
	*/
	function check_multisite() {

		// vars
		$basename = $this->settings[ 'basename' ];

		if ( ! is_multisite() ) {

			if ( is_plugin_active( $basename ) ) {

				deactivate_plugins( $basename );
				add_action( 'admin_notices', array( $this, 'admin_multisite_notices_error' ) );

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
				add_action( 'admin_notices', array( $this, 'admin_required_plugins_notices_error' ) );

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
	* admin_multisite_notices_error
	*
	* This function will add admin error notice
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function admin_multisite_notices_error() {

		// vars
		$msg = sprintf( __( "<strong>%s</strong> plugin can't be activated.<br />Multisite support must be enabled.", 'mstaxsync' ), $this->settings[ 'name' ] );

		$this->admin_notices_error( $msg );

	}

	/**
	* admin_required_plugins_notices_error
	*
	* This function will add admin error notice
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function admin_required_plugins_notices_error() {

		// vars
		$required	= $this->required_plugins;
		$msg		= sprintf( __( "<strong>%s</strong> plugin can't be activated.<br />The following plugins should be installed and activated first:<br />", 'mstaxsync' ), $this->settings[ 'name' ] );

		foreach ( $required as $key => $plugin ) {

			$path = ( ! is_numeric( $key ) ) ? "{$key}/{$plugin}.php" : "{$plugin}/{$plugin}.php";

			if ( file_exists( plugin_dir_path( __DIR__ ) . $path ) ) {
				$name = get_plugin_data( plugin_dir_path( __DIR__ ) . $path )[ 'Name' ];
			} else {
				$name = $plugin;
			}

			$msg .= "<br />&bull; {$name}";

		}

		$this->admin_notices_error( $msg );

	}

	/**
	* admin_notices_error
	*
	* This function will display admin error notice
	*
	* @since		1.0.0
	* @param		$msg (string)
	* @return		N/A
	*/
	function admin_notices_error( $msg ) {

		// vars
		$class = 'notice notice-error is-dismissible';

		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $msg );

	}

}

/**
* mstaxsync
*
* The main function responsible for returning the one true mstaxsync instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mstaxsync() {

	// globals
	global $mstaxsync;

	// initialize
	if( ! isset( $mstaxsync ) ) {

		$mstaxsync = new MSTaxSync();
		$mstaxsync->initialize();

	}

	// return
	return $mstaxsync;

}

// initialize
mstaxsync();

endif; // class_exists check