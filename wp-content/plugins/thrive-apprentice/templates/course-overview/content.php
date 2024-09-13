<?php

/**
 * Loaded for a course which has a overview post
 * - the course overview page has been edited with TAr
 * - this template calls the_content() method to load the editable content saved in overview post - content previously edited with TAr
 * - at some point we might remove some of the html elements bellow and add them in the_content() editable with TAr
 */

$course   = ! empty( $data['course'] ) && $data['course'] instanceof TVA_Course_V2 ? $data['course'] : null;
$settings = ! empty( $data['settings'] ) ? $data['settings'] : array();
$levels   = ! empty( $data['levels'] ) ? $data['levels'] : array();
$labels   = TVA_Dynamic_Labels::get( 'course_structure' );

if ( false === $course instanceof TVA_Course_V2 ) {
	return '';
}

global $post;
$post = $course->has_overview_post();
?>

<?php show_welcome_msg( $course ); ?>

<h1 class="tva_course_title"><?php echo $course->name; ?></h1>

<div class="tva_page_headline_wrapper tva-course-counters">
	<div class="tva-course-numbers">
		<?php if ( $course->get_published_modules_count() > 0 ) : ?>
			<span class="item-value"><?php echo $course->get_published_modules_count(); ?> </span>
			<span class="item-name <?php echo 1 === $course->get_published_modules_count() ? 'tva_course_module' : 'tva_course_modules'; ?>">
				<?php
				if ( 1 === $course->get_published_modules_count() ) {
					echo isset( $labels['course_module']['singular'] ) ? $labels['course_module']['singular'] : $settings['template']['course_module'];
				} else {
					echo isset( $labels['course_module']['plural'] ) ? $labels['course_module']['plural'] : $settings['template']['course_modules'];
				} ?>
			</span>
		<?php endif; ?>
		<?php if ( $course->get_published_modules_count() > 0 ) : ?>
			<span class="item-value">
				<?php echo $course->get_published_chapters_count(); ?>
			</span>
			<span class="item-name <?php echo 1 === $course->get_published_chapters_count() ? 'tva_course_chapter' : 'tva_course_chapters'; ?> ">
				<?php
				if ( 1 === $course->get_published_chapters_count() ) {
					echo isset( $labels['course_chapter']['singular'] ) ? $labels['course_chapter']['singular'] : $settings['template']['course_chapter'];
				} else {
					echo isset( $labels['course_chapter']['plural'] ) ? $labels['course_chapter']['plural'] : $settings['template']['course_chapters'];
				} ?>
			</span>
		<?php endif; ?>
		<?php if ( $course->get_published_lessons_count() > 0 ) : ?>
			<span class="item-value"><?php echo $course->get_published_lessons_count(); ?></span>
			<span class="item-name <?php echo 1 === $course->get_published_lessons_count() ? 'tva_course_lesson' : 'tva_course_lessons'; ?>">
				<?php
				if ( 1 === $course->get_published_lessons_count() ) {
					echo isset( $labels['course_lesson']['singular'] ) ? $labels['course_lesson']['singular'] : $settings['template']['course_lesson'];
				} else {
					echo isset( $labels['course_lesson']['plural'] ) ? $labels['course_lesson']['plural'] : $settings['template']['course_lessons'];
				} ?>
			</span>
		<?php endif; ?>

		<span class="tva-course-difficulty">
			<?php echo $course->get_difficulty()->id ? $course->get_difficulty()->name : ''; ?>
		</span>
	</div>

	<p class="tva_page_headline tva_page_headline_text">
		<?php echo isset( $settings['template']['page_headline_text'] ) ? $settings['template']['page_headline_text'] : TVA_Const::TVA_ABOUT; ?>
	</p>

	<?php if ( tva_course()->has_video() ) : ?>
		<div class="tva-featured-video-container-single">
			<?php echo tva_course()->get_video()->get_embed_code(); ?>
		</div>
	<?php endif ?>
</div>

<?php the_content(); ?>

<?php if ( tva_access_manager()->has_access() ) : ?>
	<a class="tva_start_course tva_main_color_bg" href="<?php echo tva_get_start_course_url( $course ); ?>">
		<?php
		/*if design preview, always show the "Start course" btn label*/
		echo tva_is_inner_frame() ? TVA_Dynamic_Labels::get_cta_label( 'not_started' ) : TVA_Dynamic_Labels::get_course_cta( $course );
		?>
	</a>
<?php endif; ?>

<div class="tva-cm-container">
	<h1 class="tva_course_structure"><?php echo $settings['template']['course_structure']; ?></h1>

	<?php if ( $course->get_visible_modules_count() ) : ?>
		<?php foreach ( $course->get_visible_modules() as $module ) : ?>
			<?php echo tva_generate_module_html( $module ); ?>
		<?php endforeach; ?>
	<?php elseif ( $course->get_visible_chapters_count() ) : ?>
		<?php foreach ( $course->get_visible_chapters() as $chapter ) : ?>
			<?php echo tva_generate_chapter_html( $chapter, true ); ?>
		<?php endforeach; ?>
	<?php else : ?>
		<?php if ( $course->get_visible_lessons_count() ) : ?>
			<?php foreach ( $course->get_ordered_visible_lessons() as $lesson ) : ?>
				<?php echo tva_generate_lesson_html( $lesson, true ); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php endif ?>
</div>
