<div id="lb-container"></div>
<div class="control-grid no-space">
	<label for="lb-animation"><?php echo esc_html__( 'Animation', 'thrive-cb' ) ?></label>
	<div class="input">
		<select id="lb-animation">
			<?php foreach ( $data as $k => $s ) : ?>
				<option value="<?php echo esc_attr( $k ) ?>"><?php echo esc_html( $s ) ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
