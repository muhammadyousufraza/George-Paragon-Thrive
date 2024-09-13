<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-column-component" class="tve-component" data-view="Column">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>

		<div class="dropdown-content">
			<div class="column-width mb-10">
				<div class="tve-control" data-view="FixedWidth"></div>
				<div class="tve-control" data-view="FullHeight"></div>
				<div class="tve-control" data-view="ColumnWidth"></div>
			</div>
			<div class="tve-control" data-view="VerticalPosition"></div>
		</div>
	</div>
</div>
