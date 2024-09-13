<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Landing_Page_Cloud_Templates_Api', false ) ) {
	require_once TVE_TCB_ROOT_PATH . '/landing-page/inc/TCB_Landing_Page_Transfer.php';
}

/**
 * Class Thrive_Theme_Cloud_Api_Base
 */
class Thrive_Theme_Cloud_Api_Base extends TCB_Landing_Page_Cloud_Templates_Api {

	/**
	 * Theme folder full path
	 *
	 * @var string
	 */
	public $theme_folder_path;

	/**
	 * Folder where to keep theme elements templates
	 *
	 * @var string
	 */
	public $theme_folder = 'thrive-theme';

	/**
	 * Name of the element which data we want from the cloud
	 *
	 * @var string
	 */
	public $theme_element = '';

	/**
	 * Takes care to have all the needed folders in order to save the archive
	 *
	 * @throws Exception
	 */
	protected function ensure_folders() {

		/**
		 * first make sure we can save the downloaded template
		 */
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			throw new Exception( $upload['error'] );
		}

		$base = trailingslashit( $upload['basedir'] ) . "{$this->theme_folder}/";

		if ( ! is_dir( $base ) && ! mkdir( $base, 0777, true ) ) {
			throw new \RuntimeException( sprintf( 'Directory "%s" was not created!', $base ) );
		}

		$this->theme_folder_path = $base;

