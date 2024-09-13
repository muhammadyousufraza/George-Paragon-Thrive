<h2 class="tvd-card-title"><?php echo esc_html( $this->get_title() ); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo esc_attr( $this->get_key() ); ?>"/>
		<div class="tvd-row">
			<div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding tvd-browsing-history"><?php echo esc_html__( 'reCaptcha version', 'thrive-dash' ) ?></div>
			<div class="tvd-col tvd-s12 tvd-m3 tvd-no-padding">
				<p>
					<input class="tvd-recaptcha-version" name="connection[version]" type="radio" value="v2"
						   id="tvd-recaptcha-v2" <#- item && item.site_key && item.connection && item.connection.version ==='v2'? 'checked="checked"':''#> >
					<label for="tvd-recaptcha-v2">V2</label>
				</p>
			</div>
			<div class="tvd-col tvd-s12 tvd-m3 tvd-no-padding">
				<p>
					<input class="tvd-recaptcha-version" name="connection[version]" type="radio" value="v3"
						   id="tvd-recaptcha-v3" <#- ! item || ( item && item.connection && item.connection.version ==='v3' ) ? 'checked="checked"':''#>>
					<label for="tvd-recaptcha-v3">V3</label>
				</p>
			</div>
		</div>
		<div class="tvd-row tvd-use-browsing-history"
		<#= item && ( item.connection && item.connection.version !=='v3' ) ? 'style="display:none;"' : '' #> >
		<div class="tvd-col tvd-s12 tvd-m9 tvd-no-padding "><?php echo esc_html__( 'Use browsing history to determine genuine traffic (recommended)', 'thrive-dash' ) ?></div>
		<div class="tvd-col tvd-s12 tvd-m3 tvd-no-padding tvd-switch">
			<label>
				<input type="checkbox" name="connection[browsing_history]" value="1" <#- ! item || ( item && item.connection && item.connection.browsing_history ) ? 'checked="checked"':''#>>
				<span class="tvd-lever"></span>
			</label>
		</div>
</div>
<div class="tvd-row tvd-use-browsing-history" <#= item && ( item.connection && item.connection.version !=='v3' ) ?  'style="display:none;"' : '' #> >
<div class="tvd-col tvd-s12 tvd-m9 tvd-no-padding "><?php echo esc_html__( 'Reject the scores below:', 'thrive-dash' ) ?></div>
<div class="tvd-row" style="margin-bottom: 0px;">
	<div class="tvd-col tvd-m10">
		<input type="range" id="rangeInputId" oninput="rangeOutputId.value = rangeInputId.value" name="connection[threshold]" min="0" max="1" step="0.1"
			   value="<#= item && ( item.connection && item.connection.threshold )?  item.connection.threshold: 0.5 #>"/>
	</div>
	<div class="tvd-col tvd-margin-top">
		<output id="rangeOutputId"><#= item && ( item.connection && item.connection.threshold )? item.connection.threshold: 0.5 #></output>
	</div>
</div>
</div>

<div class="tvd-input-field tvd-row">
	<input id="tvd-rc-api-site-key" type="text" name="site_key"
		   value="<#- item && item.site_key #>">
	<label for="tvd-rc-api-site-key"><?php echo esc_html__( 'Site key', 'thrive-dash' ) ?></label>
</div>
<div class="tvd-input-field tvd-row">
	<input id="tvd-ac-api-secret-key" type="text" name="secret_key"
		   value="<#- item && item.secret_key #>">
	<label for="tvd-ac-api-secret-key"><?php echo esc_html__( 'Secret key', 'thrive-dash' ) ?></label>
</div>
<div class="tvd-row">
    <?php $this->display_video_link(); ?>
</div>
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
