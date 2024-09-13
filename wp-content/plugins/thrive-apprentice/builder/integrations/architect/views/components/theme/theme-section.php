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

<div id="tve-theme_section-component" class="tve-component" data-view="ThemeSection">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Section Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="PageMap"></div>

		<div class="tve-control sep-bottom" data-view="LayoutVisibility"></div>

		<div class="section-select mt-5 mouseenter mouseleave" data-fn-mouseenter="mouseenter" data-fn-mouseleave="mouseleave"></div>

		<div class="tve-control sep-top" data-view="Visibility"></div>

		<div class="tve-control sep-top mt-5" data-view="SectionHeight"></div>

		<div class="tve-control mt-10" data-view="VerticalPosition"></div>

		<div class="tve-control sep-top mb-5" data-view="StretchBackground"></div>

		<div class="tve-control sep-top mt-5" data-view="InheritContentSize"></div>

		<div class="tve-control mt-5" data-view="ContentWidth"></div>

		<div class="tve-control sep-bot mt-5" data-view="StretchContent"></div>

		<div class="tve-control mb-5 mt-5" data-view="Position"></div>

		<div class="tve-control mt-5" data-view="MinWidth"></div>
	</div>
</div>
