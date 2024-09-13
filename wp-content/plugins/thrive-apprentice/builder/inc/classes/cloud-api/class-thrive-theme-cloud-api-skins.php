<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Theme_Cloud_Api_Skins
 */
class Thrive_Theme_Cloud_Api_Skins extends Thrive_Theme_Cloud_Api_Base {

	public $theme_element = 'skins';

	/**
	 * This transient name does not need to include the skin ID, just use the default transient name
	 *
	 * @return string
	 */
	public function get_transient_name() {
		return 'ttb_cloud_' . $this->theme_element;
	}

	// On skin download check first the TPM connection data and refresh the TTW auth token before going further
	public function before_zip() {

		// Check / refresh TTW access token before download skin
		$tpm_connection = method_exists( 'TPM_Connection', 'get_instance' ) && class_exists( 'TPM_Request' ) ? TPM_Connection::get_instance() : false;
		if ( $tpm_connection && true === $tpm_connection->is_expired() ) {
			$tpm_connection->refresh_token();
		}

		// User does't have his TTW account connected in TPM
		// Stop here, no need to start other requests
		if ( ! $tpm_connection ) {
			$update_check_url = sprintf( '<a href="%s" class="ttb-tpm-err-link">link</a>', admin_url( 'update-core.php?force-check=1' ) );
			throw new Exception( __( "Please make sure you have the latest version of Thrive Product Manager by clicking on this " . $update_check_url, 'thrive-theme' ) );
		}
	}
}
