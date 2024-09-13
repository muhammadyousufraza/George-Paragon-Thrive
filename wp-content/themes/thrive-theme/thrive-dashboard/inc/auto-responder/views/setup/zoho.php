<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-mm-zoho-url" type="text" name="connection[account_url]"
				   value="<?php echo esc_attr( $this->param( 'account_url' ) ); ?>">
			<label for="tvd-mm-zoho-url"><?php echo esc_html__( 'Account Url', 'thrive-dash' ) ?></label>
		</div>

		<div class="tvd-input-field">
			<input id="tvd-mm-api-client-id" type="text" name="connection[client_id]"
				   value="<?php echo esc_attr( $this->param( 'client_id' ) ); ?>">
			<label for="tvd-mm-api-client-id"><?php echo esc_html__( 'Zoho Client ID', 'thrive-dash' ) ?></label>
		</div>

		<div class="tvd-input-field">
			<input id="tvd-mm-api-client-secret" type="text" name="connection[client_secret]"
				   value="<?php echo esc_attr( $this->param( 'client_secret' ) ); ?>">
			<label for="tvd-mm-api-client-secret"><?php echo esc_html__( 'Zoho Client Secret', 'thrive-dash' ) ?></label>
		</div>

		<?php if ( empty( $this->param( 'account_url' ) ) ) : ?>
			<div class="tvd-input-field">
				<input id="tvd-mm-api-code" type="text" name="connection[access_code]"
					   value="<?php echo esc_attr( $this->param( 'access_code' ) ); ?>">
				<label for="tvd-mm-api-code"><?php echo esc_html__( 'Access Code', 'thrive-dash' ) ?></label>
			</div>
		<?php else: ?>
			<input type="hidden" name="connection[access_token]" value="<?php echo esc_attr( $this->param( 'access_token' ) ); ?>">
			<input type="hidden" name="connection[refresh_token]" value="<?php echo esc_attr( $this->param( 'refresh_token' ) ); ?>">
			<input type="hidden" name="connection[acctk_validity_time]" value="<?php echo esc_attr( $this->param( 'acctk_validity_time' ) ); ?>">
		<?php endif; ?>

		<?php $this->display_video_link(); ?>
	</form>
</div>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo esc_html__( "Cancel", 'thrive-dash' ) ?></a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-connect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo esc_html__( "Connect", 'thrive-dash' ) ?></a>
		</div>
	</div>
</div>
