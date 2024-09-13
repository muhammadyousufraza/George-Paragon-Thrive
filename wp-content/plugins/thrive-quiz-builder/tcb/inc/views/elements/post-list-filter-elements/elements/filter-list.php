<li class="thrv_wrapper tve_no_icons dynamic-item-with-icon <?php echo esc_attr( $data['classes'] ); ?>"
	data-id="<?php echo esc_attr( $data['id'] ); ?>"
	data-name="<?php echo esc_attr( $data['name'] ); ?>"
	data-selector="<?php echo ! empty( $data['css'] ) ? '.tcb-post-list-filter' . esc_attr( $data['css'] ) : ''; ?>">
	<div class="tcb-styled-list-icon"
		 data-selector="<?php echo ! empty( $data['css'] ) ? '.tcb-post-list-filter' . esc_attr( $data['css'] ) . ' .tcb-styled-list-icon .thrv_icon' : ''; ?>">
		<?php
		if ( ! empty( $data['icon'] ) ) {
			echo $data['icon'];
		} else {
			?>
			<div class="thrv_wrapper thrv_icon tve_no_drag tve_no_icons tcb-icon-inherit-style tcb-icon-display"
				 data-tcb-elem-type="filter_dynamic_list_icon"
				 data-selector="<?php echo ! empty( $data['css'] ) ? '.tcb-post-list-filter' . esc_attr( $data['css'] ) . ' .tcb-styled-list-icon .thrv_icon' : ''; ?>">
				<svg class="tcb-icon" viewBox="0 0 192 512" data-id="icon-angle-right-light" data-name="">
					<path d="M166.9 264.5l-117.8 116c-4.7 4.7-12.3 4.7-17 0l-7.1-7.1c-4.7-4.7-4.7-12.3 0-17L127.3 256 25.1 155.6c-4.7-4.7-4.7-12.3 0-17l7.1-7.1c4.7-4.7 12.3-4.7 17 0l117.8 116c4.6 4.7 4.6 12.3-.1 17z"></path>
				</svg>
			</div>
		<?php } ?>
	</div>
	<div class="thrv-advanced-inline-text tcb-filter-list-text" data-selector="<?php echo ! empty( $data['css'] ) ? esc_attr( $data['css'] ) . ' .tcb-filter-list-text' : ''; ?>">
		<span data-selector="<?php echo ! empty( $data['css'] ) ? esc_attr( $data['css'] ) . ' .tcb-filter-list-text span' : ''; ?>"><?php echo esc_attr( $data['display_name'] ); ?></span>
	</div>
</li>