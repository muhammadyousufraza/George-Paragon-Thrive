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

<div class="thrv_wrapper tcb-image-gallery tcb-elem-placeholder tcb-gallery-placeholder">
	<span class="tcb-inline-placeholder-action with-icon">
		<?php tcb_icon( 'images', false, 'editor' ); ?>
		<span class="tcb-placeholder-text"><?php echo esc_html__( '+ Select images', 'thrive-cb' ); ?></span>
	</span>
</div>
