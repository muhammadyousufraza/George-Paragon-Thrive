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
<div id="tve-certificate-component" class="tve-component" data-view="Certificate">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Certificate Options', 'thrive-apprentice' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="Title"></div>
		<div class="tve-control" data-view="Orientation"></div>
	</div>
</div>
