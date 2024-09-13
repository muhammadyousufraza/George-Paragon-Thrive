<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/**
 * Contains:
 * - autoloaders for the main library files
 * - wrappers over WordPress wp_remote_* functions
 * - helper functions
 */

/**
 * Get the timout, in seconds that should be set for an external api request
 *
 * @param string $request_method current request method
 *
 * @return int
 */
function tve_dash_request_timeout( $request_method = 'get' ) {
	/* SUPP-14901 - we couldn't find the root cause of the long download time, so we increased the timeout */
	$default_timeout = defined('THRIVE_REQ_TIMEOUT') ? THRIVE_REQ_TIMEOUT : 30;
	/* SUPP-988, SUPP-3317, SUPP-8988 increased timeout to 30, it seems some hosts have some issues, not being able to resolve API URLs in 5 seconds */
	/**
	 * Filter the default request timeout used throughout Thrive products.
	 * 30 seconds is a "hard-earned" value, it started from 10, and got increased each time a new server could not download something in that time.
	 * The filter offers a workaround for 3rd party code to increase this timeout if they have further issues.
	 * Ideally we could provide them with a mini-plugin that does this
	 *
	 * @param int    $timeout        timeout in seconds
	 * @param string $request_method allows finer control
	 *
	 * @return int
	 *
	 */
	return apply_filters( 'thrive_request_timeout', $default_timeout, $request_method );
}

/**
 * Wrapper over the WP wp_remote_get function
 *
 * @param string $url  Site URL to retrieve.
 * @param array  $args Optional. Request arguments. Default empty array.
 *
 * @return WP_Error|array The response or WP_Error on failure.
 * @see wp_remote_get
 *
 */
function tve_dash_api_remote_get( $url, $args = array() ) {
	$args['sslverify'] = false;
	/* SUPP-988 increased timeout to 15, it seems some hosts have some issues, not being able to resolve API URLs in 5 seconds */
	$args['timeout'] = tve_dash_request_timeout( 'get' );

	/* makes sure this is NEVER used to download files */
	unset( $args['stream'], $args['filename'] );

	return wp_remote_get( $url, $args );
}

/**
 * SUPP-1146 strange issue on user's host - ssl requests timed out - seems this was the way to fix it
 * this is currently not used, but I'm leaving it here so that we have it for future references
 *
 * this is how it was used:
 *
 * add_action('http_api_curl', 'tve_dash_api_curl_ssl_version');
 *
 * @param resource $handle
 */
function tve_dash_api_curl_ssl_version( &$handle ) {
	curl_setopt( $handle, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT );
}

/**
 * Wrapper over the WP wp_remote_post function - all API POST requests pass through this function
 *
 * @param string $url  Site URL to retrieve.
 * @param array  $args Optional. Request arguments. Default empty array.
 *
 * @return WP_Error|array The response or WP_Error on failure.
 * @see wp_remote_post
 *
 */
function tve_dash_api_remote_post( $url, $args = array() ) {
	$args['sslverify'] = false;
	$args['timeout']   = tve_dash_request_timeout( 'post' );

	/* makes sure this is NEVER used to download files */
	unset( $args['stream'], $args['filename'] );

	return wp_remote_post( $url, $args );
}

/**
 *
 * Wrapper over the WP wp_remote_request function - API external calls pass through this function
 * sets some default parameters
 *
 * @param string $url  Site URL to retrieve.
 * @param array  $args Optional. Request arguments. Default empty array.
 *
 * @return WP_Error|array The response or WP_Error on failure.
 * @see wp_remote_request
 *
 */
function tve_dash_api_remote_request( $url, $args = array() ) {
	$args['sslverify'] = false;

	/* makes sure this is NEVER used to download files */
	unset( $args['stream'], $args['filename'] );

	return wp_remote_request( $url, $args );
}

/**
 * Retrieve versioning saved data
 *
 * @param string $api_name
 *
 * @return array
 */
function tve_saved_api_version( $api_name = '' ) {

	if ( empty( $api_name ) ) {
		return array();
	}

	$version    = empty( $_REQUEST['connection']['version'] ) ? '' : sanitize_text_field( $_REQUEST['connection']['version'] );
	$versioning = empty( $_REQUEST['connection']['versioning'] ) ? '' : sanitize_text_field( $_REQUEST['connection']['versioning'] );

	if ( empty( $version ) && empty( $versioning ) ) {
		$saved      = get_option( 'thrive_mail_list_api', array() );
		$version    = ! empty( $saved[ $api_name ]['version'] ) ? $saved[ $api_name ]['version'] : $version;
		$versioning = ! empty( $saved[ $api_name ]['versioning'] ) ? $saved[ $api_name ]['versioning'] : $versioning;
	}

	$data['version']    = $version;
	$data['versioning'] = $versioning;

	return $data;
}

