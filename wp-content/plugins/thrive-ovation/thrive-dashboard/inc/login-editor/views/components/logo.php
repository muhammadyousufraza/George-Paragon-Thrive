<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<div id="tve-tvd-login-logo-component" class="tve-component" data-view="LoginLogo">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Logo', 'thrive-dash' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mt-5" data-view="ImagePicker"></div>

		<div class="col-xs-12 mb-10 mt-10 center-lg reset-logo">
			<button class="tve-button blue click" data-fn="resetLogo">
				<?php echo esc_html__( 'Reset Logo', 'thrive-dash' ); ?>
			</button>
		</div>

		<div class="tve-control mt-5" data-view="Size"></div>
	</div>
</div>
