<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<div id="tve-pagination_button-component" class="tve-component" data-view="PaginationButton">
	<div class="text-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
		</div>
		<div class="dropdown-content">
			<div class="tve-control gl-st-button-toggle-1" data-view="MasterColor"></div>
			<div class="master_color_warning global-edit-warning tcb-hide">
				<?php echo esc_html__( 'Changing the Master Color will unlink the element from any global color/gradient which was applied on it previously.', 'thrive-cb' ); ?>
			</div>

			<div class="hide-states">
				<div class="tve-control" data-view="icon_layout"></div>
			</div>

			<div class="tve-control tcb-icon-side-wrapper tcb-hidden mt-10" data-key="icon_side" data-view="ButtonGroup"></div>

			<div class="tcb-hidden">
				<div class="tve-control" data-view="SecondaryText"></div>
				<div class="tve-control" data-view="ButtonIcon"></div>
				<div class="tve-control gl-st-button-toggle-1 no-space sep-top" data-key="ButtonSize" data-view="ButtonGroup"></div>
				<div class="tve-control pt-5 gl-st-button-toggle-2" data-key="Align" data-view="ButtonGroup"></div>
				<div class="tve-control gl-st-button-toggle-2" data-view="ButtonWidth"></div>
				<div class="tcb-button-link-container">
					<hr>
					<div class="btn-link mt-10"></div>
				</div>
			</div>
		</div>
	</div>
</div>
