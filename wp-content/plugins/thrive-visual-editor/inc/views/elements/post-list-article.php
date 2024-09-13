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

[tcb_post_featured_image]

<div class="tcb-clear tcb-post-list-cb-clear">
	<div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box tcb-post-list-cb">
		<div class="tve-content-box-background"></div>
		<div class="tve-cb">
			[tcb_post_categories]
		</div>
	</div>
</div>

[tcb_post_title]

[tcb_post_content]

<div class="tcb-clear tcb-post-read-more-clear">
	<div class="tcb-post-read-more thrv_wrapper">
		<a href="[tcb_post_the_permalink]" class="tcb-button-link tcb-post-read-more-link">
		<span class="tcb-button-texts">
			<span class="tcb-button-text thrv-inline-text">
				<?php echo esc_html__( 'Read More', 'thrive-cb' ); ?>
			</span>
		</span>
		</a>
	</div>
</div>
