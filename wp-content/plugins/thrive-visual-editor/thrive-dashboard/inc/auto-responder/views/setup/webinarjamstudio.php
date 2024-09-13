<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-wj-api-key" type="text" name="connection[key]"
				   value="<?php echo esc_attr( $this->param( 'key' ) ); ?>">
			<label for="tvd-wj-api-key"><?php echo esc_html__( "API key", 'thrive-dash' ) ?></label>
		</div>
		<?php $version = $this->param( 'version' ); ?>

		<?php if ( $this->is_connected() && 4 !== (int) $version ) : ?>
			<div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding">
				<p>
					<input class="tvd-version-1 tvd-api-show-extra-options" name="connection[version]" type="radio" value="1"
						   id="tvd-version-1" <?php echo ! empty( $version ) && $version == 1 ? 'checked="checked"' : ''; ?> />
					<label for="tvd-version-1"><?php echo esc_html__( 'New version', 'thrive-dash' ); ?></label>
				</p>
			</div>

			<div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding">
				<p>
					<input class="tvd-version-0 tvd-api-hide-extra-options" name="connection[version]" type="radio" value="0"
						   id="tvd-version-0" <?php echo empty( $version ) || $version == 0 ? 'checked="checked"' : ''; ?> />
					<label for="tvd-version-0"><?php echo esc_html__( 'Old version', 'thrive-dash' ); ?></label>
				</p>
			</div>
		<?php endif; ?>

		<div class="tvd-col tvd-s12 tvd-m12 tvd-no-padding">
			<p>
				<input class="tvd-version-4 tvd-api-show-extra-options" name="connection[version]" type="radio" value="<?php echo esc_attr( TD_Inbox::WEBINARJAM_V4 ); ?>"
					   id="tvd-version-4" <?php echo empty( $version ) || $version == TD_Inbox::WEBINARJAM_V4 ? 'checked="checked"' : ''; ?> />
				<label for="tvd-version-4">
					<?php echo sprintf( esc_html__( 'Version %s', 'thrive-dash' ), esc_html( TD_Inbox::WEBINARJAM_V4 ) ); ?>
				</label>
			</p>
		</div>

		<?php $this->display_video_link(); ?>
	</form>
</div>
<div class="tvd-row tvd-card-title">
	<?php
	echo esc_html__( "When you switch between versions, please make sure to update all forms connected to this API." );
	?>
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
