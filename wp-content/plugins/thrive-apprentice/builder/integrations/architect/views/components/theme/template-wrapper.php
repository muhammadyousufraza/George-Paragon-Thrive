<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-template-wrapper-component" class="tve-component" data-view="ThemeLayout">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">

		<div class="tve-control" data-view="PageMap"></div>

		<div class="tve-control" data-view="LayoutVisibility"></div>

		<hr class="mt-5">

		<div class="tve-currently-editing">
			<p><?php echo __( 'Currently editing', 'thrive-theme' ); ?></p>
			<h3><?php echo thrive_template()->title(); ?></h3>

			<div class="need-help">
				<?php Thrive_Views::svg_icon( 'question-circle-light' ); ?>
				<span>
					<?php echo __( 'Need help?', 'thrive-theme' ); ?>
					<a href="#"><?php echo __( 'Open the Quick Guide', 'thrive-theme' ); ?></a>
				</span>
			</div>
		</div>

		<div class="full-width-control mt-10 mb-10">
			<button class="click" data-fn="toggleFullWidth" data-boxed="1">
				<span class="boxed-icon button-icon" data-icon="boxed"></span>
				<?php echo __( 'Boxed', 'thrive-theme' ); ?>
			</button>

			<button class="click" data-fn="toggleFullWidth" data-boxed="0">
				<span class="button-icon" data-icon="full"></span>
				<?php echo __( 'Full Width', 'thrive-theme' ); ?>
			</button>
		</div>

		<div class="tve-control" data-view="ContentWidth"></div>
		<div class="tve-control" data-view="LayoutWidth"></div>
		<?php if ( Thrive_Architect_Utils::show_progress_bar() ) : ?>
			<div class="progress-indicator-container">
				<div class="tve-control" data-view="ProgressIndicator"></div>
				<div class="indicator-location tcb-hidden">
					<div class="progress-notice">
						<span class="label"><?php echo __( 'Progress indicator location', 'thrive-theme' ); ?></span>
						<div class="tooltip-container mouseenter mouseleave" data-fn-mouseenter="openTooltip" data-fn-mouseleave="closeTooltip">
							<span class="label-icon progress-tooltip"><?php tcb_icon( 'info-circle-solid' ); ?></span>
						</div>
					</div>
					<div class="progress-position-control tve-control mt-5" data-view="ProgressPosition"></div>
					<div class="mb-10 progress-position-notice info-text tcb-hidden"><?php echo __( 'Make sure header is set to sticky for the progress indicator to display on scroll', 'thrive-theme' ); ?></div>
					<div class="tve-control" data-view="ProgressBarColor"></div>
					<div class="tve-control mt-10" data-view="ProgressBarHeight"></div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
