<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
} ?>

<div class="thrv_wrapper <?php echo THRIVE_THEME_BUTTON_CLASS; ?> <?php echo $attr['class']; ?>">
	<a href="#" class="tcb-button-link">
		<span class="tcb-button-texts">
			<span class="tcb-button-text thrv-inline-text">
				<?php echo $attr['label']; ?>
			</span>
		</span>
	</a>
</div>
