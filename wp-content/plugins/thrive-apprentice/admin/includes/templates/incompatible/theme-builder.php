<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="tva-notice-modal-outer tva-architect-incompatible">
	<div class="tva-notice-modal-inner tva-architect-incompatible-content">
		<div class="tva-architect-incompatible-content-icons">
			<img src="<?php echo TVA_Const::plugin_url( 'admin/img/ttb-logo.png' ); ?>">
			<?php tva_get_svg_icon( 'plus_light', 'ml-20 mr-20' ); ?>
			<img src="<?php echo TVA_Const::plugin_url( 'admin/img/thrive-apprentice-dashboard.png' ); ?>">
		</div>
		<p>
			<?php echo __( 'Current version of Thrive Apprentice is not compatible with the current version of Thrive Theme Builder. Please update both to the latest versions. ', 'thrive-apprentice' ); ?>
		</p>
		<a class="tva-modal-btn tva-modal-btn-green" href="<?php echo admin_url( 'update-core.php?force-check=1' ); ?>"><?php echo __( 'WordPress Updates', 'thrive-apprentice' ); ?>&nbsp;&rarr;</a>
	</div>
</div>
