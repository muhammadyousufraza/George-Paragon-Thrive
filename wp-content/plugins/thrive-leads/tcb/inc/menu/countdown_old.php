<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/12/2017
 * Time: 9:33 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$time_settings = tve_get_time_settings();
?>

<div id="tve-countdown_old-component" class="tve-component" data-view="CountdownOld">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="style" data-initializer="countdown_style_control"></div>
		<div class="tve-control" data-view="Color"></div>
		<div class="tve-control" data-view="ExternalFields"></div>
		<div class="custom-fields-state" data-state="static">
			<div class="tve-control" data-view="EndDate"></div>
			<div class="control-grid">
				<div class="label"><?php echo esc_html__( 'Time', 'thrive-cb' ); ?></div>
				<div class="input flex space-between">
					<div class="tve-control" data-view="Hour"></div>
					<div class="tve-control" data-view="Minute"></div>
				</div>
			</div>
		</div>
		<div class="control-grid">
			<span class="info-text grey-text fill mt-0"><?php echo esc_html__( 'Timezone', 'thrive-cb' ); ?> UTC <?php echo esc_html( $time_settings['tzd'] ); ?></span>
		</div>
		<div class="tve-control fill" data-view="CompleteText"></div>
	</div>
</div>

