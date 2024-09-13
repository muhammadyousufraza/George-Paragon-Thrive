<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<?php if ( ! tve_ult_allow_cloud_templates() ): ?>
	<div class="tcb-right-sidebar-white-box">
		<button class="click green-ghost-button p-5 w-100" data-fn="tve_ult_reset_template">
			<?php tcb_icon( 'undo-regular' ); ?>
			<?php echo __( 'Reset to default content', 'thrive-ult' ); ?>
		</button>
	</div>

	<hr class="mt-20" style="background: #dee8e8">
<?php endif; ?>
