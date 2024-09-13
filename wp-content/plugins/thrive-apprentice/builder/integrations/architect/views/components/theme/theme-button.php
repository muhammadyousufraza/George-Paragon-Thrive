<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div id="tve-<?php echo THRIVE_THEME_BUTTON_COMPONENT; ?>-component" class="tve-component hide-states" data-view="ThemeButton">
	<div class="text-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Main Options', 'thrive-theme' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="hide-states">
				<div class="tve-control" data-view="ButtonIcon"></div>
			</div>
			<div class="tve-control tcb-icon-side-wrapper pt-5" data-key="icon_side" data-view="ButtonGroup"></div>

			<div class="tve-control gl-st-button-toggle-1 pt-5 mb-10" data-view="ButtonGroup" data-key="ButtonSize"></div>
			<div class="tve-control gl-st-button-toggle-2 mb-10" data-view="ButtonGroup" data-key="Align"></div>
			<div class="tve-control gl-st-button-toggle-2 pt-5 mb-10" data-view="ButtonWidth"></div>
		</div>
	</div>
</div>
