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
	 * remote image URL
	 *
	 * @var (string)
	 */
	private $url = '';

	/**
	 * remote attachment data in this format:
	 *
	 * array(
	 * 		$title       = '',
	 * 		$caption     = '',
	 * 		$description = '',
	 * 		$alt_text    = '',
	 * );
	 *
	 * @var (array)
	 */
	private $attachment_data = array();

	/**
	 * attachment ID, or false if none
	 *
	 * @var (mix)
	 */
	private $attachment_id = false;

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
		add_action( 'init',				array( $this, 'init' ), 99 );
		add_action( 'add_meta_boxes',	array( $this, 'add_meta_boxes' ), 99 );
		add_action( 'save_post_post',	array( $this, 'save_post' ), 10, 3 );

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
			$synced_sites = $this->get_synced_sites( $post_terms );

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
	* @return		(mix) array of post term IDs, or false on failure
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
	* @param		$post_terms (array)
	* @return		(array)
	*/
	private function get_synced_sites( $post_terms ) {

		// vars
		$synced_sites = array();

		if ( is_array( $post_terms ) || ! empty( $post_terms ) ) {
			foreach ( $post_terms as $term_id ) {

				$synced_taxonomy_terms = get_term_meta( $term_id, 'synced_taxonomy_terms', true );

				if ( $synced_taxonomy_terms ) {
					foreach ( $synced_taxonomy_terms as $site_id => $site_term_id ) {

						if ( ! array_key_exists( $site_id, $synced_sites ) ) {

							$synced_sites[ $site_id ] = get_blog_details( array( 'blog_id' => $site_id ) );

						}

					}
				}

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

			<?php foreach ( $synced_sites as $site_id => $details ) { ?>

				<li id="site-<?php echo $site_id; ?>">
					<label><input value="<?php echo $site_id; ?>" type="checkbox" name="mstaxsync_dest_sites[]" id="site-cb-<?php echo $site_id; ?>"><?php echo $details->blogname; ?></label>
				</li>

			<?php } ?>

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
	* @return		N/A
	*/
	private function broadcast_single_post( $post, $sites ) {

		if ( ! $post || 'post' != get_post_type( $post ) || ! is_array( $sites ) || empty( $sites ) )
			return;

		$post_data = $this->get_post_data( $post );

		if ( ! isset( $post_data[ 'taxonomies' ] ) || empty( $post_data[ 'taxonomies' ] ) )
			return;

		$synced_terms = $this->get_post_synced_terms( $post->ID, $post_data[ 'taxonomies' ] );

		if ( empty( $synced_terms ) )
			return;

		foreach ( $sites as $site_id ) {

			// broadcast post to a single local site
			$this->broadcast( $post_data, $synced_terms, $site_id );

		}

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

			$image_url	= wp_get_attachment_image_src( $thumbnail_id, 'full' );
			$image_url	= $image_url ? $image_url[0] : '';

			$this->url	= $image_url;
			$thumbnail	= get_post( $thumbnail_id );

			if ( $this->url && $thumbnail ) {

				$this->attachment_data = array(
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
	* @return		(mix) new created post ID, or 0 in case of not found synced terms, or false in case of error
	*/
	private function broadcast( $post_data, $synced_terms, $site_id ) {

		if ( ! is_array( $post_data ) || empty( $post_data ) || ! is_array( $synced_terms ) || empty( $synced_terms ) || ! $site_id )
			return false;

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
	* @return		(mix) new created post ID, or false in case of error
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
		if ( $this->url && $this->attachment_data ) {
			$this->set_post_thumbnail( $post_id );
		}

		// return
		return $post_id;

	}

	/**
	* set_post_thumbnail
	*
	* This function will download and associate a main site thumbnail to a specified local site post ID
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @return		(mix) attachment ID, or false on failure
	*/
	private function set_post_thumbnail( $post_id ) {

		if ( ! $post_id )
			return false;

		// download remote file and sideload it into the uploads directory
		$file_attributes = $this->sideload();

		if ( ! $file_attributes )
			return false;

		// insert the image as a new attachment
		$this->insert_attachment( $file_attributes[ 'file' ], $file_attributes[ 'type' ] );

		if ( ! $this->attachment_id )
			return false;

		$this->update_metadata();
		$this->update_post_data();
		$this->update_alt_text();
		$this->assign_post_thumbnail( $post_id );

		// return
		return $this->attachment_id;

	}

	/**
	* sideload
	*
	* This function will sideload the remote image into the uploads directory
	*
	* @since		1.0.0
	* @param		N/A
	* @return		(mix) associative array of file attributes, or false on failure
	*/
	private function sideload() {

		// get access to the download_url() and wp_handle_sideload() functions
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// download file to temp dir
		$temp_file = download_url( $this->url, 10 );

		if ( is_wp_error( $temp_file ) )
			return false;

		$mime_type = mime_content_type( $temp_file );

		// an array similar to that of a PHP $_FILES POST array
		$file = array(
			'name'		=> $this->get_filename( $mime_type ),
			'type'		=> $mime_type,
			'tmp_name'	=> $temp_file,
			'error'		=> 0,
			'size'		=> filesize( $temp_file ),
		);

		$overrides = array(

			// tell WordPress to not look for the POST form
			// fields that would normally be present. default is true.
			// since the file is being downloaded from a remote server,
			// there will be no form fields
			'test_form'		=> false,

			// setting this to false lets WordPress allow empty files – not recommended
			'test_size'		=> true,

			// a properly uploaded file will pass this test.
			// there should be no reason to override this one
			'test_upload'	=> true,

		);

		// Move the temporary file into the uploads directory.
		$file_attributes = wp_handle_sideload( $file, $overrides );

		if ( isset( $file_attributes[ 'error' ] ) )
			return false;

		// return
		return $file_attributes;

	}

	/**
	* get_filename
	*
	* This function will get filename for attachment, including extension
	*
	* @since		1.0.0
	* @param		$mime_type (string)
	* @return		(string)
	*/
	private function get_filename( $mime_type ) {

		if ( empty( $this->attachment_data[ 'title' ] ) ) {

			// return
			return basename( $this->url );

		}

		$filename  = sanitize_title_with_dashes( $this->attachment_data[ 'title' ] );
		$extension = $this->get_extension_from_mime_type( $mime_type );

		// return
		return $filename . $extension;

	}

	/**
	* get_extension_from_mime_type
	*
	* This function will get extension from MIME type
	*
	* @since		1.0.0
	* @param		$mime_type (string)
	* @return		(string) file extension or empty string if not found
	*/
	private function get_extension_from_mime_type( $mime_type ) {

		// vars
		$extensions = array(
			'image/jpeg'	=> '.jpg',
			'image/gif'		=> '.gif',
			'image/png'		=> '.png',
			'image/x-icon'	=> '.ico',
		);

		// return
		return isset( $extensions[ $mime_type ] ) ? $extensions[ $mime_type ] : '';

	}

	/**
	* insert_attachment
	*
	* This function will insert attachment into the WordPress media library
	*
	* @since		1.0.0
	* @param		$file_path (string)
	* @param		$mime_type (string)
	* @return		N/A
	*/
	private function insert_attachment( $file_path, $mime_type ) {

		// get the path to the uploads directory
		$wp_upload_dir = wp_upload_dir();

		// prepare an array of post data for the attachment
		$attachment_data = array(
			'guid'				=> $wp_upload_dir[ 'url' ] . '/' . basename( $file_path ),
			'post_mime_type'	=> $mime_type,
			'post_title'		=> preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_content'		=> '',
			'post_status'		=> 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment_data, $file_path );

		if ( ! $attachment_id )
			return;

		$this->attachment_id = $attachment_id;

	}

	/**
	* update_metadata
	*
	* This function will update attachment meta
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	private function update_metadata() {

		$file_path = get_attached_file( $this->attachment_id );

		if ( ! $file_path )
			return;

		// get access to the wp_generate_attachment_metadata() function
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// generate metadata for the attachment and update the database record
		$attach_data = wp_generate_attachment_metadata( $this->attachment_id, $file_path );
		wp_update_attachment_metadata( $this->attachment_id, $attach_data );

	}

	/**
	* update_post_data
	*
	* This function will update attachment title, caption and description
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	private function update_post_data() {

		if ( empty( $this->attachment_data[ 'title' ] ) && empty( $this->attachment_data[ 'caption' ] ) && empty( $this->attachment_data[ 'description' ] ) )
			return;

		$data = array(
			'ID' => $this->attachment_id,
		);

		// set image title (post title)
		if ( ! empty( $this->attachment_data[ 'title' ] ) ) {
			$data[ 'post_title' ] = $this->attachment_data[ 'title' ];
		}

		// set image caption (post excerpt)
		if ( ! empty( $this->attachment_data[ 'caption' ] ) ) {
			$data[ 'post_excerpt' ] = $this->attachment_data[ 'caption' ];
		}

		// set image description (post content)
		if ( ! empty( $this->attachment_data[ 'description' ] ) ) {
			$data[ 'post_content' ] = $this->attachment_data[ 'description' ];
		}

		wp_update_post( $data );

	}

	/**
	* update_alt_text
	*
	* This function will update attachment alt text
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	private function update_alt_text() {

		if ( empty( $this->attachment_data[ 'alt_text' ] ) && empty( $this->attachment_data[ 'title' ] ) )
			return;

		// use the alt text string provided, or the title as a fallback
		$alt_text = ! empty( $this->attachment_data[ 'alt_text' ] ) ? $this->attachment_data[ 'alt_text' ] : $this->attachment_data[ 'title' ];

		update_post_meta( $this->attachment_id, '_wp_attachment_image_alt', $alt_text );

	}

	/**
	* assign_post_thumbnail
	*
	* This function will assign post thumbnail to specified post ID
	*
	* @since		1.0.0
	* @param		$post_id (int)
	* @return		N/A
	*/
	private function assign_post_thumbnail( $post_id ) {

		if ( ! $post_id )
			return;

		set_post_thumbnail( $post_id, $this->attachment_id );

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