<?php

define( 'TL_DASHBOARD_PAGE', 'thrive-dashboard_page_thrive_leads_dashboard' );
define( 'TL_REPORTING_PAGE', 'admin_page_thrive_leads_reporting' );
define( 'TL_CONTACTS_PAGE', 'admin_page_thrive_leads_contacts' );
define( 'TL_ASSETS_PAGE', 'admin_page_thrive_leads_asset_delivery' );
define( 'TL_NONCE_KEY', 'tl-verify-track-sender-666' );

add_action( 'admin_init', 'tve_leads_admin_init' );
add_filter( 'tve_dash_admin_product_menu', 'tve_leads_admin_menu' );
add_action( 'admin_enqueue_scripts', 'tve_leads_admin_enqueue', 1 );
add_action( 'admin_enqueue_scripts', 'tve_leads_dequeue_conflicting_scripts', PHP_INT_MAX );
add_action( 'admin_print_scripts', 'tve_leads_remove_junk_scripts', 11 );
add_action( 'admin_footer', 'tve_leads_output_wysiwyg_editor' );


add_filter( 'tve_leads_settings_post_types_blacklist', 'tve_leads_settings_post_types_blacklist' );
add_filter( 'nav_menu_items_' . TVE_LEADS_POST_TWO_STEP_LIGHTBOX . '_recent', 'tve_leads_filter_nav_menu_items_two_step_recent' );
/**
 * this filter is applied by thrive themes in custom menu walker
 * this filters if the Extended menu option should be displayed for a menu item
 */
add_filter( 'thrive_display_extended_menu_option', 'filter_thrive_display_extended_menu_option', 10, 2 );

if ( wp_doing_ajax() ) {
	add_action( 'tcb_ajax_load', 'tve_leads_admin_ajax_load' );
	add_filter( 'tve_autoresponder_connection_types', 'tve_leads_ajax_filter_connection_types' );
	add_filter( 'tve_autoresponder_show_submit', 'tve_leads_filter_autoresponder_submit_option' );
	add_action( 'wp_ajax_tve_leads_find_content_action', 'tve_leads_find_content_action' );
	add_action( 'wp_ajax_tve_leads_find_post_and_page_action', 'tve_leads_find_post_and_page_action' );
	add_action( 'wp_ajax_menu-quick-search', 'tve_leads_menu_quick_search', 0 ); //zero priority just because we need to trigger it before WP does
	add_action( 'wp_ajax_tve_leads_get_api_connection_list', 'tve_leads_get_api_connection_list' );
	add_action( 'wp_ajax_tve_leads_get_api_extra_fields', 'tve_leads_get_api_extra_fields' );
	add_action( 'wp_ajax_tve_leads_stop_variation', 'tve_leads_stop_variation' );

	/**
	 * get the list of display settings templates
	 */
	add_filter( 'thrive_display_options_get_templates', 'tve_leads_filter_display_settings_templates' );
	add_filter( 'thrive_display_options_get_template', 'tve_leads_filter_display_settings_get_template', 10, 2 );
}

add_action( 'tve_dash_add_menu_item', function () {
	add_submenu_page( '', 'Thrive Leads Data Upgrade', '', 'manage_options', 'thrive_leads_update', 'tve_leads_data_updates' );
} );

require_once plugin_dir_path( __FILE__ ) . 'inc/helpers.php';

/**
 * called on admin_init hook
 */
function tve_leads_admin_init() {
	add_action( 'wp_ajax_thrive_leads_backend_ajax', 'thrive_leads_backend_ajax' );
	require TVE_LEADS_PATH . 'admin/inc/classes/class-thrive-leads-data-updater.php';
}

/**
 * main entry point for the incoming ajax requests (initiated from backbone)
 *
 * passes the request to the Thrive_Leads_Ajax_Controller for processing
 */
function thrive_leads_backend_ajax() {
	require_once plugin_dir_path( __FILE__ ) . 'controllers/Thrive_Leads_Ajax_Controller.php';

	$response = Thrive_Leads_Ajax_Controller::instance()->handle();

	echo wp_json_encode( $response );
	exit();
}

/**
 * enqueue all required scripts and css
 *
 * @param string $hook
 */
