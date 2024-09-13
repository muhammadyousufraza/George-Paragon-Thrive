<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-toggle_old-component" class="tve-component" data-view="ContentToggle" >
	<div class="action-group" >
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="HoverColor"></div>
			<div class="tve-control" data-view="ToggleTextColor"></div>
			<div class="tve-control" data-view="HoverTextColor"></div>
			<hr>
			<div class="control-grid">
				<span class="label">
					<?php echo esc_html__( 'Toggles', 'thrive-cb' ); ?>
				</span>
				<button class="tve-button blue click" data-fn="add_toggle"><?php echo esc_html__( 'Add New', 'thrive-cb' ); ?></button>
			</div>
			<div id="toggle-list" class="no-space"></div>
		</div>
	</div>
</div>
