<?php
/**
 * MSTaxSync_Core
 *
 * @author		Nir Goldberg
 * @package		includes
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Core' ) ) :

class MSTaxSync_Core {

	/**
	* __construct
	*
	* A dummy constructor to ensure is only initialized once
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function __construct() {

		/* Do nothing here */

	}

	/**
	* initialize
	*
	* The real constructor to initialize MSTaxSync_Core
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function initialize() {

		// settings
		$this->settings = array(

			// taxonomies
			'taxonomies'	=> array(),

		);

		// actions
		add_action( 'init',	array( $this, 'init' ), 99 );
		add_action( 'init', array( $this, 'taxonomy_term_description_allow_html' ), 99 );

		// filters
		add_filter( 'terms_clauses', array( $this, 'taxonomy_terms_orderby' ), 10, 3 );

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

		// admin
		if ( is_admin() ) {

			// get main site ID
			$main_site_id = get_main_site_id();

			switch_to_blog( $main_site_id );

			$this->set_main_site_custom_taxonomies();

			restore_current_blog();

			// taxonomies custom columns
			$this->set_taxonomies_columns();

		}

		// action for 3rd party
		do_action( 'mstaxsync_core/init' );

	}

	/**
	* taxonomy_term_description_allow_html
	*
	* This function will allow HTML in taxonomy term description
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function taxonomy_term_description_allow_html() {

		foreach ( array( 'pre_term_description' ) as $filter ) {

			remove_filter( $filter, 'wp_filter_kses' );

			if ( ! current_user_can( 'unfiltered_html' ) ) {
				add_filter( $filter, 'wp_filter_post_kses' );
			}

		}

		foreach ( array( 'term_description' ) as $filter ) {
			remove_filter( $filter, 'wp_kses_data' );
		}

	}

	/**
	* taxonomy_terms_orderby
	*
	* This function will set orderby clause for terms queries contain 'term_order' orderby argument
	*
	* @since		1.0.0
	* @param		$pieces (array) Array of query SQL clauses
	* @param		$taxonomies (array) Array of taxonomy names
	* @param		$args (array) Array of term query arguments
	* @return		(array)
	*/
	function taxonomy_terms_orderby( $pieces, $taxonomies, $args ) {

		if ( ! is_admin() )
			return $pieces;

		if ( ! isset( $_GET[ 'orderby' ] ) || 'term_order' == $_GET[ 'orderby' ] ) {

			$pieces[ 'orderby' ] = "ORDER BY t.term_order";

		}

		// return
		return $pieces;

	}

	/**
	* set_main_site_custom_taxonomies
	*
	* This function will get main site custom taxonomies and store them in settings array
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function set_main_site_custom_taxonomies() {

		// vars
		$args = array(
			'public'	=> true,
			'_builtin'	=> false,
		);

		$taxonomies = get_taxonomies( $args, 'objects' );

		if ( $taxonomies ) {
			foreach ( $taxonomies as $tax ) {

				$this->settings[ 'taxonomies' ][] = $tax;

			}
		}

	}

	/**
	* set_taxonomies_columns
	*
	* This function will set taxonomies custom columns
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	function set_taxonomies_columns() {

		// vars
		$main_site		= is_main_site();
		$taxonomies_arr	= array();

		if ( $main_site ) {

			// main site
			$taxonomies_arr[]	= 'category';
			$taxonomies			= $this->settings[ 'taxonomies' ];

			if ( $taxonomies ) {
				foreach ( $taxonomies as $tax ) {

					$taxonomies_arr[] = $tax->name;

				}
			}

		}
		else {

			// local site
			$categories	= get_option( 'mstaxsync_sync_categories', array( 'category' ) );
			$taxonomies	= get_option( 'mstaxsync_synced_taxonomies' );

			if ( $categories && in_array( 'category', $categories ) ) {

				$taxonomies_arr[] = 'category';

			}

			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxonomy ) {

					$taxonomies_arr[] = $taxonomy;

				}
			}

		}

		if ( $taxonomies_arr ) {

			foreach ( $taxonomies_arr as $taxonomy ) {

				// add custom columns
				add_filter( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'manage_edit_columns' ) );

				// add custom columns content
				add_action( 'manage_' . $taxonomy . '_custom_column', array( $this, 'manage_custom_column' ), 10, 3 );

				// make custom columns sortable
				add_filter( 'manage_edit-' . $taxonomy . '_sortable_columns', array( $this, 'manage_edit_sortable_columns' ) );

			}

			// set orderby clause for sortable custom columns
			add_filter( 'terms_clauses', array( $this, 'manage_edit_sortable_columns_orderby' ), 10, 3 );

		}

	}

	/**
	* manage_edit_columns
	*
	* This function will add taxonomies custom columns
	*
	* @since		1.0.0
	* @param		$columns (array)
	* @return		(array)
	*/
	function manage_edit_columns( $columns ) {

		// vars
		$main_site = is_main_site();

		$custom_columns = array(
			'mstaxsync_synced'	=> $main_site ? __( 'Synced Sites', 'mstaxsync' ) : __( 'Main Site Term', 'mstaxsync' ),
		);

		// return
		return array_merge( $columns, $custom_columns );

	}

	/**
	* manage_custom_column
	*
	* This function will add taxonomies custom columns content
	*
	* @since		1.0.0
	* @param		$content (mix)
	* @param		$column_name (string)
	* @param		$term_id (int)
	* @return		(mix)
	*/
	function manage_custom_column( $content, $column_name, $term_id ) {

		// vars
		$main_site = is_main_site();

		switch ( $column_name ) {

			case 'mstaxsync_synced':
				$content = $main_site ? $this->get_synced_sites( $term_id ) : $this->get_main_site_term( $term_id );
				break;

		}

		// return
		return $content;

	}

	/**
	* get_synced_sites
	*
	* This function will return list of synced sites and term names for a main site term ID
	*
	* @since		1.0.0
	* @param		$term_id (int)
	* @return		(string)
	*/
	function get_synced_sites( $term_id ) {

		// vars
		$synced_taxonomy_terms	= get_term_meta( $term_id, 'synced_taxonomy_terms', true );
		$sites					= array();		// stores sites data in order to prevent duplicated calls for same data
		$output					= '';

		if ( $synced_taxonomy_terms ) {

			$output .= '<ul>';

			foreach ( $synced_taxonomy_terms as $site_id => $site_term_id ) {

				if ( ! array_key_exists( $site_id, $sites ) ) {

					$sites[ $site_id ] = array();

					$sites[ $site_id ][ 'site_details' ]	= get_blog_details( array( 'blog_id' => $site_id ) );
					$sites[ $site_id ][ 'site_admin_url' ]	= get_admin_url( $site_id );

				}

				switch_to_blog( $site_id );

				$term = get_term( $site_term_id );

				restore_current_blog();

				if ( $sites[ $site_id ][ 'site_details' ] && $sites[ $site_id ][ 'site_admin_url' ] && $term && ! is_wp_error( $term ) ) {
					$output .= '<li><a href="' . $sites[ $site_id ][ 'site_admin_url' ] . '">' . $sites[ $site_id ][ 'site_details' ]->blogname . '</a>: ' . $term->name . '</li>';
				}

			}

			$output .= '</ul>';

		}
		else {
			$output .= '<span aria-hidden="true">—</span><span class="screen-reader-text">No Synced Sites Terms</span>';
		}

		// return
		return $output;

	}

	/**
	* get_main_site_term
	*
	* This function will return main site term name for a local site term ID
	*
	* @since		1.0.0
	* @param		$term_id (int)
	* @return		(string)
	*/
	function get_main_site_term( $term_id ) {

		// vars
		$main_taxonomy_term		= get_term_meta( $term_id, 'main_taxonomy_term', true );
		$output					= '';

		if ( $main_taxonomy_term ) {

			// get main site ID
			$main_site_id = get_main_site_id();

			switch_to_blog( $main_site_id );

			$term = get_term( $main_taxonomy_term );

			restore_current_blog();

			if ( $term && ! is_wp_error( $term ) ) {
				$output .= $term->name;
			}

		}
		else {
			$output .= '<span aria-hidden="true">—</span><span class="screen-reader-text">No Synced Main Site Term</span>';
		}

		// return
		return $output;

	}

	/**
	* manage_edit_sortable_columns
	*
	* This function will make taxonomies custom columns sortable
	*
	* @since		1.0.0
	* @param		$columns (array)
	* @return		(array)
	*/
	function manage_edit_sortable_columns( $columns ) {

		$columns[ 'mstaxsync_synced' ] = 'mstaxsync_synced';

		// return
		return $columns;

	}

	/**
	* manage_edit_sortable_columns_orderby
	*
	* This function will set orderby clause for sortable taxonomies custom columns
	*
	* @since		1.0.0
	* @param		$pieces (array) Array of query SQL clauses
	* @param		$taxonomies (array) Array of taxonomy names
	* @param		$args (array) Array of term query arguments
	* @return		(array)
	*/
	function manage_edit_sortable_columns_orderby( $pieces, $taxonomies, $args ) {

		if ( ! is_admin() )
			return $pieces;

		if ( 'mstaxsync_synced' == $args[ 'orderby' ] ) {

			// globals
			global $wpdb;

			// vars
			$main_site			= is_main_site();
			$site_id			= get_current_blog_id();
			$termmeta_table		= $wpdb->get_blog_prefix( $site_id ) . 'termmeta';

			if ( $main_site ) {

				// main site
				$pieces[ 'join' ]		.= " LEFT JOIN " . $termmeta_table . " AS termmeta ON termmeta.meta_key = 'synced_taxonomy_terms' AND t.term_id = termmeta.term_id";
				$pieces[ 'orderby' ]	= "ORDER BY termmeta.meta_value";
				$pieces[ 'order' ]		= isset( $_GET[ 'order' ] ) ? $_GET[ 'order' ] : 'DESC';

			}
			else {

				// local site
				// vars
				$main_site_id			= get_main_site_id();
				$main_terms_table		= $wpdb->get_blog_prefix( $main_site_id ) . 'terms';

				$pieces[ 'join' ]		.= " LEFT JOIN " . $termmeta_table . " AS termmeta ON termmeta.meta_key = 'main_taxonomy_term' AND t.term_id = termmeta.term_id";
				$pieces[ 'join' ]		.= " LEFT JOIN " . $main_terms_table . " AS main_terms ON main_terms.term_id = termmeta.meta_value";
				$pieces[ 'orderby' ]	= "ORDER BY main_terms.name";
				$pieces[ 'order' ]		= isset( $_GET[ 'order' ] ) ? $_GET[ 'order' ] : 'DESC';

			}

		}

		// return
		return $pieces;

	}

	/**
	* get_main_site_custom_taxonomies
	*
	* This function will get main site custom taxonomies
	*
	* @since		1.0.0
	* @param		N/A
	* @return		(array)
	*/
	function get_main_site_custom_taxonomies() {

		// return
		return $this->settings[ 'taxonomies' ];

	}

	/**
	* get_custom_taxonomy_terms
	*
	* This function will get custom taxonomy terms
	*
	* @since		1.0.0
	* @param		$tax (object)
	* @param		$main (boolean)
	* @return		(mixed)
	*/
	function get_custom_taxonomy_terms( $tax, $main = false ) {

		// vars
		$local_site_wpml_support	= is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' );
		$locale						= get_locale();

		$terms_args = array(
			'taxonomy'		=> $tax->name,
			'hide_empty'	=> false,
		);

		if ( $main ) {

			// get main site ID
			$main_site_id = get_main_site_id();

			switch_to_blog( $main_site_id );

			$main_site_wpml_support = is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' );

			// check whether main site supports WPML while local site doesn't.
			// in such case - take into account only main terms associated with local site language
			if ( $main_site_wpml_support && ! $local_site_wpml_support ) {

				// globals
				global $wpdb;

				// get WPML tables
				$main_site_table_prefix	= $wpdb->get_blog_prefix( $main_site_id );
				$icl_locale_map_table	= $main_site_table_prefix . 'icl_locale_map';
				$icl_translations_table	= $main_site_table_prefix . 'icl_translations';

				// get main site language code according to local site locale
				$language_code = $wpdb->get_row(
					"SELECT code FROM $icl_locale_map_table
					 WHERE locale = '$locale'", ARRAY_N
				);

				if ( is_array( $language_code ) && ! empty( $language_code ) ) {

					// get term IDs NOT associated with language code
					$ex_term_ids = $wpdb->get_results(
						"SELECT element_id FROM $icl_translations_table
						 WHERE element_type = 'tax_$tax->name'
						 AND language_code != '$language_code[0]'", ARRAY_N
					);

				}

				if ( is_array( $ex_term_ids ) && ! empty( $ex_term_ids ) ) {

					$ex_term_ids_arr = array();

					foreach ( $ex_term_ids as $term_id ) {
						$ex_term_ids_arr[] = $term_id[0];
					}

					$terms_args[ 'exclude' ] = $ex_term_ids_arr;

				}

			}

		}

		$terms = get_terms( apply_filters( "mstaxsync_" . ( $main ? "main" : "local" ) . "_taxonomy_terms/{$tax->name}", $terms_args ) );

		if ( $main ) {

			restore_current_blog();

		}

		// return
		return $terms;

	}

}

/**
* mstaxsync_core
*
* The main function responsible for returning the one true instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mstaxsync_core() {

	// globals
	global $mstaxsync_core;

	// initialize
	if( ! isset( $mstaxsync_core ) ) {

		$mstaxsync_core = new MSTaxSync_Core();
		$mstaxsync_core->initialize();

	}

	// return
	return $mstaxsync_core;

}

// initialize
mstaxsync_core();

endif; // class_exists check