function tve_leads_admin_enqueue( $hook ) {
	/* first, the license check */
	if ( ! tve_leads_license_activated() ) {
		return;
	}

	/* second, the minimum required TCB version */
	if ( ! tve_leads_check_tcb_version() ) {
		return;
	}

	/* load scripts only on our dashboard page, the entry point for the backbone app */
	if ( ! in_array( $hook, array( TL_DASHBOARD_PAGE, TL_REPORTING_PAGE, TL_CONTACTS_PAGE, TL_ASSETS_PAGE, 'admin_page_thrive_leads_update' ) ) ) {
		return;
	}

	/**
	 * specific admin styles
	 */
	tve_leads_enqueue_style(
		'thrive-leads-admin',
		TVE_LEADS_ADMIN_URL . 'css/styles.css'
	);

	global $tve_leads_chart_colors;

	$data = array(
		'url'                                   => array(
			'wp'         => rtrim( get_site_url(), '/' ) . '/',
			'wp_content' => rtrim( WP_CONTENT_URL, '/' ) . '/',
			'admin'      => rtrim( get_admin_url(), '/' ) . '/',
			'includes'   => includes_url(),
			'reporting'  => menu_page_url( 'thrive_leads_reporting', false ),
		),
		'variation_test_type'                   => TVE_LEADS_VARIATION_TEST_TYPE,
		'group_test_type'                       => TVE_LEADS_GROUP_TEST_TYPE,
		'shortcode_test_type'                   => TVE_LEADS_SHORTCODE_TEST_TYPE,
		'two_step_lightbox_test_type'           => TVE_LEADS_TWO_STEP_LIGHTBOX_TEST_TYPE,
		'test_status'                           => array(
			'running'  => TVE_LEADS_TEST_STATUS_RUNNING,
			'archived' => TVE_LEADS_TEST_STATUS_ARCHIVED,
		),
		'tve_leads_post_form_type'              => TVE_LEADS_POST_FORM_TYPE,
		'tve_leads_post_shortcode_type'         => TVE_LEADS_POST_SHORTCODE_TYPE,
		'tve_leads_post_two_step_lightbox_type' => TVE_LEADS_POST_TWO_STEP_LIGHTBOX,
		'default_form_types'                    => tve_leads_prepare_default_form_types(),
		'translations'                          => require plugin_dir_path( __FILE__ ) . 'inc/translations.php',
		'CHART_RED'                             => '#F60000',
		'CHART_GREEN'                           => '#006600',
		'CHART_GREY'                            => '#C0C0C0',
		'CHART_COLORS'                          => $tve_leads_chart_colors,
		'toast_timeout'                         => 3000,
		'security'                              => wp_create_nonce( TL_NONCE_KEY ),
		'date_intervals'                        => array(
			'last_7_days'       => TVE_LAST_7_DAYS,
			'last_30_days'      => TVE_LAST_30_DAYS,
			'this_month'        => TVE_THIS_MONTH,
			'last_month'        => TVE_LAST_MONTH,
			'this_year'         => TVE_THIS_YEAR,
			'last_year'         => TVE_LAST_YEAR,
			'last_12_months'    => TVE_LAST_12_MONTHS,
			'custom_date_range' => TVE_CUSTOM_DATE_RANGE,
		),
		'api_connections'                       => Thrive_List_Manager::get_available_apis( true, [ 'exclude_types' => [ 'captcha', 'email', 'social' ] ] ),
		'license'                               => [
			'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'tl' ),
			'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'tl' ),
			'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( 'tl' ),
			'grace_time'    => TD_TTW_User_Licenses::get_instance()->get_grace_period_left( 'tl' ),
			'link'          => tvd_get_individual_plugin_license_link( 'tl' )
		]
	);

	/**
	 * some plugins that insert scripts incorrectly on our pages
	 */
	wp_dequeue_style( 'youzign_style' );
	wp_deregister_style( 'youzign_style' );

	wp_enqueue_script( 'jquery' );

	/**
	 * Include Materialize. We use the functions from the dashboard because it's already included.
	 */
	tve_dash_enqueue();

	tve_leads_enqueue_script( 'thrive-leads-init', plugins_url( 'thrive-leads/admin/js-min/_init.min.js' ), array(
		'jquery',
		'backbone',
		'tve-dash-main-js',
	) );

	wp_localize_script( 'thrive-leads-init', 'ThriveLeadsConst', $data );

	tve_leads_enqueue_script( 'tve-leads-views', plugins_url( 'thrive-leads/admin/js-min/views.min.js' ), array(
		'jquery',
		'backbone',
		'thrive-leads-init',
	) );
	tve_leads_enqueue_script( 'tve-leads-models', plugins_url( 'thrive-leads/admin/js-min/models.min.js' ), array(
		'jquery',
		'backbone',
		'thrive-leads-init',
	) );


	//autocomplete
	wp_enqueue_script( 'jquery-ui-autocomplete' );

	/* wystia script for popover videos */
	wp_enqueue_script( 'tl-wistia-popover', '//fast.wistia.com/assets/external/popover-v1.js', array(), '', true );

	switch ( $hook ) {
		case TL_DASHBOARD_PAGE:
			wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery' ) );
			tve_dash_enqueue_script( 'tve-dash-highcharts', TVE_DASH_URL . '/js/util/highcharts/highcharts.js', array(
				'jquery',
				'thrive-leads-init',
			), false, false );

			tve_dash_enqueue_script( 'tve-dash-highcharts-more', TVE_DASH_URL . '/js/util/highcharts/highcharts-more.js', array(
				'jquery',
				'thrive-leads-init',
				'tve-dash-highcharts',
			), false, false );
			tve_leads_enqueue_script( 'tve-leads-charts', plugins_url( 'thrive-leads/admin/js-min/reporting/charts.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
			) );
			tve_leads_enqueue_script( 'tve-leads-routes', plugins_url( 'thrive-leads/admin/js-min/routes.min.js' ), array(
				'jquery',
				'backbone',
				'jquery-ui-sortable',
				'thrive-leads-init',
				'tve-leads-views',
				'tve-leads-models',
			) );
			tve_leads_enqueue_script( 'tve-leads-dashboard', plugins_url( 'thrive-leads/admin/js-min/dashboard.min.js' ), array(
				'jquery',
				'backbone',
				'tve-leads-routes',
				'tve-leads-models',
				'tve-leads-views',
			) );
			break;

		case TL_REPORTING_PAGE:
			tve_leads_enqueue_script( 'tve-leads-reporting-routes', plugins_url( 'thrive-leads/admin/js-min/reporting/routes.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
				'tve-leads-reporting-views',
				'tve-leads-reporting-models',
			) );
			tve_leads_enqueue_script( 'tve-leads-reporting-views', plugins_url( 'thrive-leads/admin/js-min/reporting/views.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
				'tve-leads-views',
			) );
			tve_leads_enqueue_script( 'tve-leads-reporting-models', plugins_url( 'thrive-leads/admin/js-min/reporting/models.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
				'tve-leads-models',
			) );
			tve_dash_enqueue_script( 'tve-dash-highcharts', TVE_DASH_URL . '/js/util/highcharts/highcharts.js', array(
				'jquery',
				'thrive-leads-init',
			), false, false );
			tve_leads_enqueue_script( 'tve-leads-charts', plugins_url( 'thrive-leads/admin/js-min/reporting/charts.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
			) );
			tve_leads_enqueue_script( 'tve-leads-reporting', plugins_url( 'thrive-leads/admin/js-min/reporting/reporting.min.js' ), array(
				'jquery',
				'backbone',
				'tve-leads-reporting-routes',
				'tve-leads-reporting-models',
				'tve-leads-reporting-views',
			) );
			break;

		case TL_CONTACTS_PAGE:
			tve_leads_enqueue_script( 'tve-leads-contacts-routes', plugins_url( 'thrive-leads/admin/js-min/contacts/routes.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
				'tve-leads-contacts-views',
			) );
			tve_leads_enqueue_script( 'tve-leads-contacts-views', plugins_url( 'thrive-leads/admin/js-min/contacts/views.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
				'tve-leads-views',
			) );

			tve_leads_enqueue_script( 'tve-leads-contacts', plugins_url( 'thrive-leads/admin/js-min/contacts/contacts.min.js' ), array(
				'jquery',
				'backbone',
				'tve-leads-contacts-routes',
				'tve-leads-contacts-views',
			) );
			break;
		case TL_ASSETS_PAGE:
			wp_enqueue_script( 'editor' );
			wp_enqueue_media();
			//add_action( 'admin_head', 'wp_editor' );
			wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery' ) );
			tve_leads_enqueue_script( 'tve-leads-asset-delivery-routes', plugins_url( 'thrive-leads/admin/js-min/assets/routes.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
				'tve-leads-asset-delivery-views',
				'tve-leads-asset-delivery-models',
			) );
			tve_leads_enqueue_script( 'tve-leads-asset-delivery-views', plugins_url( 'thrive-leads/admin/js-min/assets/views.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
				'tve-leads-views',
			) );
			tve_leads_enqueue_script( 'tve-leads-asset-delivery-models', plugins_url( 'thrive-leads/admin/js-min/assets/models.min.js' ), array(
				'jquery',
				'backbone',
				'thrive-leads-init',
				'tve-leads-models',
			) );
			tve_leads_enqueue_script( 'tve-leads-asset-delivery', plugins_url( 'thrive-leads/admin/js-min/assets/assets.min.js' ), array(
				'jquery',
				'backbone',
				'tve-leads-asset-delivery-routes',
				'tve-leads-asset-delivery-models',
				'tve-leads-asset-delivery-views',
			) );
			break;
	}

	/**
	 * output the main tpls for backbone views used in dashboard
	 */
	add_action( 'admin_print_footer_scripts', 'tve_leads_backbone_templates' );
}

