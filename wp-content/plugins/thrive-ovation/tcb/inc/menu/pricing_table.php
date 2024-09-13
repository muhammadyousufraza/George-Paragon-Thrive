<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 6/27/2018
 * Time: 2:10 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-pricing_table-component" class="tve-component" data-view="PricingTable">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="PricingPalette"></div>
		<div class="tve-control" data-key="PriceInstances" data-initializer="preview_toggle_list_control"></div>
		<div class="tve-button click whitey dashed" data-fn-click="add_instance">
			<?php echo esc_html__( 'Add new instance', 'thrive-cb' ); ?>
		</div>
	</div>
</div>
