<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/3/2017
 * Time: 10:03 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-numberedlist-component" class="tve-component" data-view="NumberedList">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="starting_number"></div>
		<div class="tve-control" data-view="increment_number"></div>
		<div class="tve-control" data-view="FontFace"></div>
		<div class="tve-control" data-view="item_spacing"></div>
		<div class="tve-control" data-key="preview" data-initializer="list_preview_control"></div>
		<div class="tve-button click whitey dashed" data-fn-click="add_list_item">
			<?php echo esc_html__( 'Add new', 'thrive-cb' ); ?>
		</div>
	</div>
</div>
