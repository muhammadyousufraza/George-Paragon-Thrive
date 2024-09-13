<div class="tve-sp"></div>
<div class="tvd-row tvd-collapse">
	<div class="tvd-col tvd-s4">
		<div class="tvd-input-field">
			<input id="aweber_tags" type="text" class="tve-api-extra tve_lightbox_input tve_lightbox_input_inline" name="aweber_tags" value="<?php echo ! empty( $data['tags'] ) ? esc_attr( $data['tags'] ) : '' ?>" size="40"/>
			<label for="aweber_tags"><?php echo esc_html__( 'Tags', 'thrive-dash' ) ?></label>
		</div>
	</div>
	<?php
	$tags_message = __( "Comma-separated lists of tags to assign to a new contact in Aweber", 'thrive-dash' );
	$tags_message = apply_filters( 'tvd_tags_text_for_' . $this->get_key(), $tags_message );
	?>
	<p><?php echo esc_html( $tags_message ); ?></p>
</div>
