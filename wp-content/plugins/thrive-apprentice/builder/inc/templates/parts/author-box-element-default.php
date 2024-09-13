[thrive_author_box]
<div class="thrv_wrapper thrv-columns">
	<div class="tcb-flex-row tcb--cols--2">
		<div class="c-25 tcb-flex-col">
			[tcb_post_author_picture]
			<div class="ui-resizable-handle ui-resizable-e"></div>
		</div>
		<div class="c-50 tcb-flex-col">
			<div class="tcb-col">
				<div class="thrv_wrapper thrv_text_element theme-author-header">
					<p>
						<?php echo __( 'About the Author', 'thrive-theme' ); ?>
					</p>
				</div>
				<div class="thrv_wrapper thrv_text_element tve-froala fr-box fr-basic">
					<p>
						<a href="[tcb_post_author_link]" data-dynamic-link="tcb_post_author_link" class="tve-dynamic-link tve-froala fr-basic" dynamic-postlink="tcb_post_author_link" target="_blank" rel="nofollow" style="outline: none; display: inline-block;">
							<span class="thrive-shortcode-content" data-extra_key="" data-option-inline="1" data-shortcode="tcb_post_author_name" data-shortcode-name="Author name">
								[tcb_post_author_name inline='1']
							</span>
						</a>
					</p>
				</div>
			</div>
			<div class="ui-resizable-handle ui-resizable-e"></div>
		</div>
		<div class="c-25 tcb-flex-col">
			<div class="tcb-col ">
				<div class="thrv_wrapper thrv_text_element theme-auth-follow">
					<p class="theme-paragraph">
						<?php echo __( 'Follow me', 'thrive-theme' ); ?>
					</p>
				</div>
				<div class="thrv_social_custom thrv_author_follow">
					<div class="tve_social_items tve_social_custom tve_style_1 tve_social_ib">
						<div class="tve_s_item tve_s_fb_share" data-s="fb_share" data-href="[thrive_author_follow_urls url='fb']">
							<a href="[thrive_author_follow_urls url='fb']" target="_blank">
								<span class="tve_s_icon"></span>
							</a>
						</div>
						<div class="tve_s_item tve_s_g_share" data-s="g_share" data-href="[thrive_author_follow_urls url='g']">
							<a href="[thrive_author_follow_urls url='g']" target="_blank">
								<span class="tve_s_icon"></span>
							</a>
						</div>
						<div class="tve_s_item tve_s_t_share" data-s="t_share" data-href="[thrive_author_follow_urls url='t']">
							<a href="[thrive_author_follow_urls url='t']" target="_blank">
								<span class="tve_s_icon"></span>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div class="ui-resizable-handle ui-resizable-e"></div>
		</div>
	</div>
	<div class="thrv_wrapper thrv_text_element theme-author-desc">
		<p>
			<span class="thrive-shortcode-content" data-extra_key="" data-option-inline="1" data-shortcode="tcb_post_author_bio" data-shortcode-name="Author bio">
				[tcb_post_author_bio inline='1']
			</span>
		</p>
	</div>
</div>
[/thrive_author_box]
