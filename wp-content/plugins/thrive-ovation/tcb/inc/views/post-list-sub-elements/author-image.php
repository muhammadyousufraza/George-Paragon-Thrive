<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

global $post;
?>

<div class="thrv_wrapper tve_image_caption tcb-post-author-picture tcb-dynamic-field-source" data-css="<?php echo empty( $data['css'] ) ? '' : esc_attr( $data['css'] ) ?>">
	<span class="tve_image_frame">
		<a href="<?php echo empty( $post ) ? '' : esc_url( get_author_posts_url( $post->post_author ) ); ?>" rel="nofollow" class="tve-dynamic-link" dynamic-postlink="tcb_post_author_link">
			<img class="tve_image" width="240" data-d-f="author" alt="Author Image" src="<?php echo esc_url( TCB_Post_List_Author_Image::author_avatar() ); ?>" loading="lazy">
		</a>
	</span>
</div>
