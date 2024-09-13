const constants = {
	IDENTIFIER: '.thrive-display-testimonials',
	THRIVE_DISPLAY_TESTIMONIALS_SHORTCODE: 'thrive_display_testimonials',
	THRIVE_DISPLAY_TESTIMONIALS_CLASS: 'thrive-display-testimonials',
	THRIVE_DISPLAY_TESTIMONIALS_SHORTCODE_CLASS: 'thrive-display-testimonials-shortcode',
	thrive_display_testimonial_image_label: 'Testimonial Image',
	THRIVE_DISPLAY_TESTIMONIALS_TAGS_TAXONOMY: 'tvo_tags',
	htmlChangeActions: [
		/* Triggered when an icon is changed. Syncs icons inside the display testimonials  */
		'icon_element_changed',
		/* Sync after changing button icon */
		'tcb.button_icon.change',
		/* Sync after changing icon side */
		'tcb.button_icon_side.change',
		/* Sync after changing button secondary text */
		'tcb.button_secondary_text.change',
		/* Sync after toggling an image caption */
		'tcb.image.caption_change',
		/* Sync after changing the style of an image */
		'tcb.image.style_change',
		/* Sync after canceling the styles of an image */
		'tcb.image.style_cancel',
		/* Sync after changing image overlay */
		'tcb.image.overlay.change',
		/* Sync after changing the image 'Open full size on click' behavior */
		'tcb.image.open_full_size.changed',
		/* Sync after wrapping columns  */
		'tcb.columns_wrap.change',
		/* Sync after rendering CB placeholder  */
		'tcb.contentbox_placeholder.render',
		/* Sync after rendering a button style */
		'tcb.button_style_change',
		/* Sync after cancelling a button style  */
		'tcb.button_style_cancel',
		/* Sync after rendering a divider style */
		'tcb.divider_style.input',
		/* Sync after cancelling a divider style */
		'tcb.divider_style.cancel',
		/* Sync after changing the decoration type */
		'tcb.change_decoration',
		/* Sync after changing the responsive visibility ( from the desktop, tablet, mobile buttons in the 'Responsive' component ) */
		'tcb.responsive_visibility_changed',
		/* Sync after changing the ratio of a video */
		'tcb_action_aspect_ratio_changed',
		/* Sync after wrapping columns */
		'tcb.columns_wrap.change',
		/* Sync after changing the alignment */
		'tcb.layout.alignment_changed',
		/* Sync after changing the star rating value */
		'tcb.star_rating.changed',
	],
};

module.exports = constants;
