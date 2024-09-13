<?php

define( 'TVE_LEADS_ADMIN_URL', plugin_dir_url( __FILE__ ) . 'admin/' );

/**
 * this should handle file inclusions, requires etc
 */
require_once plugin_dir_path( __FILE__ ) . 'inc/constants.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/db.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/data.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/triggers.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/lightspeed.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/animations.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/compat.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/thrive_leads_widget.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/Thrive_Leads_Request_Handler.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/Thrive_Leads_State_Manager.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/Thrive_Leads_Template_Manager.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/classes/Thrive_Leads_Privacy.php';
require_once plugin_dir_path( __FILE__ ) . 'blocks/lead-blocks.php';

require_once plugin_dir_path( __FILE__ ) . 'tcb-bridge/tcb_action_hooks.php';

/**
 * at this point, we need to either hook into an existing Content Builder plugin, or use the copy we store in the tcb folder
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! file_exists( dirname( __FILE__, 2 ) . '/thrive-visual-editor/thrive-visual-editor.php' ) || ! is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'tcb-bridge/init.php';
}

/* check database version and run any necessary update scripts */
require_once plugin_dir_path( __FILE__ ) . 'database/Thrive_Leads_Database_Manager.php';
Thrive_Leads_Database_Manager::check();

// initialize the update checker at init because the class is loaded by Dashbaord at plugins_loaded
add_action( 'init', 'tve_leads_update_checker' );
add_action( 'init', 'tve_leads_init' );
add_action( 'init', 'tve_leads_load_plugin_textdomain' );
/**
 * On init action required the Thrive_Leads_Cloud_Templates_Api
 * Set it on 11 because it extends TCB_Landing_Page_Cloud_Templates_Api and this is loaded at 10
 */
add_action( 'init', 'tve_leads_init_cloud_templates_api', 11 );
add_action( 'widgets_init', 'tve_leads_widget_init' );

/**
 * these need to be added here, because we will make some WP admin ajax calls
 */
/**
 * ajax call that tracks the conversion for a form
 */
add_action( 'wp_ajax_tve_leads_ajax_conversion', 'tve_leads_ajax_conversion' );
/**
 * also for non-authenticated users
 */
add_action( 'wp_ajax_nopriv_tve_leads_ajax_conversion', 'tve_leads_ajax_conversion' );

/**
 * ajax call that tracks the impression for all rendered forms
 */
add_action( 'wp_ajax_tve_leads_ajax_impression', 'tve_leads_ajax_impression' );
/**
 * also for non-authenticated users
 */
add_action( 'wp_ajax_nopriv_tve_leads_ajax_impression', 'tve_leads_ajax_impression' );

if ( tve_leads_get_option( 'ajax_load' ) || apply_filters( 'tve_leads_ajax_load', false ) ) {
	/**
	 * load the forms via AJAX requests on DOM ready
	 *
	 * @deprecated
	 */
	add_action( 'wp_ajax_tve_leads_ajax_load_forms', 'tve_leads_ajax_load_forms' );
	add_action( 'wp_ajax_nopriv_tve_leads_ajax_load_forms', 'tve_leads_ajax_load_forms' );

	/**
	 * new way of doing it:
	 * load the forms during the main ajax call initiated from thrive dashboard
	 */
	add_action( 'tve_dash_main_ajax_tl_lazy_load', 'tve_leads_dash_main_ajax_load_forms', 10, 2 );
}

/**
 * logic to be applied on form impression (display) - TL will save the display as a new event in the log table
 */
add_action( TVE_LEADS_ACTION_FORM_IMPRESSION, 'tve_leads_register_impression', 10, 5 );

/**
 * new way of logging impressions - during the main ajax call initiated by thrive dashboard
 */
add_action( 'tve_dash_main_ajax_tl_impression', 'tve_leads_dash_main_ajax_impression', 10, 2 );

/**
 * logic to be applied on form conversion (successful submit) - TL will save the conversion as a new event in the log table
 */
add_action( TVE_LEADS_ACTION_FORM_CONVERSION, 'tve_leads_register_conversion', 10, 6 );

/**
 * called when a winner is decided in a test (either manually, by admin or automatically, after a conversion)
 *
 * called with 2 parameters:
 *
 *      $winner_test_item - the winner of the test (important data: form_type_id, main_group_id, variation_key, unique_impressions, impressions, conversions)
 *      $test - the actual test containing test items and various data
 *
 */
add_action( TVE_LEADS_ACTION_SET_TEST_ITEM_WINNER, 'tve_leads_set_test_item_winner', 10, 2 );

