<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access
}

/**
 * Use this file to implement the hooks defined in admin/start.php
 */

/**
 * Add the admin menu link for the dashboard page
 *
 * @param array $menus
 *
 * @return array
 */
function tve_ult_admin_menu( $menus = array() ) {
	$menus['tu'] = array(
		'parent_slug' => 'tve_dash_section',
		'page_title'  => __( 'Thrive Ultimatum', 'thrive-ult' ),
		'menu_title'  => __( 'Thrive Ultimatum', 'thrive-ult' ),
		'capability'  => TU_Product::cap(),
		'menu_slug'   => 'tve_ult_dashboard',
		'function'    => 'tve_ult_admin_dashboard',
	);


	return $menus;
}

/**
 * Output Thrive Ultimatum dashboard - the main plugin admin page
 */
function tve_ult_admin_dashboard() {
	if ( ! tve_ult_license_activated() ) {
		return tve_ult_license_warning();
	}

	if ( ! tve_ult_check_tcb_version() ) {
		return tve_ult_tcb_version_warning();
	}

	include dirname( __FILE__ ) . '/views/dashboard.php';
}

/**
 * Output each TU backbone template
 * called on the 'admin_print_footer_scripts' hook
 *
 */
function tve_ult_backbone_templates() {

	$templates = tve_dash_get_backbone_templates( plugin_dir_path( __FILE__ ) . 'views/template', 'template' );

	tve_dash_output_backbone_templates( $templates );

}

/**
 * Enqueue all required scripts and css
 *
 * @param string $hook
 */
function tve_ult_admin_enqueue_scripts( $hook ) {

	$accepted_hooks = apply_filters( 'tve_ult_accepted_admin_pages', array(
		'thrive-dashboard_page_tve_ult_dashboard',
	) );

	if ( ! in_array( $hook, $accepted_hooks ) ) {
		return;
	}

	/* first, the license check */
	if ( ! tve_ult_license_activated() ) {
		return;
	}

	/* second, the minimum required TCB version */
	if ( ! tve_ult_check_tcb_version() ) {
		return;
	}

	/**
	 * enqueue dash scripts
	 */
	tve_dash_enqueue();

	/**
	 * specific admin styles
	 */
	tve_ult_enqueue_style( 'thrive-ult-admin-style', TVE_Ult_Const::plugin_url( '/admin/css/styles.css' ) );

	/**
	 * Enqueue jquery backbone & thickbox
	 */
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'backbone' );
	wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery' ) );
	tve_dash_enqueue_script( 'tve-dash-highcharts', TVE_DASH_URL . '/js/util/highcharts/highcharts.js', array(
		'jquery',
	), false, false );

	wp_enqueue_script( 'jquery-ui-datepicker' );

	tve_ult_enqueue_script( 'tve-ult-admin-js', TVE_Ult_Const::plugin_url( 'admin/js/dist/admin.min.js' ), array(
		'jquery',
		'backbone',
	), false, true );
	/**
	 * jQuery autocomplete - needed for Display Settings search
	 */
	wp_enqueue_script( 'jquery-ui-autocomplete' );

	/* wystia script for popover videos */
	wp_enqueue_script( 'tu-wistia-popover', '//fast.wistia.com/assets/external/popover-v1.js', array(), '', true );

	wp_localize_script( 'tve-ult-admin-js', 'ThriveUlt', tve_ult_get_localization() );

	/**
	 * include backbone script templates at the bottom of the page
	 */
	add_action( 'admin_print_footer_scripts', 'tve_ult_backbone_templates' );
}

/**
 * get the localization array for the admin javascript
 *
 * @return array
 */
