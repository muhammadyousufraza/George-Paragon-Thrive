<div class="tcb-background-video-controls">
	<div class="tve-dynamic-source-tabs"></div>
<!--	todo move what's below me -->
	<div class="tve-advanced-controls custom-fields-state p-10" data-state="dynamic">
		<div class="custom-fields-options">
			<label><?php echo __( 'Custom source', 'thrive-cb' ); ?></label>
			<select data-fn="selectCustomField" class="change"></select>
		</div>
	</div>

	<div class="tve-advanced-controls custom-fields-more-options" hidden style="margin-top: -5px">
		<div class="dropdown-header" data-prop="advanced">
			<span>
				<?php echo __( 'More options', 'thrive-cb' ); ?>
			</span>
		</div>

		<div class="dropdown-content pt-0">
			<label><?php echo __( 'If not available', 'thrive-cb' ); ?></label>
			<select class="custom-fields-placeholder-options change mb-5" data-fn="selectPlaceholder">
				<option value="hide"><?php echo __( 'Hide element', 'thrive-cb' ); ?></option>
				<option value="replace"><?php echo __( 'Show default', 'thrive-cb' ); ?></option>
			</select>
			<div class="custom-fields-placeholder-input">
				<label><?php echo __( 'Default', 'thrive-cb' ); ?></label>
				<div class="responsivevideo-picker-input"></div>
			</div>
		</div>
	</div>
</div>