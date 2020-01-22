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

/**
 * Variables
 */
$tabs_to_display = array();

?>

<div class="wrap mstaxsync-wrap" id="<?php echo $menu_slug; ?>">

	<h1><?php echo $page_title; ?></h1>

	<?php

		// prepare tabs
		if ( ! empty( $tabs ) ) :

			foreach ( $tabs as $tab_slug => $tab ) :
				if ( $tab[ 'permission' ] ) {

					if ( ! $active_tab ) {
						$active_tab = $tab_slug;
					}

					$tabs_to_display[ $tab_slug ] = $tab;

				}
			endforeach;

		endif;

		// display tabs
		if ( ! empty( $tabs_to_display ) ) : ?>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs_to_display as $tab_slug => $tab ) : ?>
					<a class="nav-tab<?php echo ( $active_tab == $tab_slug ) ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( "admin.php?page={$menu_slug}&tab={$tab_slug}" ); ?>"><?php echo $tab[ 'title' ]; ?></a>
				<?php endforeach; ?>
			</h2>

			<?php if ( $active_tab ) :

				// load tools view
				mstaxsync_get_view( 'tools', array(
					'tabs'			=> $tabs_to_display,
					'active_tab'	=> $active_tab,
				) );

			endif;

		else :

			// no tabs to display
			echo '<p>' . __( 'There are no available tools', 'mstaxsync' ) . '</p>';

		endif;

	?>

</div><!-- #<?php echo $menu_slug; ?> -->