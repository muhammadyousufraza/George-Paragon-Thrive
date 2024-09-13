<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-toggle_title-component" class="tve-component" data-view="ToggleTitle">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control hide-states" data-view="TextTypeDropdown"></div>
			<div class="tve-control" data-view="DefaultState"></div>
			<div class="tve-control hide-states" data-view="ShowIcon"></div>
			<div class="tve-control hide-states" data-view="ModalPicker"></div>
			<div class="tve-control" data-view="IconColor"></div>
			<div class="tve-control hide-states" data-view="IconPlacement"></div>
			<div class="tve-control" data-view="IconSize"></div>
			<div class="tve-control show-state-expanded show-state-hover" data-view="RotateIcon"></div>
		</div>
	</div>
</div>
