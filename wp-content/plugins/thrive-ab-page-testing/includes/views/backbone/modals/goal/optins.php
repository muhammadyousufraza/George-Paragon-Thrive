<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div class="tvd-modal-content">
	<h3><?php echo __( 'Subscription Conversion Goal', 'thrive-ab-page-testing' ); ?></h3>

	<div class="top-modal-pwsvg">
		<?php tcb_icon('subs'); ?>
		<p><?php echo __( 'A subscription to any form on your test variations is counted as a conversion', 'thrive-ab-page-testing' ); ?></p>
	</div>
</div>

<div class="tvd-modal-footer">
	<div class="tvd-row">
		<div class="tvd-col tvd-center tvd-s12">
			<a href="javascript:void(0)" class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-modal-close">
				<?php echo __( 'OK', 'thrive-ab-page-testing' ) ?>
			</a>
		</div>
	</div>
</div>
