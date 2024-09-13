<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<h3 class="post-content-modal-heading">
	<?php echo __( 'Are you Sure you want to Remove the Post Content Element?', 'thrive-theme' ); ?>
</h3>

<div class="post-content-delete-columns">
	<div class="post-content-delete-text">
		<p>
			<?php echo __( 'If you remove this element, it means no content will be shown in this template (this is the content you create in the WordPress editor or using Thrive Architect).', 'thrive-theme' ); ?>
		</p>

		<p class="sub-text">
			<?php echo __( 'Note: you can re-add the post content element at any time from the elements list.', 'thrive-theme' ); ?>
		</p>
	</div>

	<div class="post-content-delete-icon">
		<?php tcb_icon( 'post-content', false, 'sidebar', '' ); ?>
		<span class="card-title"><?php echo __( 'Post Content', 'thrive-theme' ); ?></span>
	</div>
</div>

<div class="post-content-delete-footer">
	<button class="ttb-left grey click" data-fn="close">
		<?php echo __( 'No, cancel', 'thrive-theme' ); ?>
	</button>

	<button class="ttb-right red click" data-fn="deletePostContentElement">
		<?php echo __( 'Yes, Remove it', 'thrive-theme' ); ?>
	</button>
</div>
