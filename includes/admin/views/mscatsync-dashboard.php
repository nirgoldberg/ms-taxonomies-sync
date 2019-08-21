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

<div class="wrap about-wrap mscatsync-wrap">

	<h1><?php _e( 'Welcome to Multisite Categories Sync', 'mscatsync' ); ?> <?php echo $version; ?></h1>
	<div class="about-text"><?php _e( 'Thank you for installing, we hope you like it.', 'mscatsync' ); ?></div>

</div>