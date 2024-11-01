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

<div class="theme-empty-post-content-placeholder">
	<h3></h3>
	<p>
		<?php echo __( 'Your lesson or module content will be hidden from any users that do not match your access rules. Depending on the settings you choose under Thrive Apprentice -> Courses -> Access Restrictions, your course content area will either be replaced with a message or your visitors will be redirected entirely.', 'thrive-apprentice' ); ?>
	</p>

	<img src="<?php echo THEME_URL . '/inc/assets/images/empty-post-content-placeholder.png'; ?>" alt="" class="tvd-responsive-img">
</div>
