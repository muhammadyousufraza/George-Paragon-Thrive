<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<div class="thrv_wrapper tve-number-counter tcb-plain-text tcb-label-bottom" data-label-position="tcb-label-bottom" data-label="true">
	<span class="thrv-inline-text tve-number-label tcb-plain-text tcb-label-top"><?php echo __( 'Customers served!', 'thrive-cb' ); ?></span>
	<span class="tve-number-wrapper tcb-plain-text" data-anim="ticker" data-refresh-interval="10" data-from="100" data-formatted-from="100" data-to="15000" data-final-number="15,000" data-decimals="0" data-thousand-divider="," data-decimal-character="." data-speed="2507">
		<span class="tve-number-prefix tve-mini-label tcb-plain-text thrv-inline-text"></span>
		<span class="thrv-inline-text tve-number">15,000</span>
		<span class="tve-number-suffix tve-mini-label tcb-plain-text thrv-inline-text"></span>
	</span>
	<span class="thrv-inline-text tcb-plain-text tve-number-label tcb-label-bottom"><?php echo __( 'Customers served!', 'thrive-cb' ); ?></span>
</div>
