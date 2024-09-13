<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-click-tweet-component" class="tve-component" data-view="Tweet">
	<div class="borders-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="LabelText"></div>
			<div class="tve-control fill" data-view="TweetText"></div>
			<div class="tve-twitter-count"></div>
			<div class="tve-control tve-tweet-via-control" data-view="ViaUsername"></div>
			<div class="tve-control pb-5" data-view="ShareUrlCheckbox"></div>
			<div class="tve-control label-hidden" data-view="ShareUrlInput"></div>
			<div class="tcb-text-right">
				<button class="tve-button click blue" data-fn="preview"><?php echo esc_html__( 'Preview', 'thrive-cb' ); ?></button>
			</div>
		</div>
	</div>
</div>
