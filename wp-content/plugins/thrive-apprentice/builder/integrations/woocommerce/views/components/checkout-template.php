<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div id="tve-checkout-template-component" class="tve-component" data-view="CheckoutTemplate">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Checkout Options', 'thrive-theme' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="center-xs col-xs-12 mb-10 edit-mode-hidden">
			<button class="tve-button orange click" data-fn="editTemplate">
				<?php echo __( 'Edit Design', 'thrive-theme' ); ?>
			</button>
		</div>
		<div class="tve-advanced-controls mt-10">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo __( 'Display options', 'thrive-theme' ); ?>
				</span>
			</div>
			<div class="dropdown-content">
				<div class="tve-control" data-key="CheckoutFields" data-initializer="getCheckoutFields"></div>
			</div>
		</div>
	</div>
</div>
