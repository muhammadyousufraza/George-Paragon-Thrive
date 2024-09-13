<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

/**
 * Plugin Upgrader
 *
 * Used to upgrade a Thrive Plugin to the latest version
 */
class TVD_Plugin_Upgrader extends Plugin_Upgrader {

	/**
	 * Returns the download link from where the plugin should be downloaded
	 *
	 * @return string|false
	 */
	private function get_download_link() {

		if ( ! function_exists( 'plugins_api' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}

		$plugin_information = plugins_api( 'plugin_information', array( 'slug' => $this->skin->plugin ) );

		if ( $plugin_information instanceof WP_Error ) {
			return false;
		}

		return filter_var( $plugin_information->download_link, FILTER_VALIDATE_URL );
	}

	/**
	 * Downloads the latest Thrive Plugin version.
	 *
	 * @param string $plugin              Path to the plugin file relative to the plugins directory.
	 * @param array  $args                {
	 *                                    Optional. Other arguments for upgrading a plugin package. Default empty array.
	 *
	 * @type bool    $clear_update_cache  Whether to clear the plugin updates cache if successful.
	 *                                    Default true.
	 *                                    }
	 *
	 * @return array|bool|\WP_Error
	 */
	public function get_latest_version( $plugin, $args = array() ) {

		$defaults    = array(
			'clear_update_cache' => true,
		);
		$parsed_args = wp_parse_args( $args, $defaults );

		$this->init();
		$this->upgrade_strings();

		/**
		 * Added some custom strings that will replace the default update ones
		 */
		$this->strings['downloading_package'] = 'Downloading update from Thrive repositories';

		$url = $this->get_download_link();

		if ( empty( $url ) ) {
			return false;
		}

		add_filter( 'upgrader_pre_install', array( $this, 'deactivate_plugin_before_upgrade' ), 10, 2 );
		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_plugin' ), 10, 4 );

		$this->run( array(
			'package'           => $url,
			'destination'       => WP_PLUGIN_DIR,
			'clear_destination' => true,
			'clear_working'     => true,
			'hook_extra'        => array(
				'plugin' => $plugin,
				'type'   => 'plugin',
				'action' => 'update',
			),
		) );

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter( 'upgrader_pre_install', array( $this, 'deactivate_plugin_before_upgrade' ) );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_plugin' ) );

		if ( ! $this->result || is_wp_error( $this->result ) ) {
			return $this->result;
		}

		// Force refresh of plugin update information.
		wp_clean_plugins_cache( $parsed_args['clear_update_cache'] );

		return true;
	}
}
