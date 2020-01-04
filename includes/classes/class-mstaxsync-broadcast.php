<?php
/**
 * MSTaxSync_Broadcast
 *
 * @author		Nir Goldberg
 * @package		includes/classes
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Broadcast' ) ) :

class MSTaxSync_Broadcast {

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
	* The real constructor to initialize MSTaxSync_Broadcast
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public function initialize() {

		// actions
		add_action( 'init',							array( $this, 'init' ), 99 );
		add_action( 'add_meta_boxes',				array( $this, 'add_meta_boxes' ), 99 );
		add_action( 'save_post_post',				array( $this, 'save_post' ), 10, 3 );
		add_action( 'manage_posts_custom_column',	array( $this, 'broadcast_manage_custom_column' ), 10, 2);
		add_action( 'quick_edit_custom_box',		array( $this, 'broadcast_quick_edit_custom_box' ), 10, 2 );
		add_action( 'bulk_edit_custom_box',			array( $this, 'broadcast_quick_edit_custom_box' ), 10, 2 );
		add_action( 'save_post_post',				array( $this, 'broadcast_quick_edit_save_post' ), 10, 3 );

		// filters
		add_filter( 'manage_post_posts_columns',	array( $this, 'broadcast_manage_posts_columns' ) );

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

		// action for 3rd party
		do_action( 'mstaxsync_broadcast/init' );

	}

	/**
	* add_meta_boxes
	*
	* This function will initialize plugin meta boxes
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public function add_meta_boxes() {

		if ( ! is_main_site() )
			return;

		// vars
		$meta_boxes = array(
			array(
				'id'		=> 'mstaxsync_single_post_broadcast',
				'title'		=> esc_html__( 'Broadcast', 'mstaxsync' ),
				'callback'	=> array( $this, 'single_post_broadcast_meta_box' ),
				'screen'	=> 'post',
				'context'	=> 'side',
			),
		);

		// add meta boxes
		foreach ( $meta_boxes as $mb ) {
			add_meta_box(
				$mb[ 'id' ],
				$mb[ 'title' ],
				$mb[ 'callback' ],
				$mb[ 'screen' ],
				$mb[ 'context' ]
			);
		}

	}

	/**
	* single_post_broadcast_meta_box
	*
	* This function will initialize a single post broadcast meta box
	*
	* @since		1.0.0
	* @param		$post (object)
	* @return		N/A
	*/
	public function single_post_broadcast_meta_box( $post ) {

		// get post category and taxonomy terms
		$post_terms = $this->get_post_terms( $post->ID );

		if ( $post_terms ) {

			// get synced sites
			$synced_sites = $this->get_synced_sites( $post->ID, $post_terms );

			if ( $synced_sites ) {

				wp_nonce_field( basename( __FILE__ ), 'mstaxsync_single_post_broadcast' );

				$this->display_synced_sites_inputs( $synced_sites );

			}
			else {

				// no synced sites for post terms
				echo '<p>' . __( 'Post is not available for broadcasting.', 'mstaxsync' ) . '</p>' .
					 '<p>' . __( 'Make sure post\'s category and taxonomy terms are synced to local sites.', 'mstaxsync' ) . '</p>';

			}

		}
		else {

			// no post terms
			echo '<p>' . __( 'Post is not available for broadcasting.', 'mstaxsync' ) . '</p>' .
				 '<p>' . __( 'Make sure post is assigned to category and/or taxonomy terms.<br />If this is a new post, you have to publish it before broadcast.', 'mstaxsync' ) . '</p>';

		}

	}

	/**
	* get_post_terms
	*
	* This function will return array of post category and taxonomy term IDs
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @return		(mix) Array of post term IDs, or false on failure
	*/
	private function get_post_terms( $post_id ) {

		if ( ! $post_id )
			return false;

		// vars
		$post_terms = array();

		// get post category terms
		$post_category_terms = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );

		if ( ! is_wp_error( $post_category_terms ) && $post_category_terms ) {

			$post_terms = array_merge( $post_terms, $post_category_terms );

		}

		// get main site custom taxonomies
		$taxonomies = mstaxsync_get_main_site_custom_taxonomies_names();

		if ( $taxonomies ) {
			foreach ( $taxonomies as $name => $label ) {

				// get post taxonomy terms
				$post_tt = wp_get_post_terms( $post_id, $name, array( 'fields' => 'ids' ) );

				if ( ! is_wp_error( $post_tt ) && ! empty( $post_tt ) ) {

					$post_terms = array_merge( $post_terms, $post_tt );

				}

			}
		}

		// return
		return $post_terms;

	}

	/**
	* get_synced_sites
	*
	* This function will return array of synced sites according to a single post category and taxonomy terms
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @param		$post_terms (array)
	* @return		(array)
	*/
	private function get_synced_sites( $post_id, $post_terms ) {

		// vars
		$synced_sites = array();

		if ( is_array( $post_terms ) && ! empty( $post_terms ) ) {

			// get synced posts
			$synced_posts = get_post_meta( $post_id, 'synced_posts', true );

			foreach ( $post_terms as $term_id ) {

				$synced_taxonomy_terms = get_term_meta( $term_id, 'synced_taxonomy_terms', true );

				if ( $synced_taxonomy_terms ) {
					foreach ( $synced_taxonomy_terms as $site_id => $site_term_id ) {

						if ( ! array_key_exists( $site_id, $synced_sites ) ) {

							$synced_sites[ $site_id ] = array();

							$site_details = get_blog_details( array( 'blog_id' => $site_id ) );

							$synced_sites[ $site_id ][ 'site_name' ] = $site_details ? $site_details->blogname : '';

							// check if post is already synced
							if ( is_array( $synced_posts ) && array_key_exists( $site_id, $synced_posts ) ) {
								$synced_sites[ $site_id ][ 'post_id' ] = $synced_posts[ $site_id ];
							}

						}

					}
				}

			}

			if ( ! empty( $synced_sites ) ) {

				// sort $synced_sites array by site name
				uasort( $synced_sites, function( $a, $b ) {
					return strtolower( $a[ 'site_name' ] ) <=> strtolower( $b[ 'site_name' ] );
				});

			}

		}

		// return
		return $synced_sites;

	}

	/**
	* display_synced_sites_inputs
	*
	* This function will display checkbox input for each synced site
	*
	* @since		1.0.0
	* @param		$synced_sites (array)
	* @return		N/A
	*/
	private function display_synced_sites_inputs( $synced_sites ) {

		if ( ! is_array( $synced_sites ) || empty( $synced_sites ) )
			return;

		?>

		<p><?php _e( 'Broadcast to:', 'mstaxsync' ); ?></p>
		<span class="multiselect"><span class="check-all"><?php _e( 'Select All', 'mstaxsync' ); ?></span> / <span class="uncheck-all"><?php _e( 'Remove All', 'mstaxsync' ); ?></span></span>

		<ul class="synced-sites">

			<?php foreach ( $synced_sites as $site_id => $site ) {

				if ( is_array( $site ) ) {

					$synced_post_id	= isset( $site[ 'post_id' ] ) ? $site[ 'post_id' ] : '';

					if ( $synced_post_id ) {

						$input_name		= 'mstaxsync_synced_sites[]';
						$input_classes	= 'synced-post';
						$input_data		= 'data-synced-post="' . $synced_post_id . '" checked disabled';

					}
					else {

						$input_name		= 'mstaxsync_dest_sites[]';
						$input_classes	= 'unsynced-post';
						$input_data		= '';

					}

					?>

					<li id="site-<?php echo $site_id; ?>">
						<label>
							<input value="<?php echo $site_id; ?>" type="checkbox" name="<?php echo $input_name; ?>" id="site-cb-<?php echo $site_id; ?>" class="<?php echo $input_classes; ?>" <?php echo $input_data; ?> />
							<?php echo $site[ 'site_name' ] . ( $synced_post_id ? ' <span>(Synced: ' . $synced_post_id . ')</span>' : '' ); ?>
						</label>
					</li>

				<?php }

			} ?>

		</ul>

		<?php

	}

	/**
	* save_post
	*
	* This function will initialize a single post broadcast as part of save_post_post action
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @param		$post (object)
	* @param		$update (boolean)
	* @return		N/A
	*/
	public function save_post( $post_id, $post, $update ) {

		if ( ! is_main_site() || ! $update || wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || in_array( $post->post_status, array( 'trash', 'auto-draft' ) ) )
			return;

		// verify nonce
		if ( ! isset( $_POST[ 'mstaxsync_single_post_broadcast' ] ) || ! wp_verify_nonce( $_POST[ 'mstaxsync_single_post_broadcast' ], basename( __FILE__ ) ) )
			return;

		if ( isset( $_POST[ 'mstaxsync_dest_sites' ] ) && $_POST[ 'mstaxsync_dest_sites' ] ) {

			// broadcast single post
			$this->broadcast_single_post( $post, $_POST[ 'mstaxsync_dest_sites' ] );

		}

	}

	/**
	* broadcast_single_post
	*
	* This function will broadcast a single post to one or more sites
	*
	* @since		1.0.0
	* @param		$post (object)
	* @param		$sites (array)
	* @return		(array) site IDs and created post IDs
	*/
	public function broadcast_single_post( $post, $sites ) {

		if ( ! $post || 'post' != get_post_type( $post ) || ! is_array( $sites ) || empty( $sites ) )
			return;

		// vars
		$post_ids = array();

		$post_data = $this->get_post_data( $post );

		if ( ! isset( $post_data[ 'taxonomies' ] ) || empty( $post_data[ 'taxonomies' ] ) )
			return;

		$synced_terms = $this->get_post_synced_terms( $post->ID, $post_data[ 'taxonomies' ] );

		if ( empty( $synced_terms ) )
			return;

		// remove WPML actions in order to prevent attachment duplication
		MSTaxSync_Attachment::remove_wpml_save_attachment_actions();

		foreach ( $sites as $site_id ) {

			// broadcast post to a single local site
			$post_id = $this->broadcast( $post_data, $synced_terms, $site_id );

			if ( $post_id ) {
				$post_ids[] = array(
					'site_id'	=> $site_id,
					'post_id'	=> $post_id,
				);
			}

		}

		// return
		return $post_ids;

	}

	/**
	* get_post_data
	*
	* This function will return post data array
	*
	* @since		1.0.0
	* @param		$post (object)
	* @return		(array)
	*/
	private function get_post_data( $post ) {

		// vars
		$post_data = array();

		if ( ! $post )
			return $post_data;

		// get main post ID
		$post_data[ 'ID' ] = $post->ID;

		// get post attributes
		$post_data[ 'args' ] = array(
			'post_content'				=> $post->post_content,
			'post_content_filtered'		=> $post->post_content_filtered,
			'post_title'				=> $post->post_title,
			'post_excerpt'				=> $post->post_excerpt,
			'post_status'				=> $post->post_status,
			'post_type'					=> $post->post_type,
			'post_password'				=> $post->post_password,
			'post_name'					=> $post->post_name,
			'menu_order'				=> $post->menu_order,
			'post_mime_type'			=> $post->post_mime_type,
		);

		// get post format
		$post_data[ 'post_format' ] = get_post_format( $post->ID );

		// get post meta
		$post_data[ 'post_meta' ] = get_post_meta( $post->ID );

		// get post taxonomy terms
		$post_data[ 'taxonomies' ] = get_post_taxonomies( $post->ID );

		// get post thumbnail
		$thumbnail_id = get_post_thumbnail_id( $post->ID );

		if ( $thumbnail_id ) {

			$post_data[ 'thumbnail' ] = array();

			$image_url	= wp_get_attachment_image_src( $thumbnail_id, 'full' );
			$image_url	= $image_url ? $image_url[0] : '';

			$thumbnail	= get_post( $thumbnail_id );

			if ( $image_url && $thumbnail ) {

				$post_data[ 'thumbnail' ][ 'url' ] = $image_url;

				$post_data[ 'thumbnail' ][ 'attachment_data' ] = array(
					'title'			=> $thumbnail->post_title,
					'caption'		=> $thumbnail->post_excerpt,
					'description'	=> $thumbnail->post_content,
					'alt_text'		=> get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ),
				);

			}

		}

		// return
		return $post_data;

	}

	/**
	* get_post_synced_terms
	*
	* This function will return array of synced sites and terms associated with a single main site post taxonomies
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @param		$taxonomies (array)
	* @return		(array)
	*/
	private function get_post_synced_terms( $post_id, $taxonomies ) {

		// vars
		$synced_terms = array();

		if ( ! $post_id || ! is_array( $taxonomies ) || empty( $taxonomies ) )
			return $synced_terms;

		foreach ( $taxonomies as $taxonomy ) {

			$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

				// take into account only terms which are synced to local sites
				foreach ( $terms as $term_id ) {

					$synced_taxonomy_terms = get_term_meta( $term_id, 'synced_taxonomy_terms', true );

					if ( $synced_taxonomy_terms ) {
						$synced_terms[ $term_id ] = array(
							'taxonomy'				=> $taxonomy,
							'synced_taxonomy_terms'	=> $synced_taxonomy_terms,
						);
					}

				}

			}

		}

		// return
		return $synced_terms;

	}

	/**
	* broadcast
	*
	* This function will broadcast a single post to a specified local site ID
	*
	* @since		1.0.0
	* @param		$post_data (array)
	* @param		$synced_terms (array)
	* @param		$site_id (int)
	* @return		(mix) New created post ID, or 0 in case of not found synced terms, or false in case of error
	*/
	private function broadcast( $post_data, $synced_terms, $site_id ) {

		if ( ! is_array( $post_data ) || empty( $post_data ) || ! is_array( $synced_terms ) || empty( $synced_terms ) || ! $site_id )
			return false;

		// check if already synced
		// get synced posts
		$synced_posts = get_post_meta( $post_data[ 'ID' ], 'synced_posts', true );

		if ( is_array( $synced_posts ) && array_key_exists( $site_id, $synced_posts ) ) {

			// post is already synced to this site ID
			return false;

		}

		// vars
		$post_id = 0;

		switch_to_blog( $site_id );

		// find site synced terms
		foreach ( $synced_terms as $main_term_id => $data ) {
			foreach ( $data[ 'synced_taxonomy_terms' ] as $local_site_id => $local_term_id ) {
				if ( $site_id == $local_site_id ) {

					// create post
					if ( ! $post_id ) {

						$post_id = $this->insert_post( $post_data );

						if ( ! $post_id )
							return false;

					}

					// assign term
					wp_set_post_terms( $post_id, $local_term_id, $data[ 'taxonomy' ], true );

					break;

				}
			}
		}

		restore_current_blog();

		// return
		return $post_id;

	}

	/**
	* insert_post
	*
	* This function will create a new post in current blog context
	*
	* @since		1.0.0
	* @param		$post_data (array)
	* @return		(mix) New created post ID, or false in case of error
	*/
	private function insert_post( $post_data ) {

		if ( ! is_array( $post_data ) || empty( $post_data ) )
			return false;

		$post_id = wp_insert_post( $post_data[ 'args' ] );

		if ( ! $post_id )
			return false;

		// assign post format
		if ( $post_data[ 'post_format' ] ) {
			set_post_format( $post_id, $post_data[ 'post_format' ] );
		}

		// assign post meta
		if ( is_array( $post_data[ 'post_meta' ] ) ) {
			foreach ( $post_data[ 'post_meta' ] as $field => $value ) {

				if ( ! is_array( $value ) ) {
					$value = array( $value );
				}

				foreach ( $value as $k => $v ) {
					add_post_meta( $post_id, $field, $v );
				}

			}
		}

		// assign post thumbnail
		if ( isset( $post_data[ 'thumbnail' ] ) && isset( $post_data[ 'thumbnail' ][ 'url' ] ) && is_array( $post_data[ 'thumbnail' ][ 'attachment_data' ] ) ) {
			mstaxsync_set_post_attachment( $post_data[ 'thumbnail' ][ 'url' ], $post_id, $post_data[ 'thumbnail' ][ 'attachment_data' ] );
		}

		// update post correlation with main site
		$result = array();
		mstaxsync_update_post_correlation( $post_data[ 'ID' ], $post_id, $result );

		// return
		return $post_id;

	}

	/**
	* broadcast_manage_posts_columns
	*
	* This function will add broadcast custom columns
	*
	* @since		1.0.0
	* @param		$post_columns (array)
	* @return		(array)
	*/
	public function broadcast_manage_posts_columns( $post_columns ) {

		// vars
		$main_site		= is_main_site();
		$display_column	= $main_site ? get_option( 'mstaxsync_display_synced_posts_column', array( 'can' ) ) : get_option( 'mstaxsync_display_main_post_column', array( 'can' ) );

		if ( ! $display_column )
			return $post_columns;

		$custom_columns = array(
			'mstaxsync_synced'	=> $main_site ? __( 'Synced Sites', 'mstaxsync' ) : __( 'Main Site Post', 'mstaxsync' ),
		);

		// return
		return array_merge( $post_columns, $custom_columns );

	}

	/**
	* broadcast_manage_custom_column
	*
	* This function will add broadcast custom columns content
	*
	* @since		1.0.0
	* @param		$column_name (string)
	* @param		$post_id (int)
	* @return		N/A
	*/
	public function broadcast_manage_custom_column( $column_name, $post_id ) {

		// vars
		$main_site = is_main_site();

		switch ( $column_name ) {

			case 'mstaxsync_synced':
				$content = $main_site ? $this->get_synced_sites_posts( $post_id ) : $this->get_main_site_post( $post_id );
				break;

		}

		echo $content;

	}

	/**
	* get_synced_sites_posts
	*
	* This function will return list of synced sites and post IDs for a main site post ID
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @return		(string)
	*/
	private function get_synced_sites_posts( $post_id ) {

		// vars
		$synced_posts	= get_post_meta( $post_id, 'synced_posts', true );
		$sites			= array();
		$output			= '';

		if ( $synced_posts ) {

			foreach ( $synced_posts as $site_id => $site_post_id ) {

				if ( ! array_key_exists( $site_id, $sites ) ) {

					$sites[ $site_id ] = array();

					$site_details = get_blog_details( array( 'blog_id' => $site_id ) );

					$sites[ $site_id ][ 'site_name' ]		= $site_details ? $site_details->blogname : '';
					$sites[ $site_id ][ 'site_admin_url' ]	= get_admin_url( $site_id );
					$sites[ $site_id ][ 'site_post_id' ]	= $site_post_id;

				}

			}

			if ( ! empty( $sites ) ) {

				// sort $sites array by site name
				uasort( $sites, function( $a, $b ) {
					return strtolower( $a[ 'site_name' ] ) <=> strtolower( $b[ 'site_name' ] );
				});

				$output .= '<ul>';

				foreach ( $sites as $site_id => $site ) {
					if ( $site[ 'site_name' ] && $site[ 'site_admin_url' ] && $site[ 'site_post_id' ] ) {
						$output .= '<li id="site-' . $site_id . '"><a href="' . $site[ 'site_admin_url' ] . '">' . $site[ 'site_name' ] . '</a>: ' . $site[ 'site_post_id' ] . '</li>';
					}
				}

				$output .= '</ul>';

			}

		}
		else {
			$output .= '<span aria-hidden="true">—</span><span class="screen-reader-text">No Synced Sites Posts</span>';
		}

		// return
		return $output;

	}

	/**
	* get_main_site_post
	*
	* This function will return main site post ID for a local site post ID
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @return		(string)
	*/
	private function get_main_site_post( $post_id ) {

		// vars
		$main_post	= get_post_meta( $post_id, 'main_post', true );
		$output		= '';

		if ( $main_post ) {
			$output .= $main_post;
		}
		else {
			$output .= '<span aria-hidden="true">—</span><span class="screen-reader-text">No Synced Main Site Post</span>';
		}

		// return
		return $output;

	}

	/**
	* broadcast_quick_edit_custom_box
	*
	* This function will add broadcast custom columns to Quick Edit
	*
	* @since		1.0.0
	* @param		$column_name (string)
	* @param		$post_type (string)
	* @return		N/A
	*/
	public function broadcast_quick_edit_custom_box( $column_name, $post_type ) {

		if ( 'post' != $post_type || ! is_main_site() )
			return;

		switch ( $column_name ) {

			case 'mstaxsync_synced':
				$this->display_sites_inputs();
				break;

		}

	}

	/**
	* display_sites_inputs
	*
	* This function will display checkbox input for each local site
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	private function display_sites_inputs() {

		// get sites
		$sites = get_sites( array(
			'site__not_in'	=> mstaxsync_get_main_site_id(),
			'fields'		=> 'ids',
		) );

		if ( ! $sites )
			return;

		?>

		<fieldset class="inline-edit-col-right inline-edit-mstaxsync-broadcast">
			<div class="inline-edit-col">

				<span class="title inline-edit-categories-label"><?php _e( 'Broadcast', 'mstaxsync' ); ?></span>
				<input type="hidden" name="mstaxsync_dest_sites[]" value="0">

				<?php wp_nonce_field( 'mstaxsync_quick_edit_post_broadcast', 'mstaxsync_quick_edit_post_broadcast' ); ?>

				<ul class="cat-checklist mstaxsync-broadcast-checklist">

					<?php foreach ( $sites as $site_id ) {

						$site_details	= get_blog_details( array( 'blog_id' => $site_id ) );
						$site_name		= $site_details ? $site_details->blogname : '';

						?>

						<li id="site-<?php echo $site_id; ?>">
							<label class="selectit">
								<input value="<?php echo $site_id; ?>" type="checkbox" name="mstaxsync_dest_sites[]" id="site-cb-<?php echo $site_id; ?>" />
								<?php echo $site_name; ?>
							</label>
						</li>

					<?php } ?>

				</ul>

			</div>
		</fieldset>

		<?php

	}

	/**
	* broadcast_quick_edit_save_post
	*
	* This function will initialize a quick edit broadcast as part of save_post_post action
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @param		$post (object)
	* @param		$update (boolean)
	* @return		N/A
	*/
	public function broadcast_quick_edit_save_post( $post_id, $post, $update ) {

		if ( ! is_main_site() || ! $update || wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || in_array( $post->post_status, array( 'trash', 'auto-draft' ) ) )
			return;

		// verify nonce
		if ( ! isset( $_POST[ 'mstaxsync_quick_edit_post_broadcast' ] ) || ! wp_verify_nonce( $_POST[ 'mstaxsync_quick_edit_post_broadcast' ], 'mstaxsync_quick_edit_post_broadcast' ) )
			return;

		if ( isset( $_POST[ 'mstaxsync_dest_sites' ] ) && $_POST[ 'mstaxsync_dest_sites' ] ) {

			// broadcast single post
			$this->broadcast_single_post( $post, $_POST[ 'mstaxsync_dest_sites' ] );

		}

	}

}

/**
* mstaxsync_broadcast
*
* The main function responsible for returning the one true instance
*
* @since		1.0.0
* @param		N/A
* @return		(object)
*/
function mstaxsync_broadcast() {

	// globals
	global $mstaxsync_broadcast;

	// initialize
	if( ! isset( $mstaxsync_broadcast ) ) {

		$mstaxsync_broadcast = new MSTaxSync_Broadcast();
		$mstaxsync_broadcast->initialize();

	}

	// return
	return $mstaxsync_broadcast;

}

// initialize
mstaxsync_broadcast();

endif; // class_exists check