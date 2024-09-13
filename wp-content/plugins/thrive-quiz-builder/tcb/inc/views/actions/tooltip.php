<label for="e-tooltip-text" class="panel-text"><?php echo esc_html__( 'Show Tooltip on Hover', 'thrive-cb' ) ?></label>
<input type="text" class="change" data-fn="text" placeholder="<?php echo esc_attr__( 'Tooltip text', 'thrive-cb' ) ?>" id="e-tooltip-text">
<div class="mt-5">
	<label for="e-tooltip-position" class="panel-text"><?php echo esc_html__( 'Tooltip direction', 'thrive-cb' ) ?></label>
	<select id="e-tooltip-position" class="change" data-fn="pos">
		<?php foreach ( $data['positions'] as $direction => $title ) : ?>
			<option label="<?php echo esc_attr( $title ) ?>" value="<?php echo esc_attr( $direction ) ?>"><?php echo esc_html( $title ) ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="mt-5">
	<label for="tooltip-style" class="panel-text"><?php echo esc_html__( 'Style', 'thrive-cb' ) ?></label>
	<select class="change t-style" data-fn="style" id="tooltip-style">
		<?php foreach ( $data['styles'] as $k => $s ) : ?>
			<option value="<?php echo esc_attr( $k ) ?>"><?php echo esc_html( $s ) ?></option>
		<?php endforeach ?>
	</select>
</div>
