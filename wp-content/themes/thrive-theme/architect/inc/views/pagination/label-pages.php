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
	<?php echo esc_html__( 'Page ', 'thrive-cb' ); ?>
	<span class="thrive-inline-shortcode" contenteditable="false">
		<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-shortcode="tcb_pagination_current_page" data-shortcode-name="Current page number">
			1
		</span>
	</span>
	<?php echo esc_html__( ' of ', 'thrive-cb' ); ?>
	<span class="thrive-inline-shortcode" contenteditable="false">
		<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-shortcode="tcb_pagination_total_pages" data-shortcode-name="Total number of pages">
			8
		</span>
	</span>
</p>
