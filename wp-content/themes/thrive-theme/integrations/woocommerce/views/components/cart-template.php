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
<div id="tve-cart-template-component" class="tve-component" data-view="CartTemplate">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Cart Options', 'thrive-theme' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="center-xs col-xs-12 mb-10 edit-mode-hidden">
			<button class="tve-button orange click" data-fn="editTemplate">
				<?php echo __( 'Edit Design', 'thrive-theme' ); ?>
			</button>
		</div>
	</div>
</div>
