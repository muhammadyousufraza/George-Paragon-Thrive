<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div id="tve-landingpage_section-component" class="tve-component" data-view="LandingpageSection">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Section Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="PageMap"></div>

		<div class="section-select mt-5"></div>

		<div class="tve-control sep-top" data-view="Visibility"></div>

		<div class="tve-control sep-top mt-5" data-view="SectionHeight"></div>

		<div class="tve-control mt-10" data-view="VerticalPosition"></div>

		<div class="tve-control sep-top mb-5" data-view="StretchBackground"></div>

		<div class="tve-control sep-top mt-5" data-view="InheritContentSize"></div>

		<div class="tve-control mt-5" data-view="ContentWidth"></div>

		<div class="tve-control sep-bot mt-5" data-view="StretchContent"></div>
	</div>
</div>
