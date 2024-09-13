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

<div class="tcb-modal-header flex space-between">
	<span class="tcb-modal-title"><?php echo __( 'Filter courses displayed in the list', 'thrive-apprentice' ); ?></span>
	<div class="tva-counter-loading mt-10" id="tva-course-filtered-counter"><?php echo __( 'Searching', 'thrive-apprentice' ); ?>&hellip;</div>
</div>

<div class="p-40">
	<div class="flex tva-flex-elems">
		<div class="tva-flex-cs mr-10">
			<?php tcb_icon( 'filter-courses' ); ?>
			<?php echo __( 'Course selection', 'thrive-apprentice' ); ?>
			<span class="click tva-filter-clear" data-fn="resetSelectFilter"><?php tcb_icon( 'sync_light' ); ?><?php echo __( 'Reset filter', 'thrive-apprentice' ); ?></span>
			<select id="tva-course-list-course-selection" data-tva-field="terms"></select>
		</div>
		<div class="tva-flex-authors">
			<?php tcb_icon( 'filter-authors' ); ?>
			<?php echo __( 'Author(s)', 'thrive-apprentice' ); ?>
			<span class="click tva-filter-clear" data-fn="resetSelectFilter"><?php tcb_icon( 'sync_light' ); ?><?php echo __( 'Reset filter', 'thrive-apprentice' ); ?></span>
			<select id="tva-course-list-author-selection" data-tva-field="authors"></select>
		</div>
	</div>
	<div>
		<?php tcb_icon( 'filter-topics' ); ?>
		<?php echo __( 'Topics', 'thrive-apprentice' ); ?>
		<span class="click tva-filter-clear" data-fn="resetSelectFilter"><?php tcb_icon( 'sync_light' ); ?><?php echo __( 'Reset filter', 'thrive-apprentice' ); ?></span>
		<select id="tva-course-list-topics-selection" data-tva-field="topics"></select>
	</div>
	<div class="flex tva-flex-llp">
		<div class="mr-10">
			<div>
				<?php tcb_icon( 'filter-restricted' ); ?>
				<?php echo __( 'Restricted content levels', 'thrive-apprentice' ); ?>
			</div>
			<div class="tva-course-list-checkbox-wrapper" data-source="labels"></div>
		</div>
		<div class="mr-10">
			<div>
				<?php tcb_icon( 'filter-difficulty' ); ?>
				<?php echo __( 'Difficulty levels', 'thrive-apprentice' ); ?>
			</div>
			<div class="tva-course-list-checkbox-wrapper" data-source="levels"></div>
		</div>
		<div>
			<div>
				<?php tcb_icon( 'filter-progress' ); ?>
				<?php echo __( 'Course access and progress', 'thrive-apprentice' ); ?>
				<span class="tva-tooltip-parent">
					<?php tcb_icon( 'info-circle-solid' ); ?>
					<span class="tva-custom-tooltip">
						<?php echo esc_attr__( 'Course progress filters will show different results depending on the visitor and which courses they have access to.', 'thrive-apprentice' ); ?>
					</span>
				</span>
			</div>
			<div class="tva-course-list-checkbox-wrapper" data-source="progress" data-no-count="1"></div>
		</div>
	</div>
</div>

<div class="tcb-modal-footer">
	<button type="button" class="tcb-modal-cancel">
		<?php echo __( 'Cancel', 'thrive-apprentice' ); ?>
	</button>

	<button type="button" class="tcb-modal-save">
		<?php echo __( 'Save and Close', 'thrive-apprentice' ); ?>
	</button>
</div>
