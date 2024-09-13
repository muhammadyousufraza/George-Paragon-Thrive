<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( empty( $data ) || ! is_array( $data ) ) {
	return;
}
?>
<div class="control-grid switch" data-setting="<?php echo esc_attr( $data['setting'] ); ?>">
	<div class="fill">
		<span class="switch-label"><?php echo esc_html( $data['label'] ); ?></span>
		<?php if ( ! empty( $data['info'] ) ): ?>
			<span class="click tve-switch-info" data-fn="<?php echo esc_attr( $data['info_fn'] ); ?>" data-paneltlt-hover><?php tcb_icon( 'info-circle-solid' ); ?></span>
		<?php endif; ?>
	</div>
	<div class="tcb-switch">
		<label>
			<input type="checkbox" class="change"
				   data-fn="extra_settings_changed"
				   data-elem-attr="<?php echo esc_attr( $data['setting'] ); ?>"
				   data-elem-attr-val="<?php echo esc_attr( $data['checked_val'] ); ?>"
				   data-elem-attr-val-unchecked="<?php echo esc_attr( $data['unchecked_val'] ); ?>"
				<?php if ( ! empty( $data['disable_option'] ) ) : ?>
					data-disable-option="<?php echo esc_attr( $data['disable_option'] ); ?>"
					data-disable-value="<?php echo esc_attr( $data['disable_option_val'] ); ?>"
				<?php endif; ?>
			>
			<span class="tcb-lever"></span>
		</label>
	</div>
</div>
