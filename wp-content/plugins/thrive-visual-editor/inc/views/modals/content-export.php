<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<span class="tcb-modal-title m-0"><?php echo esc_html__( 'Export Content', 'thrive-cb' ); ?></span>
<div class="error-container" style="display: none;"></div>

<div class="tve-template-image">
	<div class="tvd-input-field">
		<input type="text" id="tve-export-content-name" required>
		<label for="tve-export-content-name"><?php echo esc_html__( 'Content Name', 'thrive-cb' ); ?></label>
	</div>
</div>

<div class="tcb-modal-footer m-20 p-0 flex-end">
	<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
		<?php echo esc_html__( 'Download File', 'thrive-cb' ); ?>
	</button>
</div>

