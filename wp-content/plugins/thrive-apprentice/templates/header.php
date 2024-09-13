<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

nocache_headers();

/**
 * This constant disable caching for the following known plugins:
 * - WP Super Cache
 * - W3 Total Cache
 */
! defined( 'DONOTCACHEPAGE' ) && define( 'DONOTCACHEPAGE', true );

/**
 * Disable Cache for WP Rocket
 */
add_filter( 'rocket_override_donotcachepage', '__return_false', PHP_INT_MAX );

$settings = tva_get_settings_manager()->localize_values();

if ( empty( $settings ) ) {
	include TVA_Const::plugin_path( '/templates/template_1/data.php' );
	$settings['per_page'] = TVA_Const::DEFAULT_COURSES_PER_PAGE;
}

global $post;

if ( is_tax( TVA_Const::COURSE_TAXONOMY ) && ! $post ) {
	$query_obj = get_queried_object();
	$post      = tva_get_course_by_id( $query_obj->term_id );
}

if ( tva_is_apprentice() ) {
	$arguments['topics'] = tva_get_frontend_filters();
	$preview             = $settings['preview_option'];

	if ( tva_is_inner_frame() && ( empty( $courses ) ) ) {

		if ( $preview ) {
			$arguments['published'] = true;
		} else {
			$arguments['private'] = true;
		}

		$courses = tva_get_courses( $arguments );

		if ( empty( $courses ) ) {
			unset( $arguments['published'] );
			$arguments['private'] = true;
			$courses              = tva_get_courses( $arguments );
		}
	}

	$levels = tva_get_levels();
}

$fn        = is_single() ? 'get_post_meta' : 'get_term_meta';
$id        = is_single() ? $post->ID : get_queried_object_id();
$cover_img = $fn( $id, 'tva_cover_image', true );

?>

<!DOCTYPE html>
<html>
<head>
	<meta property="og:image" content="<?php echo $cover_img; ?>"/>
	<meta name="twitter:image" content="<?php echo $cover_img; ?>">
	<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5/dist/html5shiv.js"></script>
	<script src="https://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
	<![endif]-->
	<!--[if IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/ie8.css"/>
	<![endif]-->
	<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/ie7.css"/>
	<![endif]-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>

	<?php if ( tva_is_private_term() ) : ?>

		<meta name="robots" content="noindex">

	<?php endif; ?>

	<?php wp_head(); ?>

	<?php global $is_thrive_theme; ?>
	<?php if ( $is_thrive_theme ) : ?>
		<?php $options = thrive_get_options_for_post(); ?>
		<?php if ( isset( $options['custom_css'] ) && '' != $options['custom_css'] ) : ?>
			<style type="text/css"><?php echo $options['custom_css']; ?></style>
		<?php endif; ?>

		<?php if ( isset( $options['analytics_header_script'] ) && '' != $options['analytics_header_script'] ) : ?>
			<?php echo $options['analytics_header_script']; ?>
		<?php endif; ?>
	<?php endif; ?>
</head>
<body <?php body_class(); ?>>
<?php $walker = ''; ?>
<?php if ( $is_thrive_theme ) : ?>
	<?php if ( isset( $options['analytics_body_script_top'] ) && '' != $options['analytics_body_script_top'] ) : ?>
		<?php echo $options['analytics_body_script_top']; ?>
	<?php endif; ?>
	<?php $walker = class_exists( 'thrive_custom_menu_walker', false ) ? new thrive_custom_menu_walker() : ''; ?>
<?php endif; ?>
<header class="tva-header <?php echo ! has_nav_menu( 'tva_apprentice_menu' ) ? 'tva-center-logo' : ''; ?>">
	<div class="tva-inner-header">
		<div class="clearfix has_phone">
			<div id="logo" class="header-logo <?php echo ! has_nav_menu( 'tva_apprentice_menu' ) ? 'tva-no-menu' : ''; ?>">
				<a class="lg" href="<?php echo home_url( '/' ); ?>">
					<?php if ( isset( $settings['template']['logo_type'] ) && ( false === $settings['template']['logo_type'] || 'false' === $settings['template']['logo_type'] ) ) : ?>
						<img class="tva-img-logo tva-resize-img" src="<?php echo ( isset( $settings['template']['logo_url'] ) ) ? $settings['template']['logo_url'] : ''; ?>"/>
					<?php else : ?>
						<span class="tva_text_logo_size">
						<?php echo ( isset( $settings['template']['logo_url'] ) ) ? $settings['template']['logo_url'] : ''; ?>
					</span>
					<?php endif; ?>
				</a>
			</div>
		</div>
		<?php if ( has_nav_menu( 'tva_apprentice_menu' ) ) : ?>
			<?php
			$walker = apply_filters( 'tva_menu_walker', $walker );

			wp_nav_menu(
				array(
					'theme_location' => 'tva_apprentice_menu',
					'walker'         => $walker,
				)
			);
			?>
		<?php endif; ?>
	</div>
</header>
