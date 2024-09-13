<?php
/**
 * Old Module template
 */

/** @var TVA_Module $module */
$module = ! empty( $data['module'] ) && $data['module'] instanceof TVA_Module ? $data['module'] : null;

/** @var bool $allowed */
$allowed = empty( $data['allowed'] ) ? false : (bool) $data['allowed'];
?>
<div class="tva-course-description"><?php echo $module ? $module->post_excerpt : ''; ?></div>

<div class="tva-cm-container">
	<h1><?php echo __( 'Module Structure', 'thrive-apprentice' ); ?></h1>

	<div class="tva-cm-module">
		<?php if ( $module && $module->get_visible_chapters_count() > 0 ) : ?>
			<?php foreach ( $module->get_visible_chapters() as $chapter ) : ?>
				<?php echo tva_generate_chapter_html( $chapter, $allowed ); ?>
			<?php endforeach; ?>
		<?php elseif ( $module && $module->get_visible_lessons_count() > 0 ) : ?>
			<?php foreach ( $module->get_visible_lessons() as $lesson ) : ?>
				<?php echo tva_generate_lesson_html( $lesson, $allowed ); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
