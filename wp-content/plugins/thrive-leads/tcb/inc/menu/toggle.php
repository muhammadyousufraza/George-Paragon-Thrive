<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-toggle-component" class="tve-component" data-view="Toggle" >
	<div class="action-group" >
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tcb-text-center mb-10 mr-5 ml-5">
				<button class="tve-button orange click" data-fn="editFormElements">
					<?php echo esc_html__( 'Edit Toggle Items', 'thrive-cb' ); ?>
				</button>
			</div>
			<div class="tve-control hide-states" data-view="TogglePalettes"></div>
			<div class="spacing">
				<div class="tve-control hide-tablet hide-mobile" data-view="ColumnNumber"></div>
				<div class="tve-control" data-view="ToggleWidth"></div>
				<div class="tve-control" data-view="VerticalSpace"></div>
				<div class="tve-control" data-view="HorizontalSpace"></div>
			</div>
			<div class="tve-control mt-10" data-view="AutoCollapse"></div>
			<div class="tve-control" data-view="DropdownAnimation"></div>
			<div class="tve-control" data-view="AnimationSpeed"></div>
			<hr>
			<div class="control-grid">
				<span class="label">
					<?php echo esc_html__( 'Toggles', 'thrive-cb' ); ?>
				</span>
				<button class="tve-button blue click" data-fn="addToggle"><?php echo esc_html__( 'Add New', 'thrive-cb' ); ?></button>
			</div>
			<div id="toggle-list" class="no-space"></div>
		</div>
	</div>
</div>
