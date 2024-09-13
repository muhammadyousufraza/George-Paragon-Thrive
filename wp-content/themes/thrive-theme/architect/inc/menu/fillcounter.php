<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-fillcounter-component" class="tve-component" data-view="FillCounter">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="hide-states pb-10">
				<div class="tve-control" data-view="CounterSize"></div>
				<div class="tve-control" data-view="ExternalFields"></div>
				<div class="tve-control custom-fields-state" data-state="static" data-view="FillPercent"></div>
			</div>
			<div class="tve-control" data-view="CircleColor"></div>
			<div class="tve-control" data-view="FillColor"></div>
			<div class="tve-control" data-view="InnerColor"></div>
			<div class="tve-control" data-view="DynamicPercent"></div>
		</div>
	</div>
</div>
