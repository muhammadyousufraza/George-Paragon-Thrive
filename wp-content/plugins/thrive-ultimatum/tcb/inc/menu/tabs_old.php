<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-tabs_old-component" class="tve-component" data-view="TabContent">
	<div class="action-group" >
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control hide-mobile" data-view="TabLayout"></div>
			<div class="tve-control" data-view="DefaultTab"></div>
			<div class="control-grid">
				<span class="label">
					<?php echo esc_html__( 'Tabs', 'thrive-cb' ); ?>
				</span>
				<button class="tve-button blue click" data-fn="addTabs"><?php echo esc_html__( 'Add New', 'thrive-cb' ); ?></button>
			</div>
			<div id="tabs-list"></div>
			<div class="tve-control" data-view="TabsWidth"></div>
			<hr>
			<div class="tve-control" data-view="EditTabs"></div>
			<div class="tve-control" data-view="TabBackground"></div>
			<div class="tve-control" data-view="TabBorder"></div>
			<div class="tve-advanced-controls">
				<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo esc_html__( 'Advanced', 'thrive-cb' ); ?>
				</span>
				</div>

				<div class="dropdown-content pt-0">
					<div class="tve-control" data-view="ContentColor"></div>
					<div class="tve-control" data-view="ContentBorder"></div>
				</div>
			</div>
		</div>
	</div>
</div>

