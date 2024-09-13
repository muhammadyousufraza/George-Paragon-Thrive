<h2 class="tvd-card-title">
	<?php echo esc_html( $this->get_title() ); ?>
</h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-stripe-api-key" type="text" name="connection[api_key]"
				   value="<?php echo esc_attr( $this->param( 'api_key' ) ); ?>">
			<label for="tvd-stripe-api-key"><?php echo esc_html__( 'API key', 'thrive-apprentice' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-stripe-test-key" type="text" name="connection[test_key]"
				   value="<?php echo esc_attr( $this->param( 'test_key' ) ); ?>">
			<label for="tvd-stripe-test-key"><?php echo esc_html__( 'Test API key', 'thrive-apprentice' ) ?></label>
		</div>
		<p class="tve-form-description tvd-note-text">
			<a href="https://help.thrivethemes.com/en/articles/8428265-how-to-set-up-stripe-in-thrive-apprentice#h_5ca42d7556" target="_blank"><?php echo esc_html__( 'I need help with this', 'thrive-apprentice' ) ?></a>
		</p>
	</form>
</div>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo esc_html__( 'Cancel', 'thrive-apprentice' ) ?></a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-connect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo esc_html__( 'Connect', 'thrive-apprentice' ) ?></a>
		</div>
	</div>
</div>
