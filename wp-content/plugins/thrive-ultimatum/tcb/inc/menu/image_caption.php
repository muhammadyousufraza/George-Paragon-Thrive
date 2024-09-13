<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-image_caption-component" class="tve-component" data-view="ImageCaption">
	<div class="text-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="ImageCaptionPosition"></div>
			<div class="tve-control" data-view="CaptionVerticalPosition"></div>
			<div class="tve-control hide-states" data-view="TextAlign"></div>
			<div class="tve-control" data-view="FontColor"></div>
			<div class="tve-control hide-states" data-view="FontFace"></div>
			<div class="tve-control" data-view="FontBackground"></div>
			<div class="tve-control" data-view="TextStyle"></div>
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
