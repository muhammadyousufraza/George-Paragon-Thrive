<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>
<div id="tve-menu_item_image-component" class="tve-component" data-view="MenuItemImage">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ExternalFields"></div>
		<div class="tve-control custom-fields-state" data-state="static" data-view="ImagePicker"></div>
		<hr>
		<div class="tve-control" data-view="ImageSize"></div>
		<div class="tve-control" data-view="Height"></div>
	</div>
</div>

