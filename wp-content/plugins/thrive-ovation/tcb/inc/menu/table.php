<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-table-component" class="tve-component" data-view="Table">
	<div class="table-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>

		<div class="dropdown-content">
			<div class="tve-control" data-key="cellpadding" data-view="Slider"></div>
			<div class="tve-control" data-key="valign"></div>
			<div class="tve-control" data-key="TextAlign"></div>
			<div class="tve-control" data-key="header_bg" data-view="ColorPicker"></div>
			<div class="tve-control" data-key="cell_bg" data-view="ColorPicker"></div>
			<div class="tve-control" data-key="HeaderTextColor" data-view="ColorPicker"></div>
			<div class="tve-control" data-key="BodyTextColor" data-view="ColorPicker"></div>
			<hr class="mb-5">
			<div class="tve-control mb-5" data-key="sortable" data-view="Checkbox"></div>
			<hr>
			<div class="hide-desktop hide-tablet">
				<div class="tve-control" data-key="mobile_table" data-view="Checkbox"></div>
				<span class="blue-text info-text">
					<?php echo esc_html__( 'This will apply some transformations on the table, making it responsive for mobile devices. Note that this will have unpredictable results if there are merged cells in the table.', 'thrive-cb' ); ?>
				</span>
				<hr>
				<div class="show-mobile-table">
					<div class="tve-control" data-key="mobile_header_width" data-view="Slider"></div>
				</div>
			</div>
			<div class="tcb-text-center hide-tablet hide-mobile mb-10">
				<button class="tve-button blue long click" data-fn="manage_cells"><?php echo esc_html__( 'Manage Cells', 'thrive-cb' ) ?></button>
			</div>
			<div class="tve-advanced-controls">
				<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo esc_html__( 'Advanced', 'thrive-cb' ); ?>
				</span>
				</div>

				<div class="dropdown-content pt-0">
					<div class="tve-control" data-key="even_rows" data-view="ColorPicker"></div>
					<div class="tve-control" data-key="odd_rows" data-view="ColorPicker"></div>
					<span class="click blue-text flex-mid pb-10" data-fn="clear_alternating_colors"><?php tcb_icon( 'close2' ); ?>
						&nbsp;<?php echo esc_html__( 'Clear Alternating Colors', 'thrive-cb' ); ?></span>
					<hr>
					<div class="control-grid">
						<button class="tve-button blue fill mr-5 click" data-fn="reset_widths"><?php echo esc_html__( 'Reset widths', 'thrive-cb' ) ?></button>
						<button class="tve-button blue fill ml-5 click" data-fn="reset_heights"><?php echo esc_html__( 'Reset heights', 'thrive-cb' ) ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
