<h2 class="tvd-card-title">
	<?php echo esc_html( $this->get_title() ); ?>
</h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-rc-api-access-token" type="text" name="access_token"
				   value="<?php echo esc_attr( $this->param( 'access_token' ) ); ?>">
			<label for="tvd-rc-api-access-token"><?php echo esc_html__( 'Access Token', 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-rc-api-token-secret" type="text" name="token_secret"
				   value="<?php echo esc_attr( $this->param( 'token_secret' ) ) ?>">
			<label for="tvd-rc-api-token-secret"><?php echo esc_html__( 'Access Token Secret', 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-rc-api-api-key" type="text" name="api_key"
				   value="<?php echo esc_attr( $this->param( 'api_key' ) ) ?>">
			<label for="tvd-rc-api-api-key"><?php echo esc_html__( 'Api Key', 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-ac-api-api-secret" type="text" name="api_secret"
				   value="<?php echo esc_attr( $this->param( 'api_secret' ) ) ?>">
			<label for="tvd-ac-api-api-secret"><?php echo esc_html__( 'Api Secret', 'thrive-dash' ) ?></label>
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
			<a class="tvd-api-connect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo esc_html__( 'Connect', 'thrive-dash' ) ?></a>
		</div>
	</div>
</div>

