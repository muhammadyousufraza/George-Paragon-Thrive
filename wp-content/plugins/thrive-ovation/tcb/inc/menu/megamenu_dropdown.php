<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-megamenu_dropdown-component" class="tve-component" data-view="MegamenuDropdown">
	<div class="dropdown-header component-name" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="Type"></div>
		<div class="tve-control" data-view="ColumnsNumber"></div>
		<div class="tve-control" data-view="HorizontalSpace"></div>
		<div class="tve-control" data-view="VerticalSpace"></div>
		<div class="tve-control hide-tablet hide-mobile" data-view="MaxWidth"></div>
	</div>
</div>
