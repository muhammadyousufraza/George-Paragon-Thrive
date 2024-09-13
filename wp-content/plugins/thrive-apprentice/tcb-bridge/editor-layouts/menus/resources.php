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
<div id="tve-resources-component" class="tve-component" data-view="Resources">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-apprentice' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-button-link-container">
			<hr>
			<div class="btn-link mt-10"></div>
		</div>
		<div class="info-text mb-10 course-unpublished">
			<?php echo __( 'This course is currently not published. For your customers to view the resources of this lesson it must be published', 'thrive-apprentice' ); ?>
			<a href="#" target="_blank"><?php echo __( 'here.', 'thrive-apprentice' ); ?></a>
		</div>
		<div class="info-text mb-10 lesson-unpublished">
			<?php echo __( 'Lesson not published: This lesson is currently not published. For your customers to view the resources of this lesson it must be published', 'thrive-apprentice' ); ?>
			<a href="#" target="_blank"><?php echo __( 'here.', 'thrive-apprentice' ); ?></a>
		</div>
		<div class="info-text mb-10 orange no-resources">
			<?php echo __( 'No resources found: Add resources to this lesson in Thrive Apprentice.', 'thrive-apprentice' ); ?>
			<a href="//help.thrivethemes.com/en/articles/4933179-attach-downloadable-files-to-your-lesson-s-content-in-thrive-apprentice" target="_blank"><?php echo __( 'Learn more.', 'thrive-apprentice' ); ?></a>
		</div>
		<div class="tve-control" data-view="ResourcesPalette"></div>
		<div class="tve-control" data-view="showLabel"></div>
		<div class="tve-control" data-view="showIcons"></div>
		<div class="tve-control" data-view="showDescriptions"></div>
		<div class="tve-control" data-view="showDownload"></div>
		<div class="tve-control full-width" data-view="IconAlign"></div>
		<div class="tve-control full-width mb-5" data-view="ButtonAlign"></div>
	</div>
</div>
