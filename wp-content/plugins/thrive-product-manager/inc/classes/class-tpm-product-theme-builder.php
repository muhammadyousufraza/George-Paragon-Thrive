<?php

class TPM_Product_Theme_Builder extends TPM_Product_Theme {

	public function to_array() {

		$data           = parent::to_array();
		$data['type']   = 'theme';
		$data['hidden'] = true;

		return $data;
	}

	/**
	 * Finds out URL where to download the zip from
	 *
	 * @param string $api_slug
	 *
	 * @return WP_Error|string url
	 */
	protected function _get_download_url( $api_slug ) {

		$request = array(
			'sslverify' => false,
			'body'      => array(
				'action' => 'theme_update',
				'type'   => 'latest',
			),
			'timeout'   => 60,
		);

		$thrive_update_api_url = add_query_arg( array(
			'p' => $this->_get_hash( $request['body'] ),
		), 'http://service-api.thrivethemes.com/theme/update' );

		$result = wp_remote_get( $thrive_update_api_url, $request );

		if ( ! is_wp_error( $result ) ) {
			$info = @unserialize( wp_remote_retrieve_body( $result ) );

			if ( Thrive_Product_Manager::is_debug_mode() ) {
				$package = defined( 'TTB_TEST_ARCHIVE' ) ? TTB_TEST_ARCHIVE : '';
			}

			if ( empty( $package ) ) {
				$package = ! empty( $info['package'] ) ? $info['package'] : new WP_Error( '404', 'Bad request' );
			}

			return $package;
		}

		return new WP_Error( '400', $result->get_error_message() );
	}

	/**
	 * Activates TTB
	 *
	 * @return bool|WP_Error
	 */
	public function activate() {

		if ( ! $this->previously_installed ) {
			return true;
		}

		$activated = $this->is_activated();

		if ( ! $activated && $this->is_installed() ) {
			$theme = wp_get_theme( 'thrive-theme' );

			$activated = true;

			switch_theme( $theme->get_stylesheet() );
		}

		return $activated;
	}

	/**
	 * Install Theme Builder
	 *
	 * @param array $credentials
	 *
	 * @return array|bool|WP_Error|WP_Term
	 */
	public function install( $credentials ) {
		$installed = parent::install( $credentials );

		/* If the theme builder install went ok, we will install also the default skin */
		if ( $installed && ! $this->previously_installed && ! is_wp_error( $installed ) ) {
			$skin      = TPM_Product_List::get_instance()->get_product_instance( TPM_Product_Skin::DEFAULT_TAG );
			$installed = $skin->install( $credentials );

			if ( ! is_wp_error( $installed ) ) {
				static::set_fresh_install_flag( 1 );
			}
		}

		return $installed;
	}

	/**
	 * Set a flag to let the theme know if the user installed TTB for the first time
	 * Also called from TTB
	 *
	 * @param $value
	 */
	public static function set_fresh_install_flag( $value ) {
		update_option( 'thrive_theme_is_fresh_install', $value );
	}

	/**
	 * Check if the fresh install flag is set, defaulting to 0. Also called from TTB
	 * @return bool
	 */
	public static function is_fresh_install() {
		return (int) get_option( 'thrive_theme_is_fresh_install', 0 ) === 1;
	}

	/**
	 * Used in frontend/js
	 *
	 * @return string
	 */
	public static function get_dashboard_url() {

		return admin_url( 'admin.php?page=thrive-theme-dashboard' );
	}

	/**
	 * Check if the TTB with slug thrive-theme is installed
	 *
	 * @return bool
	 */
	public function is_installed() {

		$theme = wp_get_theme( 'thrive-theme' );

		return ! is_wp_error( $theme->errors() );
	}

	/**
	 * Change the response after installing / activating theme builder
	 *
	 * @param array $data
	 *
	 * @return array|mixed
	 */
	public function before_response( $data ) {
		return $this->get_response_status( empty( $this->previously_installed ) ? 'installed' : 'ready' );
	}

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

		if ( ! $this->is_activated() ) {
			return $this->status = self::TO_ACTIVATE;
		}

		if ( ! $this->is_licensed() ) {
			$this->status = self::TO_LICENSE;

			return $this->status;
		}

		$this->status = self::READY;

		return $this->status;
	}
}
