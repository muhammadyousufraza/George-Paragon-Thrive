<?php
/**
 * Content editable with TAr
 * - saved on tve_updated_post meta of course overview post when the post is added in DB and linked to the course
 * - at some point this content html might have more elements editable with TAr
 */

/** @var array $args */

/** @var TVA_Course_V2 $course */

$course = ! empty( $data['course'] ) ? $data['course'] : null;

$course_description = $course instanceof TVA_Course_V2 ? $course->get_description() : '';
?>
<?php if ( ! empty( $course_description ) ) : ?>
	<div class="thrv_wrapper tve_wp_shortcode" data-css="tve-u-177626f6021" style="">
		<div class="tve_shortcode_raw" style="display: none">___TVE_SHORTCODE_RAW__<?php echo $course_description; ?>__TVE_SHORTCODE_RAW___</div>
	</div>
<?php endif; ?>
