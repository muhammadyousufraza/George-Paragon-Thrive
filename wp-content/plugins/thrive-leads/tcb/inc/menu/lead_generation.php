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
<div id="tve-lead_generation-component" class="tve-component" data-view="LeadGeneration">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="no-api tcb-text-center mb-10 mr-5 ml-5">
			<button class="tve-button orange click" data-fn="edit_form_elements">
				<?php echo esc_html__( 'Edit Form Elements', 'thrive-cb' ); ?>
			</button>
		</div>
		<div class="tve-control hide-states" data-view="FormPalettes"></div>
		<div class="tve-control pt-5" data-key="connectionType" data-view="ButtonGroup"></div>
		<div class="connection-controls" data-connection="api">
			<div class="tve-lg-connection mt-10">
				<span><?php echo esc_html__( 'Send leads to', 'thrive-cb' ); ?></span>
				<button class="tcb-right tve-button blue click tve-add-lg-connection" data-fn="addConnection">
					<?php echo esc_html__( 'Add Connection', 'thrive-cb' ); ?>
				</button>
			</div>
			<div class="no-api tve-control api-connections-list" data-view="ApiConnections"></div>
			<hr class="mt-10">
			<div class="control-grid">
				<div class="label">
					<?php echo esc_html__( 'Form fields', 'thrive-cb' ); ?>
					<span class="click" data-type="help" data-url="lg_custom_fields" data-link="https://help.thrivethemes.com/en/articles/4425882-how-to-add-a-custom-field-to-the-lead-generation-element" data-fn="openInstructionsLightbox"><?php tcb_icon( 'help-instructions' ); ?></span>
				</div>
				<div class="full">
					<a class="tcb-right click tve-lg-add-field" data-fn="addLGField">
						<i class="mr-5">
							<?php tcb_icon( 'plus-regular' ); ?>
						</i>
						<?php echo esc_html__( 'Add new', 'thrive-cb' ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="connection-controls mb-10" data-connection="custom-html">
			<?php echo esc_html__( 'HTML Code', 'thrive-cb' ); ?>
			<textarea id="tve_lead_generation_code" class="mt-5 mb-5" placeholder="<?php echo esc_html__( 'Insert your code here', 'thrive-cb' ); ?>"></textarea>
			<button class="tve-button blue long click" data-fn="generateForm">
				<?php echo esc_html__( 'Generate Form', 'thrive-cb' ); ?>
			</button>
			<hr>
			<div class="control-grid tve-custom-html-fields mt-5">
				<div class="label"><?php echo esc_html__( 'Form fields', 'thrive-cb' ); ?></div>
				<div class="full" data-tooltip="<?php echo esc_html__( 'Visible', 'thrive-cb' ); ?>" data-side="left">
					<?php tcb_icon( 'eye-light' ); ?>
				</div>
			</div>
		</div>
		<div class="tve-control" data-key="FieldsControl" data-initializer="getFieldsControl"></div>

		<div class="tve-lg-hidden-fields">
			<div class="control-grid">
				<div class="label"><?php echo esc_html__( 'Hidden fields', 'thrive-cb' ); ?></div>
				<div class="full" data-tooltip="<?php echo esc_html__( 'Hidden', 'thrive-cb' ); ?>" data-side="left">
					<?php tcb_icon( 'eye-light-slash' ); ?>
				</div>
			</div>
			<div class="tve-control" data-key="HiddenFieldsControl"></div>
		</div>

		<hr>

		<div id="lg-submit-options" class="skip-api no-service mb-5 click">
			<span><?php echo esc_html__( 'After successful submission', 'thrive-cb' ); ?></span>
			<div class="tve-lg-submit-options-wrapper mt-10 click" data-fn="changeSubmitOption">
				<div class="input">
					<a href="javascript:void(0)" class="click style-input flex-start dots">
						<span class="preview"></span>
						<span class="submit-value tcb-truncate t-80"></span>
						<span class="mr-5">
							<?php tcb_icon( 'pen-regular' ); ?>
						</span>
					</a>
				</div>
			</div>
			<div class="tve-lg-submit-option-control">
				<div id="lg-custom_url" data-key="redirect" class="lg-submit-options mt-10 tcb-hidden">
					<a class="click blue-text center-text tcb-hidden view-params" data-fn="showSentParams"><?php echo esc_html__( 'View variable details', 'thrive-cb' ); ?></a>
					<div class="tve-send-param-control"></div>
				</div>
				<div id="lg-success-message" data-key="message" class="lg-submit-options mt-10 tcb-hidden">
					<span>
						<?php echo esc_html__( 'Success message', 'thrive-cb' ); ?>
					</span>
					<div class="mt-10">
						<input type="text" class="change" data-fn="changeSuccessMsg" value=""/>
						<a href="javascript:void(0);" class="click" data-fn="previewSuccessMsg" data-tooltip="<?php echo esc_html__( 'Preview success message', 'thrive-cb' ); ?>" data-side="top"><?php tcb_icon( 'eye-light' ); ?></a>
					</div>
				</div>
				<div id="lg-state" class="lg-submit-options mt-10 tcb-hidden"></div>
			</div>
		</div>

		<div id="api-controls" class="mt-10">

			<div class="no-api tve-advanced-controls">
				<div class="dropdown-header" data-prop="advanced">
					<span>
						<?php echo esc_html__( 'Advanced', 'thrive-cb' ); ?>
					</span>
				</div>
				<div class="dropdown-content pt-0">
					<?php do_action( 'tcb_lead_generation_menu' ); ?>
					<div class="tve-control" data-view="FormIdentifier"></div>
					<div class="tve-lg-prevention">
						<p class="strong"><?php echo esc_html__( 'Spam prevention', 'thrive-cb' ); ?></p>
						<div class="tve-control" data-view="Captcha"></div>
						<a class="tcb-hidden info-link toggle-control mb-5" target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=tve_dash_api_connect' ) ); ?>">
							<span class="info-text"><?php echo esc_html__( 'Requires integration with Google ReCaptcha', 'thrive-cb' ); ?></span>
						</a>
					</div>
					<div class="no-api tcb-text-center mt-5">
						<button class="tve-button blue long click" data-fn="manage_error_messages">
							<?php echo esc_html__( 'Edit error messages', 'thrive-cb' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
