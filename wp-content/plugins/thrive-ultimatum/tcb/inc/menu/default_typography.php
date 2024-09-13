<div id="tve-default_typography-component" class="tve-component" data-view="DefaultTypography">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo esc_html__( 'Typography', 'thrive-cb' ); ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control hide-states" data-view="FontFace"></div>
		<div class="tve-control" data-view="FontColor"></div>
		<div class="tve-control hide-states" data-view="TextTransform"></div>
		<div class="tve-control hide-states" data-view="TextAlign"></div>
		<div class="tve-control" data-view="TextStyle"></div>
		<div class="btn-group-light typography-button-toggle-controls">
			<div class="tve-control hide-states" data-view="ToggleControls"></div>

			<div class="tve-control hide-states tcb-typography-toggle-element tcb-typography-font-size" data-view="FontSize"></div>
			<div class="tve-control hide-states tcb-typography-toggle-element tcb-typography-line-height" data-view="LineHeight"></div>
			<div class="tve-control hide-states tcb-typography-toggle-element tcb-typography-letter-spacing pb-10" data-view="LetterSpacing"></div>
		</div>
		<div class="line-spacing hide-states">
			<hr>
			<div class="tve-control" data-key="LineSpacing" data-initializer="lineSpacingControl"></div>
		</div>
		<div class="tcb-text-center clear-formatting mt-10">
			<hr class="hide-states">
			<span class="click tcb-text-uppercase clear-format custom-icon" data-fn="clear_formatting">
				<?php echo esc_html__( 'Clear element formatting', 'thrive-cb' ); ?>
			</span>
		</div>
	</div>
</div>
