<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div id="tve-menu_hamburger_wrapper-component" class="tve-component" data-view="MenuBox">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="BoxWidth"></div>

			<div class="tve-control mb-5" data-view="HorizontalPosition"></div>

			<div class="tve-control" data-view="BoxHeight"></div>

			<div class="tve-control mb-5" data-view="VerticalPosition"></div>
		</div>
	</div>
</div>
