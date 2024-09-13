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
<div class="thrv_wrapper thrv_ct_symbol tcb-elem-placeholder">
	<span class="tcb-inline-placeholder-action with-icon">
		<?php tcb_icon( 'add', false, 'editor' ); ?>
		<?php echo esc_html__( 'Insert Content Template or Symbol', 'thrive-cb' ); ?>
	</span>
</div>
