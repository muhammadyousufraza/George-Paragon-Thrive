<div class="admin-notice notice-error notice">
	<p>
		<?php echo esc_html__( "We've made some significant performance improvements to Thrive Leads. As a result, some of the database entries need to be migrated to a new, improved format. Note: The Reporting section and the main dashboard will not display accurate data until this migration is performed!", 'thrive-leads' ); ?>
		<?php echo sprintf( esc_html__( 'Click %shere%s to start the migration.', 'thrive-leads' ), '<a href="' . admin_url('admin.php?page=thrive_leads_update') . '">', '</a>' ); ?>
	</p>
</div>
