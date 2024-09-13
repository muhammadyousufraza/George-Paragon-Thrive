<?php
if ( empty( $tie_image_url ) ) {
	$tie_image_url = tqb()->plugin_url( 'tcb-bridge/assets/images/share-badge-default.png' );
}
?>
<div data-css="tve-u-16d3eaaa219" class="thrv_wrapper tve-tqb-page-type tqb-result-template-3 tve_editor_main_content" style="<?php echo esc_attr( $main_content_style ); ?>">
	<div class="thrv_wrapper thrv_heading tve-draggable tve-droppable" data-css="tve-u-15d9cf3d12a">
		<h2 data-default="Your Heading Here">Congratulations!</h2>
	</div>
	<div class="tqb-social-share-badge-container tcb-no-url thrv_wrapper thrv_social">

		<div class="tve_social_items tqb_social_style_1 tve_social_custom tve_style_4 tve_social_itb" data-counts="" data-min_shares="0">
			<div class="tve_s_item tve_s_fb_share" data-s="fb_share" data-href="{tcb_post_url}"
				 data-image="<?php echo esc_url( $tie_image_url ); ?>"
				 data-description="<?php echo esc_attr( Thrive_Quiz_Builder::QUIZ_RESULT_SOCIAL_MEDIA_MSG ); ?>">
				<a href="javascript:void(0)" class="tve_s_link"><span class="tve_s_icon"></span><span class="tve_s_text">Share</span><span class="tve_s_count">0</span></a>
			</div>
			<div class="tve_s_item tve_s_t_share" data-s="t_share" data-href="{tcb_post_url}"
				 data-tweet="<?php echo esc_attr( Thrive_Quiz_Builder::QUIZ_RESULT_SOCIAL_MEDIA_MSG ); ?>">
				<a href="javascript:void(0)" class="tve_s_link"><span class="tve_s_icon"></span><span class="tve_s_text">Tweet</span><span class="tve_s_count">0</span></a>
			</div>
			<div class="tve_s_item tve_s_pin_share" data-s="pin_share" data-href="{tcb_post_url}" data-label="Share">
				<a href="javascript:void(0)" class="tve_s_link"><span class="tve_s_icon"></span><span class="tve_s_text">Share</span><span class="tve_s_count">0</span></a>
			</div>
		</div>

		<img src="<?php echo esc_url( $tie_image_url ); ?>">

		<div class="tve_social_overlay"></div>
	</div>
</div>
