<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

global $design;
if ( empty( $design ) ) {
	$design = tve_ult_get_design( $_REQUEST[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] );
}

$design_type_details = TVE_Ult_Const::design_details( $design['post_type'] );
$name                = $design_type_details['name'];
?>
<div class="error-container"></div>
<div class="tve-modal-content">
	<div id="cb-cloud-menu" class="modal-sidebar">
		<div class="lp-menu-wrapper mt-30">
			<div class="sidebar-title">
				<p><?php echo __( 'Type', 'thrive-ult'); ?></p>
				<span class="tcb-hl"></span>
			</div>
			<div id="types-wrapper" class="mt-10"></div>
		</div>
	</div>
	<div id="cb-cloud-templates" class="modal-content">
		<div class="warning-ct-change no-margin">
			<div class="tcb-notification info-text">
				<div class="tcb-warning-label"><?php echo __( 'Warning!', 'thrive-ult'); ?></div>
				<div class="tcb-notification-content"></div>
			</div>
		</div>
		<div class="tcb-modal-header flex-center space-between">
			<div id="cb-pack-title" class="tcb-modal-title"><?php echo __( 'Templates', 'thrive-ult') ?></div>
			<span data-fn="clearCache" class="tcb-refresh mr-30 click flex-center">
				<span class="mr-10"><?php tcb_icon( 'sync-regular' ); ?></span>
				<span class="mr-10"><?php echo __( 'Refresh from cloud', 'thrive-ult'); ?></span>
			</span>
		</div>
		<div id="cb-pack-content"></div>
	</div>
</div>

