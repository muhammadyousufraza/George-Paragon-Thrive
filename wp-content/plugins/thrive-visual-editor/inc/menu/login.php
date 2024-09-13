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

<div id="tve-login-component" class="tve-component" data-view="Login">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control hide-states" data-view="Palettes"></div>
		<div class="no-api tcb-text-center login-elem-text mb-10 mr-5 ml-5">
			<button class="tve-button orange click" data-fn="editFormElements">
				<?php echo esc_html__( 'Edit Form Elements', 'thrive-cb' ); ?>
			</button>
		</div>

		<div class="tve-control" data-key="formType" data-view="ButtonGroup"></div>
		<div class="tve-control" data-key="defaultState" data-view="Select"></div>

		<div class="tve-control tcb-icon-side-wrapper mt-10 tcb-login-align" data-key="Align" data-view="ButtonGroup"></div>
		<div class="tve-control tcb-icon-side-wrapper mt-10" data-view="FormWidth"></div>
		<hr>
		<?php esc_html_e( 'Submission action(s)', 'thrive-cb' ); ?>
		<div class="tve-advanced-controls hide-states skip-api no-service controls-login mt-5">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php esc_html_e( 'After successful login', 'thrive-cb' ); ?>
				</span>
			</div>

			<div class="dropdown-content pt-0 overflow-visible">
				<div class="tve-login-options-wrapper mt-10 click" data-fn="setAfterSubmitAction" data-state="login">
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
				<div class="login-post-submit login-submit-options"></div>
				<div class="tve-control success-message-switch" data-view="Switch" data-info="true" data-key="loginShowSuccess" data-iconside="top"
					 data-label="<?php esc_attr_e( 'Show success message', 'thrive-cb' ); ?>"
					 data-icontooltip="<?php echo esc_attr__( 'Your success message will be displayed after your nominated page is loaded', 'thrive-cb' ); ?>"></div>
				<div class="tve-control message-preview" data-view="LabelInputIcon" data-key="loginSuccessMessage" data-placeholder="Success" data-label="false"
					 data-icontooltip="<?php echo esc_attr__( 'Preview success message', 'thrive-cb' ); ?>" data-iconside="top"></div>
			</div>
		</div>
		<div class="tve-advanced-controls hide-states skip-api no-service controls-register mt-5">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php esc_html_e( 'After successful registration', 'thrive-cb' ); ?>
				</span>
			</div>

			<div class="dropdown-content pt-0 overflow-visible">
				<div class="tve-register-options-wrapper mt-10 click" data-fn="setAfterSubmitAction" data-state="register">
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
				<div class="register-post-submit login-submit-options"></div>
				<div class="tve-control success-message-switch" data-view="Switch" data-info="true" data-key="registerShowSuccess" data-iconside="top"
					 data-label="<?php esc_attr_e( 'Show success message', 'thrive-cb' ); ?>"
					 data-icontooltip="<?php echo esc_attr__( 'Your success message will be displayed after your nominated page is loaded', 'thrive-cb' ); ?>"></div>
				<div class="tve-control message-preview" data-view="LabelInputIcon" data-key="registerSuccessMessage" data-placeholder="Success" data-label="false"
					 data-icontooltip="<?php echo esc_attr__( 'Preview success message', 'thrive-cb' ); ?>" data-iconside="top"></div>
				<div class="tve-control form-values" data-view="Switch" data-key="sendFormValues" style="margin: 0 -2px" data-label="<?php esc_attr_e( 'Send form values to thank you page', 'thrive-cb' ); ?>"></div>
				<a class="click blue-text center-text view-params form-values" data-fn="showSentParams"><?php echo esc_html__( 'View variable details', 'thrive-cb' ); ?></a>
			</div>
		</div>
		<hr class="mt-10">

		<div class="tve-advanced-controls hide-states skip-api no-service mt-5">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php esc_html_e( 'Advanced', 'thrive-cb' ); ?>
				</span>
			</div>

			<div class="dropdown-content pt-0">
				<div class="tve-control" data-key="hideWhenLoggedIn" data-view="Switch"></div>
			</div>
		</div>
		<?php /*
		<div class="tve-control" data-view="PassResetUrl"></div> */ ?>
	</div>
</div>
