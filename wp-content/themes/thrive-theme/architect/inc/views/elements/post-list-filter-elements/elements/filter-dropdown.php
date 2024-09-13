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
<div class="thrv_wrapper tcb-filter-dropdown tve-dynamic-dropdown tcb-local-vars-root <?php echo esc_attr( $data['dropdown_animation'] ) ?>"
	 data-style="<?php echo ! empty( $data['template'] ) ? esc_attr( $data['template'] ) : 'default'; ?>"
	 data-selector="<?php echo ! empty( $data['css'] ) ? esc_attr( $data['css'] ) : ''; ?>"
	 data-icon="<?php echo esc_attr( $data['dropdown_icon_style'] ) ?>"
	 data-dropdown-animation="<?php echo esc_attr( $data['dropdown_animation'] ) ?>"
	 data-override-colors="<?php echo ! empty( $data['override_colors'] ) ? esc_attr( $data['override_colors'] ) : ''; ?>"
	 data-placeholder="<?php echo esc_attr( $data['dropdown_placeholder'] ) ?>">
	<input type="text" style="position: absolute; opacity: 0;" autocomplete="off" readonly/>
	<a class="tve-lg-dropdown-trigger tcb-plain-text" tabindex="-1">
		<span class="tve-disabled-text-inner"><?php echo esc_attr( $data['dropdown_placeholder'] ) ?></span>
		<span class="tve-item-dropdown-trigger">
			<?php if ( ! empty( $data['dropdown_icon'] ) ) {
				echo( $data['dropdown_icon'] );
			} else { ?>
				<svg xmlns="http://www.w3.org/2000/svg" class="tve-dropdown-icon-up" viewBox="0 0 320 512"><path d="M151.5 347.8L3.5 201c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0L160 282.7l119.7-118.5c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17l-148 146.8c-4.7 4.7-12.3 4.7-17 0z"></path></svg>
			<?php } ?>
	</span>
	</a>
	<?php echo( $data['dropdown_content'] ); ?>
</div>
