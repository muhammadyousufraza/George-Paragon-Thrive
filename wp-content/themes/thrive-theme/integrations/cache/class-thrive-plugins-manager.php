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
 * Class Thrive_Plugins_Manager
 */
class Thrive_Plugins_Manager {

	/**
	 * All the cache plugins compatible with TTB
	 */
	const CACHE_PLUGINS
		= [
			'wp-rocket',
			'sg-optimizer',
			'fastest-cache',
			'total-cache',
		];

	/**
	 * Plugins for image optimization
	 *
	 * @var array pairs identifier => plugin data
	 */
	public static $image_optimization = [
		'optimole'  => [
			'slug'              => 'optimole-wp/optimole-wp.php',
			'logo'              => 'logo-optimole.png',
			'settings_redirect' => 'upload.php?page=optimole',
		],
		'smush'     => [
			'slug'   => 'wp-smushit/wp-smush.php',
			'logo'   => 'logo-smush.png',
			'status' => [
				'registered' => true,
				'configured' => true,
			],
		],
		'smush_pro' => [
			'slug'   => 'wp-smush-pro/wp-smush.php',
			'logo'   => 'logo-smush-pro.png',
			'status' => [
				'registered' => true,
				'configured' => true,
			],
		],
	];

	/**
	 * Return all the caching plugins and their related data
	 *
	 * @return array
	 */
	public static function get_cache_plugins() {
		$all = [];

		foreach ( static::CACHE_PLUGINS as $plugin ) {
			$instance = static::plugin_factory( $plugin );
			$all[]    = ( $instance && method_exists( $instance, 'get_info' ) ) ? $instance->get_info() : [];
		}

		return $all;
	}

	/**
	 * Install a plugin based on it's slug
	 * inspiration from wp_ajax_install_plugin();
	 *
	 * @param string $slug
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function install( $slug ) {
		if ( empty( $slug ) ) {
			throw new Exception( __( 'No plugin specified.', 'thrive-theme' ) );
		}

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		include_once( ABSPATH . 'wp-admin/includes/file.php' );

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => sanitize_key( wp_unslash( $slug ) ),
				'fields' => [
					'sections' => false,
				],
			)
		);

		if ( is_wp_error( $api ) ) {
			throw new Exception( $api->get_error_message() );
		}

		$response['pluginName'] = $api->name;

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );
		$error    = [];

		if ( is_wp_error( $result ) ) {
			$error['errorMessage'] = $result->get_error_message();
		} elseif ( is_wp_error( $skin->result ) ) {
			$error['errorMessage'] = $skin->result->get_error_message();
		} elseif ( $skin->get_errors()->has_errors() ) {
			$error['errorMessage'] = $skin->get_error_messages();
		} elseif ( $result === null ) {
			global $wp_filesystem;

			$error['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}
		}

		if ( ! empty( $error ) ) {
			throw new Exception( $error['errorMessage'] );
		}

		return [
			'slug'      => $slug,
			'installed' => true,
		];
	}

	/**
	 * Activate a plugin based on it's main file
	 *
	 * @param string $file
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function activate( $file ) {

		if ( ! is_plugin_inactive( $file ) ) {
			throw new Exception( __( 'The plugin is already active', 'thrive-theme' ) );
		}

		$result = activate_plugin( $file, '', is_network_admin() );

		if ( is_wp_error( $result ) ) {
			throw new Exception( $result->get_error_message() );
		}

		return [
			'file'   => $file,
			'active' => true,
		];
	}

	/**
	 * Deactivate a plugin
	 *
	 * @param string $file
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function deactivate( $file ) {
		/* Deactivate th plugin */
		deactivate_plugins( $file, true );

		/* Small check to be sure the plugin was successfully deactivated */
		$plugins = get_option( 'active_plugins', [] );
		if ( in_array( $file, $plugins, true ) ) {
			throw new Exception( __( 'The plugin could not be deactivated', 'thrive-theme' ) );
		}

		return $file;
	}

	/**
	 * Factory function that returns a plugin class instance
	 *
	 * @param string $tag
	 *
	 * @return mixed
	 */
	public static function plugin_factory( $tag ) {
		$fn = 'thrive_' . str_replace( '-', '_', ucwords( $tag, '-' ) );

		return function_exists( $fn ) ? $fn() : null;
	}

	/**
	 * Check if a plugin is installed (not necessarily activated)
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public static function is_installed( $slug ) {
		$parts = explode( '/', $slug );

		return is_dir( trailingslashit( WP_PLUGIN_DIR ) . $parts[0] );
	}

	/**
	 * Get data about image optimization plugins used by the website
	 *
	 * @return array
	 */
	public static function get_image_optimization_plugins() {
		$plugins = [];
		foreach ( static::$image_optimization as $key => $data ) {
			$plugins[ $key ] = array_merge(
				$data,
				[
					'key'               => $key,
					'active'            => is_plugin_active( $data['slug'] ),
					'installed'         => static::is_installed( $data['slug'] ),
					'settings_redirect' => isset( $data['settings_redirect'] ) ? admin_url( $data['settings_redirect'] ) : '',
				]
			);
		}

		/* handle the wp_smush / wp_smush_pro case - when wp_smush_pro is active, ignore wp_smush, and treat smush_pro as "regular" smush */
		if ( $plugins['smush_pro']['active'] ) {
			$plugins['smush'] = $plugins['smush_pro'];
			unset( $plugins['smush_pro'] );
		}

		/* Special case for optimole :- check to see if (1) registered with an api key and (2) configured with "Thrive Optimal Settings" */
		$optimole                      = thrive_optimole_wp();
		$plugins['optimole']['status'] = [
			'registered' => $optimole->is_registered(),
			'configured' => $optimole->is_configured(),
		];

		return $plugins;
	}
}
