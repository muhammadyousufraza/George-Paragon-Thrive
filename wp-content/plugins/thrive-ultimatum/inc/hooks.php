<?php
/**
 * Use this file to declare front-end hooks only
 */

global $tve_ult_frontend;

/**
 * Register required post types
 */
add_action( 'init', 'tve_ult_init' );

add_action( 'init', 'TU_Block::init' );
/**
 * initialize the update checker here because the required classes are loaded by dashboard at plugins_loaded
 */
add_action( 'init', 'tve_ult_update_checker' );

add_action( 'thrive_dashboard_loaded', 'tve_ult_dashboard_loaded' );
add_action( 'thrive_automator_init', array( 'TU\Automator\Main', 'init' ) );
/**
 * init the shortcodes that need to be rendered
 */
add_action( 'init', array( 'TU_Shortcodes', 'init' ) );

add_action( 'widgets_init', 'tve_ult_register_widget' );

/**
 * Load text domain used for translations
 */
add_action( 'init', 'tve_ult_load_plugin_textdomain' );

/**
 * After plugin is loaded load ThriveDashboard Section
 */
add_action( 'plugins_loaded', 'tve_ult_load_dash_version' );

/**
 * logic to be applied on form conversion (successful submit) - TU will check if the conversion should start any campaign
 */
add_action( 'tve_leads_form_conversion', 'tve_ult_check_campaign_trigger', 10, 6 );
add_action( 'tcb_api_form_submit', array( $tve_ult_frontend, 'check_evergreen_triggers' ) );

/**
 * add close button to editor
 */
add_action( 'admin_bar_menu', 'tve_ult_admin_bar', 100 );

add_filter( 'tcb_can_export_content', 'tu_hide_export_content', 10, 2 );

/**
 * Add TU Product to Thrive Dashboard
 */
add_filter( 'tve_dash_installed_products', 'tve_ult_add_to_dashboard' );

/**
 * Add query vars for inner frame
 */
add_filter( 'tcb_editor_edit_link_query_args', 'tu_editor_edit_link_query_args' );

/**
 * remove the white padding added by Thrive Themes surrounding the widget
 */
add_action( 'dynamic_sidebar_params', 'tve_ult_dynamic_sidebar_params' );

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	/**
	 * Frontend handler - Ajax request - output campaign designs, if any
	 */
	add_action( 'wp_ajax_' . $tve_ult_frontend->ajax_load_action(), array( $tve_ult_frontend, 'ajax_load' ) );
	add_action( 'wp_ajax_nopriv_' . $tve_ult_frontend->ajax_load_action(), array( $tve_ult_frontend, 'ajax_load' ) );

	add_action( 'wp_ajax_' . $tve_ult_frontend->conversion_events_action(), array( $tve_ult_frontend, 'ajax_conversion_event_check' ) );
	add_action( 'wp_ajax_nopriv_' . $tve_ult_frontend->conversion_events_action(), array( $tve_ult_frontend, 'ajax_conversion_event_check' ) );

	add_filter( 'tve_dash_main_ajax_tu_lazy_load', array( $tve_ult_frontend, 'ajax_load' ), 10, 2 );
	add_filter( 'tve_dash_main_ajax_tu_conversion_events', array( $tve_ult_frontend, 'ajax_conversion_event_check' ), 10, 2 );

	/**
	 * register an impression for a campaign
	 */
	add_action( 'tve_ult_action_impression', 'tve_ult_register_impression' );
}

/**
 * Starting point for frontend logic:
 *
 * we use the wp_enqueue_scripts hook to check if a campaign should be displayed
 */
if ( ! is_admin() ) {

	//TODO: remove THIS
	add_filter( 'tcb_editor_javascript_params', 'tve_ult_append_shortcode_campaigns' );

	add_action( 'wp_enqueue_scripts', array( $tve_ult_frontend, 'hook_enqueue_scripts' ) );
	add_action( 'wp_footer', array( $tve_ult_frontend, 'hook_print_footer_scripts' ), 100 );


	/**
	 * Before template redirect check if we have a cookie set for a campaign that should be displayed
	 */
	add_action( 'template_redirect', 'tve_ult_check_email_cookies', 1 );

	add_action( 'template_redirect', array( $tve_ult_frontend, 'hook_template_redirect' ), 2 );
}

register_activation_hook( TVE_ULT_PLUGIN__FILE__, 'tve_ult_activation_hook' );

add_action( 'thrive_prepare_migrations', 'tve_ult_prepare_db_migrations' );

/**
 * This allows TU shortcodes to work while placed inside thrive leads forms loaded via ajax
 */
add_action( 'tve_leads_ajax_load_prepare_variation', static function ( $thrive_leads_variation ) {
	$content = isset( $thrive_leads_variation['content'] ) ? $thrive_leads_variation['content'] : '';
	if ( strpos( $content, '[tu_countdown' ) !== false ) {
		do_shortcode( $content );
	}
} );

/**
 * Search thrive leads design variations if they have a specific string in their architect content
 */
add_filter( 'tcb_architect_content_has_string', static function ( $has_string, $string, $post_id ) {
	if ( ! $has_string ) {
		global $tve_ult_db;
		if ( $tve_ult_db->search_string_in_designs( $string ) ) {
			$has_string = true;
		}
	}

	return $has_string;
}, 13, 3 );

/**
 * Trigger campaign start from api webhook
 */
add_filter( 'tve_dash_webhook_trigger', 'tu_webhook_trigger_campaign', 10, 3 );

add_action( 'fluentcrm_contact_added_to_tags', 'tu_fluentcrm_trigger_campaign', 10, 2 );

/**
 * Add info article url for Ultimatum Countdown element
 */
add_filter( 'thrive_kb_articles', static function ( $articles ) {
	$articles['ultimatum_countdown'] = 'https://api.intercom.io/articles/4426118';

	return $articles;
} );

/**
 * Dont display metrics ribbon if we don't have any license
 */
add_filter( 'tve_dash_metrics_should_enqueue', static function ( $should_enqueue ) {
	$screen = tve_get_current_screen_key();
	if ( $screen === 'thrive-dashboard_page_tve_ult_dashboard' && ! tve_ult_license_activated() ) {
		$should_enqueue = false;
	}

	return $should_enqueue;
}, 10, 1 );

/* Hook used to clear the cache on all pages that are promotions pages on running campaigns */
add_action( 'wp', 'tve_ult_promotion_prevent_cache' );
