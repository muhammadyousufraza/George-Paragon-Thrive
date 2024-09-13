<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/27/2017
 * Time: 10:13 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-postgrid-component" class="tve-component" data-view="PostGrid">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="flex-mid pb-10">
			<button class="tve-button orange click" data-fn="edit_grid_options"><?php echo esc_html__( 'Edit Grid Options', 'thrive-cb' ); ?></button>
		</div>
		<hr>
		<div class="tve-control no-space" data-extends="Tabs" data-key="tabs" data-target=".tabs-options"></div>
		<div class="tabs-options no-space pb-10">
			<div class="tve-control" data-view="img_height"></div>
			<div class="tve-control" data-view="title_font_size"></div>
			<div class="tve-control" data-view="title_line_height"></div>
		</div>
		<hr>
		<div class="tve-control" data-view="read_more"></div>
		<div class="tve-control" data-view="read_more_color"></div>
	</div>
</div>
