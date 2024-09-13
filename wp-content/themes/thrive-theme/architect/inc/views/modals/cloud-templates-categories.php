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
<div class="error-container"></div>
<div class="tve-modal-content">
	<div id="cb-cloud-menu" class="modal-sidebar">
		<div class="fixed top">
			<div class="lp-search">
				<?php tcb_icon( 'search-regular' ); ?>
				<input type="text" data-source="search" class="keydown" data-fn="filter" placeholder="<?php echo esc_html__( 'Search', 'thrive-cb' ); ?>"/>
				<?php tcb_icon( 'close2', false, 'sidebar', 'click', [ 'data-fn' => 'domClearSearch' ] ); ?>
			</div>
		</div>
		<div class="lp-menu-wrapper mt-30">
			<div class="sidebar-title">
				<p><?php echo esc_html__( 'Type', 'thrive-cb' ); ?></p>
				<span class="tcb-hl"></span>
			</div>
			<div id="types-wrapper" class="mt-10"></div>
		</div>
	</div>
	<div id="cb-cloud-templates" class="modal-content">
		<div class="tcb-modal-tabs flex-center space-between">
			<span id="cb-pack-title" class="tcb-modal-title"><?php echo __( 'Templates', 'thrive-cb' ) ?></span>
			<span data-fn="clearCache" class="tcb-refresh mr-30 click flex-center">
				<span class="mr-10"><?php tcb_icon( 'sync-regular' ); ?></span>
				<span class="mr-10"><?php echo __( 'Refresh from cloud', 'thrive-cb' ); ?></span>
			</span>
		</div>
		<div class="warning-ct-change">
			<div class="tcb-notification info-text">
				<div class="tcb-warning-label"><?php echo esc_html__( 'Warning!', 'thrive-cb' ); ?></div>
				<div class="tcb-notification-content"></div>
			</div>
		</div>
		<div id="cb-pack-content"></div>
	</div>
</div>
