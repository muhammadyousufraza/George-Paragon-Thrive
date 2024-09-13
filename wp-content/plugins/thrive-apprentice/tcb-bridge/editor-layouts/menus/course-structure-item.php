<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div id="tve-course-structure-item-component" class="tve-component" data-view="CourseStructureItem">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-apprentice' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ToggleIcon"></div>
		<div class="tve-control" data-view="ToggleExpandCollapse"></div>
		<div class="tve-control" data-view="VerticalPosition"></div>
		<div class="tve-control" data-view="Height"></div>
		<div class="tve-control" data-view="ToggleTypeIcon"></div>
	</div>
</div>
