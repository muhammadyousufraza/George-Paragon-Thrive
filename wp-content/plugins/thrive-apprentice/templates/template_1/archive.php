<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>

<?php include_once( TVA_Const::plugin_path( 'templates/header.php' ) ); ?>

<?php
$course = tva_get_course_by_slug(
	$term,
	array(
		'published'  => true,
		'protection' => true,
	)
);
?>

<?php $topic = tva_get_topic_by_id( $course->topic ); ?>
<div class="tva-cm-redesigned-breadcrumbs">
	<?php tva_custom_breadcrumbs(); ?>
</div>
<div class="tva-frontend-template" id="tva-course-overview">
	<div class="tva-container">
		<div class="tva-course-head tva-course-head-<?php echo $topic['ID']; ?> tva-course-type-<?php echo $course->course_type_class; ?>">
			<div class="tva-course-icon">
				<?php if ( isset( $topic['icon_type'] ) && ( 'svg_icon' === $topic['icon_type'] ) && isset( $topic['svg_icon'] ) ) : ?>
					<div class="tva-svg-front" id="tva-topic-<?php echo $topic['ID']; ?>">
						<?php echo $topic['svg_icon']; ?>
					</div>
				<?php else : ?>
					<?php $img_url = $topic['icon'] ? $topic['icon'] : TVA_Const::get_default_course_icon_url(); ?>
					<div class="tva-topic-icon" style="background-image:url('<?php echo $img_url; ?>')"></div>
				<?php endif; ?>

				<span class="tva-lesson-name"><?php echo $topic['title']; ?></span>
			</div>
			<div class="tva-uni-course-type">
				<i></i>
				<span><?php echo tva_is_course_guide( $course ) ? __( 'Guide', 'thrive-apprentice' ) : $course->course_type; ?></span>
			</div>
		</div>
		<div class="tva-featured-image-container-single" <?php echo ! $course->cover_image ? 'style="background-color: ' . $topic['color'] . '; opacity: 0.7"' : ''; ?>>
			<div class="tva-image-overlay"></div>
			<?php if ( ! empty( $course->cover_image ) ) : ?>
				<div style="background-image:url('<?php echo $course->cover_image; ?>')" class="tva-image-as-bg"></div>
			<?php endif; ?>
		</div>

		<section class="tva-course-section <?php echo ( ( tva_is_course_guide( $course ) ) ) && ! is_active_sidebar( 'tva-sidebar' ) ? 'tva-course-guide-section' : ''; ?>">

			<?php echo tva_course()->get_content(); ?>

			<?php
			if ( ( 'open' === $course->comment_status ) && tva_course()->has_access() ) {
				global $post;
				$temp_post = $post;
				tva_overwrite_post(); //sets the global post to a dummy post so that TC can handle comments
				TVA_Db::setCommentsStatus();
				comments_template( '', true );
				echo apply_filters( 'comment_form_submit_field', '', array() );
				tva_add_tcm_triggers();
				$post = $temp_post;
			}
			?>
		</section>
		<?php if ( is_active_sidebar( 'tva-sidebar' ) ) : ?>
			<aside class="tva-sidebar-container">
				<div class="tva-sidebar-wrapper">
					<?php dynamic_sidebar( 'tva-sidebar' ); ?>
				</div>
			</aside>
		<?php endif; ?>
	</div>
</div>

<?php echo tva_add_apprentice_label(); ?>

<?php include_once( TVA_Const::plugin_path( 'templates/footer.php' ) ); ?>
