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
<div class="thrv_wrapper tcb-group-editing-item <?php echo esc_attr( $data['classes'] ); ?>"
	 data-id="<?php echo esc_attr( $data['id'] ); ?>"
	 data-name="<?php echo esc_attr( $data['name'] ); ?>"
	 data-selector="<?php echo ! empty( $data['css'] ) ? esc_attr( $data['css'] ) : ''; ?>">
	<div class="thrive-colors-palette-config" style="display: none !important">__CONFIG_colors_palette__{"active_palette":0,"config":{"colors":{"62516":{"name":"Main Accent","parent":-1}},"gradients":[]},"palettes":[{"name":"Default Palette","value":{"colors":{"62516":{"val":"var(--tcb-skin-color-0)"}},"gradients":[]}}]}__CONFIG_colors_palette__</div>
	<a class="tcb-button-link tcb-plain-text">
		<span class="tcb-button-texts">
			<span class="tcb-button-text"> <?php echo esc_attr( $data['display_name'] ); ?> </span>
		</span>
	</a>
</div>