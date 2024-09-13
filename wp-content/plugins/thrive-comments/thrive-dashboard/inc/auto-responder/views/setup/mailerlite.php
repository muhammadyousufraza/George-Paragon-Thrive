<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<?php $version = $this->param( 'version' );
		$is_v1         = empty( $version ) || (int) $version === 1;
		$is_v2         = ! empty( $version ) && (int) $version === 2;

		?>
		<div class="tvd-input-field">
			<input id="tvd-mm-api-key" type="text" name="connection[key] " value="<?php echo esc_attr( $this->param( 'key' ) ); ?>">
			<label for="tvd-mm-api-key" class="tvd-toggle-version-1 <?php echo $is_v1 ? '' : 'tvd-hide' ?>"><?php echo esc_html__( "API key", 'thrive-dash' ); ?></label>
			<label for="tvd-mm-api-key" class="tvd-toggle-version-2 <?php echo ( empty( $this->get_key() ) && empty( $version ) ) || $is_v2 ? '' : 'tvd-hide' ?>"><?php echo esc_html__( "API key", 'thrive-dash' ); ?></label>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding tvd-margin-bottom">
			<p>
				<input class="tvd-version-1 tvd-change-version" name="connection[version]" type="radio" value="1"
					   id="tvd-version-1" <?php echo ! empty( $this->get_key() ) && $is_v1 ? 'checked="checked"' : ''; ?> />
				<label for="tvd-version-1"><?php echo esc_html__( 'Version 1', 'thrive-dash' ); ?></label>
			</p>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding  tvd-margin-bottom">
			<p>
				<input class="tvd-version-2 tvd-change-version" name="connection[version]" type="radio" value="2"
					   id="tvd-version-2" <?php echo ( empty( $this->get_key() ) && empty( $version ) ) || $is_v2 ? 'checked="checked"' : ''; ?> />
				<label for="tvd-version-2"><?php echo esc_html__( 'Version 2', 'thrive-dash' ); ?></label>
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

