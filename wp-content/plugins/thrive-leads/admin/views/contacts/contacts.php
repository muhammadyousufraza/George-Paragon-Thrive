<script>
	ThriveLeads.objects.BreadcrumbsCollection = new ThriveLeads.collections.BreadcrumbsCollection(<?php echo json_encode( $dashboard_data['breadcrumbs'] ) ?>);
	ThriveLeads.objects.groups = new ThriveLeads.collections.Groups(<?php echo json_encode( $dashboard_data['groups'] ) ?>);
</script>
<div id="tve-content">
	<div id="tve-contacts">
		<div class="tve-header">
			<nav id="tl-nav">
				<div class="nav-wrapper">
					<div class="tve-logo tve_leads_clearfix tvd-left">
						<a href="<?php menu_page_url( 'thrive_leads_dashboard' ); ?>"
						   title="<?php echo __( 'Thrive Leads Home', 'thrive-leads' ) ?>">
							<?php echo '<img src="' . plugins_url( 'thrive-leads/admin/img' ) . '/tl-logo-full-white.png" > '; ?>
						</a>
					</div>
					<?php require_once( dirname( __FILE__, 2 ) . '/leads_menu.php' ) ?>
				</div>
			</nav>
		</div>
		<div class="tve-leads-breadcrumbs-wrapper">
			<?php require_once( dirname( __FILE__, 2 ) . '/leads_breadcrumbs.php' ) ?>
		</div>
		<?php echo tvd_get_individual_plugin_license_message( new TL_Product() ); ?>
		<?php tve_leads_check_data_updates(); ?>
		<h3 class="tvd-title"><?php echo __( 'Leads Export', 'thrive-leads' ); ?></h3>
		<div class="tvd-v-spacer"></div>
		<div class="tve-contact-wrapper">
			<form method="get" action="<?php admin_url( 'admin.php' ); ?>">
				<input type="hidden" name="page" value="thrive_leads_contacts"/>

				<div id="tve-contacts-table">
					<?php $contacts_list->display(); ?>
				</div>
			</form>
		</div>
		<div id="tve-download-manager"><?php require_once( __DIR__ . '/contacts_download.php' ) ?></div>
	</div>
</div>
<div class="tvd-v-spacer"></div>
<a class="tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-dark tvd-waves-effect"
   href="<?php echo admin_url( 'admin.php?page=thrive_leads_dashboard' ); ?>"
   title="<?php echo __( 'Back to Thrive Leads Home' ) ?>"
   id="tve-asset-group-dashboard">
	&laquo; <?php echo __( 'Back to Thrive Leads Home', 'thrive-leads' ) ?>
</a>
