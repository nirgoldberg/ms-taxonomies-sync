<?php
/**
 * Admin tools HTML content
 *
 * @author		Nir Goldberg
 * @package		includes/admin/views
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// extract args
extract( $args );

?>

<div class="mstaxsync-admin-box">

	<div class="content">

		<?php

			// load tool view
			mstaxsync_get_view( 'tools-' . $active_tab );

		?>

	</div>

</div><!-- .mstaxsync-admin-box -->