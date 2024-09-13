<?php

/**
 * Class TPM_Admin
 * - included by main plugin class if the current request is_admin()
 *
 * @see is_admin()
 */
class TPM_Admin {

	private static $instance;

	private function __construct() {

		/**
		 * update tpm_version option
		 */
		add_action(
			'admin_init',
			function () {

				/**
				 * On each TPM update clear cache
				 * - clear cache each time user updates TPM
				 */
				if ( $this->check_plugin_version( Thrive_Product_Manager::V ) ) {
					$this->clear_all_cache();
					update_option( 'tpm_version', Thrive_Product_Manager::V );
				}
			}
		);

		/**
		 * delete tpm_version from DB
		 */
		add_action(
			'admin_init',
			static function () {
				register_deactivation_hook(
					WP_PLUGIN_DIR . '/thrive-product-manager/thrive-product-manager.php',
					array(
						TPM_Admin::get_instance(),
						'deactivation_callback',
					)
				);
			}
		);
	}

	/**
	 * @return TPM_Admin
	 */
	public static function get_instance() {

		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Clears all cache
	 * - ttw products list
	 * - ttw licenses
	 */
	public function clear_all_cache() {

		TPM_Product_List::get_instance()->clear_cache();
		TPM_License_Manager::get_instance()->clear_cache();
	}

	/**
	 * Checks if the option saved in DB is lower strict than the current TPM plugin constant
	 * - used to run some code
	 *
	 * @param string version
	 *
	 * @return bool
	 */
	public function check_plugin_version( $version ) {

		return version_compare( get_option( 'tpm_version', '1.0' ), $version, '<' );
	}

	/**
	 * Deletes tpm_version option from DB
	 */
	public function delete_tpm_version() {

		delete_option( 'tpm_version' );
	}

	/**
	 * Callback when TPM is deactivated
	 * @see register_deactivation_hook()
	 */
	public function deactivation_callback() {
		TPM_Product_List::get_instance()->clear_cache();
		TPM_License_Manager::get_instance()->clear_cache();
		TPM_License_Manager::get_instance()->deactivate_all_licenses();
		$this->delete_tpm_version();
		delete_option( 'tpm_bk_connection' );
	}
}

if ( is_admin() ) {
	return TPM_Admin::get_instance();
}
