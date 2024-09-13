<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div id="tve-ovation_capture-component" class="tve-component" data-view="ovation_capture">
	<div class="text-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Capture Testimonials', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tcb-text-center">
				<button class="tve-button blue click" data-fn="change_template">
					<?php echo __( 'Change Template', 'thrive-cb' ) ?>
				</button>
			</div>

			<hr>

			<div class="tcb-text-center">
				<button class="tve-button blue click" data-fn="form_settings">
					<?php echo __( 'Form Settings', 'thrive-cb' ) ?>
				</button>
			</div>

			<hr>
			<div class="tve-control" data-view="ButtonColor"></div>
			<hr>
		</div>
	</div>
</div>
