<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\SmashBalloon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TCB\Integrations\SmashBalloon
 */
class Main {
	const IDENTIFIER = '.tcb-smash-balloon';

	public static function init() {
		if ( static::active() ) {
			static::includes();
			Hooks::add();
		}

		// Load Smash Balloon missing scripts
		$plugins = static::sb_plugins_info();

		if ( $plugins['instagram']['is_active'] ) {
			sb_instagram_scripts_enqueue( true );
		}

		if ( $plugins['youtube']['is_active'] ) {
			do_action('sby_enqueue_scripts', true);
		}

		if ( $plugins['tiktok']['is_active'] ) {
			do_action('sbtt_enqueue_scripts', true);
		}

		if ( $plugins['social-wall']['is_active'] ) {
			sbsw_scripts_enqueue( true );
		}
	}

	/**
	 * @param string $subpath
	 *
	 * @return string
	 */
	public static function get_integration_path( $subpath = '' ) {
		return TVE_TCB_ROOT_PATH . 'inc/smash-balloon/' . $subpath;
	}

	public static function includes() {
		$integration_path = static::get_integration_path();
		require_once $integration_path . 'classes/class-hooks.php';
	}

	/**
	 * Check if the plugin is active
	 *
	 * @return bool
	 */
	public static function active() {
		$active_plugins = 0;

		foreach ( static::sb_plugins_info() as $key => $plugin ) {
			if ( $plugin['is_active'] ) {
				$active_plugins += 1;
			}
		}

		return $active_plugins > 0 ? true : false;
	}

	/**
	 * List all Smash Balloon plugins
	 *
	 * @return array
	 */
	public static function sb_plugins_list() {
		$list = array(
			'facebook'  => array(
				'name'  => __( 'Custom Facebook Feed', 'thrive-cb' ),
				'value' => 'custom-facebook-feed',
				'path'  => array(
					'free' => 'custom-facebook-feed/custom-facebook-feed.php',
					'pro'  => 'custom-facebook-feed-pro/custom-facebook-feed.php'
				),
			),
			'instagram' => array(
				'name'  => __( 'Instagram Feed', 'thrive-cb' ),
				'value' => 'instagram-feed',
				'path'  => array(
					'free' => 'instagram-feed/instagram-feed.php',
					'pro'  => 'instagram-feed-pro/instagram-feed.php'
				),
			),
			'twitter'   => array(
				'name'  => __( 'Custom Twitter Feeds', 'thrive-cb' ),
				'value' => 'custom-twitter-feeds',
				'path'  => array(
					'free' => 'custom-twitter-feeds/custom-twitter-feed.php',
					'pro'  => 'custom-twitter-feeds-pro/custom-twitter-feed.php'
				),
			),
			'youtube'   => array(
				'name'  => __( 'Feeds for YouTube', 'thrive-cb' ),
				'value' => 'youtube-feed',
				'path'  => array(
					'free' => 'feeds-for-youtube/youtube-feed.php',
					'pro'  => 'youtube-feed-pro/youtube-feed.php'
				),
			),
			'tiktok'    => array(
				'name'  => __( 'TikTok Feeds', 'thrive-cb' ),
				'value' => 'tiktok-feeds',
				'path'  => array(
					'free' => 'tiktok-feeds/tiktok-feeds.php',
					'pro'  => 'tiktok-feeds-pro/tiktok-feeds-pro.php'
				),
			),
			'social-wall'    => array(
				'name'  => __( 'Social Wall', 'thrive-cb' ),
				'value' => 'social-wall',
				'path'  => array(
					'free' => '',
					'pro'  => 'social-wall/social-wall.php'
				),
			),
		);

		return $list;
	}

