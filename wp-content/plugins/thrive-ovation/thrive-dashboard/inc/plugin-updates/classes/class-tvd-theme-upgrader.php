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
 * Theme Upgrader
 *
 * Used to upgrade Thrive Theme to the latest version
 */
class TVD_Theme_Upgrader extends Theme_Upgrader {
	/**
	 * Returns the download link from where the plugin should be downloaded
	 *
	 * @return string|false
	 */
	private function get_download_link() {

		if ( ! function_exists( 'plugins_api' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/theme.php' );
		}

		$theme_information = themes_api( 'theme_information', array( 'slug' => $this->skin->theme ) );

		return filter_var( $theme_information->download_link, FILTER_VALIDATE_URL );
	}

	/**
	 * @param string $theme
	 * @param array  $args
	 *
	 * @return array|bool|WP_Error
	 */
	public function get_latest_version( $theme, $args = array() ) {

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

		add_filter( 'upgrader_pre_install', array( $this, 'current_before' ), 10, 2 );
		add_filter( 'upgrader_post_install', array( $this, 'current_after' ), 10, 2 );
		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ), 10, 4 );

		$this->run( array(
			'package'           => $url,
			'destination'       => get_theme_root(),
			'clear_destination' => true,
			'clear_working'     => true,
			'hook_extra'        => array(
				'theme'  => $theme,
				'type'   => 'theme',
				'action' => 'update',
			),
		) );

		remove_filter( 'upgrader_pre_install', array( $this, 'current_before' ) );
		remove_filter( 'upgrader_post_install', array( $this, 'current_after' ) );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ) );

		if ( ! $this->result || is_wp_error( $this->result ) ) {
			return $this->result;
		}

		// Force refresh of theme update information.
		wp_clean_themes_cache( $parsed_args['clear_update_cache'] );

		return true;
	}
}
