<div class="control-grid no-space wrap">
	<label for="v-w-url"><?php echo esc_html__( 'URL', 'thrive-cb' ) ?></label>
	<input type="text" data-setting="url" class="w-url fill" id="v-w-url">
</div>
<div class="inline-message"></div>
<div class="extra-settings">
	<div class="control-grid no-space wrap">
		<label>
			<?php echo esc_html__( 'Player color', 'thrive-cb' ) ?>
		</label>
		<div class="tcb-text-right v-setting-color full-width"></div>
	</div>
	<div class="inline-checkboxes">
		<label class="tcb-checkbox"><input type="checkbox" data-setting="a" value="1" checked="checked"><span><?php echo esc_html__( 'Autoplay', 'thrive-cb' ) ?></span></label>
		<label class="tcb-checkbox"><input type="checkbox" data-setting="p" value="1"><span><?php echo esc_html__( 'Playbar', 'thrive-cb' ) ?></span></label>
		<label class="tcb-checkbox"><input type="checkbox" data-setting="hfs" value="1"><span><?php echo esc_html__( 'Hide full-screen button', 'thrive-cb' ) ?></span></label>
	</div>
</div>
