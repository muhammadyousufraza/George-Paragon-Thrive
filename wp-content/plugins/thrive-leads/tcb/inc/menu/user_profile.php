<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>

<div id="tve-user_profile-component" class="tve-component" data-view="UserProfile">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center mb-10 mr-5 ml-5 user-profile-edit-mode-hidden">
			<button class="tve-button orange click" data-fn="editElement">
				<?php echo esc_html__( 'Edit design', 'thrive-cb' ); ?>
			</button>
		</div>
		<div class="tve-control" data-view="UserProfilePalette"></div>
		<div class="tve-control full-width" data-view="FieldsLabel"></div>
		<div class="tve-control mb-5" data-view="Width"></div>
		<div class="control-grid user-profile-edit-mode-hidden">
			<div class="label"><?php echo esc_html__( 'Form fields', 'thrive-cb' ); ?></div>
			<div class="full">
				<a class="tcb-right click tve-lg-add-field" data-fn="addField">
					<i class="mr-5">
						<?php tcb_icon( 'plus-regular' ); ?>
					</i>
					<?php echo esc_html__( 'Add new', 'thrive-cb' ); ?>
				</a>
			</div>
		</div>
		<div class="tve-control user-profile-edit-mode-hidden" data-view="FieldsControl" data-initializer="getFieldsControl"></div>

		<div id="lg-submit-options" class="skip-api no-service mb-5 click user-profile-edit-mode-hidden">
			<div id="lg-success-message" data-key="message" class="mt-10">
					<span>
						<?php echo esc_html__( 'Success message', 'thrive-cb' ); ?>
					</span>
				<div class="mt-10">
					<input type="text" class="change" data-fn="changeSuccessMsg" value=""/>
					<a href="javascript:void(0);" class="click" data-fn="previewSuccessMsg" data-tooltip="<?php echo esc_html__( 'Preview success message', 'thrive-cb' ); ?>" data-side="top"><?php tcb_icon( 'eye-light' ); ?></a>
				</div>
			</div>
		</div>
		<div class="tcb-text-center mt-5 user-profile-edit-mode-hidden">
			<button class="tve-button blue long click" data-fn="manageErrorMsg">
				<?php echo esc_html__( 'Edit error messages', 'thrive-cb' ); ?>
			</button>
		</div>
	</div>
</div>

