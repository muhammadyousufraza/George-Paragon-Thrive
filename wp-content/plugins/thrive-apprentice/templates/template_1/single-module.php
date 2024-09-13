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
<?php

$terms             = wp_get_post_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );
$course            = tva_get_course_by_slug( $terms[0]->slug, array( 'published' => true ) );
$topic             = tva_course()->get_topic();
$template_settings = tva_get_setting( 'template' );
$post_arr          = array_values( wp_list_filter( $course->modules, array( 'ID' => $post->ID ) ) );
$post              = isset( $post_arr[0] ) ? $post_arr[0] : $post;
$allowed           = tva_access_manager()->has_access();
$labels            = TVA_Dynamic_Labels::get( 'course_structure' );

if ( ! $allowed ) {
	/* gather access restrictions settings and figure out what to do */
	$access_restriction = tva_access_restriction_settings( tva_course() );
}
$tva_module = new TVA_Module( get_post() );
?>

<div class="tva-cm-redesigned-breadcrumbs">
	<?php tva_custom_breadcrumbs(); ?>
</div>
<div class="tva-page-container tva-frontend-template" id="tva-course-module">
	<div class="tva-container">
		<div class="tva-course-head tva-course-head-<?php echo $topic->ID; ?> tva-course-type-<?php echo $course->course_type_class; ?>">
			<div class="tva-course-icon">
				<?php if ( 'svg_icon' === $topic->icon_type ) : ?>
					<div class="tva-svg-front" id="tva-topic-<?php echo $topic->ID; ?>">
						<?php echo $topic->svg_icon; ?>
					</div>
				<?php else : ?>
					<div class="tva-topic-icon" style="background-image:url('<?php echo $topic->icon_url(); ?>')"></div>
				<?php endif; ?>

				<span class="tva-lesson-name"><?php echo $topic->title; ?></span>
			</div>
			<div class="tva-uni-course-type">
				<i></i>
				<span><?php echo tva_course()->is_guide() ? __( 'Guide', 'thrive-apprentice' ) : tva_course()->get_type(); ?></span>
			</div>
		</div>


		<div class="tva-featured-image-container-single">
			<div class="tva-image-overlay image-<?php echo $topic->ID . '-overlay'; ?>"></div>
			<?php if ( ( ! $allowed && tva_course()->get_cover_image() ) || ( ! $post->cover_image && tva_course()->get_cover_image() ) ) : ?>
				<div style="background-image:url('<?php echo tva_course()->get_cover_image(); ?>')" class="tva-image-as-bg tva-post-cover"></div>
			<?php elseif ( $allowed && $post->cover_image ) : ?>
				<div style="background-image:url('<?php echo $post->cover_image; ?>')" class="tva-image-as-bg tva-course-cover"></div>
			<?php else : ?>
				<div class="tva-feaured-image-colored" style="background-color: <?php echo $topic->color; ?>"></div>
			<?php endif; ?>
		</div>

		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<?php $post = isset( $post_arr[0] ) ? $post_arr[0] : $post; ?>

				<section class="tva-course-section tva-module-single-page <?php echo ! is_active_sidebar( 'tva-module-sidebar' ) ? 'tva-course-guide-section' : ''; ?>">

					<?php if ( ! $allowed ) : ?>
						<div class="tva-archive-content">
							<?php $access_restriction->the_title( '<p class="tva_course_title">', '</p>' ); ?>

							<?php echo tva_access_manager()->restrict_content( '' ); ?>
						</div>
					<?php else : ?>
						<div class="tva-archive-content">
							<?php show_welcome_msg( tva_course() ); ?>

							<h1 class="tva_course_title">
								<?php echo the_title(); ?>
							</h1>

							<div class="tva_page_headline_wrapper">
								<?php echo ( isset( $labels['course_module']['singular'] ) ? $labels['course_module']['singular'] : $template_settings['course_module'] ) . ' ' . ( (int) $post->order + 1 ); ?>
							</div>

							<?php $tva_module->the_content(); ?>

							<?php tva_add_tcm_triggers(); ?>
						</div>
					<?php endif; ?>
					<?php if ( tva_access_manager()->has_access_to_object( get_post() ) ) : ?>
						<?php comments_template( '', true ); ?>
					<?php endif; ?>
				</section>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php if ( is_active_sidebar( 'tva-module-sidebar' ) ) : ?>
			<aside class="tva-sidebar-container">
				<div class="tva-sidebar-wrapper">
					<?php dynamic_sidebar( 'tva-module-sidebar' ); ?>
				</div>
			</aside>
		<?php endif; ?>
	</div>
</div>

<?php echo tva_add_apprentice_label(); ?>