/**
 * called in admin_enqueue_scripts hook
 *
 * dequeue scripts added site-wide
 *
 * @param string $hook
 */
function tve_leads_dequeue_conflicting_scripts( $hook ) {
	/* first, the license check */
	if ( ! tve_leads_license_activated() ) {
		return;
	}

	/* second, the minimum required TCB version */
	if ( ! tve_leads_check_tcb_version() ) {
		return;
	}

	/* load scripts only on our dashboard page, the entry point for the backbone app */
	if ( ! in_array( $hook, array( TL_DASHBOARD_PAGE, TL_REPORTING_PAGE, TL_CONTACTS_PAGE, TL_ASSETS_PAGE ) ) ) {
		return;
	}

	/* membermouse jquery loaded sitewide */
	wp_dequeue_script( 'jquery-ui-1.10.3.custom.min.js' );

	/* edit flow scripts loaded sitewide */
	wp_dequeue_script( 'edit_flow-timepicker' );
	wp_dequeue_script( 'edit_flow-date_picker' );
}

/**
 * some plugins add their scripts via admin_print_scripts - this is not cool
 *
 */
function tve_leads_remove_junk_scripts() {
	$screen = get_current_screen();
	$hook   = $screen->id;

	/* first, the license check */
	if ( ! tve_leads_license_activated() ) {
		return;
	}

	/* second, the minimum required TCB version */
	if ( ! tve_leads_check_tcb_version() ) {
		return;
	}

	/* load scripts only on our dashboard page, the entry point for the backbone app */
	if ( ! in_array( $hook, array( TL_DASHBOARD_PAGE, TL_REPORTING_PAGE, TL_CONTACTS_PAGE, TL_ASSETS_PAGE ) ) ) {
		return;
	}

	/* the following lines address the "Show the URL" plugin - what a piece of software ... ! */
	wp_dequeue_script( 'smpso_zclip' );
	wp_deregister_script( 'smpso_zclip' );
	wp_dequeue_script( 'smpso_custom' );
	wp_deregister_script( 'smpso_custom' );

}

