<?php
/**
 * FileName  section-placeholder.php.
 *
 * @project: thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div id="<?php echo esc_attr( $data['id'] ) ?>" class="thrv_wrapper tcb-no-delete tve_no_drag thrive-shortcode tcb-elem-placeholder <?php echo esc_attr( $data['class'] ) ?>"<?php echo isset( $data['extra_attr'] ) ? ' ' . $data['extra_attr'] : '' ?> draggable="false">
	<span class="tcb-inline-placeholder-action with-icon"><?php tcb_icon( $data['icon'], false, 'editor' ) ?>
		<?php echo esc_html( $data['title'] ); ?>
	</span>
</div>