/**
 * handle the actual autoload (require_once)
 *
 * @param       $basepath
 * @param       $className
 * @param array $v [versioning flag and API version]
 *
 * @return bool
 */
function _tve_dash_api__autoload( $basepath, $className, $v = array() ) {
	$parts = explode( '_', $className );
	if ( empty( $parts ) ) {
		return false;
	}

	$filename = array_pop( $parts );

	$path = $basepath;
	foreach ( $parts as $part ) {
		$path .= $part . '/';
	}

	/**
	 *  Added folder namespacing by API version [used in /auto-responder/classes/Connection/v and /auto-responder/lib/vendor/]
	 *  Load the class from the version folder if exists based on template [versioning and version] params [for new implementations]
	 *  Versioned class will be instantiated in Thrive_Dash_List_Manager::connection_instance()
	 */
	if ( ! empty( $v['version'] ) && ( ! empty( $v['versioning'] ) && (int) $v['versioning'] === 1 ) ) {
		$api_version    = $v['version'];
		$versioned_file = $path . "v{$api_version}/" . $filename . '.php';

		if ( file_exists( $versioned_file ) ) {
			require_once $versioned_file;

			return true;
		}
	}

	$path .= $filename . '.php';
	if ( ! file_exists( $path ) ) {
		return false;
	}

	require_once $path;

	return true;
}

/**
 * autoload any class from the lib/vendor folder
 *
 * @param string $className
 *
 * @return bool|void
 */
function tve_dash_api_vendor_loader( $className ) {
	$namespace = 'Thrive_Dash_Api_'; // = thrive
	if ( strpos( $className, $namespace ) !== 0 ) {
		return false;
	}

	$basedir = rtrim( __DIR__, '/\\' ) . '/lib/vendor/';

	/**
	 * Get the API version info
	 */
	$parts           = explode( '_', $className );
	$connection_name = strtolower( array_pop( $parts ) );
	$api_versioning  = tve_saved_api_version( $connection_name );

	return _tve_dash_api__autoload( $basedir, str_replace( $namespace, '', $className ), $api_versioning );
}

/**
 *
 * autoload "internal" auto-responder component classes (located in inc/auto-responder/classes folder)
 *
 * @param string $className
 *
 * @return bool
 */
function tve_dash_api_classes_loader( $className ) {
	$namespace = 'Thrive_Dash_List_';
	if ( strpos( $className, $namespace ) !== 0 ) {
		return false;
	}

	$parts           = explode( '_', $className );
	$connection_name = strtolower( array_pop( $parts ) );
	$api_versioning  = tve_saved_api_version( $connection_name );

	$basedir = rtrim( __DIR__, '/\\' ) . '/classes/';

	return _tve_dash_api__autoload( $basedir, str_replace( $namespace, '', $className ), $api_versioning );
}

spl_autoload_register( 'tve_dash_api_vendor_loader' );
spl_autoload_register( 'tve_dash_api_classes_loader' );
do_action( 'thrive_dashboard_autoresponders_loaded' );
/**
 * AJAX call handler for API logs that are being retried
 * If the subscription is made with success the log is deleted from db
 */
