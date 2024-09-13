<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<p class="tcb-pagination-label-content">
	<?php echo esc_html__( 'Showing ', 'thrive-cb' ); ?>
	<span class="thrive-inline-shortcode" contenteditable="false">
		<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-shortcode="tcb_pagination_current_posts" data-shortcode-name="Number of posts on this page">
			1-15
		</span>
	</span>
	<?php echo esc_html__( ' of ', 'thrive-cb' ); ?>
	<span class="thrive-inline-shortcode" contenteditable="false">
		<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-shortcode="tcb_pagination_total_posts" data-shortcode-name="Total number of posts">
			365
		</span>
	</span>
</p>
