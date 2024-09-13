<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="error-container"></div>
<div class="tcb-modal-step">
	<div class="lp-cloud-menu modal-sidebar">
		<div class="lp-menu-wrapper">
			<div class="mt-30">
				<div class="sidebar-title">
					<p><?php echo esc_html__( 'Default Templates', 'thrive-cb' ); ?></p>
					<span class="tcb-hl"></span>
				</div>
				<div id="lp-default-filters"></div>
			</div>
			<div class="mt-30">
				<div class="sidebar-title">
					<p><?php echo esc_html__( 'My Templates', 'thrive-cb' ); ?></p>
					<span class="tcb-hl"></span>
				</div>
				<div id="lp-saved-filters"></div>
			</div>
		</div>
	</div>
	<div class="lp-cloud-templates modal-content">
		<div class="lp-template-title-text ml-10">
			<span class="set-name tcb-modal-title"><?php echo esc_html__( 'Certificate templates', 'thrive-apprentice' ); ?></span>
		</div>
		<div id="lp-set-tpl-list" class="pl-30 pt-10"></div>
	</div>
	<div class="lp-footer">
		<button class="tve-btn tve-button click green click" data-fn="save"><?php echo esc_html__( 'Apply Template', 'thrive-apprentice' ); ?></button>
	</div>
</div>
