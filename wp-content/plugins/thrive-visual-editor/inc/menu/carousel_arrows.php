<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-carousel_arrows-component" class="tve-component" data-view="CarouselArrows">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="hide-states pb-10">
			<div class="tve-control tve-choose-icon gl-st-icon-toggle-2" data-view="IconPicker"></div>
			<div class="tve-control" data-view="StylePicker" data-initializer="style"></div>
		</div>
		<div class="tve-control no-space gl-st-icon-toggle-1 pb-10" data-view="ColorPicker"></div>
		<div class="hide-states pb-10">
			<div class="tve-control gl-st-icon-toggle-1" data-view="Slider"></div>
		</div>
		<button class="tve-ghost-green-button click mb-5" data-fn="customizeArrows"><?php echo esc_html__( 'Customize arrow style', 'thrive-cb' ); ?></button>
	</div>
</div>
