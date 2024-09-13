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

<div id="tve-wc-cart-count-component" class="tve-component" data-view="CartCount">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Cart Items Count', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="color"></div>
		<div class="tve-control" data-view="size"></div>
		<div class="tve-control" data-view="horizontal-position"></div>
		<div class="tve-control" data-view="vertical-position"></div>
	</div>
</div>
