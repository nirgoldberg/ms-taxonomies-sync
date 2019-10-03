<?php
/**
 * Admin taxonomy HTML content
 *
 * @author		Nir Goldberg
 * @package		includes/admin/views
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// extract args
extract( $args );

// get taxonomy according to active tab
$tax = get_taxonomy( $active_tab );

if ( ! $tax )
	return;

?>

<div class="mstaxsync-admin-box">

	<div class="title">

		<h3><?php echo $tax->label; ?></h3>
		<p class="desc"><?php _e( 'Select terms to sync', 'mstaxsync' ); ?></p>

	</div>

	<div class="content">

		<?php

			// load taxonomy terms selection view
			mstaxsync_get_view( 'taxonomy-terms', array(
				'tax'	=> $tax,
			) );

		?>

	</div>

</div><!-- .mstaxsync-admin-box -->

<?php

	$nonce = wp_create_nonce( 'taxonomy_terms_sync' );

?>

<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings" data-nonce="<?php echo $nonce; ?>">
	<span class="ajax-loading dashicons dashicons-update"></span>
</p>

<div class="result"></div>