/**
 * add the admin menu link for the dashboard page
 *
 * @param array $menus
 *
 * @return array
 */
function tve_leads_admin_menu( $menus = array() ) {
	$menus['tl'] = array(
		'parent_slug' => 'tve_dash_section',
		'page_title'  => __( 'Thrive Leads Dashboard', 'thrive-leads' ),
		'menu_title'  => __( 'Thrive Leads', 'thrive-leads' ),
		'capability'  => TL_Product::cap(),
		'menu_slug'   => 'thrive_leads_dashboard',
		'function'    => 'thrive_leads_dashboard',
	);

	/*For Reporting, Asset Delivery and Contacts page to work, we need to add them into menus*/
	add_submenu_page( 'thrive_leads_dashboard', "Thrive Leads Reporting", "Reporting", TL_Product::cap(), "thrive_leads_reporting", "thrive_leads_reporting" );
	add_submenu_page( 'thrive_leads_dashboard', "Thrive Leads Asset Delivery", "Asset Delivery", TL_Product::cap(), "thrive_leads_asset_delivery", "thrive_leads_asset_delivery" );
	add_submenu_page( 'thrive_leads_dashboard', "Thrive Leads Export", "Lead Export", TL_Product::cap(), "thrive_leads_contacts", "thrive_leads_contacts" );


	return $menus;
}

