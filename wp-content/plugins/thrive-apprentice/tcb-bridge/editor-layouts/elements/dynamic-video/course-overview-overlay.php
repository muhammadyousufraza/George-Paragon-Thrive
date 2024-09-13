<?php
/**
 * Server template for dynamic video elements
 * - used by the shortcode renderers in editors
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<div class="video_overlay"></div>

<div class="tve_responsive_video-no_video">
	<svg class="tcb-icon tcb-tcb-icon-video-player">
		<use xlink:href="#tcb-icon-video-player"></use>
	</svg>
	<span class="tva-dynamic-video-label"><?php echo esc_html__( 'This video element will only show on courses where you have enabled a video description.', 'thrive-apprentice' ); ?></span>
</div>

<iframe style="display: none;"></iframe>
