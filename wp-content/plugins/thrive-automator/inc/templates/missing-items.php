<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/**
 * display any possible conflicts with other plugins / themes as error notification in the admin panel
 */
$is_plugin_active = apply_filters( 'tap_notice_check_plugin', false, $data['missing_plugin'], $data['missing_key'] );
?>
<div class="error">
	<p>
		<?php if ( $is_plugin_active ) : ?>
			<?php esc_html_e( 'Some of your triggers/actions from Thrive Automator have been deprecated. Go to the ', 'thrive-automator' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=thrive_automator' ) ); ?>"><?php esc_html_e( 'Thrive Automator dashboard', 'thrive-automator' ); ?></a>
			<?php esc_html_e( ' and replace them.', 'thrive-automator' ); ?>
		<?php else : ?>
			<?php echo esc_html( $data['missing_plugin'] ); ?>
			<?php esc_html_e( 'is not installed or not activated. Some automations will not work. Please go to ', 'thrive-automator' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=thrive_automator' ) ); ?>"><?php esc_html_e( 'Thrive Automator dashboard', 'thrive-automator' ); ?></a>
			<?php esc_html_e( ' and install it.', 'thrive-automator' ); ?>
		<?php endif; ?>
	</p>
</div>
