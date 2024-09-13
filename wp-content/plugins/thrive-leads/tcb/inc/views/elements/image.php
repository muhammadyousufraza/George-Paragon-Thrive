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

<div class="thrv_wrapper tve_image_caption tcb-elem-placeholder">
	<span class="tcb-inline-placeholder-action with-icon">
		<?php tcb_icon( 'image', false, 'editor' ); ?><span class="tcb-placeholder-text"><?php echo esc_html__( 'Insert Image', 'thrive-cb' ); ?></span>
	</span>
</div>
