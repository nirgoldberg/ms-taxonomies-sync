<?php
/**
 * Admin dashboard HTML content
 *
 * @author		Nir Goldberg
 * @package		includes/admin/views
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// extract args
extract( $args );

?>

<div class="wrap about-wrap mstaxsync-wrap" id="<?php echo $menu_slug; ?>">

	<h1><?php _e( 'Welcome to Multisite Taxonomies Sync', 'mstaxsync' ); ?> <?php echo $version; ?></h1>
	<div class="about-text"><?php _e( 'Thank you for installing, we hope you like it.', 'mstaxsync' ); ?></div>

	<?php
		// display tabs
		if ( ! empty( $tabs ) ) : ?>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $tab_slug => $tab ) : ?>
					<a class="nav-tab<?php echo ( $active_tab == $tab_slug ) ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( "admin.php?page={$menu_slug}&tab={$tab_slug}" ); ?>"><?php echo $tab; ?></a>
				<?php endforeach; ?>
			</h2>

		<?php endif;
	?>

</div>