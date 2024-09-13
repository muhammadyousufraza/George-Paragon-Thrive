<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! isset( $defaults ) || empty( $args ) || ! is_array( $args ) ) {
	return;
}
?>

<form name="check_for_rollbacks" class="rollback-form" action="<?php echo admin_url( '/index.php' ); ?>">
	<div>
		<h1><?php echo esc_html( $args['name'] ); ?> version: <?php echo esc_html( $args['current_version'] ); ?></h1>
	</div>
	<div>
		<p>Are you sure you want to switch to the stable version?</p>
	</div>
	<div class="wpr-submit-wrap">
		<input type="submit" value="Switch to stable version" class="button-primary"/>
		<input type="hidden" name="tvd_channel" value="tvd_switch_to_stable_channel"/>
		<?php foreach ( $defaults as $name => $default_value ) : ?>
			<input type="hidden" name="<?php echo $name; ?>" value="<?php echo esc_html( $args[ $name ] ); ?>"/>
		<?php endforeach; ?>
		<input type="button" value="No, stay on this version" class="button-secondary" onclick="location.href='<?php echo wp_get_referer(); ?>';"/>
	</div>
</form>
