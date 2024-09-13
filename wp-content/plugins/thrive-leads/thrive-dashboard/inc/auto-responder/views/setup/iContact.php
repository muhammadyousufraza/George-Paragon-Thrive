<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-ic-api-username" type="text" name="connection[apiUsername]"
					value="<?php echo esc_attr( $this->param( 'apiUsername' ) ); ?>">
			<label for="tvd-ic-api-username"><?php echo esc_html__( "iContact username", 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-ic-api-appid" type="text" name="connection[appId]"
					value="<?php echo esc_attr( $this->param( 'appId' ) ); ?>">
			<label for="tvd-ic-api-appid"><?php echo esc_html__( "Application ID", 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-ic-api-password" type="text" name="connection[apiPassword]"
					value="<?php echo esc_attr( $this->param( 'apiPassword' ) ); ?>">
			<label for="tvd-ic-api-password"><?php echo esc_html__( "Application password", 'thrive-dash' ) ?></label>
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
