<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_Product_Theme extends TPM_Product {

	/**
	 * Keep a flag to check if the theme was installed before
	 *
	 * @var bool
	 */
	protected $previously_installed = false;

	public function get_status() {

		if ( ! empty( $this->status ) ) {
			return $this->status;
		}

		if ( ! $this->is_purchased() ) {
			$this->status = self::AVAILABLE;

			return $this->status;
		}

		if ( ! $this->is_installed() ) {
			$this->status = self::TO_INSTALL;

			return $this->status;
		}

		if ( ! $this->is_licensed() ) {
			$this->status = self::TO_LICENSE;

			return $this->status;
		}

		if ( $this->is_activated() ) {
			$this->status = self::ACTIVATED;

			return $this->status;
		}

		$this->status = self::READY;

		return $this->status;
	}

	public function is_activated() {

		/** @var WP_Theme $current_theme */
		$current_theme = wp_get_theme();

		return $this->name === $current_theme->get( 'Name' );
	}

	public function is_installed() {

		$theme = wp_get_theme( $this->api_slug );

		return ! is_wp_error( $theme->errors() );
	}

	public function to_array() {

		$data         = parent::to_array();
		$data['type'] = 'theme';

		return $data;
	}

	protected function _get_download_url( $api_slug ) {

		global $wp_version;

		$args    = array(
			'slug'    => $api_slug,
			'version' => '1.0',
		);
		$request = array(
			'sslverify'  => false,
			'body'       => array(
				'action'  => 'theme_update',
				'request' => serialize( $args ),
				'api-key' => md5( home_url() ),
			),
			'timeout'    => 30,
			'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url(),
		);

		$thrive_update_api_url = add_query_arg(
			array(
				'p' => $this->_get_hash( $request['body'] ),
			),
			'http://service-api.thrivethemes.com/theme/update'
		);

		$result = wp_remote_post( $thrive_update_api_url, $request );

		if ( ! is_wp_error( $result ) ) {
			$info = @unserialize( wp_remote_retrieve_body( $result ) );
			if ( ! empty( $info ) ) {
				return $info['package'];
			}
		}

		return new WP_Error( '400', $result->get_error_message() );
	}

	public function install( $credentials ) {
		if ( $this->is_installed() ) {
			$this->previously_installed = true;

			return true;
		}

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$url = $this->_get_download_url( $this->api_slug );

		global $wp_filesystem;
		$connected = WP_Filesystem( $credentials );

		if ( false === $connected ) {
			return $wp_filesystem->errors;
		}

		require_once __DIR__ . '/class-tpm-theme-installer-skin.php';

		$skin      = new TPM_Theme_Installer_Skin( $credentials );
		$installer = new Theme_Upgrader( $skin );

		$installed = $installer->install( $url );

		if ( null === $installed ) {
			/** @var TPM_Theme_Installer_Skin $installer ->skin */
			$installed = new WP_Error( '500', end( $installer->skin->messages ) );
		}

		return $installed;
	}

	/**
	 * @param $previously_installed
	 */
	public function set_previously_installed( $previously_installed ) {
		$this->previously_installed = $previously_installed;
	}

}
