<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<div class="notice notice-error is-dismissible"><p>
		<?php esc_html_e( 'There was an error while updating the database tables', 'thrive-automator' ); ?>
		<?php esc_html( $data['product_name'] ); ?>
		<?php esc_html_e( 'Detailed error message:', 'thrive-automator' ); ?>
		<strong><?php echo esc_html( $data['error'] ); ?></strong>
		<a target="_blank" href="https://thrivethemes.com/forums/" rel=noopener><?php esc_html_e( 'Thrive Themes Support', 'thrive-automator' ) ?></a>
	</p></div>
