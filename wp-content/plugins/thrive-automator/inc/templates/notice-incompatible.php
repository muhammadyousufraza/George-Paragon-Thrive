<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$incompatible = implode( ', ', $data['incompatible'] );
$admin_url    = is_network_admin() ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' );
?>

<div class="notice notice-error error">
	<p>
		<?php esc_html_e( 'Warning! Thrive Automator is currently incompatible with the following Thrive plugins:', 'thrive-automator' ); ?>
		<?php esc_html( $incompatible ); ?>
		<?php esc_html_e( 'Please make sure all Thrive plugins are updated to their latest versions. ', 'thrive-automator' ); ?>
		<a href="<?php echo esc_url( $admin_url ) ?>"><?php esc_html_e( 'Manage plugins', 'thrive-automator' ) ?> </a>
	</p>
</div>