		if ( ! empty( $this->theme_element ) ) {
			$theme_element_folder = $this->theme_folder_path . $this->theme_element;
			if ( ! is_dir( $theme_element_folder ) && ! mkdir( $theme_element_folder, 0777, true ) ) {
				throw new \RuntimeException( sprintf( 'Directory "%s" was not created!', $theme_element_folder ) );
			}
		}

	}

	/**
	 * Get a transient name that's unique for the current element and current skin
	 *
	 * @return string
	 */
	public function get_transient_name() {
		return 'ttb_cloud_' . $this->theme_element . '_' . thrive_skin()->ID;
	}

	/**
	 * Returns a list of items from the cloud
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function get_items( $args = [] ) {

		/* Try to get items from the cache first time */
		$transient_name = $this->get_transient_name();

		/* Transient name should always be constructed based on the $args array - it allows having separate transients for each filtered result */
		if ( ! empty( $args ) ) {
			$transient_name .= '_' . md5( serialize( $args ) );

			/* We need to differentiate between singular and list */
			if ( ! empty( $args['type'] ) && $args['type'] === 'content' ) {
				$transient_name .= '_' . md5( thrive_template()->primary_template );
			}
		}

		$items = Thrive_Utils::get_transient( $transient_name );

		/* Make the request only if we don't have items in the cache */
		if ( false === $items ) {
			$params = [
				'route'         => 'getAll',
				'theme_element' => $this->theme_element,
			];

			$params = wp_parse_args( $args, $params );

			$params = array_merge( $params, TD_TTW_Connection::get_instance()->get_connection_data() );

			$response = $this->_request( $params );
			$data     = json_decode( $response, true );

			if ( empty( $data ) ) {
				throw new Exception( 'Got response: ' . $response );
			}

			if ( empty( $data['success'] ) ) {
				throw new Exception( $data['error_message'] );
			}

			if ( ! isset( $data['data'] ) ) {
				throw new Exception( 'Could not fetch the themes.' );
			}

			$this->_validateReceivedHeader( $data );

			$items = $this->before_data_list( $data['data'] );
		}

		/* Set cache with the new listing from the cloud */
		if ( ! empty( $items ) ) {
			$cache_for = apply_filters( 'thrive_theme_cloud_cache', 8 * HOUR_IN_SECONDS );

			set_transient( $transient_name, $items, $cache_for );
		}

		return $items;
	}

	/**
	 * Method to be used before the data is returned to the user
	 *
	 * @param $items
	 *
	 * @return mixed
	 */
	protected function before_data_list( $items ) {
		return $items;
	}

	/**
	 * Download item from the cloud
	 *
	 * @param string $id
	 *
	 * @return string
	 * @throws Exception
	 */
	public function download_item( $id ) {

		$this->ensure_folders();

		$new_zip_file = $this->theme_folder_path . $id . '.zip';

		return $this->get_zip( $id, $new_zip_file );
	}

	// Called before get_zip
	public function before_zip() {
	}

	/**
	 * Validate and return the tpm connection details to be used in the request sent to the API
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function validate_tpm_connection() {
		$tpm_data = $this->get_tpm_connection();
		// User doesn't have his TTW account connected in TPM
		// Stop here, no need to start other requests
		if ( empty( $tpm_data['ttw_id'] ) || empty( $tpm_data['ttw_salt'] ) ) {
			$tpm_dash_url = sprintf( '<a href="%s" class="ttb-tpm-err-link">here</a>', admin_url( 'admin.php?page=thrive_product_manager&tpm_disconnect=1' ) );
			throw new Exception( __( sprintf( 'The connection to your thrivethemes.com account has been lost. Click %s to reconnect.', $tpm_dash_url ), 'thrive-theme' ) );
		}

		return $tpm_data;
	}

	/**
	 * Get zip archive from cloud
	 *
	 * @param $id
	 * @param $new_zip_file
	 *
	 * @return mixed
	 * @throws Exception
	 */
	protected function get_zip( $id, $new_zip_file ) {

		$zip_transient = 'tcb_zip_tranzient_' . $new_zip_file;

		$has_zip = Thrive_Utils::get_transient( $zip_transient );

		if ( ! empty( $has_zip ) && file_exists( $new_zip_file ) ) {
			return $new_zip_file;
		}

		// Various checks before get_zip execution by route
		$this->before_zip();

		// Check TPM connection instance
		$tpm_data = $this->validate_tpm_connection();

		if ( empty( $tpm_data ) || ! is_array( $tpm_data ) ) {
			$tpm_data = [
				'ttw_id'   => '',
				'ttw_salt' => '',
			];
		}

		$site_url = function_exists( 'get_site_url' ) ? get_site_url() : Thrive_Utils::get_site_url();

		$params = [
			'route'         => 'download',
			'theme_element' => $this->theme_element,
			'id'            => $id,
			'user_id'       => ! empty( $tpm_data['ttw_id'] ) ? $tpm_data['ttw_id'] : '',
			'ttw_auth'      => ! empty( $tpm_data['ttw_salt'] ) ? $tpm_data['ttw_salt'] : '',
			'referrer'      => base64_encode( $site_url ),
		];

		$body = $this->_request( $params );

		$control = [
			'auth' => $this->request['headers']['X-Thrive-Authenticate'],
			'id'   => $id,
		];

		/**
		 * this means an error -> error message is json_encoded
		 */
		if ( empty( $this->received_auth_header ) ) {
			$data = json_decode( $body, true );
			throw new Exception( isset( $data['error_message'] ) ? $data['error_message'] : 'Invalid response: ' . $body );
		}

		$this->_validateReceivedHeader( $control );

		/**
		 * at this point, $body holds the contents of the zip file
		 */
		tve_wp_upload_bits( $new_zip_file, $body );

		/**
		 * Set 8h cache
		 */
		set_transient( $zip_transient, 1, 8 * HOUR_IN_SECONDS );

		return $new_zip_file;
	}

	/**
	 * TPM connection array
	 *
	 * @return mixed
	 */
	protected function get_tpm_connection() {

		return get_option( 'tpm_connection', [] );
	}

	/**
	 * Check if debug is active for theme cloud templates
	 *
	 * @return bool
	 */
	protected function theme_cloud_debug_active() {
		return defined( 'THRIVE_THEME_CLOUD_DEBUG' ) && THRIVE_THEME_CLOUD_DEBUG;
	}

	/**
	 * Process the data from the zip taken from the cloud
	 *
	 */
	public function process_zip( $zip_file_path ) {
	}
}
