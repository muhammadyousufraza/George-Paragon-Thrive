<?php

namespace Thrive\Automator\Suite;


use Thrive\Automator\Utils;

/**
 * General idea: store all ttw-account related functionality here.
 * This somewhat duplicates parts of the TPM code, but this solution prevents issues with version incompatibilities between TPM and TAP
 * In general, this will try to use the TPM code, but if it's not applicable, it will use the TAP code
 */
class TTW {
	const SLUG                  = 'thrive-product-manager/thrive-product-manager.php';
	const APPS_TOOLTIP          = 'apps_tooltip';
	const AUTOMATOR_PRODUCT_TAG = 'tap';
	const RIBBON                = 'ttw-ribbon';
	const SERVICE_API_URL       = 'https://service-api.thrivethemes.com/latest-version?api_slug=thrive_product_manager';
	const DOWNLOAD_URL          = 'https://9fba8fa71256a6495f82-41873bbf94a0f18ee40f3b2aa324e2ee.ssl.cf5.rackcdn.com/thrive-product-manager-%s.zip';

	public static function init() {
		static::includes();
		static::hooks();
	}

	public static function includes() {
		require_once __DIR__ . '/class-rest-controller.php';
		require_once __DIR__ . '/class-plugin-handler.php';
	}

	public static function hooks() {
		add_action( 'tap_output_extra_svg', [ __CLASS__, 'output_extra_svg' ] );
		add_action( 'rest_api_init', [ __CLASS__, 'init_rest' ] );
		add_action( 'admin_notices', [ __CLASS__, 'admin_notices' ], 1 );
		add_filter( 'admin_body_class', [ __CLASS__, 'admin_body_class' ] );
		add_filter( 'tap_tracking_data', [ __CLASS__, 'tracking' ] );
	}

	/**
	 * Add TPM data to tracking data
	 *
	 */
	public static function tracking( $tracking_data ) {
		$tracking_data['tpm'] = [
			'installed' => static::is_installed(),
			'active'    => static::is_active(),
			'connected' => static::has_tpm() ? static::connection()->is_connected() : false,
		];

		return $tracking_data;
	}

