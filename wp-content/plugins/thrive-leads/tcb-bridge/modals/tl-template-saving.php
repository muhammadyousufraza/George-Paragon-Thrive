<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-leads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<h2 class="tcb-modal-title"><?php echo __( 'Save as template', 'thrive-leads' ); ?></h2>

<div class="tvd-input-field margin-bottom-5 margin-top-25">
	<input type="text" id="tve-template-name" required>
	<label for="tve-template-name"><?php echo __( 'Template Name', 'thrive-leads' ); ?></label>
</div>

<div class="tcb-modal-footer clearfix padding-top-20 row end-xs">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
			<?php echo __( 'Save', 'thrive-leads' ) ?>
		</button>
	</div>
</div>
