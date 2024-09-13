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

<div class="section-preview-wrapper">
	<div class="section-preview-image">
		<img src="" data-loading-src="<?php echo tve_editor_css(); ?>/images/loading-spinner.gif" alt="">
	</div>
</div>
<div class="section-form-wrapper">
	<h2 class="tcb-modal-title">
		<?php echo sprintf( __( 'Save %s Section as:', 'thrive-theme' ), '<span class="new-section-type"></span>' ); ?>
	</h2>

	<input type="text" class="new-section-name">

	<div class="tcb-modal-footer clearfix mt-20">
		<button type="button" class="tcb-right tve-button medium blue click w-100 white-text" data-fn="save">
			<?php echo __( 'Save to Library', 'thrive-theme' ); ?>
		</button>
	</div>
</div>
