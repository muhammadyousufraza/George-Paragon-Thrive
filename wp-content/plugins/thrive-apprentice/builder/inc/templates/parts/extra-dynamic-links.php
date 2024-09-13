<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<?php if ( Thrive_Prev_Next::show() ) : ?>
	<option value="tcb_post_next_link"><?php echo __( 'Next piece of content', 'thrive-theme' ); ?></option>
	<option value="tcb_post_prev_link"><?php echo __( 'Previous piece of content', 'thrive-theme' ); ?></option>
<?php endif; ?>