function tve_ult_get_localization() {
	return array(
		'plugin_url'           => TVE_Ult_Const::plugin_url(),
		'data'                 => array(
			'campaigns'      => tve_ult_get_campaigns( array(
				'get_logs' => true,
			) ),
			'lead_groups'    => function_exists( 'tve_leads_get_groups' ) ? tve_leads_get_groups() : false,
			'shortcodes'     => function_exists( 'tve_leads_get_shortcodes' ) ? tve_leads_get_shortcodes() : false,
			'thrive_boxes'   => function_exists( 'tve_leads_get_two_step_lightboxes' ) ? tve_leads_get_two_step_lightboxes() : false,
			'tqb_optin'      => tve_ult_filter_tqb_posts(),
			'lead_gen_pages' => tvu_get_posts_with_lead_generations_element(),
			'tve_lightboxes' => tvu_get_posts_with_lead_generations_element( 'tcb_lightbox' ),
			'actions'        => TU_Event_Action::get_detailed_list(),
			'settings'       => tve_ult_get_date_settings(),
			'now'            => tve_ult_current_time( 'Y-m-d H:i' ),
		),
		'event_type'           => TVE_Ult_Const::event_types(),
		'ajax_actions'         => array(
			'admin_controller' => 'tve_ult_admin_ajax_controller',
		),
		'campaigns_types'      => TVE_Ult_Const::campaign_types(), //todo: delete this if it is not needed
		't'                    => require TVE_Ult_Const::plugin_path() . 'admin/i18n.php',
		'design_types'         => TVE_Ult_Const::design_types_details(),
		'campaign_templates'   => TVE_Ult_Const::campaign_attribute_templates(),
		'admin_nonce'          => wp_create_nonce( 'tve_ult_admin_ajax_request' ),
		'wp_timezone'          => tve_ult_get_timezone_format(),
		'date_format'          => 'dd M yy',
		'date_formats'         => TVE_Ult_Const::date_format_details( 'all' ),
		'time_format'          => 'HH:mm',//@see $.fn.timepicker.formatTime  for more formats
		'wp_timezone_offset'   => get_option( 'gmt_offset' ),
		'dash_url'             => admin_url( 'admin.php?page=tve_dash_section' ),
		'tvd_webhook_rest_url' => tvd_get_webhook_route_url( '' ),
		'integratedApis'       => tve_dash_get_webhook_trigger_integrated_apis(),
		'license'              => [
			'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'tu' ),
			'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'tu' ),
			'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( 'tu' ),
			'grace_time'    => TD_TTW_User_Licenses::get_instance()->get_grace_period_left( 'tu' ),
			'link'          => tvd_get_individual_plugin_license_link( 'tu' )
		]
	);
}

/**
 * Handles ajax requests
 */
function tve_ult_admin_ajax_controller() {
	require_once plugin_dir_path( __FILE__ ) . 'classes/class-tve-ult-admin-ajaxcontroller.php';

	$response = Tve_Ult_Admin_AjaxController::instance()->handle();

	wp_send_json( $response );
}

/**
 * filter implementation for getting the saved templates
 *
 * @param array $template_list
 *
 * @return array
 */
function tve_ult_filter_display_settings_templates( $template_list ) {
	global $tve_ult_db;
	$list = $tve_ult_db->get_display_settings_templates();
	if ( empty( $list ) ) {
		return $template_list;
	}
	foreach ( $list as $template ) {
		$template->id  = 'TU-' . $template->id;
		$template->tag = 'TU';
	}
	$template_list['Thrive Ultimatum templates'] = $list;

	return $template_list;
}

/**
 * @param $template
 * @param $template_id
 *
 * @return array|null|object|void
 */
function tve_ult_filter_display_settings_get_template( $template, $template_id ) {
	if ( strpos( $template_id, 'TU-' ) === false ) {
		return $template;
	}

	global $tve_ult_db;

	return $tve_ult_db->get_display_settings_template( str_replace( 'TU-', '', $template_id ) );
}

/**
 * Sometimes the only way to make the plugin work with other scripts is to denqueue them from some pages
 *
 * @param string $hook
 */
function tve_ult_remove_conflicting_scripts( $hook ) {

	if ( $hook === 'toplevel_page_tve_dash_section' ) {
		wp_dequeue_style( 'ks-giveaways-admin-style' );
		wp_deregister_style( 'ks-giveaways-admin-style' );
	}
}

/**
 * Return posts that contain a lead generation element based on post type
 *
 * @param string $post_type
 *
 * @return array
 */
function tvu_get_posts_with_lead_generations_element( $post_type = 'page' ) {
	$pages_with_lead_gen = array();

	foreach (
		get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => - 1,
			'fields'         => 'ids',
		) ) as $page_id
	) {
		$content = tve_get_post_meta( $page_id, 'tve_updated_post' );

		if ( strpos( do_shortcode( $content ), 'thrv_lead_generation' ) !== false ) {
			$pages_with_lead_gen[ $page_id ] = get_post( $page_id )->post_title;
		}
	}

	return $pages_with_lead_gen;
}

/**
 * Return a list with the structure of all TQB posts
 *
 */

function tve_ult_filter_tqb_posts() {

	if ( ! is_plugin_active( "thrive-quiz-builder/thrive-quiz-builder.php" ) ) {
		return array();
	}

	$posts = get_posts( array( 'post_type' => 'tqb_quiz', 'posts_per_page' => '-1' ) );

	$post_data = array_map( function ( $post ) {
		$data = get_post_meta( $post->ID, 'tqb_quiz_structure', true );
		if ( empty( $data ) ) {
			$data = array();
		}
		$data['quiz_title'] = $post->post_title;

		return $data;
	}, $posts );

	$result = array();
	foreach ( $post_data as $post ) {
		if ( ! empty( $post['optin'] ) ) {
			$result[] = $post;
		}
	}

	return $result;
}
