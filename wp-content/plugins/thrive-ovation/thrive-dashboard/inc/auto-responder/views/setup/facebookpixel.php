<h2 class="tvd-card-title">
	<?php echo esc_html( $this->get_title() ); ?>
</h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-ac-api-pixel-id" type="text" name="connection[pixel_id]"
			       value="<?php echo esc_attr( $this->param( 'pixel_id' ) ); ?>">
			<label for="tvd-ac-api-pixel-id"><?php echo esc_html__( 'Pixel ID', 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-rc-api-access-token" type="text" name="connection[access_token]"
			       value="<?php echo esc_attr( $this->param( 'access_token' ) ); ?>">
			<label for="tvd-rc-api-access-token"><?php echo esc_html__( 'Access token', 'thrive-dash' ) ?></label>
		</div>
		<p class="tve-form-description tvd-note-text">
			<a href="https://help.thrivethemes.com/en/articles/7793479-how-to-use-facebook-events-with-thrive-automator" target="_blank"><?php echo esc_html__( 'I need help with this', 'thrive-dash' ) ?></a>
		</p>
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
