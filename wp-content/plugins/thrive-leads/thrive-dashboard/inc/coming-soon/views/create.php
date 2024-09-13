<div class="create-page-wrapper">
	<p>
		<?php echo __( 'Please keep in mind that activating the feature is not enough for the "Coming Soon" page to be visible. You will first have to set it up, using the field below.', 'thrive-dash' ); ?>
	</p>
	<div class="page-source-wrapper">
		<button id="create-new-page" class="enable-state" data-enable="create">
			<span class="tvd-icon-plus-circle enable-state" data-enable="create"></span>
			<?php echo __( 'Create new page', 'thrive-dash' ); ?>
		</button>
		<div class="new-page-wrapper">
			<input id="new-page-title" name="page-title" type="text" placeholder="Page title">
			<div class="new-page-actions">
				<button class="dismiss-new-page enable-state" data-enable="search"><?php echo __( 'Cancel', 'thrive-dash' ); ?></button>
				<button class="add-new-page"><?php echo __( 'Add page', 'thrive-dash' ); ?></button>
			</div>
		</div>
	</div>
</div>
