<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div id="tve-ovation_display-component" class="tve-component" data-view="ovation_display">
	<div class="text-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Display Testimonials', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tcb-text-center">
				<button class="tve-button blue click" data-fn="change_template">
					<?php echo __( 'Change Template', 'thrive-cb' ) ?>
				</button>
			</div>

			<hr class="mt-10">

			<div class="tcb-text-center">
				<button class="tve-button blue click" data-fn="display_settings">
					<?php echo __( 'Display Settings', 'thrive-cb' ) ?>
				</button>
			</div>

			<hr class="mt-10">
			<div class="tve-control" data-view="BackgroundColor"></div>
			<div class="tve-control" data-view="BorderColor"></div>
			<div class="tve-control" data-view="TitleColor"></div>
			<div class="tve-control" data-view="TextColor"></div>
			<div class="tve-control" data-view="QuoteColor"></div>
			<div class="tve-control" data-view="NameColor"></div>
			<div class="tve-control" data-view="RoleColor"></div>
			<div class="tve-control" data-view="InfoBackgroundColor"></div>
			<div class="tve-control" data-view="InfoBorderColor"></div>
			<div class="tve-control" data-view="QuoteBackground"></div>
			<div class="tve-control" data-view="SeparatorBackground"></div>
			<div class="tve-control" data-view="ImageBorderColor"></div>
			<div class="tve-control" data-view="ArrowsColor"></div>
			<hr>
		</div>
	</div>
</div>
