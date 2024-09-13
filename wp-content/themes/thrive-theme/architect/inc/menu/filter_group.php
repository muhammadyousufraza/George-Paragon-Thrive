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
<div id="tve-filter_group-component" class="tve-component" data-view="FilterGroup">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="VerticalSpace"></div>
		<div class="tve-control" data-view="HorizontalSpace"></div>
	</div>
</div>
