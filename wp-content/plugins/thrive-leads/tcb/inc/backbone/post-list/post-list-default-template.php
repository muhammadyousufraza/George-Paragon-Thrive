<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$data_attr = '';

foreach ( TCB_Post_List::default_args() as $key => $value ) {
	$data_attr .= 'data-' . $key . '="' . $value . '" ';
}

?>

<main class="tcb-post-list tcb-compact-element thrv_wrapper" <?php echo $data_attr; ?> data-element-name="Post List" draggable="true" data-css="tve-u-16b3297f569"><?php // phpcs:ignore ?>
	<article class="<?php echo esc_attr(TCB_POST_WRAPPER_CLASS); ?>">
		<a class="tcb-post-thumbnail tcb-shortcode thrv_wrapper tve-draggable tve-droppable tcb-selector-no_save tcb-post-list-shortcode" href="#" title="blank post 5" data-type-url="post_url" data-type-display="default_image" data-css="" data-size="medium" data-post_id="" draggable="true" data-shortcode="tcb_post_featured_image"></a>
		<div class="tcb-clear tcb-post-list-cb-clear">
			<div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box tcb-post-list-cb tve-draggable tve-droppable" draggable="true">
				<div class="tve-content-box-background"></div>
				<div class="tve-cb">
					<div class="thrv_wrapper thrv_text_element">
						<p>
							<span class="thrive-inline-shortcode" contenteditable="false"><span class="thrive-shortcode-content" contenteditable="false" data-attr-link="1" data-attr-rel="0" data-attr-target="0" data-extra_key="" data-option-inline="1" data-shortcode="tcb_post_categories" data-shortcode-name="List of Categories"><a href="#"></a></span></span>
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="thrv_wrapper thrv_text_element tve-default-heading">
			<h2>
				<span class="thrive-inline-shortcode" contenteditable="false"><span class="thrive-shortcode-content" contenteditable="false" data-attr-link="0" data-attr-rel="0" data-attr-target="0" data-extra_key="" data-option-inline="1" data-shortcode="tcb_post_title" data-shortcode-name="Post title"></span></span>
			</h2>
		</div>


		<section class="tcb-post-content tcb-shortcode thrv_wrapper tve-draggable tve-droppable tcb-selector-no_save tcb-post-list-shortcode" data-size="words" data-read_more="" data-words="12" draggable="true" data-shortcode="tcb_post_content"><p><a href="#" draggable="false"></a></p></section>

		<div class="tcb-clear tcb-post-read-more-clear">
			<div class="tcb-post-read-more thrv_wrapper tve-draggable tve-droppable" draggable="true">
				<a href="#" class="tcb-button-link tcb-post-read-more-link" draggable="false">
					<span class="tcb-button-texts">
						<span class="tcb-button-text thrv-inline-text"><?php echo esc_html__( 'Read More', 'thrive-cb' ); ?></span>
					</span>
				</a>
			</div>
		</div>
	</article>
</main>
