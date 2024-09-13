<div class="thrv_wrapper tcb-elem-placeholder <?php echo esc_attr( $data['class'] ) ?>"<?php echo isset( $data['extra_attr'] ) ? ' ' . $data['extra_attr'] : '' ?>> <?php // phpcs:ignore ?>
	<span class="tcb-inline-placeholder-action with-icon"><?php tcb_icon( $data['icon'], false, 'editor' ) ?>
		<?php echo esc_html( $data['title'] ); ?>
	</span>
</div>
