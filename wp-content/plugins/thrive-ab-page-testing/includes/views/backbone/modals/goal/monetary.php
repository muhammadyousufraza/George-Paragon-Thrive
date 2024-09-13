<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div class="tvd-modal-content">
	<h3><?php echo __( 'Revenue Conversion Goal', 'thrive-ab-page-testing' ); ?></h3>

	<p><?php echo __( 'Each time a customer visits any of the pages listed below, the associated revenue is recorded', 'thrive-ab-page-testing' ); ?></p>

	<div class="thrive-ap-goal-pages"></div>
</div>

<div class="tvd-modal-footer">
	<div class="tvd-row">
		<div class="tvd-col tvd-center tvd-s12">
			<a href="javascript:void(0)" class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-modal-close">
				<?php echo __( 'OK', 'thrive-ab-page-testing' ); ?>
			</a>
		</div>
	</div>
</div>
