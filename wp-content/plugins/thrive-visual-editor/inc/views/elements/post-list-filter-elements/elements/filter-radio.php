<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<div class="thrv_wrapper tcb-local-vars-root click tcb-filter-radio <?php echo esc_attr( $data['classes'] ); ?>"
	 data-id="<?php echo esc_attr( $data['id'] ); ?>"
	 data-name="<?php echo esc_attr( $data['name'] ); ?>"
	 data-value="<?php echo ! empty( $data['template'] ) ? esc_attr( $data['template'] ) : 'default'; ?>"
	 data-override-colors="<?php echo ! empty( $data['override_colors'] ) ? esc_attr( $data['override_colors'] ) : ''; ?>"
	 data-selector="<?php echo ! empty( $data['css'] ) ? esc_attr( $data['css'] ) : ''; ?>">
	<span class="tve-checkmark"></span>
	<span class="tve-input-option-text"><?php echo esc_attr( $data['display_name'] ); ?></span>
</div>