/**
 * output Thrive Leads dashboard - the main plugin page
 */
function thrive_leads_dashboard() {
	if ( ! tve_leads_license_activated() ) {
		return tve_leads_license_warning();
	}

	if ( ! tve_leads_check_tcb_version() ) {
		return tve_leads_tcb_version_warning();
	}

	global $tvedb;

	$dashboard_data = array(
		'global_settings'   => array(
			'ajax_load' => tve_leads_get_option( 'ajax_load' ),
		),
		'groups'            => tve_leads_get_groups( array( 'no_content' => true ) ),
		'shortcodes'        => tve_leads_get_shortcodes( array(
			'active_test'    => true,
			'tracking_data'  => true,
			'get_variations' => true,
		) ),
		'two_step_lightbox' => tve_leads_get_two_step_lightboxes( array(
			'active_test'    => true,
			'tracking_data'  => true,
			'get_variations' => true,
		) ),
		'one_click_signup'  => tve_leads_get_one_click_signups(),
		'summary'           => array(
			'impressions' => $tvedb->get_summary_count( 'unique_visitor', array( 'date' => 'today' ) ),
			'conversions' => $tvedb->get_summary_count( 'conversion', array( 'date' => 'today' ) ),
		),
		'breadcrumbs'       => tve_leads_get_screen_data(),
	);
	include dirname( __FILE__ ) . '/views/dashboard.php';
}

/**
 * output Thrive Leads reporting - the main plugin page
 */
function thrive_leads_reporting() {
	if ( ! tve_leads_license_activated() ) {
		return tve_leads_license_warning();
	}

	if ( ! tve_leads_check_tcb_version() ) {
		return tve_leads_tcb_version_warning();
	}

	$tve_load_annotations = tve_leads_get_option( 'tve_load_annotations' );

	$dashboard_data = array(
		'global_settings' => array(
			'ajax_load' => tve_leads_get_option( 'ajax_load' ),
		),
		'breadcrumbs'     => tve_leads_get_screen_data(),
		'groups'          => tve_leads_get_groups(),
	);

	$reporting_data = array(
		'lead_groups'       => tve_leads_get_groups(
			array(
				'full_data'       => false,
				'tracking_data'   => false,
				'completed_tests' => false,
				'active_tests'    => false,
			)
		),
		'shortcodes'        => tve_leads_get_shortcodes(
			array( 'active_test' => false )
		),
		'two_step_lightbox' => tve_leads_get_two_step_lightboxes(
			array( 'active_test' => false )
		),
	);

	include dirname( __FILE__ ) . '/views/reporting.php';
}

/**
 * output Thrive Leads Contacts page
 */
function thrive_leads_contacts() {
	if ( ! tve_leads_license_activated() ) {
		return tve_leads_license_warning();
	}

	if ( ! tve_leads_check_tcb_version() ) {
		return tve_leads_tcb_version_warning();
	}

	require_once __DIR__ . '/inc/classes/Thrive_Leads_Contacts_List.php';

	$dashboard_data = array(
		'global_settings' => array(
			'ajax_load' => tve_leads_get_option( 'ajax_load' ),
		),
		'breadcrumbs'     => tve_leads_get_screen_data(),
		'groups'          => tve_leads_get_groups(),
	);

	$contacts_list = new Thrive_Leads_Contacts_List( array( 'ajax' => false ) );
	$contacts_list->prepare_items();

	include __DIR__ . '/views/contacts/contacts.php';
}

