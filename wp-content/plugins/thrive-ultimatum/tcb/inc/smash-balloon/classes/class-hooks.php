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
 * Class Hooks
 *
 * @package TCB\Integrations\SmashBalloon
 */
class Hooks {
	public static function add() {
		static::add_actions();
		static::add_filters();
	}

	public static function add_actions() {
		add_action( 'plugins_loaded', [ __CLASS__, 'tcb_insta_feeds' ] );
		add_action( 'tcb_output_components', [ __CLASS__, 'tcb_output_components' ] );
		add_action( 'wp_print_footer_scripts', [ __CLASS__, 'wp_print_footer_scripts' ], 9 );
		add_action( 'tcb_main_frame_enqueue', [ __CLASS__, 'tcb_main_frame_enqueue' ] );
		add_action( 'rest_api_init', [ __CLASS__, 'rest_api_init' ] );
		add_action( 'wp_head', [ __CLASS__, 'tcb_main_css_enqueue' ] );
	}

	public static function add_filters() {
		add_filter( 'tcb_element_instances', [ __CLASS__, 'tcb_element_instances' ] );
		add_filter( 'tve_main_js_dependencies', [ __CLASS__, 'tve_main_js_dependencies' ] );
		add_filter( 'thrive_theme_should_render_shortcode', [ __CLASS__, 'thrive_theme_should_render_shortcode' ], 10, 2 );
	}

	/**
	 * Include rendered element so we can use it for drag n drop
	 *
	 * @return void
	 */
	public static function wp_print_footer_scripts() {
		if ( TCB_Editor()->is_inner_frame() ) {
			$templates = tve_dash_get_backbone_templates( TVE_TCB_ROOT_PATH . 'inc/smash-balloon/views/backbone' );

			tve_dash_output_backbone_templates( $templates, 'tve-smash-balloon-' );
		}
	}

	/**
	 * Add our js file as a dependency for the main file so it loads before
	 *
	 * @param $dependencies
	 *
	 * @return mixed
	 */
	public static function tve_main_js_dependencies( $dependencies ) {

		$dependencies[] = 'smash-balloon-editor-main';

		return $dependencies;
	}

	/**
	 * Load our css file(s)
	 *
	 * @return void
	 */
	public static function tcb_main_css_enqueue() {
		wp_enqueue_style( 'smash-balloon-editor-css', tve_editor_url() . '/inc/smash-balloon/css/main.css', false, '', '' );
	}

	/**
	 * Load our js files
	 *
	 * @return void
	 */
	public static function tcb_main_frame_enqueue() {
		wp_enqueue_script( 'smash-balloon-editor-main', tve_editor_url() . '/inc/smash-balloon/js/hooks.js' );
	}

	/**
	 * Allow the shortcode to be rendered in the editor
	 *
	 * @param $should_render
	 * @param $shortcode_tag
	 *
	 * @return mixed|true
	 */
	public static function thrive_theme_should_render_shortcode( $should_render, $shortcode_tag ) {
		switch ( $shortcode_tag ) {
			case 'custom-facebook-feed':
			case 'instagram-feed':
			case 'custom-twitter-feeds':
			case 'youtube-feed':
			case 'tiktok-feeds':
			case 'social-wall':
				$should_render = true;
				break;

			default:
				// do nothing
				break;
		}

		return $should_render;
	}

	/**
	 * @param $instances
	 *
	 * @return mixed
	 */
	public static function tcb_element_instances( $instances ) {
		$element = require_once __DIR__ . '/class-element.php';

		$instances[ $element->tag() ] = $element;

		return $instances;
	}

	/**
	 * Include editor components
	 */
	public static function tcb_output_components() {
		$path  = TVE_TCB_ROOT_PATH . 'inc/smash-balloon/views/components/';
		$files = array_diff( scandir( $path ), [ '.', '..' ] );

		foreach ( $files as $file ) {

			include $path . $file;
		}
	}

	/**
	 * Initialize the rest api class
	 */
	public static function rest_api_init() {
		require_once TVE_TCB_ROOT_PATH . 'inc/smash-balloon/classes/class-rest-api.php';

		Rest_Api::register_routes();
	}
}
