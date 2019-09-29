<?php
/**
 * Admin taxonomy terms HTML content
 *
 * @author		Nir Goldberg
 * @package		includes/admin/views
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// extract args
extract( $args );

// get local site taxonomy terms
$local_terms_args = array(
	'taxonomy'		=> $tax->name,
	'hide_empty'	=> false,
);
$local_terms = get_terms( apply_filters( 'mstaxsync_local,taxonomy_terms', $local_terms_args, $tax->name ) );

?>

<div class="mstaxsync-taxonomy-terms-box">

	<div class="mstaxsync-relationship" data-name="<?php echo $tax->name; ?>">
		<div class="selection">

			<div class="choices">
				<div class="title">

					<h3><?php _e( 'Main site', 'mstaxsync' ); ?></h3>
					<p class="desc"><?php _e( 'Select terms to sync', 'mstaxsync' ); ?></p>

				</div>

				<ul class="list choices-list">

					<?php if ( $terms ) :

						mstaxsync_display_terms_hierarchically( $terms, 'choice' );

					else : ?>

						<p class="no-terms"><?php printf( __( 'There are no %s defined in main site', 'mstaxsync' ), $tax->label ); ?></p>

					<?php endif; ?>

				</ul>
			</div>

			<div class="values">
				<div class="title">

					<h3><?php _e( 'Local site', 'mstaxsync' ); ?></h3>
					<p class="desc"><?php _e( 'Sort and edit terms', 'mstaxsync' ); ?></p>

				</div>

				<ul class="list values-list">

					<?php if ( ! is_wp_error( $local_terms ) && $local_terms ) :

						$local_terms_hierarchically = array();
						mstaxsync_sort_terms_hierarchically( $local_terms, $local_terms_hierarchically );
						mstaxsync_display_terms_hierarchically( $local_terms_hierarchically, 'value' );

					endif; ?>

				</ul>
			</div>

		</div>
	</div><!-- .mstaxsync-relationship -->

</div><!-- .mstaxsync-taxonomy-terms-box -->