if ( wp_doing_ajax() ) {
	add_filter( 'option_tve_leads_ajax_load', '__return_false' );
	/**
	 * hook into the TCB form submission action - this is triggered for forms that are connected to an API
	 */
	add_action( 'tcb_api_form_submit', 'tve_leads_api_form_submit' );
	add_action( 'tve_tcb_delivery_connection', 'tve_leads_delivery_connection' );
	add_action( 'tve_tcb_add_form_options', 'tve_leads_add_form_options' );
}
/* Load Dashboard version check */
add_action( 'plugins_loaded', 'tve_leads_load_dash_version' );

add_filter( 'td_nm_trigger_types', 'tve_leads_filter_nm_trigger_types' );

add_filter( 'tcb_post_grid_banned_types', 'tve_leads_add_post_grid_banned_types' );
add_filter( 'tcb_can_export_content', 'tl_hide_export_content', 10, 2 );

if ( ! is_admin() ) {

	/**
	 * change the page title when editing a TL form
	 */
	add_filter( 'wp_title', 'tve_leads_editor_page_title', 10, 2 );

	/**
	 * sign end of content with and element to be used in JS
	 */
	add_filter( 'the_content', 'tve_leads_filter_end_content' );
	add_filter( 'tve_landing_page_content', 'tve_leads_filter_end_content' );

	/**
	 * action for enqueueing scripts and CSS, but only on display pages (on pages where we have forms to be displayed)
	 */
	if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
		add_action( 'template_redirect', 'tve_leads_enqueue_form_scripts' );
	} else {
		add_action( 'wp_enqueue_scripts', 'tve_leads_enqueue_form_scripts' );
	}

	/**
	 * print the footer JS required for tracking conversions, triggering displays etc
	 */
	add_action( 'wp_print_footer_scripts', 'tve_leads_print_footer_scripts', 11 );

	/**
	 * The only group that would be displayed for current request
	 */
	global $tve_lead_group;
	$tve_lead_group = null;

	/**
	 * it seems WP has an issue with redirection causing infinite loop if the administrator has setup the url to have uppercase characters
	 * SUPP-1043: increased priority from 10 to 12 - was causing some sort of weird 500 Internal Server Error on pagination
	 */
	add_filter( 'redirect_canonical', 'tve_leads_canonical_url_lowercase', 12, 2 );

	/**
	 * this is called before template_redirect hook
	 */
	add_action( 'wp', 'tve_leads_query_group' );

	/**
	 * filter sidebar params
	 */
	add_action( 'dynamic_sidebar_params', 'thrive_dynamic_sidebar_params' );

	/**
	 * one click signup (new name: Signup Segue)
	 */
	add_action( 'wp', 'tve_leads_one_click_signup' );
	/**
	 * filter one click signup params (new name: Signup Segue)
	 */
	add_filter( 'the_content', 'tve_leads_one_click_signup_validation' );

	add_filter( 'tve_leads_include_auto_responder', 'tve_leads_include_auto_responder_file' );

} else {
	/**
	 * Hooks the inconclusive tests action on wordpress initialization
	 */
	add_action( 'admin_init', 'tve_get_inconclusive_tests' );

	add_filter( 'tve_ult_body_end_html', 'tve_leads_filter_tu_body_end' );
}

/**
 * Add the leads product all the time to able to check external capabilities
 * e.g global elements requests permissions
 */
add_filter( 'tve_dash_installed_products', 'tve_leads_add_to_dashboard' );

/**
 * Exclude custom post for indexing
 */
add_filter( 'tve_dash_exclude_post_types_from_index', 'tve_exclude_post_types_from_index' );

/**
 * This was causing issues w/ elements inside LG from templates
 *
 * filter for specifying the root selection when editing thrive box and lightboxes
 * add_filter( 'tcb_selection_root', 'tve_editor_selection_root' );
 *
 */
/**
 * Ensure compatibility checks
 */
register_activation_hook( TVE_LEADS_PLUGIN__FILE__, 'thrive_leads_activation_hook' );

add_action( 'thrive_dashboard_loaded', 'tve_leads_dashboard_loaded' );

/**
 * Replace the default editor_selector (#tve_editor)
 */
add_filter( 'editor_selector', 'tve_leads_editor_selector' );


/**
 * WP-Rocket Compatibility - exclude files from caching
 */
add_filter( 'rocket_exclude_css', 'tve_leads_rocket_exclude_css' );
add_filter( 'rocket_exclude_js', 'tve_leads_rocket_exclude_js' );

add_filter( 'tve_dash_cache_known_plugins', 'tve_leads_detect_cache' );

add_filter( 'tcb_post_visibility_options_availability', 'tve_leads_post_visibility_options' );

add_action( 'init', 'TVE_Leads_Blocks::init' );
