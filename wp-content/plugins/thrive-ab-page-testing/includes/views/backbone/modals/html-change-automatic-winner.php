<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/14/2017
 * Time: 1:06 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div class="tvd-modal-content">

	<p><?php echo __( 'Here you can set the conditions by which a variation will be considered the winner. When a variation is considered the winner, the remaining variations will be archived and all traffic will be sent to the winner.', 'thrive-ab-page-testing' ) ?></p>
	<br>
	<?php include dirname( __FILE__ ) . '/html-test-automatic-winner.php'; ?>
</div>
<div class="tvd-modal-footer">
	<div class="tvd-row">
		<div class="tvd-col tvd-s6">
			<a href="javascript:void(0)" class="tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-waves-effect tvd-modal-close">
				<?php echo __( 'Cancel', 'thrive-ab-page-testing' ) ?>
			</a>
		</div>
		<div class="tvd-col tvd-s6">
			<a href="javascript:void(0)" class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-right tvd-modal-submit">
				<?php echo __( 'Save Settings', 'thrive-ab-page-testing' ) ?>
			</a>
		</div>
	</div>
</div>
