<?php
/**
 * MSTaxSync_Attachment
 *
 * @author		Nir Goldberg
 * @package		includes/classes
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MSTaxSync_Attachment' ) ) :

class MSTaxSync_Attachment {

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
	 * post ID to assign attachment to
	 *
	 * @var (int)
	 */
	private $post_id = 0;

	/**
	* __construct
	*
	* @since		1.0.0
	* @param		$url (string) remote image URL
	* @param		$post_id (int) post ID to assign attachment to
	* @param		$attachment_data (array) data to be used for the attachment
	* @return		N/A
	*/
	public function __construct( $url, $post_id, $attachment_data = array() ) {

		$this->url		= $url;
		$this->post_id	= $post_id;

		if ( is_array( $attachment_data ) && $attachment_data ) {
			$this->attachment_data = array_map( 'sanitize_text_field', $attachment_data );
		}

	}

	/**
	* set_post_attachment
	*
	* This function will download and associate an image as an attachment to a specified post ID
	*
	* @since		1.0.0
	* @param		N/A
	* @return		(mix) attachment ID, or false on failure
	*/
	public function set_post_attachment() {

		if ( ! $this->url || ! $this->post_id )
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
		$this->assign_post_attachment();

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

		// move the temporary file into the uploads directory
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

		$attachment_id = wp_insert_attachment( $attachment_data, $file_path, $this->post_id );

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

		// wpml
		if ( mstaxsync_is_local_site_wpml_active() ) {

			// globals
			global $sitepress;

			$post_language_details = apply_filters( 'wpml_post_language_details', NULL, $this->post_id ) ;
			$sitepress->set_element_language_details( $this->attachment_id, 'post_attachment', false, $post_language_details[ 'language_code' ] );

		}

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
	* assign_post_attachment
	*
	* This function will assign post attachment to a specified post ID
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	private function assign_post_attachment() {

		set_post_thumbnail( $this->post_id, $this->attachment_id );

	}

	/**
	* remove_wpml_save_attachment_actions
	*
	* This function will remove WPML actions in order to prevent attachment duplication
	*
	* @since		1.0.0
	* @param		N/A
	* @return		N/A
	*/
	public static function remove_wpml_save_attachment_actions() {

		// globals
		global $wp_filter;

		// vars
		$actions = array( 'add_attachment', 'edit_attachment' );

		foreach ( $wp_filter as $key => $value ) {
			if ( in_array( $key, $actions ) ) {
				foreach ( $value->callbacks as $callback ) {
					foreach ( $callback as $action ) {

						$className = get_class( $action[ 'function' ][0] );
						$functionName = $action[ 'function' ][1];

						if ( $className == 'WPML_Media_Attachments_Duplication' && $functionName == 'save_attachment_actions' ) {
							remove_action( $key, array( $action['function'][0], $functionName ) );
						}

					}
				}
			}
		}

	}

}

endif; // class_exists check

/**
 * mstaxsync_set_post_attachment
 *
 * This function will download and associate an image as an attachment to a specified post ID
 *
 * @since		1.0.0
 * @param		$url (string) remote image URL
 * @param		$post_id (int) post ID to assign attachment to
 * @param		$attachment_data (array) data to be used for the attachment
 * @return		(int) attachment ID, or false on failure
 */
function mstaxsync_set_post_attachment( $url, $post_id, $attachment_data = array() ) {

	$mstaxsync_attachment = new MSTaxSync_Attachment( $url, $post_id, $attachment_data );

	// return
	return $mstaxsync_attachment->set_post_attachment();

}