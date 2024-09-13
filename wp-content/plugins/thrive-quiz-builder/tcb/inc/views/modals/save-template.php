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

<span class="tcb-modal-title m-0"><?php echo esc_html__( 'Save Content as Template', 'thrive-cb' ) ?></span>
<div class="tcb-modal-description">
	<?php echo esc_html__( 'You can save your work as a template for use on another post/page on your site.', 'thrive-cb' ) ?>
</div>

<div class="tvd-input-field mb-5 mt-25">
	<input type="text" id="tve-template-name" required>
	<label for="tve-template-name"><?php echo esc_html__( 'Template Name', 'thrive-cb' ); ?></label>
</div>

<div class="tcb-modal-footer m-20 p-20 flex-end flex-end">
	<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
		<?php echo esc_html__( 'Save Template', 'thrive-cb' ) ?>
	</button>
</div>
