<?php
/**
 * Admin settings page filters, actions, variables and includes
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSCatSync_Admin_Settings_Page' ) ) :

class MSCatSync_Admin_Settings_Page {

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
	 * This function will initialize the admin settings page
	 *
	 * @since		1.0.0
	 * @param		N/A
	 * @return		N/A
	 */
	function __construct() {

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
	function __destruct() {

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
	function initialize() {

		// settings
		$this->$settings = array(

			// slugs
			'parent'				=> '',
			'slug'					=> '',

			// titles
			'page_title'			=> '',
			'menu_title'			=> '',

			// tabs
			/**
			 * tabs structure:
			 *
			 * '[tab slug]'	=> array(
			 * 		'title'		=> [tab title],
			 * 		'sections'	=> array(
			 * 			'[section slug]'	=> array(
			 * 				'title'			=> [section title],
			 * 				'description'	=> [section description]'
			 * 			),
			 *			...
			 * 		)
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
			 * 		'title'			=> [section title],
			 * 		'description'	=> [section description]'
			 * ),
			 * ...
			 */
			'sections'				=> array(),

			// fields
			/**
			 * fields structure:
			 *
			 * array(
			 *		'uid'			=> [field slug],
			 *		'label'			=> [field label],
			 *		'label_for'		=> [field label_for],
			 *		'tab'			=> [tab slug],
			 *		'section'		=> [section slug],
			 *		'type'			=> [field type: text/password/number/textarea/select/multiselect/radio/checkbox],
			 *		'placeholder'	=> [field placeholder],
			 *		'options'		=> [array of field options: slugs and labels]
			 *		'default'		=> [field default value]
			 *		'helper'		=> [field helper text],
			 *		'supplimental'	=> [field description text]
			 * ),
			 * ...
			 */
			'fields'				=> array()

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

		if ( empty( $this->settings[ 'fields' ] ) )
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
			$this->settings[ 'parent' ],
			$this->settings[ 'page_title' ],
			$this->settings[ 'menu_title' ],
			$capability,
			$this->settings[ 'slug' ],
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
	function html() {

		// vars
		$view = array(

			'parent'		=> $this->settings[ 'parent' ],
			'slug'			=> $this->settings[ 'slug' ],
			'page_title'	=> $this->settings[ 'page_title' ],
			'tabs'			=> $this->settings[ 'tabs' ],
			'active_tab'	=> $this->settings[ 'active_tab' ],
			'sections'		=> $this->settings[ 'sections' ]

		);

		// set active tab
		if ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] != '' && array_key_exists( $_GET[ 'tab' ], $view[ 'tabs' ] ) ) {
			$view[ 'active_tab' ] = $_GET[ 'tab' ];
		}

		// load view
		mscatsync_get_view( 'settings', $view );

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
	function setup_sections() {

		// vars
		$slug		= $this->settings[ 'slug' ];
		$tabs		= $this->settings[ 'tabs' ];
		$sections	= $this->settings[ 'sections' ];

		// setup sections
		if ( ! empty( $tabs ) ) {
			// tabs
			foreach ( $tabs as $tab_slug => $tab ) {
				foreach ( $tab[ 'sections' ] as $section_slug => $section_title ) {

					// vars
					$options_group_id	= $slug . '-' . $tab_slug;
					$section_id			= $tab_slug . '-' . $section_slug;

					// add settings section
					$this->setup_section( $section_id, $options_group_id );

				}
			}
		} elseif ( ! empty( $sections ) ) {
			// no tabs, only sections
			foreach ( $sections as $section_slug => $section ) {

				// vars
				$options_group_id	= $slug;
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
	function setup_section( $section_id, $options_group_id ) {

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
	function setup_fields() {

		// vars
		$slug		= $this->settings[ 'slug' ];
		$tabs		= $this->settings[ 'tabs' ];
		$sections	= $this->settings[ 'sections' ];
		$fields		= $this->settings[ 'fields' ];

		// setup fields
		foreach ( $fields as $field ) {

			// vars
			if ( ! empty( $tabs ) ) {

				// tabs
				$options_group_id	= $slug . '-' . $field[ 'tab' ];
				$section_id			= $field[ 'tab' ] . '-' . $field[ 'section' ];

			} elseif ( ! empty( $sections ) ) {

				// no tabs, only sections
				$options_group_id	= $slug;
				$section_id			= $field[ 'section' ];

			}

			// add settings field
			$this->setup_field( $field[ 'uid' ], $field[ 'label' ], $options_group_id, $section_id, $field );

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
	function setup_field( $field_id, $field_label, $options_group_id, $section_id, $field_args ) {

		// add settings field
		add_settings_field(
			$field_id,
			$field_label,
			'mscatsync_admin_display_form_element',
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