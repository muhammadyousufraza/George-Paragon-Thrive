<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

add_action( 'admin_menu', 'tve_dash_api_admin_menu', 20 );
add_action( 'admin_enqueue_scripts', 'tve_dash_api_admin_scripts' );
add_action( 'admin_notices', 'tve_dash_api_admin_notices', 9 );
add_action( 'wp_ajax_tve_dash_api_form_retry', 'tve_dash_api_form_retry' );
add_action( 'wp_ajax_tve_dash_api_delete_log', 'tve_dash_api_delete_log' );

if ( wp_doing_ajax() ) {
	add_action( 'wp_ajax_tve_dash_api_handle_save', 'tve_dash_api_handle_save' );
	add_action( 'wp_ajax_tve_dash_api_handle_redirect', 'tve_dash_api_api_handle_redirect' );
} else {
	add_action( 'admin_init', 'tve_dash_api_handle_save' );
}

/*
 * TTW API Videos URLs
 */
if ( is_admin() ) {
	add_action( 'current_screen', 'tve_api_video_urls' );
}

/**
 * Run on dash api connect screen
 * Build transient from TTW API with videos URLs
 */
function tve_api_video_urls() {
	if ( tve_get_current_screen_key() === 'admin_page_tve_dash_api_connect' ) {

		require_once __DIR__ . '/classes/ApiVideos.php';

		$api_videos = new ApiVideos();
	}
}


/**
 * FILTERS
 */
add_filter( 'tve_dash_localize', 'tve_dash_api_filter_localize' );
add_filter( 'tve_dash_include_ui', 'tve_dash_api_filter_ui_hooks' );

function tve_dash_api_admin_menu() {
	remove_submenu_page( 'thrive_admin_options', 'thrive_font_manager' );

	add_submenu_page( '', __( 'API Connections', 'thrive-dash' ), __( 'API Connections', 'thrive-dash' ), TVE_DASH_CAPABILITY, 'tve_dash_api_connect', 'tve_dash_api_connect' );
	add_submenu_page( '', __( 'API Connections Error Log', 'thrive-dash' ), __( 'API Connections Error Log', 'thrive-dash' ), TVE_DASH_CAPABILITY, 'tve_dash_api_error_log', 'tve_dash_api_error_log' );
}

/**
 * check for any expired connections (expired access tokens), or tokens that are about to expire and display global warnings / error messages
 */
function tve_dash_api_admin_notices() {
	if ( tve_get_current_screen_key( 'base' ) === 'admin_page_tve_dash_api_connect' ) {
		return;
	}

	require_once __DIR__ . '/misc.php';
	$connected_apis = Thrive_Dash_List_Manager::get_available_apis( true );
	$warnings       = array();

	foreach ( $connected_apis as $api_instance ) {
		if ( ! $api_instance instanceof Thrive_Dash_List_Connection_Abstract || $api_instance->param( '_nd' ) ) {
			continue;
		}

		$warnings = array_merge( $warnings, $api_instance->get_warnings() );
	}

	$nonce = sprintf( '<span class="nonce" style="display:none">%s</span>', wp_create_nonce( 'tve_api_dismiss' ) );

	$template = '<div class="%s notice is-dismissible tve-api-notice"><p>%s</p>%s</div>';

	$html = '';

	foreach ( $warnings as $err ) {
		$html .= sprintf( $template, 'error', $err, $nonce );
	}

	echo $html; // phpcs:ignore
}

/**
 * main entry point
 */
function tve_dash_api_connect() {
	require_once __DIR__ . '/misc.php';

	$available_apis = Thrive_Dash_List_Manager::get_available_apis();
	foreach ( $available_apis as $key => $api ) {
		/** @var Thrive_Dash_List_Connection_Abstract $api */
		if ( $api->is_connected() || $api->is_related() ) {
			unset( $available_apis[ $key ] );
		}
	}
	$connected_apis = Thrive_Dash_List_Manager::get_available_apis( true );

	foreach ( $connected_apis as $key => $api ) {
		if ( ! $api instanceof Thrive_Dash_List_Connection_Abstract || $api->is_related() ) {
			unset( $connected_apis[ $key ] );
		}
	}

	$api_types = Thrive_Dash_List_Manager::$API_TYPES;

	$api_types = apply_filters( 'tve_filter_api_types', $api_types );

	$types = array();
	foreach ( $api_types as $type => $label ) {
		$types[] = array(
			'type'  => $type,
			'label' => $label,
		);
	}

	Thrive_Dash_List_Manager::flash_messages();

	include __DIR__ . '/views/admin-list.php';
}

