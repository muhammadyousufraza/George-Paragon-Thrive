<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/16/2018
 * Time: 3:56 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-checkout_form-component" class="tve-component" data-view="checkout_form">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Checkout Form Options', 'thrive-apprentice' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="FieldsControl" data-initializer="get_fields_control"></div>
	</div>
</div>

