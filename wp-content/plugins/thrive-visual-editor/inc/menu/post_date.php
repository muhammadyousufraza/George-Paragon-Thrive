<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div id="tve-post_date-component" class="tve-component" data-view="PostDate">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Post Date Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="Type"></div>
		<div class="tve-control" data-view="DateFormatSelect"></div>

		<div class="control-grid tcb-date-format-input">
			<div class="tcb-label">
				<?php echo esc_html__( 'Format String', 'thrive-cb' ); ?>
				<span class="click tcb-post-date-tooltip-icon" data-fn="openTooltip">
					<?php tcb_icon( 'info-circle-solid' ); ?>
				</span>
			</div>
			<div class="tve-control" data-view="DateFormatInput"></div>
		</div>

		<div class="tve-control" data-view="ShowTimeFormat"></div>

		<div class="tve-control" data-view="TimeFormatSelect"></div>

		<div class="control-grid tcb-time-format-input">
			<div class="tcb-label">
				<?php echo esc_html__( 'Format String', 'thrive-cb' ); ?>
				<span class="click tcb-post-date-tooltip-icon" data-fn="openTooltip">
					<?php tcb_icon( 'info-circle-solid' ); ?>
				</span>
			</div>
			<div class="tve-control" data-view="TimeFormatInput"></div>
		</div>
	</div>
</div>
