<?php
/**
 * Admin tools / resync posts HTML content
 *
 * @author		Nir Goldberg
 * @package		includes/admin/views
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Variables
 */
$nonce = wp_create_nonce( 'resync_posts' );

?>

<p class="description"><?php _e( 'Reassign current synced category and taxonomy terms for all synced posts', 'mstaxsync' ); ?></p>

<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary mstaxsync-resync-posts" value="<?php _e( 'Resync Posts', 'mstaxsync' ); ?>" data-nonce="<?php echo $nonce; ?>">
	<span class="ajax-loading dashicons dashicons-update"></span>
</p>

<div class="resync-posts-summary"></div>

<div class="resync-posts-result"></div>