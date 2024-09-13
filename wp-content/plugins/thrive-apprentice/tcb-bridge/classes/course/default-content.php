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
[tva_course_begin]
	<?php echo \TVA\Architect\Course\Shortcodes\Shortcodes::get_default_template( 'course-item' ); ?>
[/tva_course_begin]
[tva_course_module_list]

	[tva_course_module_begin]
		<?php echo \TVA\Architect\Course\Shortcodes\Shortcodes::get_default_template( 'module' ); ?>
	[/tva_course_module_begin]

	[tva_course_chapter_list]

		[tva_course_chapter_begin]
			<?php echo \TVA\Architect\Course\Shortcodes\Shortcodes::get_default_template( 'chapter' ); ?>
		[/tva_course_chapter_begin]

		[tva_course_lesson_list]
			<?php echo \TVA\Architect\Course\Shortcodes\Shortcodes::get_default_template( 'lesson' ); ?>
			<?php echo \TVA\Architect\Course\Shortcodes\Shortcodes::get_default_template( 'assessment' ); ?>
		[/tva_course_lesson_list]

		[tva_course_chapter_end]

	[/tva_course_chapter_list]

	[tva_course_module_end]

[/tva_course_module_list]
[tva_course_end]
