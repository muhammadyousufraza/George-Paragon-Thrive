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

<span class="tcb-modal-title m-0"><?php echo esc_html__( 'Save Page Template', 'thrive-cb' ) ?></span>
<div class="mt-10 mb-30">
	<?php echo esc_html__( 'You can save the current page as a template for use on another post / page on your site.', 'thrive-cb' ) ?>
</div>

<div class="control-grid wrap">
	<label class="label full-width" for="tve-lp-template-name"><?php echo esc_html__( 'Template Name', 'thrive-cb' ); ?></label>
	<input type="text" id="tve-lp-template-name" required>
</div>

<div class="tve-tags-wrapper control-grid wrap mt-20">
	<label class="label full-width"><?php echo esc_html__( 'Tags', 'thrive-cb' ); ?></label>
	<div class="tve-tags-list"></div>
	<div class="control-grid fill">
		<input type="text" class="tve-new-tag-name mr-50">
		<a class="tve-add-tag">
			<span class="circle mr-5">+</span>
			<span>
				<?php echo esc_html__( 'Add tag', 'thrive-cb' ) ?>
			</span>
		</a>
	</div>
</div>

<div class="tcb-modal-footer m-20 p-0 flex-end">
	<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
		<?php echo esc_html__( 'Save Template', 'thrive-cb' ) ?>
	</button>
</div>
