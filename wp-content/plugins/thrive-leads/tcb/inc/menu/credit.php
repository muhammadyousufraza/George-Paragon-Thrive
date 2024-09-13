<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/28/2017
 * Time: 11:41 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-credit-component" class="tve-component" data-view="Credit">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="style" data-initializer="credit_style_control"></div>
		<div class="tve-control mt-20" data-key="monochrome_background" data-view="ColorPicker"></div>
		<div class="control-grid">
			<div class="label"><?php echo esc_html__( 'Cards', 'thrive-cb' ); ?></div>
			<button class="tve-button click" data-fn="open_cards"><?php echo esc_html__( 'Add new', 'thrive-cb' ); ?></button>
		</div>
		<div class="tve-control" data-key="preview" data-initializer="card_preview_control"></div>
		<div class="tve-control mt-10" data-key="size" data-view="Slider"></div>
	</div>
</div>
