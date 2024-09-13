<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-columns-component" class="tve-component" data-view="Columns">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>

		<div class="dropdown-content">
			<div class="tve-control" data-view="GutterWidth"></div>
			<div class="tve-control" data-view="MinHeight"></div>
			<div class="tve-control" data-view="VerticalPosition"></div>
			<hr>
			<div class="tve-control" data-view="ColumnsOrder"></div>
			<div class="tve-control" data-view="Wrap"></div>
			<span class="info-text grey-text"><?php echo esc_html__( 'If you enable wrapping, the columns will not be resizable anymore', 'thrive-cb' ); ?></span>

			<div class="tve-control pt-10" data-view="ColumnWidth"></div>
			<div class="tve-control" data-view="FullWidth"></div>
			<div class="click flex-center mt-10 tve-columns-reset" data-fn="resetLayout">
				<span class="mr-10"><?php tcb_icon( 'sync-regular' ); ?></span>
				<span><?php echo esc_html__( 'Reset column layout', 'thrive-cb' ); ?></span>
			</div>
		</div>
	</div>
</div>
