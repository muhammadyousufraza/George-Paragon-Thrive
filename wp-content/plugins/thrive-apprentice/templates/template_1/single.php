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

<?php if ( $post->post_type === TVA_Const::MODULE_POST_TYPE ) : ?>
	<?php include_once( TVA_Const::plugin_path( 'templates/template_1/single-module.php' ) ); ?>
<?php elseif ( $post->post_type === TVA_Const::LESSON_POST_TYPE ) : ?>
	<?php include_once( TVA_Const::plugin_path( 'templates/template_1/single-lesson.php' ) ); ?>
<?php endif; ?>

<?php include_once( TVA_Const::plugin_path( 'templates/footer.php' ) ) ?>
