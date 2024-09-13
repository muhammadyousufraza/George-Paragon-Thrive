<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_Product_Plugin extends TPM_Product {

	/**
	 * Checks if product is installed/downloaded in WP_PLUGIN_DIR
	 *
	 * @return bool
	 */
	public function is_installed() {

		if ( empty( $this->file ) ) {
			return false;
		}

		$path = rtrim( WP_PLUGIN_DIR, '/' ) . '/' . trim( $this->file, '/' );

		return file_exists( $path );
	}

	/**
	 * Returns array of its properties
	 *
	 * @return array
	 */
	public function to_array() {

		$data         = parent::to_array();
		$data['type'] = 'plugin';

		return $data;
	}

	protected function _get_download_url() {

		$options = array(
			'timeout'   => 20, //seconds
			'sslverify' => false,
			'headers'   => array(
				'Accept' => 'application/json',
			),
		);
		/**
		 * prepare the POST parameters
		 */
		$options['body'] = array(
			'api_slug' => $this->api_slug,
		);

		$url    = add_query_arg( array( 'p' => $this->_get_hash( $options['body'] ) ), 'http://service-api.thrivethemes.com/plugin/update' );
		$result = wp_remote_post( $url, $options );

		if ( ! is_wp_error( $result ) ) {
			$info = json_decode( wp_remote_retrieve_body( $result ), true );
			if ( ! empty( $info ) ) {
				return $info['download_url'];
			}
		}

		return new WP_Error( '400', wp_remote_retrieve_body( $result ) );
	}

	/**
	 * @param $credentials array
	 *
	 * @return bool|WP_Error
	 */
	public function install( $credentials ) {

		if ( $this->is_installed() ) {
			return true;
		}

		add_filter( 'upgrader_package_options', array( $this, 'upgrader_package_options' ) );

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$url = $this->_get_download_url();

		if ( is_wp_error( $url ) ) {

			$error = "Couldn't get download URL for " . $this->name;

			TPM_Log_Manager::get_instance()->set_message( $error )->log();

			return new WP_Error( 'download_url', $error );
		}

		/** @var $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;
		$connected = WP_Filesystem( $credentials );

		if ( $connected === false ) {
			return $wp_filesystem->errors;
		}

		require_once __DIR__ . '/class-tpm-plugin-installer-skin.php';

		$installer = new Plugin_Upgrader( new TPM_Plugin_Installer_Skin( $credentials ) );

		$result = $installer->install( $url );

		remove_filter( 'upgrader_package_options', array( $this, 'upgrader_package_options' ) );

		return $result;
	}

	public function upgrader_package_options( $options ) {

		$options['clear_destination'] = true;

		return $options;
	}

	public function is_activated() {

		return is_plugin_active( $this->file );
	}

	public function activate() {

		if ( $this->is_activated() ) {
			return true;
		}

		return activate_plugin( $this->file );
	}
}
