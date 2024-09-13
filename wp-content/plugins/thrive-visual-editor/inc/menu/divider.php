<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/20/2017
 * Time: 3:59 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-divider-component" class="tve-component" data-view="Divider">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="style" data-initializer="divider_style_control"></div>
		<div class="tve-control btn-group-light hide-states" data-view="ToggleColorControls"></div>
		<div class="tve-control tcb-divider-toggle-element tcb-divider-gradient-color" data-view="DividerGradient"></div>
		<div class="tve-control tcb-divider-toggle-element tcb-divider-solid-color" data-view="divider_color"></div>
		<div class="tve-control" data-view="thickness"></div>
	</div>
</div>
