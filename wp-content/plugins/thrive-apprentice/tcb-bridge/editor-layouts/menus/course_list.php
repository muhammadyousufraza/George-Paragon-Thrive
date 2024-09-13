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
<div id="tve-course_list-component" class="tve-component" data-view="CourseList">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Course List Options', 'thrive-apprentice' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="row sep-bottom tcb-text-center post-list-actions">
			<div class="col-xs-12">
				<button class="tve-button orange click" data-fn="courseListEditMode"><?php echo __( 'Edit Design', 'thrive-apprentice' ); ?></button>
				<button class="tve-button grey click margin-left-20 tcb-relative" data-fn="filterPosts"><?php echo __( 'Filter Courses', 'thrive-apprentice' ); ?></button>
			</div>
		</div>

		<div class="tve-control mt-10 hide-tablet hide-mobile full-width" data-view="Type"></div>

		<div class="tve-control no-space mt-10 hide-tablet hide-mobile" data-view="TopicFilter"></div>
		<div class="tve-control no-space mt-10 hide-tablet hide-mobile" data-view="CourseSearch"></div>

		<div class="tve-control mt-5" data-view="ColumnsNumber"></div>

		<div class="tve-control sep-top" data-view="HorizontalSpace"></div>
		<div class="tve-control mt-5 mb-5 no-space sep-bottom" data-view="VerticalSpace"></div>

		<div class="tve-control hide-tablet hide-mobile" data-view="PaginationType"></div>
		<div class="tve-control sep-bottom no-space post-list-actions" data-view="NumberOfItems"></div>

		<div class="tve-control no-space hide-tablet hide-mobile" data-view="Linker"></div>
		<div class="info-text orange hide-tablet hide-mobile">
			<?php echo __( 'This option disables all animations for this element and all child link options', 'thrive-apprentice' ); ?>
		</div>
		<div class="tve-advanced-controls extend-grey mt-5">
			<div class="dropdown-header" data-prop="advanced">
				<span><?php echo __( 'Advanced', 'thrive-apprentice' ); ?></span>
			</div>
			<div class="dropdown-content pt-0">
				<?php echo __( 'No results message', 'thrive-apprentice' ); ?>
				<textarea id="tva-course-list-no-courses-message" class="mt-5 mb-5 change" data-fn="changeNoCoursesMessage" placeholder="<?php echo esc_attr__( 'Message that appears when no courses are found', 'thrive-apprentice' ); ?>"></textarea>
				<div class="tve-control" data-view="MessageColor"></div>
			</div>
		</div>
	</div>
</div>
