<?php global $tve_leads_help_videos ?>
<div id="tve-content">
	<script type="text/javascript">
		var TVE_Page_Data = {
			'groups': <?php echo json_encode( $dashboard_data['groups'] ) ?>,
			'shortcodes': <?php echo json_encode( $dashboard_data['shortcodes'] ) ?>,
			globalSettings: <?php echo json_encode( $dashboard_data['global_settings'] ) ?>,
		};

		function thrive_leads_init() {
			ThriveLeads.objects.groups = new ThriveLeads.collections.Groups(<?php echo json_encode( $dashboard_data['groups'] ) ?>);
			ThriveLeads.objects.shortcodeCollection = new ThriveLeads.collections.Shortcode(<?php echo json_encode( $dashboard_data['shortcodes'] ) ?>);
			ThriveLeads.objects.TwoStepLightboxCollection = new ThriveLeads.collections.TwoStepLightbox(<?php echo json_encode( $dashboard_data['two_step_lightbox'] ) ?>);
			ThriveLeads.objects.OneClickSignupCollection = new ThriveLeads.collections.OneClickSignup(<?php echo json_encode( $dashboard_data['one_click_signup'] ) ?>);
			ThriveLeads.objects.BreadcrumbsCollection = new ThriveLeads.collections.BreadcrumbsCollection(<?php echo json_encode( $dashboard_data['breadcrumbs'] ) ?>);
		}

		if ( typeof ThriveLeads === 'undefined' ) {
			window.addEventListener( 'load', thrive_leads_init );
		} else {
			thrive_leads_init();
		}
	</script>

	<script>

		jQuery( document ).ready( function () {
			jQuery( '.tvd-modal-trigger' ).leanModal( {
				dismissible: true,
				in_duration: 200,
				out_duration: 300
			} );
		} );

	</script>

	<script type="text/template" id="tve-leads-dashboard">
		<div class="tve-header">
			<nav id="tl-nav">
				<div class="nav-wrapper">
					<div class="tve-logo tvd-left">
						<a href="<?php menu_page_url( 'thrive_leads_dashboard' ); ?>"
						   title="<?php echo __( 'Thrive Leads Home', 'thrive-leads' ) ?>">
							<?php echo '<img src="' . plugins_url( 'thrive-leads/admin/img' ) . '/tl-logo-full-white.png" > '; ?>
						</a>
					</div>
					<?php require_once( dirname( __FILE__ ) . '/leads_menu.php' ) ?>
				</div>
			</nav>
		</div>
		<div class="tve-leads-breadcrumbs-wrapper">
			<?php require_once( dirname( __FILE__ ) . '/leads_breadcrumbs.php' ) ?>
		</div>
		<?php echo tvd_get_individual_plugin_license_message( new TL_Product() ); ?>
        <div id="tl-dashboard-wrapper">
            <# if(has_non_uniques) { #>
            <div class="messagesList">
                <div class="error">
                    <p>
                        <?php echo __( 'We\'ve detected that your Thrive Leads database could be optimized', 'thrive-leads' ) ?>
                        - <a href="javascript:void(0)"
                             class="tve-leads-db-optimize"><?php echo __( 'click here to optimize it', 'thrive-leads' ) ?></a>.
                        <?php echo __( 'This will clear some old data that it not used anymore, and free up database space.', 'thrive-leads' ) ?>
                    </p>
                </div>
            </div>
            <# } #>
            <?php tve_leads_check_data_updates(); ?>
            <h3 class="tvd-title"><?php echo __( "Today's Summary", 'thrive-leads' ) ?></h3>
            <div class="tvd-row tvd-collapse tvd-summary-row">
                <div class="tvd-col tvd-s12 tvd-m4">
                    <div class="tvd-card tvd-greenish tvd-medium-xsmall">
                        <div class="tvd-card-content">
                            <span class="tvd-icon-eye tvd-stats-icon"></span>
                            <p class="tvd-stats">
                                <?php echo $dashboard_data['summary']['impressions'] ?>
                                <span><?php echo _n( 'Unique Impression', 'Unique Impressions', $dashboard_data['summary']['impressions'], 'thrive-leads' ) ?></span>
                            </p>
                        </div>
                        <div class="tvd-card-action">
                            <a class="tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-light tvd-waves-effect"
                               href="<?php menu_page_url( 'thrive_leads_reporting', true ); ?>"><?php echo __( 'View More', 'thrive-leads' ) ?></a>
                        </div>
                    </div>
                </div>
                <div class="tvd-col tvd-s12 tvd-m4">
                    <div class="tvd-card tvd-teal tvd-medium-xsmall">
                        <div class="tvd-card-content">
                            <span class="tvd-icon-paper-plane tvd-stats-icon"></span>
                            <p class="tvd-stats">
                                <?php echo $dashboard_data['summary']['conversions'] ?>
                                <span><?php echo _n( 'Conversion', 'Conversions', $dashboard_data['summary']['conversions'], 'thrive-leads' ) ?></span>
                            </p>
                        </div>
                        <div class="tvd-card-action">
                            <a class="tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-light tvd-waves-effect"
                               href="<?php menu_page_url( 'thrive_leads_reporting', true ); ?>"><?php echo __( 'View More', 'thrive-leads' ) ?></a>
                        </div>
                    </div>
                </div>
                <div class="tvd-col tvd-s12 tvd-m4">
                    <div class="tvd-card tvd-blue tvd-medium-xsmall">
                        <div class="tvd-card-content">
                            <span class="tvd-icon-line-chart tvd-stats-icon"></span>
                            <p class="tvd-stats">
                                <?php echo tve_leads_conversion_rate( $dashboard_data['summary']['impressions'], $dashboard_data['summary']['conversions'] ) ?>
                                <span><?php echo __( 'Conversion Rate', 'thrive-leads' ) ?></span>
                            </p>
                        </div>
                        <div class="tvd-card-action">
                            <a class="tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-light tvd-waves-effect"
                               href="<?php menu_page_url( 'thrive_leads_reporting', true ); ?>"><?php echo __( 'View More', 'thrive-leads' ) ?></a>
                        </div>
                    </div>
                </div>
                <div id="no-form-overlay">
                    <div class="tvd-card tvd-white">
                        <div class="tvd-card-content tvd-center-align">
                            <p><?php echo __( 'In this area, a daily snapshot will be displayed. Stats will be gathered automatically as soon as you create and publish at least one form using the options below.', 'thrive-leads' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tve-lead-groups">
                <div class="tvd-row">
                    <h3 class="tvd-left tvd-title tvd-margin-right">
                        <?php echo __( 'Lead Groups', 'thrive-leads' ) ?>
                    </h3>
                    <a class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-blue tve-add-group tvd-left"
                       href="javascript:void(0)"
                       title="<?php echo __( 'Add New Lead Group' ) ?>"
                       id="tve-lead-add"><?php echo __( 'Add New', 'thrive-leads' ) ?></a>
                </div>
                <div class="hide-no-groups">
                    <div class="tvd-group-header">
                        <div class="tvd-row">
                            <div class="tvd-col tvd-s2">
                                <h5>&nbsp;</h5>
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Impressions', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Impressions', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Conversions', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Conversions', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate"
                                    title="<?php echo __( 'Conversion Rate', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Conversion Rate', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s2">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Display On Desktop', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Display On Desktop', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s2">
                                <h5 class="tvd-truncate"
                                    title="<?php echo __( 'Display On Mobile', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Display On Mobile', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s3">
                                <h5>&nbsp;</h5>
                            </div>
                        </div>
                    </div>
                    <ul class="" data-collapsible="expandable" id="tve-group-list"></ul>
                </div>

                <div class="show-no-groups" style="display: none">
                    <p>
                        <?php echo __( "You don't have any Lead Groups created yet. Click the 'Add new' button to create one.", 'thrive-leads' ) ?>
                    </p>
                    <a data-source="vWEfgUJQ0LU"
                       class="tvd-open-video"><img
                                src="<?php echo TVE_LEADS_ADMIN_URL . "img/video-thumb-lead-groups.jpg" ?>" alt=""/></a>
                </div>
            </div>

            <div id="tve-lead-shortcodes">
                <div class="tvd-row">
                    <h3 class="tvd-left tvd-title tvd-margin-right">
                        <?php echo __( 'Lead Shortcodes', 'thrive-leads' ) ?>
                    </h3>
                    <a class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-blue tvd-left tve-add-shortcode"
                       href="javascript:void(0)" title="<?php echo __( 'Add New Lead Shortcode' ) ?>"
                       id="tve-lead-add"><?php echo __( 'Add New', 'thrive-leads' ) ?></a>
                </div>
                <ul class="tvd-collection tvd-with-header tve-shortcode-list-header" style="display: none;"
                    id="tve-shortcode-list">
                    <li class="tvd-collection-header">
                        <div class="tvd-row">
                            <div class="tvd-col tvd-s2">
                                &nbsp;
                            </div>
                            <div class="tvd-col tvd-s3">
                                &nbsp;
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Form Impressions', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Form Impressions', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Conversions', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Conversions', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Conversion Rate', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Conversion Rate', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Content Locking', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Content Locking', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s3">
                                &nbsp;
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="no-shortcodes">
                    <p>
                        <?php echo __( "You don't have any Lead Shortcodes created yet. Click the 'Add new' button to create one.", 'thrive-leads' ) ?>
                    </p>
                    <a data-source="9FRCaY26fPo"
                       class="tvd-open-video"><img
                                src="<?php echo TVE_LEADS_ADMIN_URL . "img/video-thumb-shortcode.jpg" ?>" alt=""/></a>
                </div>
            </div>

            <div id="tve-two-step-lightbox">
                <div class="tvd-row">
                    <h3 class="tvd-left tvd-title tvd-margin-right">
                        <?php echo __( 'ThriveBoxes', 'thrive-leads' ) ?>
                    </h3>
                    <a class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-blue tvd-left tve-add-two-step-lightbox"
                       href="javascript:void(0)" title="<?php echo __( 'Add New ThriveBox' ) ?>"
                       id="tve-lead-add"><?php echo __( 'Add New', 'thrive-leads' ) ?></a>
                </div>
                <ul class="tvd-collection tvd-with-header"
                    id="tve-two-step-lightbox-list" style="display: none;">
                    <li class="tvd-collection-header">
                        <div class="tvd-row">
                            <div class="tvd-col tvd-s3">
                                &nbsp;
                            </div>
                            <div class="tvd-col tvd-s3">
                                &nbsp;
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Form Impressions', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Form Impressions', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Conversions', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Conversions', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Conversion Rate', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Conversion Rate', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s2">
                                &nbsp;
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="no-two-step-lightbox">
                    <p>
                        <?php echo __( "You don't have any ThriveBoxes created yet. Click the 'Add new' button to create one.", 'thrive-leads' ) ?>
                    </p>
                    <a data-source="d9rgh2e12aM"
                       class="tvd-open-video"><img
                                src="<?php echo TVE_LEADS_ADMIN_URL . "img/video-thumb-2-step.jpg" ?>" alt=""/></a>
                </div>
            </div>

            <div id="tve-one-click-signup">
                <div class="tvd-row">
                    <h3 class="tvd-left tvd-title tvd-margin-right">
                        <?php echo __( 'Signup Segue - One Click Signup Links', 'thrive-leads' ) ?>
                    </h3>
                    <a class="tve-add-one-click-signup tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-blue tvd-left"
                       href="javascript:void(0)" title="<?php echo __( 'Add New Signup Segue' ) ?>"
                       id="tve-lead-add"><?php echo __( 'Add New', 'thrive-leads' ) ?></a>
                </div>
                <ul class="tvd-collection tvd-with-header" id="tve-one-click-signup-list" style="display: none;">
                    <li class="tvd-collection-header">
                        <div class="tvd-row">
                            <div class="tvd-col tvd-s3">
                                &nbsp;
                            </div>
                            <div class="tvd-col tvd-s3">
                                &nbsp;
                            </div>
                            <div class="tvd-col tvd-s1">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Signups', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Signups', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s2">
                                <h5 class="tvd-truncate" title="<?php echo __( 'Service', 'thrive-leads' ) ?>">
                                    <?php echo __( 'Service', 'thrive-leads' ) ?>
                                </h5>
                            </div>
                            <div class="tvd-col tvd-s3">
                                &nbsp;
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="no-one-click-signup">
                    <p>
                        <?php echo __( "You don't have any Signup Segues created yet. Click the 'Add new' button to create one.", 'thrive-leads' ) ?>
                    </p>
                    <a data-source="5GdzdRKL7G4"
                       class="tvd-open-video"><img
                                src="<?php echo TVE_LEADS_ADMIN_URL . "img/video-thumb-signup-segue.png" ?>"
                                alt=""/></a>
                </div>
            </div>
        </div>
	</script>
</div>
