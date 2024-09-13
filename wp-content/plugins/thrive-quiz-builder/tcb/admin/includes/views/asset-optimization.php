<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<?php include TVE_DASH_PATH . '/templates/header.phtml'; ?>

<div class="ls-breadcrumbs">
	<a href="<?php echo admin_url( 'admin.php?page=tve_dash_section' ); ?>" class="tvd-breadcrumb-back">
		<?php echo __( 'Thrive Dashboard', 'thrive-cb' ); ?>
	</a>
	<a href="<?php echo admin_url( 'admin.php?page=tve_lightspeed' ); ?>" class="tvd-breadcrumb-back">
		<?php echo __( 'Project Lightspeed', 'thrive-cb' ); ?>
	</a>
	<span>
		<?php echo __( 'Asset Optimization', 'thrive-cb' ); ?>
	</span>
</div>

<div id="lightspeed-admin-wrapper"></div>
