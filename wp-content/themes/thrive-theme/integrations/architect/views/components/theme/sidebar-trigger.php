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

<div id="tve-sidebar-trigger-component" class="tve-component" data-view="SidebarTrigger">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mb-5" data-view="ExpandedIcon"></div>
		<div class="tve-control mb-5" data-view="CollapsedIcon"></div>
		<div class="tve-control mb-5" data-view="IconColor"></div>
		<div class="tve-control mb-5" data-view="Size"></div>
	</div>
</div>
