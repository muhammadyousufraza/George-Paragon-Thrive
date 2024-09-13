<h2 class="tvd-card-title"><?php echo esc_html($this->get_title()); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-rc-api-client-id" type="text" name="connection[client_id]"
					value="<?php echo esc_attr( $this->param( 'client_id' ) ); ?>">
			<label for="tvd-rc-api-client-id"><?php echo esc_html__( 'Client ID', 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-ac-api-client-secret" type="text" name="connection[client_secret]"
					value="<?php echo esc_attr( $this->param( 'client_secret' ) ); ?>">
			<label for="tvd-ac-api-client-secret"><?php echo esc_html__( 'Client Secret', 'thrive-dash' ) ?></label>
		</div>
		<?php $this->display_video_link(); ?>
	</form>
</div>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo esc_html__( 'Cancel', 'thrive-dash' ) ?></a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-redirect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo esc_html__( 'Connect', 'thrive-dash' ) ?></a>
		</div>
	</div>
</div>

