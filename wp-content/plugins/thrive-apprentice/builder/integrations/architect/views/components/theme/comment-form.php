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

<div id="tve-comment-form-component" class="tve-component" data-view="CommentForm">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Comment Form', 'thrive-theme' ); ?>
		<i></i>
	</div>

	<div class="dropdown-content">
		<div class="tve-control" data-key="FieldsControl" data-initializer="getFieldsControl"></div>
		<hr class="control-add-remove-label-hr">
		<div class="tve-control" data-view="AddRemoveLabels"></div>
	</div>
</div>
