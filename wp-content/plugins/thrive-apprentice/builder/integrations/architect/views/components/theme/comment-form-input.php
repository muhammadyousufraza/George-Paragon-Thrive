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

<div id="tve-comment-form-input-component" class="tve-component" data-view="CommentFormInput">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Comment Form Input', 'thrive-theme' ); ?>
		<i></i>
	</div>

	<div class="dropdown-content">
		<div class="tve-control" data-view="PlaceholderInput"></div>
		<hr>
		<div class="tve-control" data-view="TextareaHeight"></div>
	</div>
</div>
