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
		<div class="tva-footer">
			<?php dynamic_sidebar( 'tva-footer' ); ?>
		</div>
		<?php if ( $is_thrive_theme ) : ?>
			<?php if ( isset( $options['analytics_body_script'] ) && $options['analytics_body_script'] != '' ) : ?>
				<?php echo $options['analytics_body_script']; ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php wp_footer(); ?>
	</body>
</html>