function tve_dash_api_form_retry() {
	check_ajax_referer( 'tve-dash' );

	if ( ! current_user_can( TVE_DASH_CAPABILITY ) ) {
		wp_die( '' );
	}
	$connection_name = ! empty( $_POST['connection_name'] ) ? sanitize_text_field( $_POST['connection_name'] ) : null;
	$list_id         = ! empty( $_POST['list_id'] ) ? sanitize_text_field( $_POST['list_id'] ) : null;
	$email           = ! empty( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : null;
	$name            = ! empty( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
	$phone           = ! empty( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
	$log_id          = ! empty( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : null;
	$url             = ! empty( $_POST['url'] ) ? sanitize_text_field( $_POST['url'] ) : null;

	if ( empty( $connection_name ) ) {
		exit( json_encode( array(
			'status'  => 'error',
			'message' => __( 'Connection is not specified !', 'thrive-dash' ),
		) ) );
	}

	if ( empty( $list_id ) ) {
		exit( json_encode( array(
			'status'  => 'error',
			'message' => __( 'Where should I subscribe this user? List is not specified !', 'thrive-dash' ),
		) ) );
	}

	if ( empty( $email ) ) {
		exit( json_encode( array(
			'status'  => 'error',
			'message' => __( 'Email is not specified !', 'thrive-dash' ),
		) ) );
	}

	$data = array(
		'email' => $email,
		'name'  => $name,
		'phone' => $phone,
		'url'   => $url,
	);

	if ( $list_id == "asset" ) {

		$api = Thrive_Dash_List_Manager::connection_instance( $connection_name );
		if ( ! $api ) {
			$response = __( 'Cannot establish API connection', 'thrive-dash' );
		} else {

			$post_data['_asset_group'] = ! empty( $_POST['_asset_group'] ) ? sanitize_text_field( $_POST['_asset_group'] ) : '';
			$post_data['email']        = sanitize_email( $_POST['email'] );
			if ( isset( $_POST['name'] ) ) {
				$post_data['name'] = sanitize_text_field( $_POST['name'] );
			}

			$response = true;
			try {
				$api->sendEmail( $post_data );
			} catch ( Exception $e ) {
				$response = $e->getMessage();
			}

		}

	} else {
		$response = tve_api_add_subscriber( $connection_name, $list_id, $data, false );
	}


	if ( $response !== true ) {
		exit( json_encode( array(
			'status'  => 'error',
			'message' => $response,
		) ) );
	}

	if ( ! empty( $log_id ) ) {

		global $wpdb;

		$delete_result = $wpdb->delete( $wpdb->prefix . 'tcb_api_error_log', array( 'id' => $log_id ), array( '%d' ) );

		if ( $delete_result === false ) {
			exit( json_encode( array(
				'status'  => 'error',
				'message' => __( "Subscription was made with success but we could not delete the log from database !", 'thrive-dash' ),
			) ) );
		}
	}

	exit( json_encode( array(
		'status'  => 'success',
		'message' => __( 'Subscription was made with success !', 'thrive-dash' ),
	) ) );
}

/**
 * AJAX call handler to delete API's logs
 */
function tve_dash_api_delete_log() {

	check_ajax_referer( 'tve-dash' );

	if ( ! current_user_can( TVE_DASH_CAPABILITY ) ) {
		wp_die( '' );
	}
	$log_id = ! empty( $_POST['log_id'] ) ? intval( $_POST['log_id'] ) : null;

	if ( empty( $log_id ) ) {
		exit( json_encode( array(
			'status'  => 'error',
			'message' => __( "Log ID is not valid !", 'thrive-dash' ),
		) ) );
	}

	global $wpdb;

	$delete_result = $wpdb->delete( $wpdb->prefix . 'tcb_api_error_log', array( 'id' => $log_id ), array( '%d' ) );
	if ( $delete_result === false ) {
		exit( json_encode( array(
			'status'  => 'error',
			'message' => sprintf( __( "An error occurred: %s", 'thrive-dash' ), $wpdb->last_error ),
		) ) );
	}

	if ( $delete_result === 0 ) {
		exit( json_encode( array(
			'status'  => 'error',
			'message' => sprintf( __( "The log with ID: %s could not be found !", 'thrive-dash' ), $log_id ),
		) ) );
	}

	exit( json_encode( array(
		'status'  => 'success',
		'message' => sprintf( __( "API Log with ID: %s has been deleted with success !", 'thrive-dash' ), $log_id ),
	) ) );
}

if ( ! class_exists( 'Thrive_List_Manager' ) ) {
	class Thrive_List_Manager extends Thrive_Dash_List_Manager {

	}
}

if ( ! class_exists( 'Thrive_List_Connection_Mandrill' ) ) {
	class Thrive_List_Connection_Mandrill extends Thrive_Dash_List_Connection_Mandrill {

	}
}

if ( ! class_exists( 'Thrive_List_Connection_Postmark' ) ) {
	class Thrive_List_Connection_Postmark extends Thrive_Dash_List_Connection_Postmark {

	}
}

if ( ! class_exists( 'Thrive_List_Connection_SparkPost' ) ) {
	class Thrive_List_Connection_SparkPost extends Thrive_Dash_List_Connection_SparkPost {

	}
}

if ( ! class_exists( 'Thrive_List_Connection_Mailgun' ) ) {
	class Thrive_List_Connection_Mailgun extends Thrive_Dash_List_Connection_Mailgun {

	}
}

if ( ! class_exists( 'Thrive_List_Connection_Awsses' ) ) {
	class Thrive_List_Connection_Awsses extends Thrive_Dash_List_Connection_Awsses {

	}
}

if ( ! class_exists( 'Thrive_List_Connection_SendinblueEmail' ) ) {
	class Thrive_List_Connection_SendinblueEmail extends Thrive_Dash_List_Connection_SendinblueEmail {

	}
}