	/**
	 * Get active plugins of Smash Balloon
	 *
	 * @return array
	 */
	public static function sb_plugins_info() {
		// WordPress core list of installed plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$list = [];
		$installed_plugins = get_plugins();

		foreach ( static::sb_plugins_list() as $plugin_key => $plugin_info ) {
			$name  = $plugin_info['name'];
			$value = $plugin_info['value'];
			$path  = $plugin_info['path'];

			$is_installed = isset( $installed_plugins[ $path['pro'] ] ) || isset( $installed_plugins[ $path['free'] ] ) ? true : false;
			$is_active = is_plugin_active( $path['pro'] ) || is_plugin_active( $path['free'] ) ? true : false;

			$list = array_merge( $list, array(
				$plugin_key => array(
					'name'         => $name,
					'value'        => $value,
					'is_installed' => $is_installed,
					'is_active'    => $is_active
				)
			) );
		}

		return $list;
		// Structure example:
		// Array( [plugin] => Array( [name] => Plugin Name, [value] => plugin-key, [is_installed] => 1, [is_active] => 0 ) )
	}

	/**
	 * List available plugins
	 *
	 * @return array
	 */
	public static function sb_available_plugins() {
		$list = [];
		$plugins = Main::sb_plugins_info();

		// Placeholder
		// Fix: Add empty value first in list to solve the control update/change funcs.
		$list[] = [
			'name'  => __( 'Select a plugin', 'thrive-cb' ),
			'value' => '',
		];

		foreach ( $plugins as $key => $plugin ) {
			if ( $plugin['is_active'] === TRUE ) {
				$list[] = [
					'name'  => $plugin['name'],
					'value' => $plugin['value']
				];
			}
		}

		return $list;
	}

	/**
	 * List available feeds
	 *
	 * @return array|bool
	 */
	public static function sb_available_feeds( $plugin_name ) {
		$args = array( 'page' => 1 );
		$data = '';
		$list = [];

		// List SB plugins info
		$plugins = Main::sb_plugins_info();

		switch ( $plugin_name ) {
			case 'facebook':
				$data = \CustomFacebookFeed\Builder\CFF_Db::feeds_query( $args );
				break;

			case 'instagram':
				$data = \InstagramFeed\Builder\SBI_Db::feeds_query( $args );
				break;

			case 'twitter':
				$data = \TwitterFeed\Builder\CTF_Db::feeds_query( $args );
				break;

			case 'youtube':
				$data = \SmashBalloon\YouTubeFeed\Builder\SBY_Db::feeds_query( $args );
				break;

			case 'tiktok':
				$src  = new \SmashBalloon\TikTokFeeds\Common\Database\FeedsTable;
				$data = $src->get_feeds( $args = array());
				break;

			case 'social-wall':
				$data = \SB\SocialWall\Database::query_feeds( $args );
				break;

			default:
				// Do nothing.
				break;
		}

		// Placeholder
		// Fix: Add empty value first in list to solve the control update/change funcs.
		$list[] = [
			'name'  => __( 'Select a feed', 'thrive-cb' ),
			'value' => '',
		];

		foreach ( $plugins as $key => $plugin ) {
			if ( $plugin_name === $key && $plugin['is_active'] ) {
				foreach ( $data as $feed ) {
					$list[] = [
						'name'  => $feed['feed_name'],
						'value' => $feed['id'],
					];
				}
			}
		}

		return '' !== $data ? $list : false;
	}

	/**
	 * Render shortcode
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function render( $attr = [], $content = '' ) {
		if ( ! is_array( $attr ) ) {
			$attr = [];
		}

		$attr = array_map( static function ( $v ) {
			return str_replace( [ '|{|', '|}|' ], [ '[', ']' ], esc_attr( $v ) );
		}, $attr );

		/* Ensure default values */
		$attr = array_merge( [
			'data-type' => '',
			'data-feed'   => '',
		], $attr );

		$classes = array( str_replace( '.', '', static::IDENTIFIER ), THRIVE_WRAPPER_CLASS );

		if ( empty( $content ) ) {
			$content .= '<div class="tve-smash-balloon">';
				$content .= '<div class="tve-smash-balloon__inner">';
					$content .= 'Choose your feed type and feed';
				$content .= '</div>';
			$content .= '</div>';
		}

		return \TCB_Utils::wrap_content( $content, 'div', '', $classes, $attr );
	}
}
