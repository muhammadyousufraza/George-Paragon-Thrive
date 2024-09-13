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
<h2 class="tcb-modal-title">
	<?php echo esc_html__( 'Compose Email', 'thrive-cb' ); ?>
</h2>
<div class="tve-email-setup">
	<div class="tve-advanced-controls m-0">
		<div class="dropdown-header open" data-prop="primary">
			<span class="dropdown-title"><?php echo esc_html__( 'Primary Email', 'thrive-cb' ); ?></span>
		</div>
		<div class="dropdown-content pt-0 pb-0" data-prop="primary">
			<div class="input-container-with-label mt-5">
				<span class="tve-email-label"><?php echo esc_html__( 'To', 'thrive-cb' ); ?></span>
				<input type="text" class="tve-email-data change input" data-fn="setValue" data-prop="to">
				<span class="click tve-add-more ml-5" data-fn="toggleRecipients">+ CC/BCC</span>
			</div>
			<span class="tcb-error" data-error-prop="to"></span>
			<div class="tve-email-more-recipients tcb-hidden">
				<div class="input-container-with-label mt-5">
					<span class="tve-email-label"><?php echo esc_html__( 'CC', 'thrive-cb' ); ?></span>
					<input type="text" class="tve-email-data change input" data-fn="setValue" data-prop="cc">

				</div>
				<span class="tcb-error" data-error-prop="cc"></span>
				<div class="input-container-with-label mt-5">
					<span class="tve-email-label"><?php echo esc_html__( 'BCC', 'thrive-cb' ); ?></span>
					<input type="text" class="tve-email-data change input" data-fn="setValue" data-prop="bcc">
				</div>
				<span class="tcb-error" data-error-prop="bcc"></span>
			</div>
			<div class="input-container-with-label mt-5">
				<span class="tve-email-label"><?php echo esc_html__( 'From name', 'thrive-cb' ); ?></span>
				<input type="text" class="tve-email-data change input" data-fn="setValue" data-prop="from_name">
			</div>
			<span class="tcb-error" data-error-prop="from_name"></span>
			<div class="input-container-with-label mt-5">
				<span class="tve-email-label"><?php echo esc_html__( 'From email', 'thrive-cb' ); ?></span>
				<input type="text" class="tve-email-data change input" data-fn="setValue" data-prop="from_email">
			</div>
			<span class="tcb-error" data-error-prop="from_email"></span>
			<div class="input-container mt-15">
				<input type="text" class="tve-email-data change input prevent-focus" data-fn="setValue" data-prop="email_subject">
			</div>
			<div class="input-container mt-15">
				<textarea class="tve-email-data change input prevent-focus" data-fn="setValue" data-prop="email_message"></textarea>
			</div>

			<div class="tve-email-shortcodes mt-10 mb-10">
				<span class="tve-email-label mr-15"><?php echo esc_html__( 'Add shortcodes', 'thrive-cb' ); ?></span>
				<div class="tve-email-shortcode">
					<select class="tve-select-shortcode"></select>
					<span class="tve-lg-shortcode-select-arrow"><?php tcb_icon( 'a_down' ); ?></span>
				</div>
				<div class="tve-email-add-shortcode click tve-button ml-15 ghost blue" data-fn="addShortcode" data-target="email_message"><?php echo esc_html__( 'Insert Field', 'thrive-cb' ); ?></div>
			</div>
		</div>
	</div>
	<div class="tve-advanced-controls m-0 mt-15">
		<div class="dropdown-header" data-prop="confirmation">
			<span class="dropdown-title"><?php echo esc_html__( 'Send confirmation email to user that submitted the form', 'thrive-cb' ); ?></span>
			<div class="tve-email-enable-confirmation"></div>
		</div>
		<div class="dropdown-content pt-0 pb-0" data-prop="confirmation">
			<div class="input-container mt-5">
				<input type="text" class="tve-email-data change input prevent-focus" data-fn="setValue" data-prop="email_confirmation_subject">
			</div>
			<div class="input-container mt-15">
				<textarea class="tve-email-data change input prevent-focus" data-fn="setValue" data-prop="email_confirmation_message"></textarea>
			</div>
			<div class="tve-email-shortcodes mt-10 mb-10" data-prop="confirmation">
				<span class="tve-email-label mr-15"><?php echo esc_html__( 'Add shortcodes', 'thrive-cb' ); ?></span>
				<div class="tve-email-shortcode">
					<select class="tve-select-shortcode"></select>
					<span class="tve-lg-shortcode-select-arrow"><?php tcb_icon( 'a_down' ); ?></span></div>
				<div class="tve-email-add-shortcode click tve-button ml-15 ghost blue" data-fn="addShortcode" data-target="email_confirmation_message"><?php echo esc_html__( 'Insert Field', 'thrive-cb' ); ?></div>
			</div>
		</div>
	</div>
</div>

<div class="tcb-modal-footer">
	<button type="button" class="justify-self-start tve-button medium tcb-modal-cancel ghost grey"><?php echo esc_html__( 'Cancel', 'thrive-cb' ); ?></button>
	<button type="button" class="tcb-right tve-button medium tcb-modal-save">
		<?php echo esc_html__( 'Save and Apply', 'thrive-cb' ); ?>
	</button>
</div>
