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

<?php include_once( TVA_Const::plugin_path( 'templates/header.php' ) ) ?>
TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2TEMPLATE 2
<header class="tuni-header">
	<div class="wrp uni side_logo clearfix has_phone" id="head_wrp">
		<div class="h-i">
			<div id="logo" class="header-logo">
				<a class="lg" href="<?php echo home_url( '/' ); ?>">
					<img
							src="<?php echo get_stylesheet_directory_uri() ?>/images/university/university-logo.png"
							alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
							width="" height=""/>
				</a>
			</div>
		</div>
	</div>
</header>

<?php $course = tva_get_course_by_slug( $term ); ?>
<?php $topic = tva_get_topic_by_id( $course->topic ); ?>
<div class="wrp cnt clearfix">
	<div class="">
		<section>
			<?php if( count( $courses ) > 1) :?>
				<div class="tva-breadcrumbs">
					<?php tva_custom_breadcrumbs(); ?>
				</div>
			<?php endif; ?>

			<div class="tva-course-head tva-course-head-<?php echo $topic['ID'] ?>">
				<img class="tva-course-icon" src="<?php echo ! empty( $topic['icon'] ) ? $topic['icon'] : TVA_Const::get_default_course_icon_url(); ?>"/>
				<span class="tva-uni-course-type"><?php echo $course->post_formats ?></span>
			</div>
			<div class="tva-featured-image-container-single">
				<div class="tva-image-overlay image-<?php echo $topic['ID'] . '-overlay' ?>">
					<h1 class="tva_course_title tva-section-title"><?php echo $course->name; ?></h1>
					<p class="ttw-uni-section-sub-title tva_paragraph"><?php echo $course->description; ?></p>
					<!-- Not being in the loop we always have the first post-->
					<a href="<?php echo tva_get_start_course_url( $course ); ?>; ?>"><?php echo isset( $settings['template']['start_course'] ) ? $settings['template']['start_course'] : TVA_Const::TVA_START; ?></a>
				</div>
				<?php if ( ! empty( $course->featured_image ) ) : ?>
					<img src="<?php echo $course->cover_image; ?>">
				<?php endif; ?>
			</div>

			<div class="tva-archive-content">
				<h1 class="tva_page_headline"><strong><?php echo isset( $settings['template']['page_headline_text'] ) ? $settings['template']['page_headline_text'] : TVA_Const::TVA_ABOUT; ?></strong></h1>
				<p class="tva_paragraph"><?php echo $course->description; ?></p>
			</div>
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<h1 class="tva_lesson_headline"><a class="tva_main_color" href="<?php echo get_permalink($post->ID); ?>"><?php echo the_title(); ?></a></h1>
				<?php endwhile; ?>
			<?php endif; ?>
			<?php echo do_shortcode( '[tva_progress_bar]' ); ?>
		</section>
		<aside class="tva-sidebar-container">
			<?php dynamic_sidebar( 'tva-sidebar' ); ?>
		</aside>
	</div>
</div>

<?php include_once( TVA_Const::plugin_path( 'templates/footer.php' ) ) ?>
