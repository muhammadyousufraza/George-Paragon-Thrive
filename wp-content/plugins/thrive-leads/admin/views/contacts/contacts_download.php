<div class="tve-contacts-download">
	<div class="tvd-input-field tvd-margin-top-xsmall">
		<select class="tve-manager-source" name="tve-manager-source" autocomplete="off" id="tve-manager-source">
			<option value="all"><?php echo __( 'All Contacts in Database', 'thrive-leads' ); ?></option>

			<?php if ( ! empty( $contacts_list->items ) ): ?>
				<option value="current_report"><?php echo __( 'All Contacts in Current Report', 'thrive-leads' ); ?></option>
			<?php endif; ?>
		</select>
		<label for="tve-manager-source"><?php echo __( 'I want to download', 'thrive-leads' ); ?></label>
	</div>

	<div class="tve-manager-file-type" style="margin: 0 40px">
		<div class="tvd-input-field tvd-margin-top-xsmall">
			<select class="tve-manager-type" name="tve-manager-type" id="tve-manager-type">
				<option value="excel"><?php echo __( 'Excel', 'thrive-leads' ); ?> (.xls)</option>
				<option value="csv"><?php echo __( 'Comma-Separated Values', 'thrive-leads' ); ?> (.csv)</option>
			</select>
			<label for="tve-manager-type"><?php echo __( 'As file', 'thrive-leads' ); ?></label>
		</div>
	</div>

	<a href="#" class="tvd-waves-effect tvd-waves-light tvd-btn tvd-no-load tvd-btn-green tve-manager-download-button"><?php echo __( 'Start Download', 'thrive-leads' ); ?></a>
</div>
<div style="display: none">
	<img style="width: 200px;" class="tve-pending-spinner" src="<?php echo includes_url(); ?>js/thickbox/loadingAnimation.gif">
</div>

<div id="tve-leads-delete-contact" class="tvd-modal tvd-red">
	<div class="tvd-modal-content">
		<a href="javascript:void(0)" class=" tvd-modal-action tvd-modal-close tvd-modal-close-x">
			<i class="tvd-icon-close2"></i>
		</a>
		<h3 class="tvd-modal-title tvd-center-align">
			<?php echo __( 'Are you sure you want to remove this contact?', 'thrive-leads' ); ?>
		</h3>
	</div>
	<div class="tvd-modal-footer">
		<div class="tvd-row">
			<div class="tvd-col tvd-s12 tvd-m6">
				<a href="javascript:void(0)" class="tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-light tvd-waves-effect tvd-modal-close">
					<?php echo __( 'Cancel', 'thrive-leads' ); ?>
				</a>
			</div>
			<div class="tvd-col tvd-s12 tvd-m6">
				<a href="" class="tvd-waves-effect tvd-waves-light tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-light tve-modal-delete-contact tvd-right">
					<?php echo __( 'Yes', 'thrive-leads' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>



