<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<div class="tvd-input-field">
			<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
			<input id="tvd-kt-api-user" type="text" name="connection[kt_user]"
				   value="<?php echo esc_attr( $this->param( 'user' ) ); ?>">
			<label for="tvd-kt-api-user"><?php echo esc_html__( "Username", 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-kt-api-password" type="text" name="connection[kt_password]"
				   value="<?php echo esc_attr( $this->param( 'password' ) ); ?>">
			<label for="tvd-kt-api-password"><?php echo esc_html__( "Password", 'thrive-dash' ) ?></label>
		</div>
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
