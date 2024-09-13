<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-gr-api-key" type="text" name="connection[key]" value="<?php echo esc_attr( $this->param( 'key' ) ); ?>">
			<label for="tvd-gr-api-key"><?php echo esc_html__( "API key", 'thrive-dash' ) ?></label>
		</div>
		<?php $version = $this->param( 'version' ); ?>
		<div class="tvd-input-field tvd-extra-options <?php echo empty( $version ) || $version == 2 ? 'tvd-hide"' : ''; ?>">
			<?php $url = $this->param( 'url' ); ?>
			<input id="tvd-gr-api-url" type="text" name="connection[url]"
				   value="<?php echo ! empty( $url ) ? esc_url( $url ) : 'https://api.getresponse.com/v3' ?>">
			<label for="tvd-gr-api-url"><?php echo esc_html__( "API URL", 'thrive-dash' ) ?></label>
		</div>
		<p class="tvd-extra-options <?php echo empty( $version ) || $version == 2 ? 'tvd-hide"' : ''; ?>"><strong><?php echo esc_html__( 'Notification:', 'thrive-dash' ) ?> </strong><?php echo esc_html__( 'The Enterprise URL is only needed if you\'re connecting to an Enterprise GetResponse Account.', 'thrive-dash' ) ?></p>
		<div class="tvd-input-field tvd-extra-options <?php echo empty( $version ) || $version == 2 ? 'tvd-hide"' : ''; ?>">
			<?php $enterprise = $this->param( 'enterprise' ); ?>
			<input id="tvd-gr-api-enterprise" type="text" name="connection[enterprise]"
				   value="<?php echo ! empty( $enterprise ) ? esc_attr( $enterprise ) : '' ?>">
			<label for="tvd-gr-api-enterprise"><?php echo esc_html__( "Enterprise URL", 'thrive-dash' ) ?></label>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding">
			<p>
				<input class="tvd-version-2 tvd-api-hide-extra-options" name="connection[version]" type="radio" value="2"
					   id="tvd-version-2" <?php echo empty( $version ) || $version == 2 ? 'checked="checked"' : ''; ?> />
				<label for="tvd-version-2"><?php echo esc_html__( 'Version 2', 'thrive-dash' ); ?></label>
			</p>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding">
			<p>
				<input class="tvd-version-3 tvd-api-show-extra-options" name="connection[version]" type="radio" value="3"
					   id="tvd-version-3" <?php echo ! empty( $version ) && $version == 3 ? 'checked="checked"' : ''; ?> />
				<label for="tvd-version-3"><?php echo esc_html__( 'Version 3', 'thrive-dash' ); ?></label>
			</p>
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

