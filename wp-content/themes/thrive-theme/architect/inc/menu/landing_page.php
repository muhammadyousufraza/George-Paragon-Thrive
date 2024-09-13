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
<div id="tve-landing_page-component" class="tve-component" data-view="LpBase">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="PageMap"></div>
		<hr>
		<div class="tve-control" data-view="ContentFullWidth"></div>
		<hr>
		<div class="tve-control width-setting" data-view="ContentWidth"></div>
		<div class="tve-control max-width-settings" data-view="ContentMaxWidth"></div>
	</div>
</div>
