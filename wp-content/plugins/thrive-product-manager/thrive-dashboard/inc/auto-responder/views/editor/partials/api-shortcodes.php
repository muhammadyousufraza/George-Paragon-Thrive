<h4 class="tl-clickable tl-toggle-tab-display collapsed tvd-pointer" data-target="#tve-shortcode-list">
	<?php echo esc_html__( 'Available Shortcodes', 'thrive-dash' ) ?>
</h4>
<div class="tvd-relative">
	<ul class="tvd-collection tvd-not-visible" id="tve-shortcode-list">
		<li class="tvd-collection-item">
			<div class="tve-copy-row">
				<div class="tve-shortcode-input">
					<div class="tvd-input-field">
						<input readonly="readonly" type="text" value="[lead_email]" class="tve-copy tve_lightbox_input"/>
					</div>
				</div>
				<div class="tve-shortcode-copy">
					<a class="tve-copy-to-clipboard tve_editor_button tve_editor_button_blue" href="javascript:void(0)">
						<span class="tve-copy-text"><?php echo esc_html__( 'Copy', 'thrive-dash' ) ?></span>
					</a>
				</div>
				<div class="tvd-col tvd-s6">
					<div class="tve-align-stupid-text">
						<?php echo esc_html__( 'Displays the email of the person who opted in.', 'thrive-dash' ) ?>
					</div>
				</div>
			</div>
		</li>
		<li class="tvd-collection-item">
			<div class="tve-copy-row tvd-row tvd-collapse tvd-no-mb">
				<div class="tve-shortcode-input">
					<div class="tvd-input-field">
						<input readonly="readonly" type="text" value="[lead_name]" class="tve-copy tve_lightbox_input"/>
					</div>
				</div>
				<div class="tve-shortcode-copy">
					<a class="tve-copy-to-clipboard tve_editor_button tve_editor_button_blue" href="javascript:void(0)">
						<span class="tve-copy-text"><?php echo esc_html__( 'Copy', 'thrive-dash' ) ?></span>
					</a>
				</div>
				<div class="tvd-col tvd-s6">
					<div class="tve-align-stupid-text">
						<?php echo esc_html__( 'Displays the name of the person who opted in.', 'thrive-dash' ) ?>
					</div>
				</div>
			</div>
		</li>
	</ul>
</div>
