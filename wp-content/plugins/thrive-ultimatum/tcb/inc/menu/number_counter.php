<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-number_counter-component" class="tve-component" data-view="NumberCounter">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="pb-10">
				<div class="tve-control" data-view="StartingValue"></div>
				<div class="tve-control" data-view="EndValue"></div>
				<hr>
				<div class="tve-control" data-view="DecimalPrecision"></div>
				<div class="tve-control" data-view="DecimalCharacter" style="display:none;"></div>
				<hr>
				<div class="tve-control" data-view="ThousandsDivider"></div>
				<hr>
				<div class="control-grid no-space">
					<label><?php echo __( 'Prefix/Suffix', 'thrive-cb' ) ?></label>
					<div class="tve-control fixed-45" data-view="Prefix"></div>
					<span>#</span>
					<div class="tve-control fixed-45" data-view="Suffix"></div>
				</div>
				<hr class="mt-10">
				<div class="tve-control" data-view="ShowLabel"></div>
				<div class="tve-control" data-view="LabelPosition" style="display: none"></div>
				<hr>
				<div class="tve-control" data-view="Size"></div>
				<hr>
				<div class="tve-control" data-view="Speed"></div>
				<div class="tve-control" data-view="CustomSpeed" style="display: none"></div>
				<div class="tve-button lightgreen control-grid center click" data-fn="previewAnimation">
					<span class="mr-5">
						<?php tcb_icon( 'eye-light' ); ?>
					</span>
					<span>
						<?php echo __( 'Preview animation', 'thrive-cb' ) ?>
					</span>
				</div>
			</div>
		</div>
	</div>
</div>
