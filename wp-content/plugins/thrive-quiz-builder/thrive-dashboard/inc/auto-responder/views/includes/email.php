<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
if ( ! empty( $args['name'] ) && false !== $has_shortcode ) : ?>
	<br>
	<b><?php echo ! empty( $labels['name'] ) ? esc_html( $labels['name'] ) . ':' : esc_html__( 'Name:', 'thrive-dash' ); ?> </b><span> <?php echo esc_html( $args['name'] ); ?> </span>
<?php endif; ?>

<?php if ( false !== $has_shortcode ) : ?>
	<br>
	<b><?php echo ! empty( $labels['email'] ) ? esc_html( $labels['email'] ) . ':' : esc_html__( 'Email:', 'thrive-dash' ); ?></b><span> <?php echo esc_html( $args['email'] ); ?> </span>
<?php endif; ?>

<?php if ( ! empty( $args['phone'] ) && false !== $has_shortcode ) : ?>
	<br>
	<b><?php echo ! empty( $labels['phone'] ) ? esc_html( $labels['phone'] ) . ':' : esc_html__( 'Phone:', 'thrive-dash' ); ?></b><span> <?php echo esc_html( $args['phone'] ); ?> </span>
<?php endif; ?>

<?php if ( isset( $args['include_date'] ) && 1 === (int) $args['include_date'] ) : ?>
	<br>
	<b><?php echo esc_html__( 'Date:', 'thrive-dash' ); ?> </b> <span> <?php echo esc_html( date_i18n( 'jS F, Y' ) ); ?> </span>
<?php endif; ?>

<?php if ( isset( $args['include_time'] ) && 1 === (int) $args['include_time'] ) : ?>
	<br>
	<b><?php echo esc_html__( 'Time:', 'thrive-dash' ); ?> </b> <span> <?php echo esc_html( $time ); ?> </span>
<?php endif; ?>

<?php if ( isset( $args['include_page_url'] ) && 1 === (int) $args['include_page_url'] ) : ?>
	<br>
	<b><?php echo esc_html__( 'Page URL:', 'thrive-dash' ); ?> </b> <span> <a href="<?php echo esc_url( $args['url'] ); ?>"> <?php echo esc_html( $args['url'] ); ?> </a> </span>
<?php endif; ?>

<?php if ( isset( $args['include_ip'] ) && 1 === (int) $args['include_ip'] ) : ?>
	<br>
	<b><?php echo esc_html__( 'IP Address:', 'thrive-dash' ); ?> </b> <span> <?php echo esc_html( tve_dash_get_ip() ); ?> </span>
<?php endif; ?>

<?php if ( isset( $args['include_device_settings'] ) && 1 === (int) $args['include_device_settings'] ) : ?>
	<br>
	<b><?php echo esc_html__( 'Device Settings:', 'thrive-dash' ); ?> </b><span> <?php echo esc_html( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '' ); ?> </span>
<?php endif; ?>
<br>