/* hook to remove http_referrer and wpnonce from the url when performing actions */
add_action( 'admin_init', 'tve_leads_contacts_action_redirect' );
function tve_leads_contacts_action_redirect() {
	if ( ! empty( $_GET['tve_template_redirect_contacts'] ) && ! empty( $_GET['_wp_http_referer'] ) ) {
		wp_redirect( remove_query_arg( array(
			'_wp_http_referer',
			'_wpnonce',
			'tve_template_redirect_contacts',
		), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	}
}

/**
 * output Thrive Leads Asset Delivery - the main plugin page
 */
function thrive_leads_asset_delivery() {
	if ( ! tve_leads_license_activated() ) {
		return tve_leads_license_warning();
	}

	if ( ! tve_leads_check_tcb_version() ) {
		return tve_leads_tcb_version_warning();
	}

	$dashboard_data = array(
		'global_settings' => array(
			'ajax_load' => tve_leads_get_option( 'ajax_load' ),
		),
		'breadcrumbs'     => tve_leads_get_screen_data(),
		'groups'          => tve_leads_get_groups(),
	);
	$asset_groups   = tve_leads_get_asset_groups( array( 'active_test' => false ) );

	$all_apis = Thrive_List_Manager::get_available_apis( false, [ 'include_types' => [ 'email' ] ] );

	$apis            = array();
	$connected_apis  = array();
	$structured_apis = array();

	foreach ( $all_apis as $k => $api ) {
		/** @var Thrive_Dash_List_Connection_Abstract $api */
		$credentials = Thrive_List_Manager::credentials( $k );
		$apis[]      = array(
			'connection'          => $k,
			'title'               => $api->get_title(),
			'connected'           => $api->is_connected(),
			'connection_instance' => $credentials,
		);

		if ( $api->is_connected() ) {
			$connected_apis[]  = $api;
			$structured_apis[] = array(
				'connection'          => $k,
				'active'              => get_option( 'tve_api_delivery_service' ),
				'connection_instance' => $credentials,
			);
		}
	}

	$wizard = get_option( 'tve_leads_asset_wizard_complete' );

	$assets_data = array(
		'assets' => $asset_groups,
		'wizard' => array(
			'proprieties'     => tve_leads_get_wizard_proprieties( $asset_groups ),
			'connected_apis'  => $connected_apis,
			'apis'            => $apis,
			'admin_name'      => tve_leads_assets_get_admin_name(),
			'email_data'      => tve_leads_assets_get_email_data(),
			'structured_apis' => $structured_apis,
			'wizard_complete' => $wizard,
		),
	);


	include dirname( __FILE__ ) . '/views/assets.php';

}

/**
 * output a page where users can activate their license
 */
function tve_leads_license_activation() {
	include dirname( dirname( __FILE__ ) ) . '/inc/license_activation.php';
}

/**
 * AJAX- load a custom file through the WP api
 *
 * @param string $ajax_load
 */
function tve_leads_admin_ajax_load( $ajax_load ) {
	$base = plugin_dir_path( dirname( __FILE__ ) );
	switch ( $ajax_load ) {
		case 'thrive_leads_templates':
			include $base . 'editor-lightbox/lb_templates.php';
			exit;
			break;
	}
}

/**
 * This filter should strip out the HTML connection type for set up a lead generation element
 * This filter should be applied only if the form type is short-code with content locking option true
 *
 * @param $connection_types
 *
 * @return mixed
 * @author Dan
 */
function tve_leads_ajax_filter_connection_types( $connection_types ) {
	$shortcode = tve_leads_get_shortcode( $_POST['post_id'] );

	if ( ! $shortcode ) {
		return $connection_types;
	}

	if ( $shortcode->content_locking ) {
		$types['api'] = __( 'API', 'thrive-leads' );

		return $types;
	}

	return $connection_types;
}

/**
 * check if the form being edited is a content locking shortcode and if so, hide the "After the form is submitted" options
 *
 * @param bool $show_submit
 *
 * @return bool
 */
function tve_leads_filter_autoresponder_submit_option( $show_submit ) {
	$shortcode = tve_leads_get_shortcode( $_POST['post_id'] );

	if ( ! $shortcode || ! $shortcode->content_locking ) {
		return true;
	}

	return false;
}

/**
 * AJAX for searching posts by a specific post type
 */
function tve_leads_find_content_action() {
	$type = $_GET['type'];
	$s    = wp_unslash( $_GET['q'] );
	$s    = trim( $s );

	$args        = array(
		'post_type'   => $type,
		'post_status' => 'publish',
		's'           => $s,
	);
	$posts_array = get_posts( $args );

	$json = array();
	foreach ( $posts_array as $id => $item ) {
		$json [] = array(
			'label' => $item->post_title,
			'id'    => $item->ID,
			'value' => $item->post_title,
			'url'   => get_the_permalink( $item->ID ),
		);
	}
	wp_send_json( $json );
}

/**
 * AJAX for searching posts by a specific post type
 */
function tve_leads_find_post_and_page_action() {
	$s = wp_unslash( $_GET['q'] );
	$s = trim( $s );

	$args        = array(
		'post_type'   => array( 'page', 'post' ),
		'post_status' => 'publish',
		's'           => $s,
	);
	$posts_array = get_posts( $args );

	$json = array();
	foreach ( $posts_array as $id => $item ) {
		$json [] = array(
			'label' => $item->post_title,
			'id'    => $item->ID,
			'value' => $item->post_title,
			'url'   => get_the_permalink( $item->ID ),
		);
	}
	wp_send_json( $json );
}

/**
 * AJAX for returning the selected Api connection's list (used at One Click Signup, new name: Signup Segue)
 */
function tve_leads_get_api_connection_list() {
	$api = wp_unslash( $_GET['api'] );
	if ( ! $api || ! array_key_exists( $api, Thrive_List_Manager::$AVAILABLE ) ) {
		exit();
	}
	$connection = Thrive_List_Manager::connection_instance( $api );
	$cache      = empty( $_REQUEST['force_fetch'] );
	$lists      = $connection->get_lists( $cache );
	wp_send_json( $lists );
}

/**
 * AJAX for returning the selected Api connection's extra fields (used at One Click Signup, new name: Signup Segue)
 */
function tve_leads_get_api_extra_fields() {
	$api  = wp_unslash( $_GET['api'] );
	$args = array();
	if ( ! $api || ! array_key_exists( $api, Thrive_List_Manager::$AVAILABLE ) ) {
		exit();
	}

	if ( ! empty( $_GET['extra'] ) ) {
		foreach ( $_GET['extra'] as $key => $value ) {
			$new_key          = str_replace( $_GET['api'] . '_', '', $key );
			$args[ $new_key ] = $value;
		}
	}

	$connection = Thrive_List_Manager::connection_instance( $api );
	ob_start();
	$connection->render_extra_editor_settings( $args );
	$content = ob_get_contents();
	ob_end_clean();
	wp_send_json( array( 'html' => $content ) );
}


/**
 * filter implementation for getting the saved templates
 *
 * @param array $template_list
 *
 * @return array
 */
function tve_leads_filter_display_settings_templates( $template_list ) {
	global $tvedb;

	$list = $tvedb->get_display_settings_templates();

	if ( empty( $list ) ) {
		return $template_list;
	}

	foreach ( $list as $template ) {
		$template->id  = 'TL-' . $template->id;
		$template->tag = 'TL';
	}
	$template_list['Thrive Leads templates'] = $list;

	return $template_list;
}

/**
 * get a display settings template that belongs to Thrive Leads by ID
 *
 * @param $template
 * @param $template_id
 *
 * @return array|null|object|void
 */
function tve_leads_filter_display_settings_get_template( $template, $template_id ) {
	if ( strpos( $template_id, 'TL-' ) === false ) {
		return $template;
	}

	global $tvedb;

	$row = $tvedb->get_display_settings_template( str_replace( 'TL-', '', $template_id ) );

	if ( empty( $row ) ) {
		return $row;
	}
	$row->show_options = $row->show_group_options;
	$row->hide_options = $row->hide_group_options;

	return $row;
}


/**
 * Stop variation ajax method
 */
function tve_leads_stop_variation() {
	if ( ! empty( $_POST['variation_id'] ) && ! empty( $_POST['test_id'] ) ) {
		$response = tve_leads_stop_test_item( intval( $_POST['variation_id'] ), intval( $_POST['test_id'] ) );
		if ( $response ) {
			wp_send_json( array( 'status' => 'ok' ) );
		}
	}

	wp_send_json( array( 'status' => 'error' ) );
}