	public static function admin_body_class( $classes ) {
		if ( ! empty( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === 'tve_dash_api_connect' && ! empty( $_GET['body_class'] ) ) {
			$classes .= ' ' . sanitize_html_class( $_GET['body_class'] );
		}

		return $classes;
	}

	public static function init_rest() {
		$controller = new Rest_Controller();
		$controller->register_routes();
	}

	public static function output_extra_svg() {
		include_once __DIR__ . '/suite-icons.svg';
	}

	public static function is_installed(): bool {
		$installed_plugins = get_plugins();

		return array_key_exists( static::SLUG, $installed_plugins ) || in_array( static::SLUG, $installed_plugins, true );
	}

	public static function is_active(): bool {
		return is_plugin_active( static::SLUG );
	}

	/**
	 * Print admin reminder for those who don't have TPM
	 *
	 * @return void
	 */
	public static function admin_notices() {
		if ( static::should_display_ribbon() ) {
			Utils::tap_template( 'suite-ribbon' );
		}
	}

	/**
	 * Whether the ribbon should be displayed
	 *
	 * @return bool
	 */
	public static function should_display_ribbon(): bool {
		return ! Utils::get_user_meta( 0, 'notice-dismissed-' . static::RIBBON ) &&
		       ! Utils::has_suite_access() &&
		       ( ! static::is_installed() || ! static::is_active() || ! static::connection()->is_connected() );
	}

	/**
	 * Get array data for localizing js
	 *
	 * @return array
	 */
	public static function localize(): array {
		$localize = [
			'installed'     => static::is_installed(),
			'active'        => static::is_active(),
			'connected'     => false,
			'download_link' => static::get_download_link(),
		];

		if ( static::has_tpm() ) {
			$localize = array_merge( $localize, [
				'connected' => static::connection()->is_connected(),
				'login_url' => add_query_arg(
					[
						'popup' => 1,
					],
					static::connection()->get_login_url()
				),
			] );
		}
		if ( Utils::has_suite_access() ) {
			$localize = array_merge( $localize, [
				'connected' => true,
				'active'    => true,
				'installed' => true,
			] );
		}

		return $localize;
	}

	/**
	 * Get download link for TPM
	 *
	 * @return string
	 */
	public static function get_download_link() {
		$response = wp_remote_get( static::SERVICE_API_URL );
		if ( $response instanceof \WP_Error ) {
			$url = 'https://thrivethemes.com/automator/'; //fallback just in case
		} else {
			$url = sprintf( static::DOWNLOAD_URL, $response['body'] );
		}

		return $url;
	}

	/**
	 * Register new user on TTW. Call this from an api endpoint
	 *
	 * @param string $user_email
	 * @param string $password unhashed password
	 * @param string $user_first_name
	 * @param string $user_last_name
	 *
	 * @return \WP_Error|true
	 */
	public static function register_user( string $user_email, string $password, string $user_first_name, string $user_last_name = '' ) {
		if ( ! static::has_tpm() ) {
			return new \WP_Error( 'no_tpm', __( 'TPM is not installed', 'thrive-automator' ) );
		}

		$user_data = [
			'email'      => $user_email,
			'password'   => $password,
			'first_name' => $user_first_name,
			'last_name'  => $user_last_name,
		];

		$endpoint = add_query_arg(
			[
				'tpm_token' => static::connection()->get_token(),
				'site_url'  => site_url(),
			],
			\Thrive_Product_Manager::get_ttw_url() . '/api/v1/public/users'
		);

		$response = wp_remote_post(
			$endpoint,
			[
				'headers' => [
					'Content-type' => 'application/json',
				],
				'body'    => wp_json_encode( $user_data ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data ) ) {
			return new \WP_Error( 'invalid_response', __( 'Invalid response received from thrivethemes.com: ', 'thrive-automator' ) . wp_remote_retrieve_body( $response ) );
		}

		if ( empty( $data['success'] ) ) {
			return new \WP_Error( 'registration_error', $data['message'] );
		}

		// prepare data for TPM connection
		$tpm_data = array_merge(
			array_map( 'base64_decode', $data['tpm'] ),
			[
				'status'   => \TPM_Connection::CONNECTED,
				'ttw_salt' => $data['tpm']['ttw_salt'],
			]
		);

		static::connection()->set_data( $tpm_data );

		update_option( \TPM_Connection::NAME, $tpm_data );
		delete_option( 'tpm_bk_connection' );

		tpm_cron()->schedule( $tpm_data['ttw_expiration'] );

		tpm_delete_transient( 'td_ttw_licenses_details' );
		tpm_delete_transient( 'td_ttw_connection_error' );

		// Activate Automator license
		$result = static::activate_license();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// finally, we can return true :)
		return true;
	}

	/**
	 * Activate a license for Automator
	 *
	 * @return \WP_Error|true
	 */
	public static function activate_license() {
		if ( ! static::has_tpm() ) {
			return new \WP_Error( 'no_tpm', __( 'TPM is not installed', 'thrive-automator' ) );
		}

		\TPM_Product_List::get_instance()->clear_cache();
		\TPM_License_Manager::get_instance()->clear_cache();

		$product_list = \TPM_Product_List::get_instance();
		$product      = $product_list->get_product_instance( static::AUTOMATOR_PRODUCT_TAG );

		if ( ! $product->is_licensed() ) {
			$product->search_license();
			$licensed = \TPM_License_Manager::get_instance()->activate_licenses( [ $product ] );

			if ( false === $licensed ) {
				return new \WP_Error( 'license_error', __( 'Could not activate license', 'thrive-automator' ) );
			}

			\TPM_Product_List::get_instance()->clear_cache();
			\TPM_License_Manager::get_instance()->clear_cache();
		}

		return true;
	}

	/**
	 * Get the TPM token
	 *
	 * @return \TPM_Connection
	 */
	public static function connection() {
		return \TPM_Connection::get_instance();
	}

	/**
	 * Check if TPM is available
	 *
	 * @return bool
	 */
	public static function has_tpm(): bool {
		return class_exists( '\TPM_Connection', false );
	}

	public static function admin_init() {
		if ( ! static::has_tpm() ) {
			return;
		}

		/**
		 * overwrite TPM functionality, because this is handled in a popup. TPM relies on redirects in the same browser window
		 */
		if ( ! empty( $_REQUEST['tpm_token'] ) && ! empty( $_REQUEST['popup'] ) ) {
			remove_action( 'current_screen', [ thrive_product_manager(), 'try_process_connection' ] );

			add_action( 'current_screen', static function () {

				add_filter( 'wp_redirect', static function ( $location ) {

					// check successful connection
					$messages = static::connection()->apply_messages();

					if ( ! empty( $messages ) && $messages[0]['status'] ?? '' === 'success' ) {
						//activate license
						static::activate_license();

						Utils::tap_template( 'suite-redirect' );

						// do not redirect, instead just close the window.
						wp_die();
					}

					return $location;
				} );

				thrive_product_manager()->try_process_connection();
			} );
		}
	}
}
