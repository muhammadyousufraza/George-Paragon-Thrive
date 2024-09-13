<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

[tvo_testimonial_dynamic_variables]
<div class="thrv_wrapper thrv_text_element tve-default-heading tcb_similar_edit">
	<h2>
	<span class="thrive-inline-shortcode">
		<span class="thrive-shortcode-content" data-shortcode="tvo_testimonial_title" data-shortcode-name="Testimonial title" data-option-inline="1">
			[tvo_testimonial_title inline=1]
		</span>
	</span>
	</h2>
</div>

<div class="thrv_wrapper tve_image_caption tcb-dynamic-field-source tcb_similar_edit" data-dynamic="featured">
	<span class="tve_image_frame">
		[tvo_testimonial_image data-d-f="featured"]
	</span>
</div>

<div class="thrv_wrapper thrv_text_element tve-draggable tve-droppable">
	<div class="tcb-plain-text tve-droppable">
		<span class="thrive-inline-shortcode" contenteditable="false">
			<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-shortcode="tvo_testimonial_author" data-option-inline="1">
				[tvo_testimonial_author inline=1]
			</span>
		</span>
	</div>
</div>

<div class="thrv_wrapper thrv_text_element tve-draggable tve-droppable">
	<div class="tcb-plain-text tve-droppable">
		<span class="thrive-inline-shortcode" contenteditable="false">
			<span class="thrive-shortcode-content"
				  contenteditable="false"
				  data-extra_key=""
				  data-shortcode="tvo_testimonial_role"
				  data-option-inline="1">
				[tvo_testimonial_role inline=1]
			</span>
		</span>
	</div>
</div>

<div class="thrv_wrapper thrv_text_element tve-draggable tve-droppable">
	<div class="tcb-plain-text tve-droppable">
		<span class="thrive-inline-shortcode" contenteditable="false">
			<span class="thrive-shortcode-content" contenteditable="false" data-attr-link="1" data-attr-rel="0" data-attr-target="0" data-extra_key="" data-option-inline="1" data-shortcode="tvo_testimonial_website" data-shortcode-name="Website">
				[tvo_testimonial_website inline=1]
			</span>
		</span>
	</div>
</div>

<div class="thrive-testimonial-content thrv_wrapper tve-draggable tve-droppable">
	<div class="thrv_wrapper thrv_text_element tve-draggable tve-droppable">
		<div class="tcb-plain-text tve-droppable">
		<span class="thrive-inline-shortcode" contenteditable="false">
			<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-option-inline="1" data-shortcode="tvo_testimonial_content" data-shortcode-name="Testimonial content">
				[tvo_testimonial_content inline=1]
			</span>
		</span>
		</div>
	</div>
</div>
