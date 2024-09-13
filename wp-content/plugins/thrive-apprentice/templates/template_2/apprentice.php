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

<div class="wrp cnt clearfix">
	<div class="bSeCont ">
		<section class="bSe tuni-page-content">
			<?php $arguments['topics'] = tva_get_frontend_filters(); ?>
			<?php $arguments['page'] = 1; ?>
			<?php $arguments['per_page'] = $user_settings['per_page']; ?>
			<?php $arguments['published'] = true; ?>

			<?php $courses = tva_get_courses( $arguments ); ?>
			<?php if ( ! empty( $courses ) ) : ?>
				<?php foreach ( $courses as $course ) : ?>
					<h1><a href="<?php echo $course->url ?>"><?php echo $course->name; ?></a></h1>
				<?php endforeach; ?>
			<?php else : ?>

			<?php endif; ?>
		</section>
	</div>
</div>

<?php include_once( TVA_Const::plugin_path( 'templates/footer.php' ) ) ?>
