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

<span class="tcb-modal-title m-0"><?php echo esc_html__( 'Export Template', 'thrive-cb' ) ?></span>
<div class="error-container" style="display: none;"></div>

<div class="tve-template-image">

	<div class="thumbnail-preview choose-image">
		<div class="thumbnail-text">
			<span>
				<?php echo esc_html__( 'Click to upload a photo.', 'thrive-cb' ); ?>
			</span>
			<span>
				<?php echo esc_html__( 'Recommended image size: 166x140px. If you do not choose a picture, the default template thumbnail will be used.', 'thrive-cb' ); ?>
			</span>
		</div>

		<button type="button" class="tve-button remove-image"><?php tcb_icon( 'trash-light' ); ?><?php echo esc_html__( 'Remove', 'thrive-cb' ) ?></button>
	</div>

	<div class="tvd-input-field">
		<input type="text" id="tve-export-template-name" required>
		<label for="tve-export-template-name"><?php echo esc_html__( 'Template Name', 'thrive-cb' ); ?></label>
	</div>
</div>

<div class="tcb-modal-footer m-20 p-0 flex-end">
	<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
		<?php echo esc_html__( 'Download File', 'thrive-cb' ) ?>
	</button>
</div>

