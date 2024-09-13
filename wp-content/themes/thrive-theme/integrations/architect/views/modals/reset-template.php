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

<h3><?php echo __( 'Reset Template', 'thrive-theme' ); ?></h3>
<p>
	<?php echo __( 'Are you sure you want to reset the template ?', 'thrive-theme' ); ?>
	<?php echo __( 'You will lose any customizations made to it.', 'thrive-theme' ); ?>
</p>

<div class="ttd-modal-footer">
	<button class="ttb-left grey click" data-fn="close"><?php echo __( 'Cancel', 'thrive-theme' ); ?></button>
	<button class="ttb-right red click" data-fn="reset"><?php echo __( 'Reset Template', 'thrive-theme' ); ?></button>
</div>
