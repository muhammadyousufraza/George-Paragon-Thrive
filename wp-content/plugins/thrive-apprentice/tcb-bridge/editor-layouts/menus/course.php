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
<div id="tve-course-component" class="tve-component" data-view="Course">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Course Options', 'thrive-apprentice' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center">
			<button class="tve-button orange mb-10 click" data-fn="editCourse">
				<?php echo __( 'Edit Design', 'thrive-apprentice' ); ?>
			</button>
		</div>
		<hr>
		<div class="tve-control hide-states" data-view="Palettes"></div>
		<div class="control-grid">
			<div class="label">
				<?php echo __( 'Change course', 'thrive-apprentice' ); ?>
			</div>
		</div>
		<div class="tve-control mb-10" data-view="changeCourse"></div>
		<div class="tva-autodetect-course-structure-wrapper" style="display: none;">
			<div class="tve-control mt-10" data-view="AutoCourseStructure"></div>
			<div class="tva-auto-course-structure-notice info-text blue mt-0 mb-15">
				<?php echo __( 'The course display level will be automatically set based on the course content viewed.', 'thrive-apprentice' ); ?>
			</div>
		</div>
		<div class="tva-display-level-wrapper">
			<div class="control-grid mt-10">
				<div class="label">
					<?php echo __( 'Course display level', 'thrive-apprentice' ); ?>
				</div>
			</div>
			<div class="tve-control mb-10" data-view="displayLevel"></div>
		</div>
		<div class="tve-control mb-10" data-view="ToggleModule"></div>
		<div class="control-grid">
			<div class="label">
				<?php echo __( 'Allow the following to be collapsed', 'thrive-apprentice' ); ?>
				<span id="tva-apprentice-lesson-list-module-info-tooltip" data-side="top" data-tooltip="<?php echo __( 'Modules cannot be collapsed if ‘Hide module header’ has been enabled.', 'thrive-apprentice' ) ?>" class="blue-text">
					<?php tcb_icon( 'info' ); ?>
				</span>
			</div>
		</div>
		<div class="tve-control mb-10" data-view="AllowCollapsed"></div>
		<div class="default-state-toggle">
			<div class="control-grid">
				<div class="label">
					<?php echo __( 'Default state', 'thrive-apprentice' ); ?>
					<span id="tva-apprentice-lesson-list-default-state-info-tooltip" data-side="top" data-tooltip="<?php echo __( 'The collapsed state will be ignored on courses that do not have a collapsible parent, such as lesson only courses with no chapters or modules', 'thrive-apprentice' ) ?>" class="blue-text">
						<?php tcb_icon( 'info' ); ?>
					</span>
				</div>
			</div>
			<div class="tve-control" data-view="DefaultState"></div>
			<div class="tve-control mt-10" data-view="AutoCollapse"></div>
			<div class="tve-control mt-10" data-view="ShowAssessments"></div>
		</div>
		<div id="tva-course-message" class="mt-10"></div>
	</div>
</div>
