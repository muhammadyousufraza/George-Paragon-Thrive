<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div id="tve-notification-component" class="tve-component" data-view="Notification">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="non-edit-mode-controls">
			<div class="tcb-text-center mb-10 mr-5 ml-5">
				<button class="tve-button orange click" data-fn="editNotifications">
					<?php echo __( 'Edit design', 'thrive-cb' ); ?>
				</button>
			</div>
			<hr>
			<div class="tve-notification-spacing mb-10">
				<div class="tve-control mt-5 full-width" data-view="DisplayPosition"></div>
				<div class="tve-control mt-5" data-view="VerticalSpacing"></div>
				<div class="tve-control mt-5" data-view="HorizontalSpacing"></div>
			</div>
			<hr>
			<div class="tve-control mt-5" data-view="AnimationDirection"></div>
			<div class="tve-control mt-5" data-view="AnimationTime"></div>
		</div>
		<div class="edit-mode-controls">
			<div class="tve-control mt-5" data-view="MaximumWidth"></div>
			<div class="tve-control mt-5" data-view="MinimumHeight"></div>
			<div class="tve-control mt-5 mb-5" data-view="VerticalPosition"></div>
		</div>
	</div>
</div>
