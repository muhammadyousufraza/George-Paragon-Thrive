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

<div id="tve-template-content-component" class="tve-component" data-view="ThemeLayout">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Layout Container', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="PageMap"></div>

		<div class="tve-control" data-view="LayoutVisibility"></div>

		<div class="full-width-control mt-10 mb-10">
			<button class="click" data-fn="toggleFullWidth" data-boxed="1">
				<span class="boxed-icon button-icon" data-icon="boxed"></span>
				<?php echo __( 'Boxed', 'thrive-theme' ); ?>
			</button>

			<button class="click" data-fn="toggleFullWidth" data-boxed="0">
				<span class="button-icon" data-icon="full"></span>
				<?php echo __( 'Full Width', 'thrive-theme' ); ?>
			</button>
		</div>

		<div class="tve-control" data-view="ContentWidth"></div>
		<div class="tve-control" data-view="LayoutWidth"></div>
	</div>
</div>
