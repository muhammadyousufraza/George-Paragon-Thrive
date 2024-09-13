<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-text-component" class="tve-component" data-view="Text">
	<div class="text-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
		</div>
		<div class="dropdown-content">
			<div class="tve-control btn-group-light hide-states" data-view="ToggleColorControls"></div>
			<div class="tve-control tcb-color-toggle-element tcb-text-solid-color" data-view="FontColor"></div>
			<div class="tve-control tcb-color-toggle-element tcb-text-gradient-color" data-view="FontGradient"></div>
			<div class="tve-control tcb-color-toggle-element tcb-text-gradient-color" data-view="FontBaseColor"></div>
			<div class="tve-control hide-states" data-view="FontFace"></div>
			<div class="tve-control" data-view="FontBackground"></div>

			<div class="tcb-highlights-advanced-tab-header click" data-fn="toggleAdvancedHighlightsTab">
				<?php echo esc_html__( 'Highlight Options', 'thrive-cb' ); ?>
			</div>
			<div class="tcb-highlights-advanced-tab-content mt-0 pt-0 mb-5 tcb-hidden">
				<div class="tve-control" data-view="HighlightType"></div>
				<div class="tve-control" data-view="HighlightStrokeWidth"></div>
				<div class="tve-control" data-view="HighlightPosition"></div>
				<hr class="tve-text-highlight-dasharray-divider tcb-hidden">
				<div class="tve-control" data-view="DasharrayLineLength"></div>
				<div class="tve-control" data-view="DasharrayGapLength"></div>
				<hr class="tve-text-highlight-animation-divider">
				<div class="tve-control" data-view="HighlightAnimation"></div>
				<div class="tve-control" data-view="HighlightAnimationDuration"></div>
				<div class="tve-control" data-view="HighlightAnimationDelay"></div>

				<div class="tve-control pb-10" data-view="DeviceHighlightStatus"></div>
			</div>

			<div class="tve-control mt-5" data-view="TextStyle"></div>
			<div class="tve-control hide-states" data-view="TextTransform"></div>
			<div class="tve-control btn-group-light hide-states" data-view="ToggleControls"></div>
			<div class="tve-control hide-states tcb-text-toggle-element tcb-text-font-size" data-view="FontSize"></div>
			<div class="tve-control hide-states tcb-text-toggle-element tcb-text-line-height" data-view="LineHeight"></div>
			<div class="tve-control hide-states tcb-text-toggle-element tcb-text-letter-spacing" data-view="LetterSpacing"></div>
			<div class="line-spacing hide-states">
				<hr>
				<div class="tve-control" data-key="LineSpacing" data-initializer="lineSpacingControl"></div>
			</div>
			<hr class="hide-states">
			<div class="tcb-text-center">
				<span class="click tcb-text-uppercase clear-format custom-icon" data-fn="clear_formatting">
					<?php echo esc_html__( 'Clear all formatting', 'thrive-cb' ); ?>
				</span>
			</div>

			<div class="tve-advanced-controls hide-states">
				<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo esc_html__( 'Advanced', 'thrive-cb' ); ?>
				</span>
				</div>

				<div class="dropdown-content pt-0">
					<div class="tve-control" data-key="typefocus" data-initializer="typefocus_control"></div>
					<div class="tve-control hide-in-theme" data-key="HeadingToggle"></div>
					<div class="tve-control hide-in-theme" data-key="HeadingRename"></div>
					<div class="tve-control full-width hide-in-theme" data-key="HeadingAltText"></div>
				</div>
			</div>
		</div>
	</div>
</div>
