<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>
<div id="tve-rating-component" class="tve-component" data-view="Rating">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ExternalFields"></div>
		<div class="hide-states">
			<div class="tve-control  custom-fields-state" data-state="static" data-key="ratingValue" data-initializer="rating_value_control"></div>
			<div class="tve-control pb-10" data-key="style" data-initializer="rating_style_control"></div>
		</div>
		<div class="tve-control" data-key="fill" data-view="ColorPicker"></div>
		<div class="tve-control" data-key="background" data-view="ColorPicker"></div>
		<div class="tve-control" data-key="outline" data-view="ColorPicker"></div>
		<div class="tve-control" data-key="size" data-view="Slider"></div>
	</div>
</div>
