<?php
/** var $this Thrive_Dash_List_Connection_SendinblueEmail */
?>
<h2 class="tvd-card-title"><?php echo $this->get_title() ?></h2>
<p class="tvd-center">
	<?php echo __( 'formerly SendinBlue', 'thrive-dash' ); ?>
</p>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo $this->get_key() ?>"/>
		<input type="hidden" name="connection[v3]" value="1"/>
		<div class="tvd-input-field">
			<input id="tvd-aw-api-email" type="text" name="connection[key]"
			       value="<?php echo $this->param( 'key' ) ?>">
			<label for="tvd-aw-api-email"><?php echo __( 'API key', 'thrive-dash' ) ?></label>
		</div>
		<p><?php echo __( 'Would you also like to connect to the Transactional Email Service ?', 'thrive-dash' ) ?></p>
		<br/>
		<div class="tvd-col tvd-s12 tvd-m4 tvd-no-padding">
			<p>
				<input class="tvd-new-connection-yes" name="connection[new_connection]" type="radio" value="1"
				       id="tvd-new-connection-yes" <?php echo $this->param( 'new_connection' ) == 1 ? 'checked="checked"' : ''; ?> />
				<label for="tvd-new-connection-yes"><?php echo __( 'Yes', 'thrive-dash' ); ?></label>
			</p>
		</div>
		<div class="tvd-col tvd-s12 tvd-m4 tvd-no-padding">
			<p>
				<?php $connection = $this->param( 'new_connection' ); ?>
				<input class="tvd-new-connection-no" name="connection[new_connection]" type="radio" value="0"
				       id="tvd-new-connection-no" <?php echo empty( $connection ) || $connection == 0 ? 'checked="checked"' : ''; ?> />
				<label for="tvd-new-connection-no"><?php echo __( 'No', 'thrive-dash' ); ?></label>
			</p>
		</div>
		<?php $this->display_video_link(); ?>
	</form>
</div>
<?php if ( ! empty( $this->param( 'key' ) ) && ! $this->is_v3() ) : ?>
	<a class="tvd-api-upgrade-v3 tvd-api-connect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-blue tvd-full-btn tvd-margin-bottom"
	   href="javascript:void(0)"><?php echo __( 'I want to use API v3', 'thrive-dash' ) ?>
	</a>
<?php endif ?>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo __( 'Cancel', 'thrive-dash' ) ?></a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-connect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo __( 'Connect', 'thrive-dash' ) ?></a>
		</div>
	</div>
</div>
