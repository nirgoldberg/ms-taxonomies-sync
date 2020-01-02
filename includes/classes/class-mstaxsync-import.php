<?php
/**
 * MSTaxSync_Import
 *
 * @author		Nir Goldberg
 * @package		includes/classes
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Import' ) ) :

class MSTaxSync_Import {

	/**
	* __construct
	*
	* A dummy constructor to ensure is only initialized once
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public function __construct() {

		/* Do nothing here */

	}

	/**
	* initialize
	*
	* The real constructor to initialize MSTaxSync_Import
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public function initialize() {

		// actions
		add_action( 'init', array( $this, 'init' ), 99 );

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
	public function init() {

		// exit if called too early
		if ( ! did_action( 'plugins_loaded' ) )
			return;

		// admin
		if ( is_admin() ) {

			// taxonomies custom columns
			$this->set_taxonomies_columns();

		}

		// action for 3rd party
		do_action( 'mstaxsync_import/init' );

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
	private function set_taxonomies_columns() {

		// vars
		$main_site		= is_main_site();
		$taxonomies_arr	= array();
		$import_posts	= get_option( 'mstaxsync_import_taxonomy_terms_posts', array( 'can' ) );

		if ( ! $main_site && $import_posts ) {

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

			if ( $taxonomies_arr ) {
				foreach ( $taxonomies_arr as $taxonomy ) {

					// add custom columns
					add_filter( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'manage_edit_columns' ) );

					// add custom columns content
					add_action( 'manage_' . $taxonomy . '_custom_column', array( $this, 'manage_custom_column' ), 10, 3 );

				}
			}

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
	public function manage_edit_columns( $columns ) {

		$custom_columns = array(
			'mstaxsync_import'	=> __( 'Posts Import', 'mstaxsync' ),
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
	public function manage_custom_column( $content, $column_name, $term_id ) {

		switch ( $column_name ) {

			case 'mstaxsync_import':
				$content = $this->get_main_site_term_count( $term_id );
				break;

		}

		// return
		return $content;

	}

	/**
	* get_main_site_term_count
	*
	* This function will return main site term count for a local site term ID
	*
	* @since		1.0.0
	* @param		$term_id (int)
	* @return		(string)
	*/
	private function get_main_site_term_count( $term_id ) {

		// vars
		$main_taxonomy_term		= get_term_meta( $term_id, 'main_taxonomy_term', true );
		$output					= '';
		$import_nonce			= wp_create_nonce( 'import_taxonomy_term' );

		if ( $main_taxonomy_term ) {

			// switch to main site
			switch_to_blog( mstaxsync_get_main_site_id() );

			$term = get_term( $main_taxonomy_term );

			restore_current_blog();

			if ( $term && ! is_wp_error( $term ) ) {

				if ( $term->count ) {

					$output .= '<span class="mstaxsync-import" data-id="' . $term->term_id . '" data-term-count="' . $term->count . '" data-nonce="' . $import_nonce . '">';
					$output .= $term->count > 1 ? sprintf( __( 'Import %s posts', 'mstaxsync' ), $term->count ) : __( 'Import one post', 'mstaxsync' );
					$output .= '</span>';

				}
				else {
					$output .= __( 'No posts to import', 'mstaxsync' );
				}

				$output .= '<span class="ajax-loading dashicons dashicons-update"></span>';

			}

		}
		else {
			$output .= '<span aria-hidden="true">—</span><span class="screen-reader-text">No Synced Main Site Term</span>';
		}

		// return
		return $output;

	}

}

/**
* mstaxsync_import
*
* The main function responsible for returning the one true instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mstaxsync_import() {

	// globals
	global $mstaxsync_import;

	// initialize
	if( ! isset( $mstaxsync_import ) ) {

		$mstaxsync_import = new MSTaxSync_Import();
		$mstaxsync_import->initialize();

	}

	// return
	return $mstaxsync_import;

}

// initialize
mstaxsync_import();

endif; // class_exists check