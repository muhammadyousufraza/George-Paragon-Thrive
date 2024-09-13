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

<div id="tve-mini-cart-component" class="tve-component" data-view="MiniCart">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Mini Cart Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="center-xs col-xs-12 mb-10 hide-states">
			<button class="tve-button orange mini-cart-edit-mode click" data-fn="enterEditMode">
				<?php echo esc_html__( 'Edit Design', 'thrive-cb' ); ?>
			</button>
		</div>
		<hr class="mini-cart-edit-mode">
		<div class="tve-control" data-view="color"></div>
		<div class="tve-control hide-states" data-view="align"></div>
		<div class="tve-control hide-states" data-view="size"></div>
		<div class="tve-control hide-states" data-view="cart-type"></div>
		<div class="tve-control hide-states" data-view="cart-text"></div>
		<div class="tve-control hide-states" data-view="trigger"></div>
		<div class="tve-control hide-states" data-view="direction"></div>
		<div class="tve-control hide-states" data-view="icon-position"></div>
	</div>
</div>
