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

<div id="tve-amp-settings-component" class="tve-component" data-view="AMPSettings">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Accelerated Mobile Pages (AMP)', 'thrive-theme' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mb-10" data-view="DisableAMP"></div>
	</div>
</div>