/**
 * check to see if we currently need to save some credentials, early in the admin section (e.g. a redirect from Oauth)
 */
function tve_dash_api_handle_save() {
	if ( ! current_user_can( TVE_DASH_CAPABILITY ) ) {
		wp_die( '' );
	}
	require_once __DIR__ . '/misc.php';
	$is_google_drive_response = ! empty( $_REQUEST['state'] ) && strpos( sanitize_text_field( $_REQUEST['state'] ), 'connection_google_drive' ) === 0;

	/**
	 * either a POST from a regular form, or an oauth redirect
	 */
	if ( ! $is_google_drive_response && empty( $_REQUEST['api'] ) && empty( $_REQUEST['oauth_token'] ) && empty( $_REQUEST['disconnect'] ) ) {
		return;
	}

	if ( $is_google_drive_response ) {
		$api = 'google_drive';
	} else {
		$api = sanitize_text_field( $_REQUEST['api'] );
	}

	$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
	$connection = Thrive_Dash_List_Manager::connection_instance( $api );

	if ( is_null( $connection ) ) {
		return;
	}

	$response = array(
		'success' => false,
		'message' => __( 'Unknown error occurred', 'thrive-dash' ),
	);
	if ( ! empty( $_REQUEST['disconnect'] ) ) {
		$connection->disconnect()->success( $connection->get_title() . ' ' . __( 'is now disconnected', 'thrive-dash' ) );
		//delete active conection for thrive ovation
		$active_connection = get_option( 'tvo_api_delivery_service', false );
		if ( $active_connection && $active_connection == $api ) {
			delete_option( 'tvo_api_delivery_service' );
		}
		tve_dash_remove_api_from_one_click_signups( $api );
		$response['success'] = true;
		$response['message'] = __( 'Service disconnected', 'thrive-dash' );
	} elseif ( ! empty( $_REQUEST['test'] ) ) {
		$result = $connection->test_connection();
		if ( is_array( $result ) && isset( $result['success'] ) && ! empty( $result['message'] ) ) {
			$response = $result;
		} else {
			$response['success'] = is_string( $result ) ? false : $result;
			$response['message'] = $response['success'] ? __( 'Connection works', 'thrive-dash' ) : __( 'Connection Error', 'thrive-dash' );
		}
	} else {
		$saved               = $connection->read_credentials();
		$response['success'] = $saved === true;
		$response['message'] = $saved === true ? __( 'Connection established', 'thrive-dash' ) : $saved;
	}
	/* Check if we need to upgrade an api */
	if ( ! empty( $_REQUEST['api'] ) ) {
		$upgraded_key = $_REQUEST['api'] . '-upgraded';
		if ( ! empty( $_REQUEST[ $upgraded_key ] ) && $_REQUEST[ $upgraded_key ] === '1' && method_exists( $connection, 'upgrade' ) ) {
			$response = $connection->upgrade();
		}
	}

	if ( $doing_ajax ) {
		exit( json_encode( $response ) );
	}

	$admin_url = admin_url( 'admin.php?page=tve_dash_api_connect' );
	if ( $response['success'] !== true ) {
		update_option( 'tve_dash_api_error', $response['message'] );
		wp_redirect( $admin_url . '#failed/' . $api );
		exit;
	}

	wp_redirect( $admin_url . '#done/' . $api );
	exit();
}

/**
 *  Handles the creation of the authorization URL and redirection to that url for token generation purposes
 */
