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
<div class="thrv_wrapper tve_lg_checkbox_wrapper tve-updated-dom tcb-local-vars-root <?php echo esc_attr( $data['classes'] ); ?>"
	 data-id="<?php echo esc_attr( $data['id'] ); ?>"
	 data-name="<?php echo esc_attr( $data['name'] ); ?>"
	 data-value="<?php echo ! empty( $data['template'] ) ? esc_attr( $data['template'] ) : 'default'; ?>"
	 data-override-colors="<?php echo ! empty( $data['override_colors'] ) ? esc_attr( $data['override_colors'] ) : ''; ?>"
	 data-selector="<?php echo ! empty( $data['css'] ) ? esc_attr( $data['css'] ) : ''; ?>">
	<label>
		<span class="tve-checkmark">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"/></svg>
		</span>
		<span class="tve-input-option-text"><?php echo esc_attr( $data['display_name'] ); ?></span>
	</label>
</div>
