<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<?php
/** @var $this Thrive_Dash_List_Connection_MailPoet */
?>
<?php $installed = $this->pluginInstalled(); ?>
<?php if ( ! empty( $installed ) ) : ?>
	<div class="tvd-row">
		<p><?php echo esc_html__( 'Click the button below to enable MailPoet integration.', 'thrive-dash' ) ?></p>
	</div>
	<form>
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>">

		<?php if ( count( $installed ) > 1 ) : ?>
			<?php $version = $this->param( 'version' ); ?>
			<div class="tvd-row">
				<div class="tvd-col tvd-s12 tvd-m6">
					<p>
						<input class="tvd-new-connection-yes" name="connection[version]" type="radio" value="2"
							   id="tvd-new-connection-yes" <?php echo empty( $version ) || $version == 2 ? 'checked="checked"' : ''; ?> />
						<label for="tvd-new-connection-yes"><?php echo esc_html__( 'Version 2', 'thrive-dash' ); ?></label>
					</p>
				</div>
				<div class="tvd-col tvd-s12 tvd-m6">
					<p>

						<input class="tvd-new-connection-no" name="connection[version]" type="radio" value="3"
							   id="tvd-new-connection-no" <?php echo $version == 3 ? 'checked="checked"' : ''; ?> />
						<label for="tvd-new-connection-no"><?php echo esc_html__( 'Version 3', 'thrive-dash' ); ?></label>
					</p>
				</div>
			</div>
		<?php else : ?>
			<input type="hidden" name="connection[version]" value="<?php echo esc_attr( $installed[0] ); ?>">
		<?php endif; ?>
		<?php $this->display_video_link(); ?>
	</form>
	<div class="tvd-card-action">
		<div class="tvd-row tvd-no-margin">
			<div class="tvd-col tvd-s12 tvd-m6">
				<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo esc_html__( 'Cancel', 'thrive-dash' ) ?></a>
			</div>
			<div class="tvd-col tvd-s12 tvd-m6">
				<a class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn tvd-api-connect"
				   href="javascript:void(0)"><?php echo esc_html__( 'Connect', 'thrive-dash' ) ?></a>
			</div>
		</div>
	</div>
<?php else : ?>
	<p><?php echo esc_html__( 'You currently do not have any MailPoet WP plugin installed or activated.', 'thrive-dash' ) ?></p>
	<br>
	<div class="tvd-card-action">
		<div class="tvd-row tvd-no-margin">
			<div class="tvd-col tvd-s12">
				<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo esc_html__( 'Cancel', 'thrive-dash' ) ?></a>
			</div>
		</div>
	</div>
<?php endif ?>
