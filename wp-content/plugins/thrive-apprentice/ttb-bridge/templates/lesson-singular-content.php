<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>
<div class="thrv_wrapper thrv_text_element">
	<p>
		<span class="thrive-shortcode-content" data-shortcode="tva_content_post_title" data-shortcode-name="Title" data-extra_key="">[tva_content_post_title]</span>
	</p>
</div>
<div class="thrv_wrapper tve_image_caption tcb-dynamic-field-source" data-tcb-events="">
	<span class="tve_image_frame">[tcb_dynamic_field type="featured" alt="Featured Image" title="Featured Image" data-classes="tve_image" data-css=""]</span>
</div>

<div class="thrv_wrapper thrv-columns">
	<div class="tcb-flex-row tcb--cols--2">
		<div class="tcb-flex-col">
			<div class="tcb-col">
				[tcb_post_published_date]
			</div>
		</div>
		<div class="tcb-flex-col">
			<div class="tcb-col">
				[tcb_post_author_name]
			</div>
		</div>
	</div>
</div>

[tcb_post_content size="content"]
