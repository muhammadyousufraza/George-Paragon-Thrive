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
<li class="tve-dynamic-dropdown-option tve_no_icons tve-dynamic-dropdown-editable <?php echo esc_attr( $data['classes'] ); ?>"
	data-id="<?php echo esc_attr( $data['id'] ); ?>"
	data-name="<?php echo esc_attr( $data['name'] ); ?>"
	data-value="<?php echo esc_attr( $data['id'] ); ?>"
	data-selector="<?php echo !empty($data['css']) ? esc_attr( $data['css'] ) : ''; ?>">
	<div class="tve-input-option-text tcb-plain-text">
		<span contenteditable="false"><?php echo esc_attr( $data['display_name'] ); ?></span>
	</div>
</li>
