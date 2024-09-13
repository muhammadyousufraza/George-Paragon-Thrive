<?php
/**
 * Backbone template for dynamic video overlay
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<div class="video_overlay"></div>

<div class="tve_responsive_video-no_video">
	<#= TVE.icon( 'video-player', 'svg', 'editor' ) #>
	<span class="tva-dynamic-video-label"><?php echo esc_html__( 'This video element will only show on courses where you have enabled a video description.', 'thrive-apprentice' ); ?></span>
</div>

<iframe style="display: none;"></iframe>
