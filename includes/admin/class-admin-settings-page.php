<?php
/**
 * Admin settings page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Admin_Settings_Page' ) ) :

class MSTaxSync_Admin_Settings_Page {

	/**
	 * Instances array
	 *
	 * @var (array)
	 */
	protected static $_instances = array();

	/**
	 * Settings array
	 *
	 * @var (array)
	 */
	protected $settings = array();

	/**
	 * Main site indicator
	 *
	 * @var (boolean)
	 */
	protected $is_main_site = false;

	/**
	 * __construct
	 *
	 * This function will initialize the admin settings page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	public function __construct() {

		// main site indicator
		$this->is_main_site = is_main_site();

		// initialize
		$this->initialize();

		// actions
		$this->add_action( 'admin_menu',	array( $this, 'admin_menu' ),		10, 0 );
		$this->add_action( 'admin_init',	array( $this, 'setup_sections' ),	10, 0 );
		$this->add_action( 'admin_init',	array( $this, 'setup_fields' ) ,	10, 0 );

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
	public function __destruct() {

		// unset stored instance
		unset( self::$_instances[ array_search( $this, self::$_instances, true ) ] );

	}

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
		$this->$settings = array(

			// slugs
			'parent_slug'			=> '',
			'menu_slug'				=> '',

			// titles
			'page_title'			=> '',
			'menu_title'			=> '',

			// tabs
			/**
			 * tabs structure:
			 *
			 * '[tab slug]'	=> array(
			 *		'target'	=> [main/local],
			 * 		'title'		=> [tab title],
			 * 		'sections'	=> array(
			 * 			'[section slug]'	=> array(
			 *				'target'		=> [main/local],
			 * 				'title'			=> [section title],
			 * 				'description'	=> [section description],
			 * 			),
			 *			...
			 * 		),
			 * ),
			 * ...
			 */
			'tabs'					=> array(),
			'active_tab'			=> '',

			// sections
			/**
			 * sections structure:
			 *
			 * '[section slug]'	=> array(
			 *		'target'		=> [main/local],
			 * 		'title'			=> [section title],
			 * 		'description'	=> [section description],
			 * ),
			 * ...
			 */
			'sections'				=> array(),

			// fields
			/**
			 * fields structure:
			 *
			 * array(
			 *		'target'		=> [main/local],
			 *		'uid'			=> [field slug],
			 *		'label'			=> [field label],
			 *		'label_for'		=> [field label_for],
			 *		'tab'			=> [tab slug],
			 *		'section'		=> [section slug],
			 *		'type'			=> [field type: text/password/number/textarea/select/multiselect/radio/checkbox],
			 *		'placeholder'	=> [field placeholder],
			 *		'options'		=> [array of field options: slugs and labels],
			 *		'default'		=> [array of field option slug],
			 *		'supplimental'	=> [field description text],
			 *		'helper'		=> [field helper text],
			 * ),
			 * ...
			 */
			'fields'				=> array(),

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
	protected function add_action( $tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1 ) {

		if ( empty( $this->settings[ 'fields' ] ) )
			return;

		// add action
		add_action( $tag, $function_to_add, $priority, $accepted_args );

	}

	/**
	 * admin_menu
	 *
	 * This function will add Multisite Taxonomies Sync submenu item to the WP admin
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	public function admin_menu() {

		// exit if no show_admin
		if ( ! mstaxsync_get_setting( 'show_admin' ) )
			return;

		// vars
		$capability = mstaxsync_get_setting( 'capability' );

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
	 * This function will display the admin settings page content
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	public function html() {

		// vars
		$view = array(

			'parent_slug'	=> $this->settings[ 'parent_slug' ],
			'menu_slug'		=> $this->settings[ 'menu_slug' ],
			'page_title'	=> $this->settings[ 'page_title' ],
			'tabs'			=> $this->settings[ 'tabs' ],
			'active_tab'	=> $this->settings[ 'active_tab' ],
			'sections'		=> $this->settings[ 'sections' ],
			'is_main_site'	=> $this->is_main_site,

		);

		// set active tab
		if ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] != '' && array_key_exists( $_GET[ 'tab' ], $view[ 'tabs' ] ) ) {
			$view[ 'active_tab' ] = $_GET[ 'tab' ];
		}

		// load view
		mstaxsync_get_view( 'settings', $view );

	}

	/**
	 * setup_sections
	 *
	 * This function will setup admin settings page sections
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	public function setup_sections() {

		// vars
		$menu_slug	= $this->settings[ 'menu_slug' ];
		$tabs		= $this->settings[ 'tabs' ];
		$sections	= $this->settings[ 'sections' ];

		// setup sections
		if ( ! empty( $tabs ) ) {
			// tabs
			foreach ( $tabs as $tab_slug => $tab ) {

				foreach ( $tab[ 'sections' ] as $section_slug => $section ) {

					// vars
					$options_group_id	= $menu_slug . '-' . $tab_slug;
					$section_id			= $tab_slug . '-' . $section_slug;

					// add settings section
					$this->setup_section( $section_id, $options_group_id );

				}

			}
		}
		elseif ( ! empty( $sections ) ) {
			// no tabs, only sections
			foreach ( $sections as $section_slug => $section ) {

				// vars
				$options_group_id	= $menu_slug;
				$section_id			= $section_slug;

				// add settings section
				$this->setup_section( $section_id, $options_group_id );

			}
		}

	}

	/**
	 * setup_section
	 *
	 * This function will setup admin settings page section
	 *
	 * @since		1.0.0
	 * @param		$section_id (string)
	 * @param		$options_group_id (string)
	 * @return		N/A
	 */
	protected function setup_section( $section_id, $options_group_id ) {

		// add settings section
		add_settings_section(
			$section_id,
			false,
			false,
			$options_group_id
		);

	}

	/**
	 * setup_fields
	 *
	 * This function will setup admin settings page fields
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	public function setup_fields() {

		// vars
		$menu_slug	= $this->settings[ 'menu_slug' ];
		$tabs		= $this->settings[ 'tabs' ];
		$sections	= $this->settings[ 'sections' ];
		$fields		= $this->settings[ 'fields' ];

		// setup fields
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {

				// verify field target
				if ( isset( $field[ 'target' ] ) && ( ( $this->is_main_site && 'local' == $field[ 'target' ] ) || ( ! $this->is_main_site && 'main' == $field[ 'target' ] ) ) )
					continue;

				// vars
				if ( ! empty( $tabs ) ) {

					// tabs
					$options_group_id	= $menu_slug . '-' . $field[ 'tab' ];
					$section_id			= $field[ 'tab' ] . '-' . $field[ 'section' ];

				} elseif ( ! empty( $sections ) ) {

					// no tabs, only sections
					$options_group_id	= $menu_slug;
					$section_id			= $field[ 'section' ];

				}

				// add settings field
				$this->setup_field( $field[ 'uid' ], $field[ 'label' ], $options_group_id, $section_id, $field );

			}
		}

	}

	/**
	 * setup_field
	 *
	 * This function will setup admin settings page field
	 *
	 * @since		1.0.0
	 * @param		$field_id (string)
	 * @param		$field_label (string)
	 * @param		$options_group_id (string)
	 * @param		$section_id (string)
	 * @param		$field_args (array)
	 * @return		N/A
	 */
	protected function setup_field( $field_id, $field_label, $options_group_id, $section_id, $field_args ) {

		// add settings field
		add_settings_field(
			$field_id,
			$field_label,
			'mstaxsync_admin_display_form_element',
			$options_group_id,
			$section_id,
			$field_args
		);

		// register setting
		register_setting( $options_group_id, $field_id );

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
	protected static function get_instances( $include_subclasses = false ) {

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