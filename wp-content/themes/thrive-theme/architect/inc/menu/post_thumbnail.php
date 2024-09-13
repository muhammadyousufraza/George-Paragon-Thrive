<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-post_thumbnail-component" class="tve-component" data-view="PostThumbnail">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Featured Image Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="type_url"></div>
		<?php tcb_template( 'post-list-linked-article-notice' ) ?>
		<div class="tve-control" data-key="type_title"></div>
		<?php tcb_template( 'post-list-linked-article-notice' ) ?>
		<div class="tve-control mt-5" data-key="type_display"></div>
		<div class="tve-control" data-key="size"></div>
		<div class="tve-control" data-view="ImageSize"></div>
	</div>
</div>
