<div class="animation-container">
	<label for="anim-animation"><?php esc_html_e( 'Animation', 'thrive-cb' ) ?></label>
	<select class="change" data-fn="select" id="anim-animation">
		<?php foreach ( $data as $key => $group ) : ?>
			<optgroup label="<?php echo esc_attr( $group['title'] ) ?>">
				<?php foreach ( $group['items'] as $k => $item ) : ?>
					<option data-default="<?php echo in_array( 'tve-viewport', $item['trigger'] ) ? 1 : 0 ?>"
					        data-hover="<?php echo in_array( 'mouseover', $item['trigger'] ) ? 1 : 0 ?>"
					        value="<?php echo esc_attr( $k ) ?>"
					        label="<?php echo esc_attr( $item['title'] ) ?>"><?php echo esc_html( $item['title'] ) ?></option>
				<?php endforeach ?>
			</optgroup>
		<?php endforeach ?>
	</select>
</div>
<div class="sep"></div>
<div class="trigger-container" id="anim-trigger" style="display: none">
	<label for="animation-trigger"><?php echo esc_html__( 'Animation Trigger', 'thrive-cb' ) ?></label>
	<select id="animation-trigger" class="change tcb-select" data-fn="change_trigger"></select>
</div>
<label class="tcb-checkbox"><input type="checkbox" class="anim-loop tcb-checkbox"><span><?php esc_html_e( 'Loop animation', 'thrive-cb' ) ?></span></label>
