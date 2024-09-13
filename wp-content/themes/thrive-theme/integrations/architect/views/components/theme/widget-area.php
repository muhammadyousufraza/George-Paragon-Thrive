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

<div id="tve-thrive_widget_area-component" class="tve-component" data-view="WidgetArea">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Widget Area', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mb-5" data-view="Orientation"></div>
		<div class="tve-control mb-5" data-view="Sidebars"></div>
		<div class="mb-5">
			<a href="<?php echo admin_url( 'widgets.php' ); ?>" class="info-text" target="_blank">
				<?php echo tcb_icon( 'info' ) ?>
				<?php echo __( 'Click here to edit your widget areas.', 'thrive-theme' ) ?>
			</a>
		</div>
	</div>
</div>
