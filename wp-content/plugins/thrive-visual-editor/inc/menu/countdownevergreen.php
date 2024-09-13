<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/16/2017
 * Time: 12:58 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-countdownevergreen-component" class="tve-component" data-view="CountdownEvergreen">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="style" data-initializer="countdown_style_control"></div>
		<div class="tve-control" data-view="Color"></div>
		<div class="control-grid no-space">
			<div class="label"><?php echo esc_html__( 'Time', 'thrive-cb' ); ?></div>
			<div class="input flex space-between">
				<span class="tve-control" data-view="Day"></span>
				<span class="tve-control" data-view="Hour"></span>
			</div>
		</div>
		<div class="control-grid no-space mt-5 pb-5">
			<div class="label">&nbsp;</div>
			<div class="input flex space-between">
				<span class="tve-control" data-view="Minute"></span>
				<span class="tve-control" data-view="Second"></span>
			</div>
		</div>

		<div class="tve-control no-space" data-view="StartAgain"></div>
		<div class="control-grid no-space tcb-hidden mt-5 tcb-start-again-control">
			<div class="label">&nbsp;</div>
			<div class="input flex space-between">
				<span class="tve-control" data-view="ExpDay"></span>
				<span class="tve-control" data-view="ExpHour"></span>
			</div>
		</div>
		<hr class="mt-10">
		<div class="tve-control fill" data-view="CompleteText"></div>
	</div>
</div>
