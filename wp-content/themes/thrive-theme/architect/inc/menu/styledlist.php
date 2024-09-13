<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/9/2017
 * Time: 10:20 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-styledlist-component" class="tve-component" data-view="StyledList">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ModalPicker"></div>
		<div class="tve-control" data-view="item_spacing"></div>
		<hr>
		<div class="control-grid">
			<span class="label"><?php echo esc_html__( 'Items', 'thrive-cb' ); ?></span>
			<div class="tve-button click blue" data-fn-click="add_list_item">
				<?php echo esc_html__( 'Add New', 'thrive-cb' ); ?>
			</div>
		</div>

		<div class="tve-control" data-key="preview" data-initializer="list_preview_control"></div>

	</div>
</div>
