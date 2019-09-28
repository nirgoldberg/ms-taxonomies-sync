<?php
/**
 * Admin taxonomies HTML content
 *
 * @author		Nir Goldberg
 * @package		includes/admin/views
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// extract args
extract( $args );

?>

<div class="wrap mstaxsync-wrap" id="<?php echo $menu_slug; ?>">

	<h1><?php echo $page_title; ?></h1>

	<?php

		// display tabs
		if ( ! empty( $tabs ) ) : ?>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $tab_slug => $tab ) : ?>
					<a class="nav-tab<?php echo ( $active_tab == $tab_slug ) ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( "admin.php?page={$menu_slug}&tab={$tab_slug}" ); ?>"><?php echo $tab[ 'title' ]; ?></a>
				<?php endforeach; ?>
			</h2>

			<?php

				if ( $active_tab ) {

					// load taxonomy view
					mstaxsync_get_view( 'taxonomy', array(
						'active_tab'	=> $active_tab,
					) );

				}

			?>

		<?php else : ?>

			<p><?php printf( __( 'No taxonomies were selected to be synced.<br /><a href="%s">Please select at least one taxonomy</a>', 'mstaxsync' ), 'admin.php?page=mstaxsync-settings' ); ?></p>

		<?php endif;

	?>

</div><!-- #<?php echo $menu_slug; ?> -->