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
<div id="tve-theme-hf-component" class="tve-component" data-view="ThemeHF">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="PageMap"></div>

		<div class="tve-control sep-bottom" data-view="LayoutVisibility"></div>

		<div class="section-select mt-5 sep-bottom mouseenter mouseleave" data-fn-mouseenter="mouseenter" data-fn-mouseleave="mouseleave"></div>

		<div class="default-section-options">
			<div class="tve-control mb-5 mt-10" data-view="Visibility"></div>

			<div class="tve-control mb-5 sep-top" data-view="StretchBackground"></div>

			<div class="tve-control mb-5 sep-top" data-view="InheritContentSize"></div>

			<div class="tve-control mb-5" data-view="ContentWidth"></div>

			<div class="tve-control mt-5" data-view="StretchContent"></div>
		</div>

		<div class="section-edit-options">
			<div class="tve-control mb-5" data-view="HeaderPosition"></div>

			<div class="tve-control mb-5" data-view="VerticalPosition"></div>

			<div class="tve-control mb-5" data-view="SectionHeight"></div>
		</div>
	</div>
</div>