function tve_dash_api_api_handle_redirect() {
	if ( ! current_user_can( TVE_DASH_CAPABILITY ) ) {
		wp_die( '' );
	}

	if ( empty( $_REQUEST['api'] ) && empty( $_REQUEST['oauth_token'] ) && empty( $_REQUEST['disconnect'] ) ) {
		return;
	}

	$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

	require_once __DIR__ . '/misc.php';
	$connection = Thrive_Dash_List_Manager::connection_instance( sanitize_text_field( $_REQUEST['api'] ) );

	if ( is_null( $connection ) ) {
		return;
	}

	$response    = array(
		'success' => false,
		'message' => __( 'Unknown error occurred', 'thrive-dash' ),
	);
	$credentials = ! empty( $_POST['connection'] ) ? map_deep( $_POST['connection'], 'sanitize_text_field' ) : array();

	$connection->set_credentials( $credentials );
	$result = $connection->getAuthorizeUrl();

	$response['success'] = ! ( ( filter_var( $result, FILTER_VALIDATE_URL ) ) === false );
	$response['message'] = ! $response['success'] ? 'An unknown error has occurred' : $result;

	if ( $doing_ajax ) {
		exit( json_encode( $response ) );
	}

	wp_redirect( admin_url( 'admin.php?page=tve_dash_api_connect' ) . '#failed/' . sanitize_text_field( $_REQUEST['api'] ) );

	exit();
}

/**
 * Enqueue specific scripts for api connections page
 *
 * @param string $hook
 */
function tve_dash_api_admin_scripts( $hook ) {
	$accepted_hooks = array(
		'admin_page_tve_dash_api_connect',
		'admin_page_tve_dash_api_error_log',
	);

	if ( ! in_array( $hook, $accepted_hooks ) ) {

		return;
	}

	if ( $hook === 'admin_page_tve_dash_api_error_log' ) {
		tve_dash_enqueue_script( 'tve-dash-api-admin-logs', TVE_DASH_URL . '/inc/auto-responder/dist/admin-logs-list.min.js', array(
			'tve-dash-main-js',
			'jquery',
			'backbone',
		) );

		return;
	}

	/**
	 * global admin JS file for notifications
	 */
	tve_dash_enqueue_script( 'tve-dash-api-admin-global', TVE_DASH_URL . '/inc/auto-responder/dist/admin-global.min.js', array(
		'tve-dash-main-js',
		'jquery',
		'backbone',
	) );

	$api_response = array(
		'message' => get_option( 'tve_dash_api_error' ),
	);
	if ( ! empty( $api_response['message'] ) ) {
		wp_localize_script( 'tve-dash-api-admin-global', 'tve_dash_api_error', $api_response );
		delete_option( 'tve_dash_api_error' );
	}
}

/**
 * for now, just a dump of the error logs from the table
 */
function tve_dash_api_error_log() {
	include plugin_dir_path( __FILE__ ) . 'views/admin-error-logs.php';
}

/**
 * hide notices for a specific API connection
 */
function tve_dash_api_hide_notice() {
	if ( ! empty( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'tve_api_dismiss' ) ) {
		exit( '-1' );
	}

	$key = ! empty( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : '';

	require_once __DIR__ . '/misc.php';

	$connection = Thrive_Dash_List_Manager::connection_instance( $key );
	$connection->set_param( '_nd', 1 )->save();

	exit( '1' );
}

/**
 * remove api connection from one click signups (new name: Signup Segue)
 */
function tve_dash_remove_api_from_one_click_signups( $api_name ) {
	$one_click_signups = get_posts( array( 'post_type' => 'tve_lead_1c_signup' ) );
	foreach ( $one_click_signups as $item ) {
		$connections = get_post_meta( $item->ID, 'tve_leads_api_connections', true );
		foreach ( $connections as $j => $connection ) {
			if ( $connection['apiName'] == $api_name ) {
				unset( $connections[ $j ] );
			}
		}
		update_post_meta( $item->ID, 'tve_leads_api_connections', $connections );
	}
}

function tve_dash_api_filter_localize( $localize ) {
	$localize['actions']['api_handle_save']     = 'tve_dash_api_handle_save';
	$localize['actions']['api_handle_redirect'] = 'tve_dash_api_handle_redirect';

	return $localize;
}

function tve_dash_api_filter_ui_hooks( $hooks ) {
	//this hook includes the general scripts from dash
	//$hooks[] = 'admin_page_tve_dash_api_error_log';

	return $hooks;
}

