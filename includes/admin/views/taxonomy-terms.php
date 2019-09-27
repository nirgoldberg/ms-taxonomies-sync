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

?>

<div class="mstaxsync-taxonomy-terms-box">

	<div class="mstaxsync-relationship" data-name="<?php echo $tax->name; ?>">
		<div class="selection">

			<div class="choices">
				<ul class="list choices-list">

					<?php mstaxsync_display_terms_hierarchically( $terms ); ?>

				</ul>
			</div>

			<div class="values">
				<ul class="list values-list"></ul>
			</div>

		</div>
	</div><!-- .mstaxsync-relationship -->

</div><!-- .mstaxsync-taxonomy-terms-box -->