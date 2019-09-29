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

// get main site taxonomy terms
$terms_args = array(
	'taxonomy'		=> $tax->name,
	'hide_empty'	=> false,
);

// get main site
$main_site_id = get_main_site_id();

switch_to_blog( $main_site_id );

$terms = get_terms( apply_filters( 'mstaxsync_taxonomy_terms', $terms_args, $tax->name ) );

restore_current_blog();

?>

<div class="mstaxsync-admin-box">

	<div class="title">

		<h3><?php echo $tax->label; ?></h3>
		<p class="desc"><?php _e( 'Select terms to sync', 'mstaxsync' ); ?></p>

	</div>

	<div class="content">

		<?php if ( ! is_wp_error( $terms ) ) :

			if ( $terms ) {

				$terms_hierarchically = array();
				mstaxsync_sort_terms_hierarchically( $terms, $terms_hierarchically );

				$terms = $terms_hierarchically;

			}

			// load taxonomy terms selection view
			mstaxsync_get_view( 'taxonomy-terms', array(
				'tax'	=> $tax,
				'terms'	=> $terms,
			) );

		endif; ?>

	</div>

</div><!-- .mstaxsync-admin-box -->

<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings"></p>