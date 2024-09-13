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

<div class="thrive-template-settings-notice">
	<h3>
		<?php echo __( 'Template Settings', 'thrive-theme' ); ?>
	</h3>

	<p>
		<?php echo __( 'You can modify the template that this content is using directly in the Thrive Architect post settings.', 'thrive-theme' ); ?>
	</p>
	<?php //todo link to the video needed here, will come back later ?>
	<a href="javascript:void()" style="display:none">
		<?php echo __( 'Show me how', 'thrive-theme' ); ?>
		<svg viewBox="0 0 576 512">
			<path d="M336.2 64H47.8C21.4 64 0 85.4 0 111.8v288.4C0 426.6 21.4 448 47.8 448h288.4c26.4 0 47.8-21.4 47.8-47.8V111.8c0-26.4-21.4-47.8-47.8-47.8zm189.4 37.7L416 177.3v157.4l109.6 75.5c21.2 14.6 50.4-.3 50.4-25.8V127.5c0-25.4-29.1-40.4-50.4-25.8z">
			</path>
		</svg>
	</a>
</div>
