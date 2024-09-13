<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div id="tve-thrive_comments-component" class="tve-component" data-view="CommentSection">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="mb-5 tcb-text-center">
			<button class="tve-button orange click" data-fn="editSectionElements">
				<?php echo __( 'Edit Elements', 'thrive-theme' ); ?>
			</button>
		</div>
		<div class="tve-advanced-controls mt-10">
			<div class="dropdown-header" data-prop="advanced">
				<span><?php echo __( 'Advanced', 'thrive-theme' ); ?></span>
				<i></i>
			</div>
			<div class="dropdown-content clear-top">
				<button class="tve-button blue long click" data-fn="manageErrorMsg">
					<?php echo __( 'Edit error messages', 'thrive-theme' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

