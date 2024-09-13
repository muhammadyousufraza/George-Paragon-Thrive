<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div class="notifications-content-wrapper thrv_wrapper tcb-animated" data-ct="notification-0" data-ct-name="Default notification"  data-position="top-center" data-timer="3000" data-animation="down" data-state="success">
	<div class="notification-success notifications-content" style="--notification-color:rgb(74, 178, 93)">
		<div class="thrv_wrapper thrv-columns">
			<div class="tcb-flex-row v-2 tcb-desktop-no-wrap tcb-medium-no-wrap tcb-mobile-no-wrap">
				<div class="tcb-flex-col">
					<div class="tcb-col">
						<div class="thrv_wrapper thrv_icon tcb-icon-display tcb-local-vars-root" data-style-d="square_inverted">
							<svg class="tcb-icon" viewBox="0 0 512 512" data-id="icon-check-solid" data-name="">
								<path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
							</svg>
						</div>
					</div>
				</div>
				<div class="tcb-flex-col">
					<div class="tcb-col">
						<div class="thrv_wrapper thrv-notification_message">
							<?php echo __( 'Success message!', 'thrive-cb' ) ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="notification-warning notifications-content" style="--notification-color:rgb(243, 156, 15)">
		<div class="thrv_wrapper thrv-columns">
			<div class="tcb-flex-row v-2 tcb-desktop-no-wrap tcb-medium-no-wrap tcb-mobile-no-wrap">
				<div class="tcb-flex-col">
					<div class="tcb-col">
						<div class="thrv_wrapper thrv_icon tcb-icon-display tcb-local-vars-root" data-style-d="square_inverted">
							<svg class="tcb-icon" viewBox="0 0 512 512" data-id="icon-info-circle-solid" data-name="">
								<path d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path>
							</svg>
						</div>
					</div>
				</div>
				<div class="tcb-flex-col">
					<div class="tcb-col">
						<div class="thrv_wrapper thrv-notification_message">
							<?php echo __( 'Warning message!', 'thrive-cb' ) ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="notification-error notifications-content" style="--notification-color:rgb(214, 54, 56)">
		<div class="thrv_wrapper thrv-columns">
			<div class="tcb-flex-row v-2 tcb-desktop-no-wrap tcb-medium-no-wrap tcb-mobile-no-wrap">
				<div class="tcb-flex-col">
					<div class="tcb-col">
						<div class="thrv_wrapper thrv_icon tcb-icon-display tcb-local-vars-root" data-style-d="square_inverted">
							<svg class="tcb-icon" viewBox="0 0 24 24" data-id="icon-alert-solid" data-name="">
								<path d="M13,14H11V10H13M13,18H11V16H13M1,21H23L12,2L1,21Z"></path>
							</svg>
						</div>
					</div>
				</div>
				<div class="tcb-flex-col">
					<div class="tcb-col">
						<div class="thrv_wrapper thrv-notification_message">
							<?php echo __( 'Error message!', 'thrive-cb' ) ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>