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

<h3>
	<?php echo __( 'Unsaved Changes', 'thrive-theme' ); ?>
</h3>

<p>
	<?php echo __( 'Do you want to save your changes before exiting?', 'thrive-theme' ); ?>
</p>

<div class="ttd-modal-footer">
	<button class="ttb-left red click" data-fn="continueWithoutSaving"><?php echo __( 'Continue without Saving', 'thrive-theme' ); ?></button>
	<button class="ttb-right green click" data-fn="saveAndContinue"><?php echo __( 'Save and Exit', 'thrive-theme' ); ?></button>
</div>
