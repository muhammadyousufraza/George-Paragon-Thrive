<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div class="tcb-modal-tabs flex-center space-between">
	<span class="tcb-modal-title"><?php echo esc_html__( 'Choose', 'thrive-cb' ); ?> <span class="element-name"></span></span>
	<span data-fn="clearCache" class="tcb-refresh mr-30 click flex-center">
		<span class="mr-10"><?php tcb_icon( 'sync-regular' ); ?></span>
		<span><?php echo esc_html__( 'Refresh from cloud', 'thrive-cb' ); ?></span>
	</span>
</div>
<div class="tve-templates-wrapper ml-50">
	<div id="cloud-templates" class="content-templates tve-templates-container pb-10"></div>
</div>
<div class="tcb-modal-footer flex space-between" style="position: fixed;">
	<button type="button" class="tcb-left tve-button medium gray tcb-modal-cancel"><?php echo esc_html__( 'Cancel', 'thrive-cb' ); ?></button>
	<button type="button" class="tcb-right tve-button medium green tcb-modal-save"><?php echo esc_html__( 'Choose Divider', 'thrive-cb' ); ?></button>
</div>
