<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-contact_form-component" class="tve-component" data-view="ContactForm">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="control-grid">
			<div class="label"><?php echo esc_html__( 'Form Fields', 'thrive-cb' ); ?></div>
			<div class="full">
				<button class="tcb-right tve-button blue click" id="tcb-add-contact-form-item" data-fn="add_cf_item">
					<?php echo esc_html__( 'ADD NEW', 'thrive-cb' ) ?>
				</button>
			</div>
		</div>
		<div class="tve-control" data-key="FieldsControl" data-initializer="get_fields_control"></div>
		<hr>
		<div class="tve-control" data-view="AddRemoveLabels"></div>
		<div class="tve-control tcb-cf-add-remove-req-mark-control" data-view="AddRemoveRequiredMarks"></div>
		<hr>
		<div class="tve-control" data-view="ZapierConnection"></div>
		<div class="tve-cf-zapier-connection mb-5">
			<div class="tve-control mb-5" data-key="ZapierIp" data-view="Checkbox"></div>
			<div class="tve-control" data-key="ZapierTags" data-view="LabelInput"></div>
			<span class="info-text grey-text">
				<?php echo esc_html__( 'Comma-separated lists of tags', 'thrive-cb' ) ?>
			</span>
		</div>
		<div class="tve-cf-email-setup">
			<hr>
			<div class="control-grid">
				<button class="blue button-link click" data-fn="manage_settings" style="font-size: 13px;">
					<?php tcb_icon( 'envelope' ); ?>
					<?php echo esc_html__( 'Email & after submit setup', 'thrive-cb' ); ?>
				</button>
			</div>
		</div>
		<div class="tve-advanced-controls">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo esc_html__( 'Advanced', 'thrive-cb' ); ?>
				</span>
			</div>
			<div class="dropdown-content pt-0">
				<button class="tve-button blue long click" data-fn="manage_error_messages">
					<?php echo esc_html__( 'Edit error messages', 'thrive-cb' ) ?>
				</button>
			</div>
		</div>
	</div>
</div>
