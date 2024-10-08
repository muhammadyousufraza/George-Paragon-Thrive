<?php
/**
 * Use this file to declare hooks - actions and filters
 */


/**
 * Load text domain used for translations
 */
add_action( 'init', 'tvo_load_plugin_textdomain' );

/* plugin init */
add_action( 'init', 'tvo_plugin_init' );

/* Check if default capabilities were defined */
add_action( 'thrive_dashboard_loaded', 'tve_ovation_dash_loaded' );

/* load dashboard version */
add_action( 'plugins_loaded', 'tvo_load_dash' );

/**
 * check for update at init because dashboard loads the required classes at plugins_loaded
 */
add_action( 'init', 'tvo_update_checker' );

/**
 * add dashboard features
 */
add_filter( 'tve_dash_features', 'tvo_dashboard_add_features' );

//add_filter( 'tiny_mce_before_init', 'my_format_TinyMCE' );
/**
 * Load REST Routes
 */
add_action( 'rest_api_init', 'tvo_create_initial_rest_routes' );

/**
 * Hook into the 'init' action, the creation of the tvo taxonomies
 */
add_action( 'init', 'tvo_taxonomy' );

/**
 * Hook into the 'init' action so that the function
 * Containing our post type registration is not
 * unnecessarily executed.
 */
add_action( 'init', 'tvo_register_post_types' );

/**
 *Adds a custom column to admin comments page
 */
add_filter( 'manage_edit-comments_columns', 'tvo_comment_columns' );

/**
 * Adds content to the custom column previously created
 */
add_filter( 'manage_comments_custom_column', 'tvo_comment_column', 10, 2 );

/**
 * Adds available email services types
 */
add_filter( 'tve_filter_api_types', 'tvo_filter_api_types' );

/**
 *  Adds custom code in the admin footer
 */
add_action( 'admin_footer', 'tvo_add_code_after_footer' );

/**
 * Logs testimonial fields activity
 */
add_action( 'tvo_log_testimonial_activity', 'tvo_log_testimonial_activity' );

/**
 * Logs testimonial status activity
 */
add_action( 'tvo_log_testimonial_status_activity', 'tvo_log_testimonial_status_activity' );

/**
 * Logs testimonial source activity
 */
add_action( 'tvo_log_testimonial_source_activity', 'tvo_log_testimonial_source_activity' );

/**
 * Logs testimonial email activity
 */
add_action( 'tvo_log_testimonial_email_activity', 'tvo_log_testimonial_email_activity' );

/**
 * Hooks the process testimonial email link action on wordpress initialization
 */
add_action( 'wp', 'tvo_process_testimonial_actions' );

if ( has_filter( 'tve_leads_ajax_load_forms' ) ) {
	add_filter( 'tve_leads_ajax_load_forms', 'tvo_ajax_load_library' );
}

/**
 * Hooks the notfication manager trigger types
 */
add_filter( 'td_nm_trigger_types', 'tvo_filter_nm_trigger_types' );

/**
 * Adds js scripts to capture forms
 */
add_action( 'tcb_lightbox_load_resources', 'tvo_load_resources' );

/**
 * Init Blocks
 */
add_action( 'init', 'TVO_Block::init' );

/**
 * Add info article url for Capture Testimonials and Display Testimonials elements
 */
add_filter( 'thrive_kb_articles', static function ( $articles ) {
	$articles['capture_testimonials'] = 'https://api.intercom.io/articles/7175657';
	$articles['display_testimonials'] = 'https://api.intercom.io/articles/7172144';

	return $articles;
} );

/**
 * Don't display metrics ribbon if we don't have any license
 */
add_filter( 'tve_dash_metrics_should_enqueue', static function ( $should_enqueue ) {
	$screen = tve_get_current_screen_key();
	if ( $screen === 'thrive-dashboard_page_tvo_admin_dashboard' && ! tvo_check_license() ) {
		$should_enqueue = false;
	}

	return $should_enqueue;
}, 10, 1 );

/**
 * Add the connection to the API list
 */
add_action( 'thrive_dashboard_autoresponders_loaded', static function () {
	require_once TVO_PATH . '/inc/classes/api-connection/Ovation.php';
} );

add_action( 'tcb_output_extra_editor_svg', static function () {
	include TVO_PATH . '/tcb-bridge/css/icons.svg';
} );

