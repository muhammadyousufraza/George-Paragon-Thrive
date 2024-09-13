<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 2/12/2019
 * Time: 4:13 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-contentblock-component" class="tve-component" data-view="ContentBlock">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ModalPicker"></div>
		<div class="tve-local-variables pt-10 hide-tablet hide-mobile"></div>
	</div>
</div>
