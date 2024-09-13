<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<h2><?php echo esc_html__( 'Reset Template', 'thrive-dash' ); ?></h2>
<br>
<p>
	<?php echo esc_html__( 'Are you sure you want to reset the design ?', 'thrive-dash' ); ?>
	<?php echo esc_html__( 'You will lose any customizations made to it.', 'thrive-dash' ); ?>
</p>
<br><br>

<div class="ttd-modal-footer">
	<button class="tcb-left tve-button medium grey click" data-fn="close"><?php echo esc_html__( 'Cancel', 'thrive-dash' ); ?></button>
	<button class="tcb-right tve-button medium red click" data-fn="reset"><?php echo esc_html__( 'Reset Design', 'thrive-dash' ); ?></button>
</div>
