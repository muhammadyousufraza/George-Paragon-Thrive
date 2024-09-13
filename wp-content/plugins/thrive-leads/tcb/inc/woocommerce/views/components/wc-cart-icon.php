<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-wc-cart-icon-component" class="tve-component" data-view="CartIcon">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Cart Icon', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control tve-choose-icon gl-st-icon-toggle-2" data-view="ModalPicker"></div>

		<div class="tve-control" data-view="color"></div>
		<div class="tve-control" data-view="size"></div>
	</div>
</div>
